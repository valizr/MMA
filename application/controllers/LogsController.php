<?php
class LogsController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function indexAction()
	{	
		$params = array();
		$modul = $this->getRequest()->getParam('modul');		
		$action = $this->getRequest()->getParam('actionType');
		$data_inceput = $this->getRequest()->getParam('data_inceput');
		$data_sfarsit = $this->getRequest()->getParam('data_sfarsit');

		//form cautare dupa modul/tip de actiune/data
		$form =new Default_Form_SearchLogs();	
		$form->modul->setValue($modul);
		$form->actionType->setValue($action);
		$form->data_inceput->setValue($data_inceput);
		$form->data_sfarsit->setValue($data_sfarsit);
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/logs/search.phtml'))));
		$this->view->form = $form;
	
		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$post = $this->getRequest()->getPost();				
				$model=new Default_Model_Logs();
				$model->setOptions($form->getValues());		
			}
		}
		//end form
		
		//fetch loguri
		$logs = new Default_Model_Logs();
		$select = $logs->getMapper()->getDbTable()->select();
			if(!empty($action)){
				$params['actionType'] = $action;
				$select->where('actionType = ?',$action);
			}
			if(!empty($modul)){	
				$params['modul'] = $modul;
				$select->where('modul = ?',$modul);
			}
			
			if(!empty($data_inceput)){
				$params['data_inceput'] = $data_inceput;
				$select->where("DATE(created) >= ?", $data_inceput);
			}
			
			if(!empty($data_sfarsit)){
				$params['data_sfarsit'] = $data_sfarsit;
				$select->where("DATE(created) <= ?", $data_sfarsit);
			}
			$select->order ('created DESC');
		$result = $logs->fetchAll($select);			
		
		if(NULL != $result)
		{
			$paginator = Zend_Paginator::factory($result);
			$paginator->setItemCountPerPage(10);
			$paginator->setCurrentPageNumber($this->_getParam('page'));
			$paginator->setPageRange(5);
			$this->view->result = $paginator;
			$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
			$this->view->totalItemCount = $paginator->getTotalItemCount();
			
			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial(array('_pagination.phtml',$params));
		}
	}	
}
