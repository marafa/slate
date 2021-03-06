<?php

namespace Emergence\People\ContactPoint;

class Link extends AbstractPoint
{
    static public $defaultLabel = 'Link';

    static public $templates = array(
        'Website' => array(
            'class' => __CLASS__
            ,'placeholder' => 'http://example.com'
            ,'pattern' => '/^https?:\\/\\/.*$/i'
        )
    );
    
    public $url;

    public function loadString($string)
    {
        $this->url = (string)$string;
    }
    
    public function toString()
    {
        return $this->url;
    }
    
    public function toHTML()
    {
        return sprintf(
            '<a class="contact-link contact-url" href="%s">%s</a>'
            ,htmlspecialchars($this->url)
            ,htmlspecialchars($this->toString())
        );
    }
    
    public function serialize()
    {
        return $this->url;
    }
    
    public function unserialize($serialized)
    {
        $this->url = $serialized;
    }

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        if ($errors = \Validators\URL::isInvalid($this->url)) {
            $this->_validator->addError('url', 'URL invalid:'.reset($errors));
        }
        
        // save results
        return $this->finishValidation();
    }
}