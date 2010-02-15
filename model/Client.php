<?php

include_once 'model/Issue.php';

class Client extends MappedObject
{
    public $title;
    public $description;
    
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

	/**
	 * @var unmapped
	 */
	public $versioningService;
    
    public function __construct()
    {
        $this->constraints['relationship'] = new CVLValidator(array("Other", "Partner", "Lead", "Opportunity", "Customer", "Dead", "Supplier", "Press", "Recruitment Agent"));
        $this->created = date('Y-m-d H:i:s', time());
    }

	public function created()
	{
		$this->versioningService->createVersion($this);
	}

    /**
     * Gets all the projects for this client
     */
    public function getProjects()
    {
    	return $this->projectService->getProjectsForClient($this->me());
    }
    
    /**
     * Get all the issues for a given client
     */
    public function getIssues()
    {
    	return $this->issueService->getIssues(array('issue.clientid=' => $this->me()->id, 'status <> '=> Issue::STATUS_CLOSED));
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
	public $versioncreated;
	public $validfrom;
	public $label;

	public function me()
	{
		$dbService = za()->getService('DbService');
		$type = substr(get_class($this), 0, strrpos(get_class($this), 'Version'));
		return $dbService->getById($this->recordid, $type);
	}

	public function created() {}
	public function update() {}
}
?>