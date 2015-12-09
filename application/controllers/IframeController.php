<?php
class IframeController extends Zend_Controller_Action
{
	public function init(){
		$bootstrap = $this->getInvokeArg('bootstrap');
        if($bootstrap->hasResource('db')) {
        	$this->db = $bootstrap->getResource('db');
        }
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
    }	

	public function messagesAttachementAction()
	{
		$id     = (int)  $this->getRequest()->getParam('id');
		$this->view->id = $id;
	}
}