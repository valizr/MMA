<?php
class SettingsController extends Zend_Controller_Action
{
    public function init()
    {
    	/* Initialize action controller here */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->message = $this->_flashMessenger->getMessages();
		
		//Needs_Tools::hasAccess('acces_modul_setari',true);
    }

	public function editPasswordAction()
	{		
		$account = new Default_Model_Users();
		$account->find(Zend_Registry::get('user')->getId());
		
		$form = new Default_Form_EditPassword();
		$form->editPassword();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/users/edit-password.phtml'))));
		$this->view->form = $form;

		if($this->getRequest()->isPost()) {								
			if($form->isValid($this->getRequest()->getPost()))
			{	
				$post = $this->getRequest()->getPost();				
				if(md5($post['oldPassword']) == $account->getPassword()) {
					$account->setPassword(md5($post['password']));									
					if($account->save()) {	
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>".Zend_Registry::get('translate')->_('password_change_success_message')."</p><a href='javascript:;'></a></div>");										
					}else{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('administrators_edit_password_error_message')."</p><a href='javascript:;'></a></div>");										
					}						
				} else{		
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('administrators_invalid_old_password')."</p><a href='javascript:;'></a></div>");																	

				}
				$this->_redirect(WEBROOT.'settings/edit-password');
			}
		}
	}
}