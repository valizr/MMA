<?php
class UsersController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function indexAction()
	{
		$model = new Default_Model_Users();
		
		$params = array();
		
		$name = $this->getRequest()->getParam('nameSearch');		
		$email = $this->getRequest()->getParam('emailSearch');		
		$page= $this->getRequest()->getParam('page')?(int) $this->getRequest()->getParam('page'):1;			
		$shop = $this->getRequest()->getParam('idShopSearch');
		$level = $this->getRequest()->getParam('idRoleSearch');
		
		$select = $model->getMapper()->getDbTable()->select()
						->where('NOT deleted');
						if(!empty($name)){
							$params['nameSearch'] = $name;
							$select->where('name LIKE ?','%'.$name.'%');
						}
						if(!empty($email)){
							$params['emailSearch'] = $email;
							$select->where('email LIKE ?','%'.$email.'%');
						}
						if(!empty($shop)){	
							$params['idShopSearch'] = $shop;
							$select->where('idShop = ?',$shop);
						}
						if(!empty($level)){	
							$params['idRoleSearch'] = $level;
							$select->where('idRole = ?',$level);
						}
						$select->order('created DESC');
				
		
		$this->view->page = $page;
		$this->view->search=$params;
		$result = $model->fetchAll($select);
		
		if (NULL != $result) {
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
		
		Needs_Roles::hasAccess('adaugare_utilizator',true);
		
		$form = new Default_Form_Users();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/users/user-add.phtml'))));
		$this->view->form = $form;
		
		$formSearch = new Default_Form_UsersSearch();
		$formSearch->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/users/user-search.phtml'))));
		$this->view->formSearch = $formSearch;
		
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
		
			if(!empty($post['action']) && $post['action'] == 'add'){
				if($form->isValid($post)) 
				{			
					$model = new Default_Model_Users();
					$model->setOptions($form->getValues());
					$password	= substr(md5(sha1(rand(0, 9999999))), 0, 6);
					$code		= substr(md5(sha1(rand(0, 9999999))), 0, 6);
					$model->setPassword(md5($password)); //generare parola random la inregistrare user
					$model->setCode($code);
					$model->setStatus(0);
					
					if($id = $model->save())
					{
						//BEGIN:salvam drepturile
						if($form->getValue('resourceId'))
						{
							foreach ($form->getValue('resourceId') as $value)
							{
								$resourceUser = new Default_Model_ResourceUsers();
								$resourceUser->setResourceId($value);
								$resourceUser->setUserId($id);
								$resourceUser->save();
							}

						}
						//END:salvam drepturile
						//BEGIN: CHECK IF DISTRICT MANAGER AND SAVE SHOPS
						if($form->getValue('idRole') == 14){
							foreach ($form->getValue('idShopMulti') as $value)
								{
									$shopUser = new Default_Model_DistrictManagerShops();
									$shopUser->setIdShop($value);
									$shopUser->setIdUser($id);
									$shopUser->save();
								}
						}
						//END: CHECK IF DISTRICT MANAGER AND SAVE SHOPS
						
						
						//BeGIN: send email with user data, generated password and activation link					

						$activation = '<a href="'.WEBROOT.'auth/activation/code/'.$code.'">Activate</a>';							
						 
						$emailTemplate = new Default_Model_EmailTemplates();
						$select = $emailTemplate->getMapper()->getDbTable()->select()
								->where('const = ?','user_activare_cont')								
								->limit(1);
						$emailTemplate->fetchRow($select);
					
						if(NULL != $emailTemplate->getContent())
						{
							$emailArray = array(); 											
							$name    = $model->getName();

							$message = nl2br($emailTemplate->getContent());
							$message = str_replace("{"."$"."name}",$name, $message);
							$message = str_replace("{"."$"."password}",$password, $message);
							$message = str_replace("{"."$"."activation_link}",$activation, $message);
							
							$emailArray['subject']			= $emailTemplate->getSubject();
							$emailArray['content']			= $message;
							$emailArray['toEmail']			= $model->getEmail();
							$emailArray['toName']			= $name;
							$emailArray['fromEmail']		= FROM_EMAIL;
							$emailArray['fromName']			= FROM_NAME;
							$emailArray['SMTP_USERNAME']	= SMTP_USERNAME;
							$emailArray['SMTP_PASSWORD']	= SMTP_PASSWORD;
							$emailArray['SMTP_PORT']		= SMTP_PORT;
							$emailArray['SMTP_URL']			= SMTP_URL;                                  
							Needs_Tools::sendEmail($emailArray);
						}					
						//END:  send email 
						
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>User was successfully added!<a href='javascript:;'>Close</a></p></div>");
					}
					else
					{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The user was not saved!<a href='javascript:;'>Close</a></p></div>");
					}

					$this->_redirect('/users');
				}		
			}
		}	
	}
	
	public function editAction() {
		
		require_once APPLICATION_PUBLIC_PATH.'/library/tsThumb/ThumbLib.inc.php';
		
		Needs_Roles::hasAccess('editare_utilizator', true);
		$page= ($this->getRequest()->getParam('page'))?(int) $this->getRequest()->getParam('page'):1;		
		$this->view->page = $page;
		
		$id = $this->getRequest()->getParam('id');
		$model = new Default_Model_Users();
		if ($model->find($id)) {
			$form = new Default_Form_Users();
			$form->edit($model);
			$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/users/edit-user.phtml'))));
			$this->view->form = $form;

			if ($this->getRequest()->isPost()) {
				if ($form->isValid($this->getRequest()->getPost())) {
					$model->setOptions($form->getValues());
					if ($model->save()) {
						//Needs_Tools::DeleteLegaturiDistrictManager( $model->getId() );
//						if($form->getValue('idRole') == 14){
//							foreach ($form->getValue('idShopMulti') as $idShop) {
//								$modelDistrictManager = new Default_Model_DistrictManagerShops();
//								$modelDistrictManager->setIdShop($idShop);
//								$modelDistrictManager->setIdUser($model->getId());
//								$modelDistrictManager->save();
//							}
//						}
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>User was edited successfully!<a href='javascript:;'>Close</a></p></div>");
					} else {
						$this->_flashMessenger->addMessage("<div class='failure  canhide'><p>User was not edited!<a href='javascript:;'>Close</a></p></div>");
					}					
					$this->_redirect('/users/index/page/'.$page);		
				}				
			}
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
		$page= ($this->getRequest()->getParam('page'))?(int) $this->getRequest()->getParam('page'):1;	
		$id = $this->getRequest()->getParam('id');
		$model = new Default_Model_Users();
		if ($model->find($id)) {
			if($id != $authAccount->getId()){
				if ($model->delete()) {
					//Needs_Tools::DeleteLegaturiDistrictManager($id);
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>User deleted<a href='javascript:;'>Close</a></p></div>");
				} else {
					$this->_flashMessenger->addMessage("<div class='failure  canhide'><p>User was not deleted<a href='javascript:;'>Close</a></p></div>");
				}
			}else{
				$this->_flashMessenger->addMessage("<div class='failure  canhide'><p>You cannot delete the account that you are logged in with!<a href='javascript:;'>Close</a></p></div>");
			}
			$this->_redirect('/users/index/page/'.$page);	
		}
	}
	
	public function editPasswordAction()
	{		
		$accountId = new Default_Model_Users();
		$accountId->find(Zend_Registry::get('user')->getId());
		
		$form = new Default_Form_EditPassword();
		$form->editUserPassword();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/users/edit-password.phtml'))));
		$this->view->form = $form;
		
		if($this->getRequest()->isPost()) {								
			if($form->isValid($this->getRequest()->getPost()))
			{	
				$post = $this->getRequest()->getPost();		
				if($accountId->getPassword()) {
					$accountId->setPassword(md5($post['password']));									
					if($accountId->save()) {	
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Password successfully changed!<a href='javascript:;'>Close</a></p></div>");								
					}else{
						$this->_flashMessenger->addMessage("<div class='failure  canhide'><p>Password was not changed!<a href='javascript:;'>Close</a></p></div>");									
					}						
				} else{		
					$this->_flashMessenger->addMessage("<div class='failure  canhide'><p>Invalid old password!<a href='javascript:;'>Close</a></p></div>");			

				}
				$this->_redirect('users');
			}
		}
	}
}
