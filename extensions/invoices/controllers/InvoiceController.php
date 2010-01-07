<?php
include_once 'extensions/invoices/model/Invoice.php';
class InvoiceController extends BaseController 
{
    /**
     * DbService
     *
     * @var DbService
     */
    public $dbService;
    
    /**
     * The ProjectService
     *
     * @var ProjectService
     */
    public $projectService;
    
    /**
     * The client service
     *
     * @var ClientService
     */
    public $clientService;
    
    public function editAction($model=null)
    {
        $this->view->project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        $this->view->timesheets = $this->projectService->getTimesheets(array('projectid='=>$this->view->project->id));
        parent::editAction($model);
    }
    
    public function onModelSaved($model)
    {
        if ($model->projectid) {
            // Not editing
            $this->redirect('project', 'view', array('id'=>$model->projectid));
        }
    }
    
    public function viewAction()
    {
        $this->viewInvoiceAction();
    }
    
    public function viewInvoiceAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/plain');
		$invoice = $this->byId();
		$timesheet = $this->projectService->getTimesheet($invoice->timesheetid);
		
		if (!$timesheet) {
		    throw new Exception("Must have a timesheet to view invoice");
		}
		
		$project = $this->projectService->getProject((int) $invoice->projectid);
        $this->view->client = $this->clientService->getClient($project->clientid);
		$this->view->invoice = $invoice;
		$timesheet->to = date('Y-m-d 23:59:59', strtotime($timesheet->to));
		// $this->view->tasks = $this->projectService->getTasks(array("projectid=" => $project->id));
		$this->view->records = $this->projectService->getSummaryTimesheet(null, null, $project->id, null, $timesheet->id, $timesheet->from, $timesheet->to);
		$this->view->project = $project;

        $this->renderRawView('invoice/view.php');
    }
    
    /**
     * Get the items that need to appear in the project listing
     */
    public function projectListAction()
    {
        // $project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        $project = $this->projectService->getProject((int) $this->_getParam('projectid'));
        
        $this->view->invoices = $this->dbService->getObjects('Invoice', array('projectid='=> $project->id));
        $this->view->model = $project;
        $this->renderRawView('invoice/ajax-invoice-list.php');
    }
}
?>