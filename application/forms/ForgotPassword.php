<?php
class Default_Form_ForgotPassword extends Zend_Form 
{
	function init()
	{
        // Set the method for the display form to POST
        $this->setMethod('post');
		$this->addAttribs(array('id'=>'formLogin', 'class'=>''));
        
        // Add an email element
        $this->addElement(
			'text', 'email', array(
            'required'   => true,          
            'attribs'    => array('class'=>'text large required','id'=>'email','placeholder'=>'Email address'),            
        ));

        $this->addElement(
			'submit', 'submit', array(
            'ignore'   => true,
            'attribs'    => array('class'=>'submit'),
            'label'    => 'Submit',
			'value'		=> 'Submit'
        ));

	}
}