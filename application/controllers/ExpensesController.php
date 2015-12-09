<?php
class ExpensesController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
		require_once APPLICATION_PUBLIC_PATH.'/library/tsThumb/ThumbLib.inc.php';
	}
	
	public function indexAction()
	{		
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();		
		
		$params     = array();
		$conditions = array();
		
		if($this->getRequest()->getParam('nameSearch')){
			$params['nameSearch'] = $this->getRequest()->getParam('nameSearch');
		}		
		if($this->getRequest()->getParam('idGroupSearch')){
			$params['idGroupSearch'] = $this->getRequest()->getParam('idGroupSearch');
		}
                if($this->getRequest()->getParam('fromDate')){
			$params['fromDate'] = date("Y-m-d",strtotime($this->getRequest()->getParam('fromDate')));
		}
                if($this->getRequest()->getParam('toDate')){
			$params['toDate'] = date("Y-m-d",strtotime($this->getRequest()->getParam('toDate')));
		}
                
		//BEGIN:SELECT EXPENSES

		$conditions['pagination']	=	true;
		
		$expenses = new Default_Model_Expenses();
		$select = $expenses->getMapper()->getDbTable()->select()
				->from(array('p'=>'expenses'),
					array('p.id','p.name','p.price','p.date','p.created','p.deleted'))
                                ->where('p.type=?',0)
				->where('NOT p.deleted')
                                ->where('idMember=?',Zend_Registry::get('user')->getId());
                
				if(!empty($params['nameSearch'])){
					$select->where('p.name LIKE ?','%'.$params['nameSearch'].'%');
				}
				if(!empty($params['idGroupSearch'])){
					$select->joinLeft(array('pg' => 'product_groups'), 'p.`id` = pg.`idProduct`',array('gid'=>'pg.idGroup'))
						->where('pg.idGroup = ?',$params['idGroupSearch']);
				}
                                if(!empty($params['fromDate'])){
					$select->where('p.date >= ?',$params['fromDate']);
				}
                                if(!empty($params['toDate'])){
					$select->where('p.date <= ?',$params['toDate']);
				}
                                $select->joinLeft(array('uf' => 'uploaded_files'), 'p.`id` = uf.`idMessage`',array('ufiles'=>'uf.id', 'recurrent'=>'uf.idUser'))
                                        ->setIntegrityCheck(false);
		$select->order(array('date DESC'));
                
                $resultExpense = Needs_Tools::showExpensesDashboardbyDate((!empty($params['fromDate'])?$params['fromDate']:date('Y-m-01')), (!empty($params['toDate'])?$params['toDate']:date('Y-m-d')), (!empty($params['idGroupSearch'])?$params['idGroupSearch']:''), (!empty($params['nameSearch'])?$params['nameSearch']:''));
                $this->view->resultExpense=$resultExpense;
                
		//END:SELECT PROJECTS
		$form = new Default_Form_Expenses();	
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/expenses/add-expense.phtml'))));
		$this->view->form = $form;
                
		if($this->getRequest()->isPost() && $this->getRequest()->getParam('control')=='addExpense')
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$post = $this->getRequest()->getPost();
				$model=new Default_Model_Expenses();
				$model->setOptions($form->getValues());
                                $model->setDate(date("Y-m-d", strtotime($post["date"])));
                                $model->setType('0');
				if($expenseId = $model->save())
				{
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
                                                    $gallery->setIdUser(0);//0 - means the image is for non recurrent expense
                                                    $gallery->setIdMessage($expenseId);
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
                                        }                                    
                                        $idGroup = $this->getRequest()->getParam('idGroup');
					$modelGroup=new Default_Model_ProductGroups();
					$modelGroup->setIdProduct($expenseId);
					$modelGroup->setIdGroup($idGroup);
                                        $modelGroup->setRepeated(0);
					$modelGroup->save();
					
					//mesaj de succes
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Expense was added successfully<a href='javascript:;'></a><p></div>");
				}else{
					//mesaj de eroare
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Expense was not added<a href='javascript:;'></a><p></div>");
				}
				//redirect
				$this->_redirect(WEBROOT.'expenses');	
			}	
		}
		$resultRE = Needs_Tools::cronjob();
                if ($resultRE) {
                    $this->_flashMessenger->addMessage("<div class='success  canhide'><p>Recurrent Expenses added successfully<a href='javascript:;'></a><p></div>");
                    $this->_redirect(WEBROOT.'expenses');
                }
                        
		$formsearch = new Default_Form_ExpenseSearch();
		$formsearch->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/expenses/expense-search.phtml'))));
		$this->view->formsearch = $formsearch;
		$this->view->search=$params;
		
		
		// pagination
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setItemCountPerPage(15);
		$paginator->setCurrentPageNumber($this->_getParam('page'));
		$paginator->setPageRange(5);
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial(array('_pagination.phtml', $params));

		$this->view->result = $paginator;
		$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
		$this->view->totalItemCount = $paginator->getTotalItemCount();

	}
	
	public function detailsAction()
	{
		$userId = null;
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null != $authAccount) {
			if (null != $authAccount->getId()) {
				$this->view->userlogat= $authAccount;
			}
		}
		$id = (int) $this->getRequest()->getParam('id');
		if($id)
		{
			// BEGIN: Find model
			$model = new Default_Model_Expenses();			
			if($model->find($id))
			{
				$this->view->result = $model;	
			}
			$select = $model->getMapper()->getDbTable()->select()
					->where('NOT deleted')							
					->order(array('created DESC'));
			$result = $model->fetchAll($select);
			// END: Find model
			//START: Adaugare comment
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
					$model->setIdProject($id);
					$saved_comment = $model->save();
					if($saved_comment)
						{
							$admins = (Needs_Tools::findAdmins());
							$adminIds = (Needs_Tools::findAdmins('id'));
							foreach ($adminIds as $adminId)
							$emailTemplate = new Default_Model_EmailTemplates();
							
							$auth = Zend_Auth::getInstance();
							$authAccount = $auth->getStorage()->read();
							if (null != $authAccount) {
								if (null != $authAccount->getId()) {
									$user = new Default_Model_Users();
									$user->find($authAccount->getId());
								}
							}
							$select = $emailTemplate->getMapper()->getDbTable()->select()
									->where('const = ?','adaugare_comentariu_admin')								
									->limit(1);
							$emailTemplate->fetchRow($select);
							if(NULL != $emailTemplate->getContent())
							{
								$emailArray = array();
								$name    = $user->name.' '.$user->surname;

								$message = $emailTemplate->getContent();
								$message = str_replace("{"."$"."name}",$name, $message);

								$notification=new Default_Model_NotificationMessages();
								$notification->setIdUser($authAccount->getId());
								$notification->setIdProject($id);
								$notification->setSubject($emailTemplate->getSubject());
								$notification->setMessage($message);							
								if ($idNotification=$notification->save()){
								
									$notify=new Default_Model_NotificationTo();
									$notify->setIdNotification($idNotification);
									$notify->setStatus('1');
									foreach ($adminIds as $adminId){
										$notify->setIdUserTo($adminId);
										$notify->save();
									}
									
								} else {
									$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Notificarile nu au fost salvate cu succes<a href='javascript:;'></a></p></div>");
								}

								$emailArray['subject']			= $emailTemplate->getSubject();
								$emailArray['content']			= $message;
								$emailArray['toEmail']			= $admins;
								$emailArray['toName']			= $name;
								$emailArray['fromEmail']		= EMAIL;
								$emailArray['fromName']			= FROM_NAME;
								$emailArray['SMTP_USERNAME']	= SMTP_USERNAME;
								$emailArray['SMTP_PASSWORD']	= SMTP_PASSWORD;
								$emailArray['SMTP_PORT']		= SMTP_PORT;
								$emailArray['SMTP_URL']			= SMTP_URL;
								Needs_Tools::sendEmail($emailArray);
							}
							$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The comment was added successfully!<a href='javascript:;'></a></p></div>");
						}
						else
						{
							$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The comment was not added successfully!<a href='javascript:;'></a></p></div>");
						}

					$this->_redirect(WEBROOT.'/expenses/details/id/'.$id);
				}
			}
			//end adaugare comment
			//$modelc = new Default_Model_Comments();
//			$select = $modelc->getMapper()->getDbTable()->select()
//			->from(array('c' => 'comment'), array('c.*'))
//			->where("NOT c.deleted")
//			->where("c.idParent IS NULL")
//			->where("c.idProduct=?",$model->getId())					
//			->order(array('c.created DESC'));
//			$resultc = $modelc->fetchAll($select);
//
//			if(NULL != $resultc)
//			{
//				$paginator = Zend_Paginator::factory($resultc);
//				$paginator->setItemCountPerPage(25);
//				$paginator->setCurrentPageNumber($this->_getParam('page'));
//				$paginator->setPageRange(5);
//				$this->view->resultc = $paginator;
//				$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
//				$this->view->totalItemCount = $paginator->getTotalItemCount();
//
//				Zend_Paginator::setDefaultScrollingStyle('Sliding');
//				Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination.phtml');
//			}
		}
	}
	
	public function editAction()
	{
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null != $authAccount) {
			if (null != $authAccount->getId()) {
				$user = new Default_Model_Users();
				$user->find($authAccount->getId());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		/*$hasAccess = Needs_Roles::hasAccess(Zend_Registry::get('user')->getIdRole(),'adaugare_proiect');
		if(!$hasAccess)
		{
			$this->_redirect(WEBROOT.'products');
		}*/
		
		$model = new Default_Model_Expenses();
		if ($model->find($id)) {
			$form = new Default_Form_Expenses();
			
			$form->edit($model);
				$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/expenses/edit-expense.phtml'))));
			$this->view->form = $form;
			
			if ($this->getRequest()->isPost()) {
				if ($this->getRequest()->getPost('submit')) {
					if ($form->isValid($this->getRequest()->getPost())) {
                                                $post = $this->getRequest()->getPost();
						$model->setOptions($form->getValues());
                                                $model->setDate(date("Y-m-d", strtotime($post["date"])));
                                                $model->setType('0');
						if ($expenseId = $model->save()) {
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
                                                    $gallery->setIdUser(0);//0 - means the image is for non recurrent expense
                                                    $gallery->setIdMessage($expenseId);
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
                                        }                                    

							Needs_Tools::DeleteLegaturi( $expenseId );
							$idGroup = $this->getRequest()->getParam('idGroup');
                                                        $modelGroup=new Default_Model_ProductGroups();
                                                        $modelGroup->setIdProduct($expenseId);
                                                        $modelGroup->setIdGroup($idGroup);
                                                        $modelGroup->setRepeated(0);
                                                        $modelGroup->save();
							$post = $this->getRequest()->getPost();
							//mesaj de succes
							$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Expense was modified successfully<a href='javascript:;'></a></p></div>");
						} else {
							$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Expense was not modified<a href='javascript:;'></a></p></div>");
						}

						$this->_redirect(WEBROOT.'expenses');
					}
				}
			}
		}
	}
	
        public function downloadAction()
	{
		$id	= $this->getRequest()->getParam('id');
		//$loggedInUserId = Zend_Registry::get('user')->getId();
		$model = new Default_Model_UploadedFiles();
		if($model->find($id))
		{
			$name=$model->getName();
			$this->view->result = $name;
		}
	}
        
	public function deleteAction() {
			$userId = NULL;
			$auth = Zend_Auth::getInstance();
			$authAccount = $auth->getStorage()->read();
			if (null!=$authAccount) {
				if (null!=$authAccount->getId()) {
					$userId = $authAccount->getId();   
				}
			}
		
		$id = $this->getRequest()->getParam('id');
		$model = new Default_Model_Expenses();
		if ($model->find($id)) {
			if ($expenseId = $model->delete()) {
					Needs_Tools::DeleteLegaturi( $expenseId );
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Expense was deleted successfully!<a href='javascript:;'></a></p></div>");
				} else {
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Expense was not deleted!<a href='javascript:;'></a></p></div>");
				}
			$this->_redirect('expenses');
		}
	}
}