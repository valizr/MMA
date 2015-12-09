<?php
class Default_Form_Login extends Zend_Form 
{
	function init()
	{
        // Set the method for the display form to POST
        $this->setMethod('post');
		$this->addAttribs(array('id'=>'formLogin', 'class'=>''));
        
        // Add an email element
        $this->addElement(
			'text', 'tbUser', array(
            'required'   => true,          
            'attribs'    => array('class'=>'text large required','id'=>'email','placeholder'=>Zend_Registry::get('translate')->_('admin_email_address')),                      
        ));

        // Add an password element
        $this->addElement(
			'password', 'tbPass', array(
            'attribs'    => array('class'=>'text large required','id'=>'password','placeholder'=>Zend_Registry::get('translate')->_('admin_password')),
            'required'   => true,
        ));

        $this->addElement(
			'submit', 'submit', array(
            'ignore'   => true,
            'attribs'    => array('class'=>'submit'),
            'label'    => Zend_Registry::get('translate')->_('admin_login'),
			'value'		=> Zend_Registry::get('translate')->_('admin_login')
        ));
	}
}
