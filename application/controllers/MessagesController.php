<?php
class MessagesController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function indexAction()
	{
		// BEGIN:FILTERS
		$filters = array(); //array with variables to send to pagination (filters)	
		$type = $this->getRequest()->getParam('type'); //can be 'sent','trash' or empty (inbox)	
		$this->view->type = $type;
		if(!empty($type)){
			$filters['type'] = $type; 
		}
		$searchTxt = $this->getRequest()->getParam('searchTxt');
		if(!empty($searchTxt)){
			$filters['searchTxt'] = $searchTxt; 
		}
		$this->view->search = $filters;
		// END:FILTERS
		
		//BEGIN:SEARCH FORM
		$formSearch = new Default_Form_MessagesSearch();
		$formSearch->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/messages-search.phtml'))));
		$this->view->formSearch = $formSearch;
		//END:SEARCH FORM
		
		//BEGIN:FORM ADD
		$replyId = $this->getRequest()->getParam('replyId');		
		$form = new Default_Form_Messages();
        $form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/messages.phtml'))));
		if(!empty($replyId)){
			$model=new Default_Model_Messages();
			$model->find($replyId);
			if($model->getIdUserTo() == Zend_Registry::get('user')->getId()){
				$form->reply($model);
			}
		}
        $this->view->form = $form;		
		
		if($this->getRequest()->isPost())
		{		
			$post = $this->getRequest()->getPost();
			if(!empty($post['action']) && $post['action'] == 'add'){
				//if is valid save message
				if($form->isValid($this->getRequest()->getPost())) 
				{

					//save message
					$model=new Default_Model_Messages();
					$model->setOptions($form->getValues());
					$model->setIdUserFrom(Zend_Registry::get('user')->getId());
					$savedId = $model->save();
					if($savedId)
					{
						//BEGIN:SAVE ATTACHMENTS
						if(!empty($post['galleryFiles']) && is_array($post['galleryFiles']))
						{
							foreach ($post['galleryFiles'] as $valuesGallery) {

								$tmpFiles = new Default_Model_TempFiles();
								if($tmpFiles->find($valuesGallery)){
									$gallery  = new Default_Model_UploadedFiles();
									$gallery->setIdMessage($savedId);
									$gallery->setType($tmpFiles->getFileType());
									$gallery->setIdUser(Zend_Registry::get('user')->getId());
									$gallery->setModule('messages');
									$gallery->setName($tmpFiles->getFileName());
									$gallery->save();

									//copy picture and crop
									$tempFile = APPLICATION_PUBLIC_PATH.'/media/temps/'.$tmpFiles->getFileName();																
									$targetFile = APPLICATION_PUBLIC_PATH.'/media/files/'.$tmpFiles->getFileName();
									@copy($tempFile,$targetFile);
									@unlink($tempFile);	
									$tmpFiles->delete();
								}
							}
						}
						//END:SAVE ATTACHMENTS
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Your message was succesfully sent.</p><a href='javascript:;'></a></div>");
					}
					else
					{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Error sending message!</p><a href='javascript:;'></a></div>");
					}
					$this->_redirect(WEBROOT.'messages');
				}
			}
		}
		//END:FORM	ADD
		
		//BEGIN:LISTING		
		$model = new Default_Model_Messages();
		$select = $model->getMapper()->getDbTable()->select();
		if(!empty($type) && $type == 'sent'){  //sent
			$select->from(array('u'=>'messages'),array('u.id','idUserFrom'=>'u.idUserTo','u.subject','u.created'))
					->where('u.idUserFrom = ?',Zend_Registry::get('user')->getId())
					->where('NOT u.deletedFrom')
					->where('NOT u.trashedFrom');
		}elseif(!empty($type) && $type == 'trash'){  //trash
			$select->from(array('u'=>'messages'),array('u.id','u.idUserFrom','u.idUserTo','u.subject','u.created'))
					->where(""
						. "(u.idUserTo = '".Zend_Registry::get('user')->getId()."' AND u.trashedTo = 1 AND NOT u.deletedTo)  "
						. "OR "
						. "(u.idUserFrom = '".Zend_Registry::get('user')->getId()."'  AND u.trashedFrom = 1 AND NOT u.deletedFrom)");
		}else{ //inbox
			$select->from(array('u'=>'messages'),array('u.id','u.idUserFrom','u.idUserTo','u.subject','u.created'))
					->where('u.idUserTo = ?',Zend_Registry::get('user')->getId())
					->where('NOT u.deletedTo')
					->where('NOT u.trashedTo');
		}	
		if(!empty($searchTxt)){
			$select->where("u.subject  LIKE ('%".$searchTxt."%') OR u.message  LIKE ('%".$searchTxt."%')");
		}		
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
		$this->view->sentNr				= Needs_Messages::getSentMessagesNumber();
		$this->view->trashNr			= Needs_Messages::getTrashMessagesNumber();
		
		$this->view->result				= $paginator;
		$this->view->itemCountPerPage	= $paginator->getItemCountPerPage();
		$this->view->totalItemCount		= $paginator->getTotalItemCount();		
		
		//END:LISTING
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

