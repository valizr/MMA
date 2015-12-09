<?php
class AuthController extends Zend_Controller_Action
{
	protected $_flashMessenger = null;
	
    public function init()
    {
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
        $bootstrap = $this->getInvokeArg('bootstrap');
        if($bootstrap->hasResource('db')) {
        	$this->db = $bootstrap->getResource('db');
        }
    }

    public function loginAction()
    {
        $form = new Default_Form_Login();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/login.phtml'))));
        $this->view->form = $form;
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
            	$dbAdapter = new Zend_Auth_Adapter_DbTable($this->db, 'users', 'email', 'password', 'MD5(?) AND deleted = "0"');
            	$dbAdapter -> setIdentity($this->getRequest()->getPost('tbUser'))
            			   -> setCredential($this->getRequest()->getPost('tbPass'));            	
            	$auth = Zend_Auth::getInstance();
            	$result = $auth->authenticate($dbAdapter);					
            	if(!$result->isValid()) {                    
					switch($result->getCode()) {					
					    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
					    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:							
								$this->_flashMessenger->addMessage("<div class='error canhide'><p>Incorrect email or password!<a href='javascript:;'>Close</a><p>/div>");
					        break;					
					    default:						
					        /** do stuff for other failure **/
								$this->_flashMessenger->addMessage("<div class='error canhide'><p>Incorrect email or password!<a href='javascript:;'>Close</a></p>/div>");
					        break;
					}
            	} else {                   
                    $adminUserId = $dbAdapter->getResultRowObject();                   
		        	$adminUser = new Default_Model_Users();
                   	$adminUser -> find($adminUserId->id); 
					if($adminUser->getStatus() == 0){
						$this->_flashMessenger->addMessage("<div class='error canhide'><p>Your account was not confirmed! Please check your email for the confirmation email!<a href='javascript:;'>Close</a></p></div>");
						$auth->clearIdentity();						
					}else{
						$storage = $auth->getStorage();
						$adminUser->saveLastlogin();						
						$storage->write($adminUser);					
					}		        	
		       	}				
           		$this->_redirect('/auth/login/');
            }
        }
    }
	
	public function forgotPasswordAction()
    {
        $form = new Default_Form_ForgotPassword();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/forgot-password.phtml'))));
        $this->view->formForgotPassword = $form;
		
		$model=new Default_Model_Users();
        if($this->getRequest()->isPost()) {
            if($form->isValid($this->getRequest()->getPost())) {
				
				$model->setOptions($form->getValues());
            	$select = $model->getMapper()->getDbTable()->select()
				->from(array('u' => 'users'), array('u.*'))
                            ->where("NOT u.deleted")
							->where("u.email=?",$model->getEmail());
				$result = $model->fetchRow($select);
				if(count($result) != 0){
					$newpass='';
					$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
					for ($i = 0; $i < 8; $i++) {
						$n = rand(0,
								count($alphabet) - 1);
						$pass[$i] = $alphabet[$n];
						$newpass.=$pass[$i];
					}
					$newpassMd5=md5($newpass);
					$model->setPassword($newpassMd5);
					$savedPass=$model->save();
					
					$emailArray = array();
					$emailArray['subject']			= 'Resetare Parola';
					$emailArray['content']			= 'Noua dumneavoastra parola este: '.$newpass;
					$emailArray['toEmail']			= $model->getEmail();
					$emailArray['fromEmail']		= "noreply@tfsvreau1site.ro";
					$emailArray['fromName']			= "Resetare Parola";
					$emailArray['SMTP_USERNAME']	= SMTP_USERNAME;
					$emailArray['SMTP_PASSWORD']	= SMTP_PASSWORD;
					$emailArray['SMTP_PORT']		= SMTP_PORT;
					$emailArray['SMTP_URL']			= SMTP_URL;
					$sent = Needs_Tools::sendEmail($emailArray);
					
					if ($sent && $savedPass){
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The password was successfully changed!</p><a href='javascript:;'></a></div>");
					}
					else
					{
						$this->_flashMessenger->addMessage("<div class='error canhide'><p>The password was not changed!<a href='javascript:;'></a></p></div>");
					}
				}else{
					$this->_flashMessenger->addMessage("<div class='error canhide'><p>The email was not found in the database!<a href='javascript:;'></a></p></div>");
				}
           		$this->_redirect('/auth/forgot-password/');
            }
        }
    }

    public function logoutAction()
    {
    	$this->_helper->layout->disableLayout();
    	$auth = Zend_Auth::getInstance();
    	if($auth->hasIdentity()) {
    		$auth->clearIdentity();
    	}
   		$this->_redirect('/auth/login');
    }
	
	public function activationAction()
	{
		$code    = $this->getRequest()->getParam('code');
		if($code){
            $modelUsers = new Default_Model_Users();
            $selectUsers = $modelUsers->getMapper()->getDbTable()->select()
                            ->where('NOT deleted')
                            ->where('code = ?', $code);
            $modelUsers->fetchRow($selectUsers);          
            if($modelUsers->getId())
            {
				if($modelUsers->getStatus() == 0){
					$modelUsers->setCode(NULL);
					$modelUsers->setStatus(1);
					$modelUsers->save();
					$this->_flashMessenger->addMessage("<div class='success_msg canhide'><p>Account successfully activated!</p></div>");
				}else{
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Your account was already activated!</p></div>");
				}	 
            }
        }
        $this->_redirect('/auth/login'); 
	}
}