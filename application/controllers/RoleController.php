<?php

/**
 * RoleController
 * 
 * @author Kadar Ana-Maria
 * @version 
 */
class RoleController extends Zend_Controller_Action {
	
	public function init()
	{
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
		$bootstrap = $this->getInvokeArg('bootstrap');
        if($bootstrap->hasResource('db')) {
        	$this->db = $bootstrap->getResource('db');
        }
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() 
	{		
		$this->view->db = $this->db ; 
		$this->view->account = Zend_Registry::get('user');
	}
	
	public function addAction()
	{
		//check if the auth user has acces to this modul
//		if(!Needs_Tools::hasAccess(Zend_Registry::get('user')->getRoleId(),'adaugare_rol')){
//			$this->_redirect('/');
//		}		
		
		$id = $this->getRequest()->getParam('id');
		if($id == null)
		{
			//default id is the logged in user id
			$id = Zend_Registry::get('user')->getIdRole();
		}	
			
		//check if user can edit this  (if it's his child)
		$parentModel = new Default_Model_Role();
		if(!$parentModel->find(Zend_Registry::get('user')->getIdRole()))
			$this->_redirect('/');		
		$graph = new Needs_Graph($parentModel, true,array('idParent','id'),'array');		
		if(!$graph->ifSubchild($id))
			$this->_redirect('/');		
		
		$modelParent = new Default_Model_Role();
		if(!$modelParent->find($id))		
			$this->_redirect('/role');
		
		$form = new Default_Form_Role();	

		$form->parentName->setValue($modelParent->getName());
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/role/adaugare.phtml'))));
		$this->view->form = $form;
		
		if($this->getRequest()->isPost()) {
			//BEGIN:Uniqe validation per Tenancy
			if($this->getRequest()->getParam('tenancyId'))
			{
				$where = "deleted = '0'";				
				
				$wheres = array('role', 'name', $where);
				$form->nume->addValidator('Db_NoRecordExists', true, $wheres);
			}
			//END:Uniqe validation per Tenancy
			
			if($form->isValid($this->getRequest()->getPost()))
			{
				//save
				$model = new Default_Model_Role();
				$model->setOptions($form->getValues());
				$model->setIdParent($id);
				$model->setIsAdmin(0);
				$idRole = $model->save();				
				if($idRole)
				{					
					//if is Admin role, give all the resources					
					$this->_flashMessenger->addMessage("<div class='success canhide'><p>Rolul a fost adaugat cu succes.</p><a href='javascript:;'></a></div>");
				}
				else
				{
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>S-a produs o eroare. Rolul nu a fost adaugat.</p><a href='javascript:;'></a></div>");
				}
					
				$this->_redirect('/role');
		
			}
		}
		
		
	}
	
	public function editAction()
	{
//		//check if the auth user has acces to this modul
//		if(!Needs_Tools::hasAccess(Zend_Registry::get('user')->getRoleId(),'editare_rol')){
//			$this->_redirect('/');
//		}		
		$id = $this->getRequest()->getParam('id');
//		
//		//check if user can edit this role (if it's his role child role)
//		if(!Needs_Tools::checkIfSubRole(Zend_Registry::get('user')->getRoleId(),$id)){
//			$this->_redirect('/');
//		}
		
		$model = new Default_Model_Role();
		if($model->find($id))
		{
			//parent
			$modelParinte = new Default_Model_Role();
			if(!$modelParinte->find($model->getIdParent()))
			{
				$this->_redirect('/role');
			}
			
			$form = new Default_Form_Role();
			$form->parentName->setValue($modelParinte->getName());
			$form->edit($model);			
			$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/role/adaugare.phtml'))));
			$this->view->form = $form;

			if($this->getRequest()->isPost()) {	
				
				if($form->isValid($this->getRequest()->getPost()))
				{
					//save			
					$model->setOptions($form->getValues());
					if($model->save())
					{					
						$this->_flashMessenger->addMessage("<div class='success canhide'><p>Rolul a fost editat cu succes.</p><a href='javascript:;'></a></div>");
					}
					else
					{
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>S-a produs o eroare in editarea rolului. Nu s-a efectuat nici o modificare</p><a href='javascript:;'></a></div>");
					}

					$this->_redirect('/role');
				}
			}
		}	
		
	}
	
	public function deleteAction()
	{
//		//check if the auth user has acces to this modul
//		if(!Needs_Tools::hasAccess(Zend_Registry::get('user')->getRoleId(),'stergere_rol')){
//			$this->_redirect('/');
//		}		
//		
		$id = $this->getRequest()->getParam('id');
//		//check if user can delete this role (if it's his role child role)
//		if(!Needs_Tools::checkIfSubRole(Zend_Registry::get('user')->getRoleId(),$id)){
//			$this->_redirect('/');
//		}
		
		$model = new Default_Model_Role();
		if($model->find($id))
		{			
			//all sub childs goes a level up
			$parentId = $model->getIdParent(); 
			
			$graph = new Needs_Graph($model, false,array('idParent','id'),'array',true);
			if($graph->moveChildren($parentId))
			{
				//TODO:fallback if couldn't delete children
			}			
			
			if($model->delete())
			{				
				$this->_flashMessenger->addMessage("<div class='success canhide'><p>Rolul a fost sters cu succes.</p><a href='javascript:;'></a></div>");
			}
			else
			{
				$this->_flashMessenger->addMessage("<div class='failure canhide'><p>S-a produs o eroare in stergerea rolului. Nu s-a efectuat nici o modificare</p><a href='javascript:;'></a></div>");
			}
		}
		$this->_redirect(WEBROOT.'role');		
	}
	
	
}
