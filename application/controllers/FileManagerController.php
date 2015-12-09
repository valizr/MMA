<?php
class FileManagerController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function indexAction()
	{
		// BEGIN:FILTERS
		$filters = array(); //array with variables to send to pagination (filters)
		$searchTxt = $this->getRequest()->getParam('searchTxt');
		if(!empty($searchTxt)){
			$filters['searchTxt'] = $searchTxt; 
		}
		$this->view->search = $filters;
		// END:FILTERS
		
		//BEGIN:SEARCH FORM
		$formSearch = new Default_Form_FileManagerSearch();
		$formSearch->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/file-manager-search.phtml'))));
		$this->view->formSearch = $formSearch;
		//END:SEARCH FORM
		
		//BEGIN:FORM ADD
		$replyId = $this->getRequest()->getParam('replyId');		
		$form = new Default_Form_FileManager();
        $form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/file-manager.phtml'))));
		
        $this->view->form = $form;
		
		$formshare = new Default_Form_ShareFile();
		
		if($this->getRequest()->isPost())
		{		
			$post = $this->getRequest()->getPost();
			if(!empty($post['action']) && $post['action'] == 'add'){
			//if is valid save message
				if($form->isValid($this->getRequest()->getPost())) 
				{
						//BEGIN:SAVE ATTACHMENTS
						if(!empty($post['galleryFiles']) && is_array($post['galleryFiles']))
						{
							foreach ($post['galleryFiles'] as $valuesGallery) {

								$tmpFiles = new Default_Model_TempFiles();
								if($tmpFiles->find($valuesGallery)){
									$post = $this->getRequest()->getPost();
									$gallery = new Default_Model_FileManager();
									$gallery->setOptions($form->getValues());
									$gallery->setType($tmpFiles->getFileType());
									$gallery->setSize($tmpFiles->getFileSize());
									$gallery->setModule('sharedfiles');
									$gallery->setName($tmpFiles->getFileName());
									$savedId=$gallery->save();
									if ($savedId){
										$shared=new Default_Model_SharedList();
										$shared->setIdUser(Zend_Registry::get('user')->getId());
										$shared->setIdFile($savedId);
										$shared->save();
									}
									//copy picture and crop
									$tempFile = APPLICATION_PUBLIC_PATH.'/media/temps/'.$tmpFiles->getFileName();																
									$targetFile = APPLICATION_PUBLIC_PATH.'/media/files/'.$tmpFiles->getFileName();
									@copy($tempFile,$targetFile);
									@unlink($tempFile);	
									$tmpFiles->delete();
								}
							}
						//END:SAVE ATTACHMENTS
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Your file was succesfully uploaded.</p><a href='javascript:;'></a></div>");
						}
						
					else
					{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Error uploading file!</p><a href='javascript:;'></a></div>");
					}
					$this->_redirect(WEBROOT.'file-manager');
				}
			}
			if(!empty($post['action']) && $post['action'] == 'sharefile'){
			//if is valid save shared file message
				if($formshare->isValid($this->getRequest()->getPost())) 
				{
					$model=new Default_Model_Messages();
					$model->setOptions($formshare->getValues());
					$model->setIdUserFrom(Zend_Registry::get('user')->getId());
					$model->save();
					
						//BEGIN:SAVE ATTACHMENTS
					$shared=new Default_Model_SharedList();
					$shared->setOptions($formshare->getValues());
					//echo $formshare->getValue('idUserTo');
					//die();//aici e ok
					$shared->setIdUser($formshare->getValue('idUserTo'));//aici nu seteaza
					$shared->save();
					//END:SAVE ATTACHMENTS
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Your file was succesfully shared.</p><a href='javascript:;'></a></div>");
				}
				else
				{
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Error sharing file!</p><a href='javascript:;'></a></div>");
				}				
			$this->_redirect(WEBROOT.'file-manager');
			}		
		}
		//END:FORM	ADD
		
		//BEGIN:LISTING		
		$model = new Default_Model_FileManager();
		$select = $model->getMapper()->getDbTable()->select();
		//if(!empty($type) && $type == 'sent'){  //sent
			$select->from(array('sl'=>'shared_list'),array('sl.idUser','sl.created'))
					->joinLeft(array('uf' => 'uploaded_files'), 'uf.id = sl.idFile', array('uf.id','uf.name','uf.description','uf.type','uf.size'))
					->where('sl.idUser = ?',Zend_Registry::get('user')->getId())
					->where('NOT sl.deleted');
		//	}
		if(!empty($searchTxt)){
			$select->where("uf.name LIKE ('%".$searchTxt."%') OR uf.description LIKE ('%".$searchTxt."%')");
		}
		$select->order('sl.created DESC')				
			   ->setIntegrityCheck(false);
		// pagination
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($this->_getParam('page'));
		$paginator->setPageRange(5);
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial(array('_pagination.phtml', $filters));

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

	public function downloadAction()
	{
		$id		= $this->getRequest()->getParam('id');
		$loggedInUserId = Zend_Registry::get('user')->getId();
		$model = new Default_Model_UploadedFiles();
		if($model->find($id))
		{
			$name=$model->getName();
			$this->view->result = $name;
		}
	}
	
	public function deleteAction()
	{
		$id		= $this->getRequest()->getParam('id');
		$loggedInUserId = Zend_Registry::get('user')->getId();
		$model = new Default_Model_SharedList();
		$select = $model->getMapper()->getDbTable()->select();
		//if(!empty($type) && $type == 'sent'){  //sent
		$select->where('idUser = ?',$loggedInUserId)
				->where('idFile = ?',$id)
				->where('NOT deleted');
		$result=$model->fetchRow($select);
		
		if($model->find($result->getId()))
		{
			$model->delete();
			$this->_flashMessenger->addMessage('<span class="mess-true">The file was successfully deleted.</span>');
		}else{
				$this->_flashMessenger->addMessage('<span class="mess-false">Error deleting file!</span>');
		}
		$this->_redirect(WEBROOT.'file-manager');
	}	
}	

