<?php
class CommentsController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function addAction()
	{
		$form_comments = new Default_Form_Comments();
        $form_comments->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/comments.phtml'))));
        $this->view->plugin_form_comments = $form_comments;
			
		if($this->getRequest()->isPost())
		{
			
			$model=new Default_Model_Comments();
			$action = $this->getRequest()->getPost('action');				
			if($form_comments->isValid($this->getRequest()->getPost())) 
			{
				$model->setOptions($form_comments->getValues());

				$saved_comment = $model->save();

				/*$emailArray = array();

				$emailArray['subject']			= 'Intrebare';
				$emailArray['content']			= $this->getRequest()->getPost('mesaj');
				$emailArray['toEmail']			= EMAIL_QUESTION_NOTIFICATION;
				$emailArray['toName']			= INSTITUTION_NAME;
				$emailArray['fromEmail']		= $this->getRequest()->getPost('email');
				$emailArray['fromName']			= $this->getRequest()->getPost('nume');
				$emailArray['SMTP_USERNAME']	= SMTP_USERNAME;
				$emailArray['SMTP_PASSWORD']	= SMTP_PASSWORD;
				$emailArray['SMTP_PORT']		= SMTP_PORT;
				$emailArray['SMTP_URL']			= SMTP_URL;
				$sent = Needs_Tools::sendEmail($emailArray);*/
				if($saved_comment)
					{
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The comment was added successfully!</p><a href='javascript:;'></a></div>");
					}
					else
					{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The comment was not added successfully!</p><a href='javascript:;'></a></div>");
					}

				$this->_redirect(WEBROOT.'/comments');
			}
		}
	}
	public function indexAction()
	{
		$model = new Default_Model_Comments();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('c' => 'comment'), array('c.*'))
                            ->where("NOT c.deleted")
							->where("c.idParent IS NULL")
							->order(array('c.created DESC'));		
		$result = $model->fetchAll($select);

		if(NULL != $result)
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
		}
	}
	public function detailsAction()
	{
		$id = $this->getRequest()->getParam('id');
		if($id)
		{
			$model = new Default_Model_Comments();
			/*if($model->find($id)){
				$vizualizare = $model->setVizualizare();
			}*/
			$select = $model->getMapper()->getDbTable()->select()
					->where('NOT deleted')
					->where('id=?',$id);
			$result = $model->fetchRow($select);
	
			if($result)
			{
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
			$model = new Default_Model_Comments();
			if($model->find($id))
			{				
				if($model->delete())				
				{					
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>".Zend_Registry::get('translate')->_('Comment deleted successfully')."</p><a href='javascript:;'></a></div>");
				}
				else
				{
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('Comment not deleted')."</p><a href='javascript:;'></a></div>");
				}
			}
			else
			{
				$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('Error deleting comment')."!</p><a href='javascript:;'></a></div>");
			}
		}
		$this->_redirect('/projects/details/id/'.$idProject);
	}
}