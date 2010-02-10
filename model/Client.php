<?php

include_once 'model/Issue.php';

class Client extends Bindable 
{
    public $id;
    public $title;
    public $description;
    
    public $updated;
    public $created;
    public $creator;
	public $modifier;
    
    public $billingaddress;
    public $postaladdress;
    public $relationship = "Other";
    public $website;
    public $email;
    public $phone;
    public $fax;
    
    public $deleted = 0;

    public $constraints;
    public $requiredFields = array('title');
    public $searchableFields = array('title', 'description', 'postaladdress', 'billingaddress', 'relationship', 'deleted');
    
    /**
     * The project service
     * @var unmapped
     */
    public $projectService;
    
    /**
     * IssueService
     *
     * @var unmapped
     */
    public $issueService;
    
    public function __construct()
    {
        $this->constraints['relationship'] = new CVLValidator(array("Other", "Partner", "Lead", "Opportunity", "Customer", "Dead", "Supplier", "Press", "Recruitment Agent"));
        $this->created = date('Y-m-d H:i:s', time());
    }
    
    /**
     * Gets all the projects for this client
     */
    public function getProjects()
    {
    	return $this->projectService->getProjectsForClient($this);
    }
    
    /**
     * Get all the issues for a given client
     */
    public function getIssues()
    {
    	return $this->issueService->getIssues(array('issue.clientid=' => $this->id, 'status <> '=> Issue::STATUS_CLOSED));
    }
}

/**
 * Version class for versions
 */
class ClientVersion extends Client
{
	/**
	 * The original record's ID
	 *
	 * @var int
	 */
	public $recordid;
	public $validfrom;
	public $label;

	/**
     * Gets all the projects for this client
     */
    public function getProjects()
    {
    	return array();
    }

    /**
     * Get all the issues for a given client
     */
    public function getIssues()
    {
    	return array();
    }

	public function created() {}
	public function update() {}
}
?>