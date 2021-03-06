<?php

// TODO: bring routing style up to par with latest conventions 

namespace Slate\Courses;

use SpreadSheetWriter;
use Emergence\CMS\BlogRequestHandler;
use Emergence\CMS\BlogPost;
use Slate\Term;
use Slate\Courses\SectionParticipant;

class SectionsRequestHandler extends \RecordsRequestHandler
{
    // RecordsRequestHandler config
    static public $recordClass = 'Slate\\Courses\\Section';
    static public $accountLevelBrowse = false;
    static public $browseOrder = 'Code';

    static public function handleRecordsRequest($action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'addParticipants':
                return static::handleParticipantAddRequest();
            default:
                return parent::handleRecordsRequest($action);
        }
    }
    static public function handleRecordRequest(\ActiveRecord $Section, $action = false)
    {
        if (!static::checkReadAccess($Section)) {
            return static::throwUnauthorizedError();
        }

        switch ($action ? $action : $action = static::shiftPath()) {
            case 'roster':
                return static::handleRosterRequest($Section);
            case 'roster-download':
                $GLOBALS['Session']->requireAccountLevel('Staff');

                $csvWriter = new SpreadSheetWriter(array(
                    'filename' => $Section->Code.'_roster'
                    ,'autoHeader' => true
                ));

                $students = SectionParticipant::getAllByQuery('SELECT s.* FROM `%s` s INNER JOIN people p ON (p.ID = s.PersonID) WHERE CourseSectionID=%u AND Role="Student" ORDER BY p.LastName,p.FirstName', array(SectionParticipant::$tableName, $Section->ID));

                foreach ($students as $student) {
                    $csvWriter->writeRow(array(
                        'LastName' => $student->Person->LastName
                        ,'FirstName' => $student->Person->FirstName
                        ,'Username' => $student->Person->Username
                        ,'Email' => $student->Person->Email
                        ,'Student ID' => $student->Person->StudentNumber
                        ,'Advisor' => $student->Person->Advisor->FullName
                        ,'GraduationYear' => $student->Person->GraduationYear
                    ));
                }

                $csvWriter->close();
                exit();
            case 'post':
                $GLOBALS['Session']->requireAuthentication();
                return BlogRequestHandler::handleCreateRequest(BlogPost::create(array(
                    'Class' => 'Emergence\CMS\BlogPost'
                    ,'Context' => $Section
                )));
            case 'removeParticipant':
                return static::handleParticipantRemovalRequest($Section);
            case 'rss':
                return static::getBlogsByCourseSection($Section);
            default:
                return parent::handleRecordRequest($Section, $action);
        }
    }

    static public function handleRosterRequest(CourseSection $Section)
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        return static::respond('sectionRoster', array(
            'success' => true
            ,'data' => $Section->Participants
        ));
    }

    static public function getBlogsByCourseSection(CourseSection $Section)
    {
        static::$responseMode = 'xml';

        $blogs = BlogPost::getAllByWhere(array(
            'ContextClass' => 'Slate\\Courses\\Section'
            ,'ContextID' => $Section->ID
        ));

        return static::respond('rss',array(
            'success' => true
            ,'data' => $blogs
            ,'Title' => 'SLA Class ' . $Section->Title . ' Blog Posts'
            ,'Link' => 'http://'.$_SERVER['HTTP_HOST'].'/sections/' . $Section->Handle
        ));
    }

    static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
    {
        if (empty($_REQUEST['AllCourses']) && $GLOBALS['Session']->PersonID) {
            $conditions[] = 'ID IN (SELECT CourseSectionID FROM course_section_participants WHERE PersonID = '.$GLOBALS['Session']->PersonID.')';
        }

        if (!empty($_REQUEST['TermID'])) {
            $term = Term::getByWhere(array('ID' => $_REQUEST['TermID']));
            //MICS::dump($term, 'this',true);
            $concurrentTerms = $term->getConcurrentTermIDs();
            $containedTerms = $term->getContainedTermIDs();
            $termIDs = array_unique(array_merge($concurrentTerms, $containedTerms));

            $conditions[] = sprintf('TermID IN (%s)',join(',',$termIDs));
        }

        if (!empty($_REQUEST['start']) && !empty($_REQUEST['limit'])) {
            $options['offset'] = $_REQUEST['start'];
            $options['limit'] = $_REQUEST['limit'];
        }

        return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
    }

    static public function handleParticipantRemovalRequest($Section)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['PersonID']) {
            $Participant = SectionParticipant::getByWhere(array(
                'CourseSectionID' => $Section->ID
                ,'PersonID' => $_POST['PersonID']
            ));

            $Participant->destroy();

            return static::respond('sections', array(
                'data' => $Participant
                ,'success' => true
            ));
        }

    }

    static public function handleParticipantAddRequest()
    {
        $courses = array();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['PersonID'] && $_POST['SectionIDs']) {
            $courses = Section::assignCourses($_POST['PersonID'], $_POST['SectionIDs'], $_POST['Role']);

            return static::respond('sections', array(
                'data' => $courses
                ,'success' => true
            ));
        }
    }
}