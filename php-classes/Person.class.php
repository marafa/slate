<?php

class Person extends VersionedRecord
{
    // support subclassing
    static public $rootClass = __CLASS__;
    static public $defaultClass = __CLASS__;
    static public $subClasses = array(__CLASS__);

    // VersionedRecord configuration
    static public $historyTable = 'history_people';

    // ActiveRecord configuration
    static public $tableName = 'people';
    static public $singularNoun = 'person';
    static public $pluralNoun = 'people';
    static public $collectionRoute = '/people';

    static public $fields = array(
        'FirstName' => array(
            'includeInSummary' => true
        )
        ,'LastName' => array(
            'includeInSummary' => true
        )
        ,'MiddleName' => array(
            'notnull' => false
        )
        ,'Gender' => array(
            'type' => 'enum'
            ,'values' => array('Male','Female')
            ,'notnull' => false
        )
        ,'BirthDate' => array(
            'type' => 'date'
            ,'notnull' => false
            ,'accountLevelEnumerate' => 'Staff'
        )
        ,'Location' => array(
            'notnull' => false
        )
        ,'About' => array(
            'type' => 'clob'
            ,'notnull' => false
        )
        ,'PrimaryPhotoID' => array(
            'type' => 'uint'
            ,'notnull' => false
        )
        ,'PrimaryEmailID' => array(
            'type' => 'uint'
            ,'notnull' => false
            ,'accountLevelEnumerate' => 'Staff'
        )
        ,'PrimaryPhoneID' => array(
            'type' => 'uint'
            ,'notnull' => false
            ,'accountLevelEnumerate' => 'Staff'
        )
        ,'PrimaryPostalID' => array(
            'type' => 'uint'
            ,'notnull' => false
            ,'accountLevelEnumerate' => 'Staff'
        )
    );

    static public $relationships = array(
        'GroupMemberships' => array(
            'type' => 'one-many'
            ,'class' => 'GroupMember'
            ,'indexField' => 'GroupID'
            ,'foreign' => 'PersonID'
        )
        ,'Notes' => array(
            'type' => 'context-children'
            ,'class' => 'Note'
            ,'contextClass' => 'Person'
            ,'order' => array('ID' => 'DESC')
        )
        ,'Groups' => array(
            'type' => 'many-many'
            ,'class' => 'Group'
            ,'linkClass' => 'GroupMember'
            ,'linkLocal' => 'PersonID'
            ,'linkForeign' => 'GroupID'
        )
        ,'PrimaryPhoto' => array(
            'type' => 'one-one'
            ,'class' => 'PhotoMedia'
            ,'local' => 'PrimaryPhotoID'
        )
        ,'Photos' => array(
            'type' => 'context-children'
            ,'class' => 'PhotoMedia'
            ,'contextClass' => __CLASS__
        )
        ,'Comments' => array(
            'type' => 'context-children'
            ,'class' => 'Comment'
            ,'contextClass' => __CLASS__
            ,'order' => array('ID' => 'DESC')
        )
        ,'PrimaryEmail' => array(
            'type' => 'one-one'
            ,'class' => '\\Emergence\\People\\ContactPoint\\Email'
        )
        ,'PrimaryPhone' => array(
            'type' => 'one-one'
            ,'class' => '\\Emergence\\People\\ContactPoint\\Phone'
        )
        ,'PrimaryPostal' => array(
            'type' => 'one-one'
            ,'class' => '\\Emergence\\People\\ContactPoint\\Postal'
        )
        ,'ContactPoints' => array(
            'type' => 'one-many'
            ,'class' => 'ContactPoint'
        )
        ,'Relationships' => array(
            'type' => 'one-many'
            ,'class' => 'Emergence\\People\\Relationship'
        )
    );

    static public $dynamicFields = array(
        'PrimaryEmail' => array(
            'accountLevelEnumerate' => 'Staff'
        )
        ,'PrimaryPhone' => array(
            'accountLevelEnumerate' => 'Staff'
        )
        ,'PrimaryPostal' => array(
            'accountLevelEnumerate' => 'Staff'
        )
        ,'PrimaryPhoto'
        ,'Photos'
        ,'groupIDs' => array(
            'method' => 'getGroupIDs'
        )
    );

    static public $searchConditions = array(
        'FirstName' => array(
            'qualifiers' => array('any','name','fname','firstname','first')
            ,'points' => 2
            ,'sql' => 'FirstName LIKE "%%%s%%"'
        )
        ,'LastName' => array(
            'qualifiers' => array('any','name','lname','lastname','last')
            ,'points' => 2
            ,'sql' => 'LastName LIKE "%%%s%%"'
        )
        ,'Username' => array(
            'qualifiers' => array('any','username','uname','user')
            ,'points' => 2
            ,'sql' => 'Username LIKE "%%%s%%"'
        )
        ,'Gender' => array(
            'qualifiers' => array('gender','sex')
            ,'points' => 2
            ,'sql' => 'Gender LIKE "%s"'
        )
        ,'Class' => array(
            'qualifiers' => array('class')
            ,'points' => 2
            ,'sql' => 'Class LIKE "%%%s%%"'
        )
        ,'AccountLevel' => array(
            'qualifiers' => array('accountlevel')
            ,'points' => 2
            ,'sql' => 'AccountLevel LIKE "%%%s%%"'
        )
        ,'Group' => array(
            'qualifiers' => array('group')
            ,'points' => 1
            ,'join' => array(
                'className' => 'GroupMember'
                ,'aliasName' => 'GroupMember'
                ,'localField' => 'ID'
                ,'foreignField' => 'PersonID'
            )
            ,'callback' => 'getGroupConditions'
        )
        ,'RelatedTo' => array(
    		'qualifiers' => array('related-to')
			,'points' => 1
			,'sql' => 'ID IN (SELECT IF(RelatedPerson.ID = relationships.RelatedPersonID, relationships.PersonID, relationships.RelatedPersonID) FROM people RelatedPerson RIGHT JOIN relationships ON (RelatedPerson.ID IN (relationships.PersonID, relationships.RelatedPersonID)) WHERE RelatedPerson.Username = "%s")'
		)
        ,'RelatedToID' => array(
    		'qualifiers' => array('related-to-id')
			,'points' => 1
			,'sql' => 'ID IN (SELECT IF(RelatedPerson.ID = relationships.RelatedPersonID, relationships.PersonID, relationships.RelatedPersonID) FROM people RelatedPerson RIGHT JOIN relationships ON (RelatedPerson.ID IN (relationships.PersonID, relationships.RelatedPersonID)) WHERE RelatedPerson.ID = %u)'
		)
    );

    // Person
    static public $requireGender = false;
    static public $requireBirthDate = false;
    static public $requireLocation = false;
    static public $requireAbout = false;


    public function getValue($name)
    {
        switch ($name) {
            case 'FullName':
                return $this->FirstName . ' ' . $this->LastName;
            case 'FirstInitial':
                return strtoupper(substr($this->FirstName, 0, 1));
            case 'LastInitial':
                return strtoupper(substr($this->LastName, 0, 1));
            case 'FirstNamePossessive':
                if (substr($this->FirstName, -1) == 's') {
                    return $this->FirstName . '\'';
                } else {
                    return $this->FirstName . '\'s';
                }
            case 'FullNamePossessive':
                $fullName = $this->FullName;

                if (substr($fullName, -1) == 's') {
                    return $fullName . '\'';
                } else {
                    return $fullName . '\'s';
                }
            case 'Email':
                \Emergence\Logger::general_warning('Deprecated: Read on Person(#{PersonID})->Email, use ->PrimaryEmail instead', array('PersonID' => $this->ID));

                return $this->PrimaryEmail ? (string)$this->PrimaryEmail : null;
            case 'EmailRecipient':
                return $this->PrimaryEmail ? sprintf('"%s" <%s>', $this->FullName, $this->PrimaryEmail) : null;
            default:
                return parent::getValue($name);
        }
    }
    
    public function setValue($name, $value)
    {
        switch ($name) {
            case 'Email':
                \Emergence\Logger::general_warning('Deprecated: Write on Person(#{PersonID})->Email, use ->PrimaryEmail = Email::fromString(...) instead', array('PersonID' => $this->ID));

                if (!$this->isPhantom) {
                    $Existing = \Emergence\People\ContactPoint\Email::getByString($value, array('PersonID' => $this->ID));
                }
                
                $this->PrimaryEmail = $Existing ? $Existing : \Emergence\People\ContactPoint\Email::fromString($value, $this, true);
                break;
            default:
                return parent::setValue($name, $value);
        }
    }

    static public function getByHandle($handle)
    {
        return User::getByHandle($handle);
    }

    static public function getByEmail($email)
    {
        $EmailPoint = \Emergence\People\ContactPoint\Email::getByString($email);
        return $EmailPoint ? $EmailPoint->Person : null;
    }

    static public function getByFullName($firstName, $lastName)
    {
        return static::getByWhere(array(
            'FirstName' => $firstName
            ,'LastName' => $lastName
        ));
    }

    static public function getOrCreateByFullName($firstName, $lastName, $save = false)
    {
        if ($Person = static::getByFullName($firstName, $lastName)) {
            return $Person;
        } else {
            return static::create(array(
                'FirstName' => $firstName
                ,'LastName' => $lastName
            ), $save);
        }
    }

    static public function parseFullName($fullName)
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        if (count($parts) != 2) {
            throw new Exception('Full name must contain a first and last name separated by a space');
        }

        return array(
            'FirstName' => $parts[0]
            ,'LastName' => $parts[1]
        );
    }

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        $this->_validator->validate(array(
            'field' => 'Class'
            ,'validator' => 'selection'
            ,'choices' => self::$subClasses
            ,'required' => false
        ));

        $this->_validator->validate(array(
            'field' => 'FirstName'
            ,'minlength' => 2
            ,'required' => true
            ,'errorMessage' => 'First name is required'
        ));

        $this->_validator->validate(array(
            'field' => 'LastName'
            ,'minlength' => 2
            ,'required' => true
            ,'errorMessage' => 'Last name is required'
        ));

        $this->_validator->validate(array(
            'field' => 'BirthDate'
            ,'validator' => 'date_ymd'
            ,'required' => static::$requireBirthDate
        ));

        $this->_validator->validate(array(
            'field' => 'Gender'
            ,'validator' => 'selection'
            ,'required' => static::$requireGender
            ,'choices' => self::$fields['Gender']['values']
        ));


        // investigate dirty PrimaryEmail/PrimaryEmailID
        if (($this->isFieldDirty('PrimaryEmailID') && $this->PrimaryEmailID) || (!$this->PrimaryEmailID && $this->PrimaryEmail)) {
            // check if repossessing another's email point
            if ($this->PrimaryEmail->PersonID && $this->PrimaryEmail->PersonID != $this->ID) {
                $this->_validator->addError('PrimaryEmailID', 'PrimaryEmail already belongs to another person');
            }
        }

        // investigate dirty PrimaryPhone/PrimaryPhoneID
        if (($this->isFieldDirty('PrimaryPhoneID') && $this->PrimaryPhoneID) || (!$this->PrimaryPhoneID && $this->PrimaryPhone)) {
            // check if repossessing another's email point
            if ($this->PrimaryPhone->PersonID && $this->PrimaryPhone->PersonID != $this->ID) {
                $this->_validator->addError('PrimaryPhoneID', 'PrimaryPhone already belongs to another person');
            }
        }

        // investigate dirty PrimaryPostal/PrimaryPostalID
        if (($this->isFieldDirty('PrimaryPostalID') && $this->PrimaryPostalID) || (!$this->PrimaryPostalID && $this->PrimaryPostal)) {
            // check if repossessing another's email point
            if ($this->PrimaryPostal->PersonID && $this->PrimaryPostal->PersonID != $this->ID) {
                $this->_validator->addError('PrimaryPostalID', 'PrimaryPostal already belongs to another person');
            }
        }


        // save results
        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        parent::save($deep);

        if ($this->isFieldDirty('PrimaryEmailID') && $this->PrimaryEmailID) {
            $this->PrimaryEmail->PersonID = $this->ID;
            $this->PrimaryEmail->save(false);
        }

        if ($this->isFieldDirty('PrimaryPhoneID') && $this->PrimaryPhoneID) {
            $this->PrimaryPhone->PersonID = $this->ID;
            $this->PrimaryPhone->save(false);
        }

        if ($this->isFieldDirty('PrimaryPostalID') && $this->PrimaryPostalID) {
            $this->PrimaryPostal->PersonID = $this->ID;
            $this->PrimaryPostal->save(false);
        }
    }

    static public function getGroupConditions($handle, $matchedCondition)
    {
        $group = Group::getByHandle($handle);

        if (!$group) {
            return false;
        }

        $containedGroups = DB::allRecords('SELECT ID FROM %s WHERE `Left` BETWEEN %u AND %u', array(
            Group::$tableName
            ,$group->Left
            ,$group->Right
        ));

        $containedGroups = array_map(function($group) {
            return $group['ID'];
        },$containedGroups);

        $condition = $matchedCondition['join']['aliasName'].'.GroupID'.' IN ('.implode(',',$containedGroups).')';

        return $condition;
    }

    public function getGroupIDs()
    {
        return array_map(function($Group){
            return $Group->ID;
        }, $this->Groups);
    }
}