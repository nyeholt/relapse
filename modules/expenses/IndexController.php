<?php

class Expenses_IndexController extends NovemberController
{
    /**
     * @var UserService
     */
    public $userService;
    
    /**
     * @var ExpenseService
     */
    public $expenseService;
    
    /**
     * @var ClientService
     */
    public $clientService;
    
    public function indexAction()
    {
        // Get and list out all the users
		$lm = $this->clientService->getClient(za()->getConfig('owning_company'));
		if ($lm != null) {
	        $users = $this->userService->getUsersForClient($lm->id);
	        $this->view->users = $users;
	        $this->renderView('expenses/index.php');
		} else {
			echo "Error: Owning company not found";
		}
    }

    public function listforuserAction()
    {
        $user = $this->userService->getUserByField('username', $this->_getParam('username')); 
        if ($user == null) {
            $user = za()->getUser();
        }

        $this->view->user = $user;
        
        $this->view->reports = $this->expenseService->getExpenseReports(array('expensereport.username='=>$user->username, 'expensereport.locked=' => '1', 'expensereport.paiddate<>'=>'null'));
        
        $this->renderView('expenses/list.php');
    }
    
/**
     * View the expenses for a given user / client
     */
    public function viewAction()
    {
        $view = new CompositeView();
        $view->addScriptPath('extensions/expenses/views');
        
        $report = $this->byId(null, 'ExpenseReport');
        
        $client = null;
        $user = null;
        
        $expenses = array();
        // we either have a fixed report, or we have a dynamic one
        if ($report) {
            if (mb_strlen($report->username)) {
                $expenses = $this->expenseService->getExpenses(array('userreportid='=>$report->id));
                $user = $this->userService->getUserByField('username', $report->username);
            } else {
                $expenses = $this->expenseService->getExpenses(array('expensereportid='=>$report->id));
                $client = $this->clientService->getClient($report->clientid);
            }
            
            $view->start = $report->from;
            $view->end = $report->to;
        } else {
            $client = $this->clientService->getClient($this->_getParam('clientid'));
	        $user = $this->userService->getUserByField('username', $this->_getParam('username'));
	        $view->user = $user;
        
	        $start = $this->_getParam('start', $this->_getParam('start', $this->calculateDefaultStartDate()));
	        $end = $this->_getParam('end', $this->_getParam('end', $this->calculateDefaultEndDate()));
	        $expenses = $this->expenseService->getDynamicExpenseReport($start, $end, $user, $client);
	        $view->start = $start;
            $view->end = $end;
        }

        $view->expenses = $expenses;
        $view->client = $client;
        $view->user = $user;
        $view->report = $report;
        $view->mode = $this->_getParam('pdf') ? 'pdf' : 'html';
        
        $content = $view->render('expense/view.php');
        
        if ($this->_getParam('pdf')) {
            ini_set('memory_limit', '32M');
            
            include_once "dompdf/dompdf_config.inc.php";
            include_once "dompdf/include/dompdf.cls.php";
            include_once "dompdf/include/frame_tree.cls.php";
            include_once "dompdf/include/stylesheet.cls.php";
            include_once "dompdf/include/frame.cls.php";
            include_once "dompdf/include/style.cls.php";
            include_once "dompdf/include/attribute_translator.cls.php";
            include_once "dompdf/include/frame_factory.cls.php";
            include_once "dompdf/include/frame_decorator.cls.php";
            include_once "dompdf/include/positioner.cls.php";
            include_once "dompdf/include/block_positioner.cls.php";
            include_once "dompdf/include/block_frame_decorator.cls.php";
            include_once "dompdf/include/frame_reflower.cls.php";
            include_once "dompdf/include/block_frame_reflower.cls.php";
            include_once "dompdf/include/frame_reflower.cls.php";
            include_once "dompdf/include/text_frame_reflower.cls.php";
            include_once "dompdf/include/canvas_factory.cls.php";
            include_once "dompdf/include/canvas.cls.php";
            include_once "dompdf/include/abstract_renderer.cls.php";
            include_once "dompdf/include/renderer.cls.php";
            include_once "dompdf/include/cpdf_adapter.cls.php";
            include_once "dompdf/include/font_metrics.cls.php";
            include_once "dompdf/include/block_renderer.cls.php";
            include_once "dompdf/include/text_renderer.cls.php";
            include_once "dompdf/include/image_cache.cls.php";
            include_once "dompdf/include/text_frame_decorator.cls.php";
            include_once "dompdf/include/inline_positioner.cls.php";
            include_once "dompdf/include/page_frame_reflower.cls.php";
            include_once "dompdf/include/page_frame_decorator.cls.php";
            include_once "dompdf/include/table_frame_decorator.cls.php";
            include_once "dompdf/include/cellmap.cls.php";
            include_once "dompdf/include/table_frame_reflower.cls.php";
            include_once "dompdf/include/table_row_frame_decorator.cls.php";
            include_once "dompdf/include/null_positioner.cls.php";
            include_once "dompdf/include/table_row_frame_reflower.cls.php";
			include_once "dompdf/include/table_cell_frame_decorator.cls.php";
			include_once "dompdf/include/table_cell_positioner.cls.php";
			include_once "dompdf/include/table_cell_frame_reflower.cls.php";
			include_once "dompdf/include/table_row_group_frame_decorator.cls.php";
			include_once "dompdf/include/table_row_group_frame_reflower.cls.php";
			include_once "dompdf/include/table_cell_renderer.cls.php";
			include_once "dompdf/include/inline_frame_decorator.cls.php";
			include_once "dompdf/include/inline_frame_reflower.cls.php";
			include_once "dompdf/include/image_frame_decorator.cls.php";
			include_once "dompdf/include/image_frame_reflower.cls.php";
			include_once "dompdf/include/inline_renderer.cls.php";
			include_once "dompdf/include/image_renderer.cls.php";
			include_once "dompdf/include/dompdf_exception.cls.php";
			
			$dompdf = new DOMPDF();
			// $dompdf->set_paper('letter', 'landscape');
			$dompdf->load_html($content);
			$dompdf->render();
			
			$name = "expenses-".date('Y-m-d', strtotime($view->start)).'-to-'.date('Y-m-d', strtotime($view->end)).'.pdf';
			$dompdf->stream($name);

        } else {
            echo $content;
        }
    }
}
?>