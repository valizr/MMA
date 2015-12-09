<?php
class TemplatesController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->message = $this->_flashMessenger->getMessages();
		Needs_Tools::hasAccess('acces_modul_setari',true);
    }

	public function indexAction()
	{
		$model = new Default_Model_EmailTemplates();
		$select = $model->getMapper()->getDbTable()->select()				
				->where('type = ?','admin')
				->group('const');
		$result = $model->fetchAll($select);
		$this->view->adminEmails = $result;
			
		$modelUser = new Default_Model_EmailTemplates();
		$selectUser = $modelUser->getMapper()->getDbTable()->select()				
				->where('type = ?','user')
				->group('const');
		$resultUser = $modelUser->fetchAll($selectUser);
		$this->view->userEmails = $resultUser;
			
	}
	
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id'); 
		$model = new Default_Model_EmailTemplates();
		
		
		if(!$model->find($id)){
			$this->_redirect('/templates/');	
		}	
		$this->view->emailTemplate = $model;
        
        //get all the languages
        $formTranslation = new Default_Form_EmailTemplate();
        $formTranslation->edit($model);
        $formTranslation->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/emailTemplate.phtml'))));

        $this->view->forms = $formTranslation;

        $form = new Default_Form_EmailTemplate();
        $form->edit($model);
        $form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/emailTemplate.phtml'))));
        $this->view->form = $form;
        if($this->getRequest()->isPost())
        {					
            if($form->isValid($this->getRequest()->getPost())) 
            {
                $model->setOptions($form->getValues());	

                if($model->save())
                {						
                    $this->_flashMessenger->addMessage("<div class='success  canhide'><p>".Zend_Registry::get('translate')->_('templete_edit_success_message')."</p><a href='javascript:;'></a></div>");
                }
                else
                {
                    $this->_flashMessenger->addMessage("<div class='failure canhide'><p>".Zend_Registry::get('translate')->_('templete_edit_error_message')."</p><a href='javascript:;'></a></div>");
                }
                $this->_redirect('/templates/edit/id/'.$id);						
            }
        }
	}
    
}	