<?php
class NotificationsController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function indexAction()
	{
		$userId = null;
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null != $authAccount) {
			if (null != $authAccount->getId()) {
				$userId = $authAccount->getId();
			}
		}
		$model=new Default_Model_NotificationMessages();
		$notification=new Default_Model_NotificationTo();
		$params = array();
		
		$subject = $this->getRequest()->getParam('subject');		
		$message = $this->getRequest()->getParam('message');
		
		$select = $notification->getMapper()->getDbTable()->select()
			->from(array('n' => 'notification_to'), array('n.*'))
            ->where("NOT n.deleted")
			->where("n.status=?",'1')
			->where("n.idUserTo=?",$userId);
		$result = $notification->fetchAll($select);
		$notifications=(count($result)!=0)?count($result):0;
		
		$select2 = $model->getMapper()->getDbTable()->select()
						->from(array('nm'=>'notification_messages'), array('nm.*'))
						->joinLeft(array('n' => 'notification_to'), 'nm.`id` = n.`idNotification`',array('nid'=>'n.id','status'=>'n.status'))
					    ->where("NOT n.deleted")
						->where("n.idUserTo=?",$userId);
						if(!empty($subject)){
							$params['subject'] = $subject;
							$select2->where('subject LIKE ?','%'.$subject.'%');
						}
						if(!empty($message)){
							$params['message'] = $message;
							$select2->where('message LIKE ?','%'.$message.'%');
						}
			$select2->order(array('n.status desc','n.created desc'))
					->setIntegrityCheck(false);

		$form = new Default_Form_NotificationsSearch();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/notifications/notifications-search.phtml'))));
		$this->view->form = $form;
		$this->view->search=$params;
		$result2 = $model->fetchAll($select2);		
		
		if(NULL != $result2)
		{
			$paginator = Zend_Paginator::factory($result2);
			$paginator->setItemCountPerPage(25);
			$paginator->setCurrentPageNumber($this->_getParam('page'));
			$paginator->setPageRange(5);
			$this->view->result = $paginator;
			$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
			$this->view->totalItemCount = $notifications;

			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination.phtml');
		}
	}
	public function detailsAction()
	{
		$userId = null;
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null != $authAccount) {
			if (null != $authAccount->getId()) {
				$userId = $authAccount->getId();
			}
		}
		$id = $this->getRequest()->getParam('id');
		if($id)
		{
			$model = new Default_Model_NotificationMessages();
			/*if($model->find($id)){
				$vizualizare = $model->setVizualizare();
			}*/
			$select = $model->getMapper()->getDbTable()->select()
					->where('NOT deleted')
					->where('id=?',$id);
			$result = $model->fetchRow($select);

			if($result)
			{
				$modelto=new Default_Model_NotificationTo();
				$select = $modelto->getMapper()->getDbTable()->select()
					->where('NOT deleted')
					->where('idNotification=?',$id)
					->where('idUserTo=?',$userId);
				$result2=$modelto->fetchRow($select);

				if ($modelto->find($result2->id));
				if ($modelto->getStatus()==1)
					$this->view->notificationBg = "necitit";
					$modelto->setStatus('0');
					$modelto->save();

				$this->view->result = $result;
			}
		}
	}
	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		$idProject = $this->getRequest()->getParam('idProject');
		if($id)
		{
			$model = new Default_Model_NotificationTo();
			if($model->find($id))
			{				
				if($model->delete())				
				{					
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>".Zend_Registry::get('translate')->_('Notificare stearsa cu succes')."</p><a href='javascript:;'></a></div>");
				}
				else
				{
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('Notificarea nu a fost stearsa')."</p><a href='javascript:;'></a></div>");
				}
			}
			else
			{
				$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('Eroare stergere notificare')."!</p><a href='javascript:;'></a></div>");
			}
		}
		$this->_redirect('/notifications');
	}
}