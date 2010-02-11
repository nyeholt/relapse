<?php

class Contact extends MappedObject
{
    public $firstname;
    public $lastname;
    public $title;
    public $department;

    public $postaladdress;
    public $businessaddress;
    public $switchboard;
    public $directline;
    public $fax;
    public $mobile;
    public $email;
    public $altemail;
    public $status;
    
    public $clientid;
    
    public $constraints = array('email' => 'Zend_Validate_EmailAddress', 'altemail'=>'Zend_Validate_EmailAddress');
    public $requiredFields = array('firstname');
    public $searchableFields = array('firstname', 'lastname', 'postaladdress', 'businessaddress', 'email', 'mobile');

    public function __construct()
    {
        $this->created = date('Y-m-d H:i:s');
        $this->updated = date('Y-m-d H:i:s');
        $this->constraints['__this'] = new UniqueValueValidator('email');
    }
}
?>