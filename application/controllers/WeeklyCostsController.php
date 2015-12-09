<?php
class WeeklyCostsController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function addAction()
	{
		$form_weeklyCosts = new Default_Form_WeeklyCosts();
        $form_weeklyCosts->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/weekly-costs/add.phtml'))));
        $this->view->plugin_form_weeklycosts = $form_weeklyCosts;
			
		if($this->getRequest()->isPost())
		{
			
			$model=new Default_Model_WeeklyCosts();
			$action = $this->getRequest()->getPost('action');				
			if($form_weeklyCosts->isValid($this->getRequest()->getPost())) 
			{
				$model->setOptions($form_weeklyCosts->getValues());

				$saved_weeklyCosts = $model->save();

				if($saved_weeklyCosts)
					{
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The data was added successfully!</p><a href='javascript:;'></a></div>");
					}
					else
					{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The data was not added successfully!</p><a href='javascript:;'></a></div>");
					}

				$this->_redirect(WEBROOT.'/weekly-costs');
			}
		}
	}
	public function indexAction()
	{
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();	
		
		$model = new Default_Model_WeeklyCosts();
		
		$date = strtotime(date('Y-m-d'));
	
		$thisWeekStartDate	=  date('Y-m-d', mktime(0, 0, 0, date('m',$date), date('d',$date)-date('w',$date), date('Y',$date)));
		$thisWeekEndDay		=  date('Y-m-d', mktime(0, 0, 0, date('m',$date), date('d',$date)-date('w',$date)+6, date('Y',$date)));
		
		$dateFrom	= ($this->getRequest()->getParam('dateFrom') != null) ? $this->getRequest()->getParam('dateFrom'):$thisWeekStartDate;
		$dateTo		= ($this->getRequest()->getParam('dateTo') != null) ? $this->getRequest()->getParam('dateTo'):$thisWeekEndDay;
		
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('c' => 'weekly_costs'), array('c.*'))
				->where('c.idUser = ?',Zend_Registry::get('user')->getId())
				->where('dateFrom=?',$dateFrom)
				->where('dateTo=?',$dateTo)
                ->where("NOT c.deleted")
				->order(array('c.created DESC'));
		$model->fetchRow($select);

		

		$form_weeklyCosts = new Default_Form_WeeklyCosts();
		$form_weeklyCosts->edit($dateFrom,$dateTo,$model);
//		$form_weeklyCosts->dateFrom->setValue($dateFrom);
//		$form_weeklyCosts->dateTo->setValue($dateTo);
		
        $form_weeklyCosts->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/weekly-costs/add.phtml'))));
        $this->view->plugin_form_weeklycosts = $form_weeklyCosts;

		if($this->getRequest()->isPost())
		{
			$model=new Default_Model_WeeklyCosts();
			$action = $this->getRequest()->getPost('action');
			if($form_weeklyCosts->isValid($this->getRequest()->getPost()))
			{
				$model->setOptions($form_weeklyCosts->getValues());
				$model->setIdUser($authAccount->getId());
				$model->setIdShop(Zend_Registry::get('user')->getIdShop());				
				$saved_weeklyCosts = $model->save();

				if($saved_weeklyCosts)
					{
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The data was added successfully!</p><a href='javascript:;'></a></div>");
					}
					else
					{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The data was not added successfully!</p><a href='javascript:;'></a></div>");
					}

				$this->_redirect(WEBROOT.'weekly-costs/index/dateFrom/'.$this->getRequest()->getParam('dateFrom').'/dateTo/'.$this->getRequest()->getParam('dateTo'));
			}
		}
		
		/*if(NULL != $result)
		{
			$paginator = Zend_Paginator::factory($result);
			$paginator->setItemCountPerPage(25);
			$paginator->setCurrentPageNumber($this->_getParam('page'));
			$paginator->setPageRange(5);
			$this->view->result = $paginator;
			$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
			$this->view->totalItemCount = $paginator->getTotalItemCount();

			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination.phtml');
		}*/
	}

	public function deleteAction()
	{		
		$id = $this->getRequest()->getParam('id');
		if($id)
		{
			$model = new Default_Model_WeeklyCosts();
			if($model->find($id))
			{				
				if($model->delete())				
				{					
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>".Zend_Registry::get('translate')->_('Weekly Costs Data deleted successfully')."</p><a href='javascript:;'></a></div>");
				}
				else
				{
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('Weekly Costs Data not deleted')."</p><a href='javascript:;'></a></div>");
				}
			}
			else
			{
				$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('Error deleting Weekly Costs Data')."!</p><a href='javascript:;'></a></div>");
			}
		}
		$this->_redirect('/weekly-costs/details/id/'.$idProject);
	}
}