<?php
class Default_Form_NotificationsSearch extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'formNotificationsAddSearch', 'class'=>''));
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
	
		// BEGIN: nume text
		$subject = new Zend_Form_Element_Text('subject');
		$subject->setAttribs(array('class'=>'text large','placeholder'=>'Subiect'));
		$subject->setRequired(false);
		$this->addElement($subject);
		// END: nume text
		
		// BEGIN: nume text
		$message = new Zend_Form_Element_Text('message');
		$message->setAttribs(array('class'=>'text large','placeholder'=>'Mesaj'));
		$message->setRequired(false);
		$this->addElement($message);
		// END: nume text
		
		$search = new Zend_Form_Element_Submit('searchNotifications');
		$search->setValue('Cauta');
		$search->setAttribs(array('class'=>'submit'));
		$search->setIgnore(true);
		$this->addElement($search);
	}
}