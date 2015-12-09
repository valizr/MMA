<?php
class IndexController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function indexAction()
	{
            
		// BEGIN:FILTERS
		$filters = array(); //array with variables to send to pagination (filters)	
		// END:FILTERS
		
		//BEGIN:LISTING	MESSAGES	
		$model = new Default_Model_Messages();
		$select = $model->getMapper()->getDbTable()->select();

		$select->from(array('u'=>'messages'),array('u.id','u.idUserFrom','u.idUserTo','u.subject','u.created'))
				->where('u.idUserTo = ?',Zend_Registry::get('user')->getId())
				->where('NOT u.deletedTo')
				->where('NOT u.trashedTo');
			
		$select->order('u.created  DESC')
			   ->setIntegrityCheck(false);
		// pagination
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($this->_getParam('page'));
		$paginator->setPageRange(5);
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial(array('_pagination.phtml', $filters));

		$this->view->inboxNr			= Needs_Messages::getInboxMessagesNumber();

		$this->view->result		= $paginator;
		$this->view->itemCountPerPage	= $paginator->getItemCountPerPage();
		$this->view->totalItemCount	= $paginator->getTotalItemCount();		

		//END:LISTING MESSAGES
                
                //BEGIN:LISTING LATEST EXPENSES / INCOME	
		$model = new Default_Model_Expenses();
		$select = $model->getMapper()->getDbTable()->select();

		$select->from(array('u'=>'expenses'),array('u.id','u.name','u.price','u.date','u.type'))
				->where('u.idMember = ?',Zend_Registry::get('user')->getId())
				->where('NOT u.deleted');
			
		$select->order('u.date  DESC');
		// pagination
		$paginatorLatest = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginatorLatest->setItemCountPerPage(10);
		$paginatorLatest->setCurrentPageNumber($this->_getParam('page'));
		$paginatorLatest->setPageRange(5);
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial(array('_pagination.phtml', $filters));

		$this->view->resultLatest		= $paginatorLatest;
		$this->view->itemCountPerPageLatest	= $paginatorLatest->getItemCountPerPage();
		$this->view->totalItemCountLatest	= $paginatorLatest->getTotalItemCount();		

		//END:LISTING LATEST EXPENSES / INCOME
		
                //START: Expenses PIE
		$groups = new Default_Model_Groups();
		$select = $groups->getMapper()->getDbTable()->select()
				->from(array('g'=>'groups'),
					array('g.id','g.name','g.color','g.created','g.deleted'))
				->joinLeft(array('pg' => 'product_groups'), 'g.id = pg.idGroup', array())
                                ->joinLeft(array('p' => 'expenses'), 'p.id = pg.idProduct', array('price'=>'SUM(p.price)'))
                                ->where('NOT g.deleted')
                                ->where('p.type=?',0)
                                ->where('p.date>=?',date('Y-m-01'))
                                ->group('g.id')
                                ->setIntegrityCheck(false);
                $resultPieExpenses=$groups->fetchAll($select);
                $this->view->resultPieExpenses=$resultPieExpenses;
                //END: Expenses PIE
                
                //START: Income PIE
		$groups2 = new Default_Model_Groups();
		$select2 = $groups2->getMapper()->getDbTable()->select()
				->from(array('g'=>'groups'),
					array('g.id','g.name','g.color','g.created','g.deleted'))
				->joinLeft(array('pg' => 'product_groups'), 'g.id = pg.idGroup', array())
                                ->joinLeft(array('p' => 'expenses'), 'p.id = pg.idProduct', array('price'=>'SUM(p.price)'))
                                ->where('NOT g.deleted')
                                ->where('p.type=?',1)
                                ->where('p.date>=?',date('Y-m-01'))
                                ->group('g.id')
                                ->setIntegrityCheck(false);
                $resultPieIncome=$groups2->fetchAll($select2);
                $this->view->resultPieIncome=$resultPieIncome;
                //END: Income PIE
                
		$date = date('Y-m-d');
		$thisWeekStartDate	=  Needs_Tools::getWeekDaysByDate($date,$type='start');
		$thisWeekEndDay		=  Needs_Tools::getWeekDaysByDate($date,$type='end');	
			
		$this->view->dateFrom	= $thisWeekStartDate;
		$this->view->dateTo	= $thisWeekEndDay;
		
//		$resultInfo= Needs_Tools::showProjectedDashboard($thisWeekStartDate,$thisWeekEndDay);
//		if ($resultInfo){
//			$this->view->foodCost=$resultInfo["foodCost"];
//			$this->view->laborCost=$resultInfo["laborCost"];
//			$this->view->idShop=$resultInfo["idShop"];
//		}else{
//                        $this->view->foodCost='';
//			$this->view->laborCost='';
//			$this->view->idShop='';
//                }
                $resultIncomeExpense = Needs_Tools::showIncomeExpensesDashboard(date('Y'), date('m'));
                if ($resultIncomeExpense){
                    $value=array();
                    foreach ($resultIncomeExpense as $values){
                        $value[] = $values->getPrice();
                    }
                    $this->view->incomeAmount=isset($value[1])?$value[1]:0;
                    $this->view->expensesAmount=isset($value[0])?$value[0]:0;
                }else{
                    $this->view->incomeAmount=0;
                    $this->view->expensesAmount=0;
                }
                $date=date('Y-m-d');
                $day=date('d',strtotime($date));
                $feb = (date('L', strtotime($date))?29:28); //an bisect
                $days=array(0,31,$feb,31,30,31,30,31,31,30,31,30,31,31);//nr zile pe luna
                //$month=array(1,2,3,4,5,6,7,8,9,10,11,12);
                $newdate = strtotime ( '-'.$days[date('n', strtotime($date))].' days' , strtotime ( $date ) ) ;
                
                $resultIncomeExpenseLastMonth = Needs_Tools::showIncomeExpensesDashboard(date('Y', $newdate), date('m', $newdate));
                if ($resultIncomeExpenseLastMonth){
                    $value=array();
                    foreach ($resultIncomeExpenseLastMonth as $values){
                        $value[] = $values->getPrice();
                    }
                    $this->view->incomeAmountLastMonth=isset($value[1])?$value[1]:0;
                    $this->view->expensesAmountLastMonth=isset($value[0])?$value[0]:0;
                }else{
                    $this->view->incomeAmountLastMonth=0;
                    $this->view->expensesAmountLastMonth=0;
                }
                $this->view->newdate=$newdate;
		//$this->view->form = $form;
	}
	
	public function detailsAction()
	{
		$id		= $this->getRequest()->getParam('id');
		$type	= $this->getRequest()->getParam('type');
		$this->view->type = $type;
		
		$loggedInUserId = Zend_Registry::get('user')->getId();
		$modelMesaj = new Default_Model_Messages();
		if($modelMesaj->find($id) && ($modelMesaj->getIdUserFrom() == $loggedInUserId || $modelMesaj->getIdUserTo() == $loggedInUserId) )
		{
			//BEGIN:mark as read			
			if($modelMesaj->getRead() == 0){
				$model = new Default_Model_Messages();
				$model->find($id);
				$model->makeRead();
			}
			//END:mark as read
			
			$this->view->result = $modelMesaj;
		}
	}

	public function deleteAction()
	{
		$id		= $this->getRequest()->getParam('id');
		$type	= $this->getRequest()->getParam('type');
		$loggedInUserId = Zend_Registry::get('user')->getId();
		$modelMesaj = new Default_Model_Messages();
		if($modelMesaj->find($id))
		{
			$deleted = false;
			$trash	 = false;
			if($modelMesaj->getIdUserFrom() == $loggedInUserId){
				if($modelMesaj->getTrashedFrom() == 1){
					$deleted = $modelMesaj->deleteFrom();
				}else{
					$deleted = $modelMesaj->trashFrom();
					$trash = true;
				}				
			}elseif($modelMesaj->getIdUserTo() == $loggedInUserId){
				if($modelMesaj->getTrashedTo() == 1){
					$deleted = $modelMesaj->deleteTo();
				}else{
					$deleted = $modelMesaj->trashTo();
					$trash = true;
				}				
			}
			if($deleted && $trash)
			{				
				$this->_flashMessenger->addMessage('<span class="mess-true">The massage was successfully moved to trash.</span>');
			}elseif($deleted){
				$this->_flashMessenger->addMessage('<span class="mess-true">Your message was successfully deleted.</span>');
			}else{
				$this->_flashMessenger->addMessage('<span class="mess-false">Error deleting message!</span>');
			}			
		}
		$this->_redirect(WEBROOT.'/messages/index/type/'.$type);
	}
}