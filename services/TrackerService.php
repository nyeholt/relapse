<?php

include_once 'model/TrackerEntry.php';

/**
 * Class used for tracking actions etc
 *
 */
class TrackerService 
{
    /**
     * The db service
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     * Track an action
     * 
     * Params is an array of 
     * 
     * array(
     * 'user' => 'username/uniqueid',
     * 'url' => 'urlbeingviewed',
     * 'actionname' => 'the name of the action'
     * 'actionid' => 'uniqueid for the actor in the action'
     * 'remoteip' => 'the remote ip'
     * )
     *
     * @param  array $params
     */
    public function trackAction($params)
    {
        $entry = new TrackerEntry();
        $entry->bind($params);
        
        $validator = new ModelValidator();
        if (!$validator->isValid($entry)) {
            throw new InvalidModelException($validator->getMessages());
        }
        
        // Save the entry
        $this->dbService->createObject($entry);        
    }
    
    /**
     * Track an action
     *
     * @param unknown_type $actionname
     * @param unknown_type $actionid
     * @param unknown_type $user
     * @param unknown_type $url
     */
    public function track($actionname, $actionid, $user=null, $url=null, $data=null)
    {
        $params = array();
        if ($user == null) {
            $params['user'] = za()->getUser()->getUsername();
        }
        if ($url == null) {
            $params['url'] = current_url();
        }
        
		$params['actionname'] = $actionname;
		$params['actionid'] = $actionid;
		$params['entrydata'] = $data;

		$this->trackAction($params);
    }
    
    public function getTotalEntries()
    {
        $select = $this->dbService->select();
        /* @var $select Zend_Db_Select */
		$select->from('trackerentry', new Zend_Db_Expr("count(*) as total"));
		
		$count = $this->dbService->fetchOne($select);

		return $count;
    }
    
    /**
     * Get tracker entries
     *
     * @param unknown_type $where
     * @return unknown
     */
    public function getEntries($where, $page=null, $number=null)
    {
        return $this->dbService->getObjects('TrackerEntry', $where, 'id desc', $page, $number);
    }
}
?>