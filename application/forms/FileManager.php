<?php
class Default_Form_FileManager extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'file-manager', 'class'=>''));
		
		$action = new Zend_Form_Element_Hidden('action');
		$action->setValue('add');
		$this->addElement($action);

		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Description');
		$description->setAttribs(array('class'=>'mess_textarea validate[required]', 'placeholder'=>'Description','style'=>'width:272px'));
		$this->addElement($description);
		
		$button = new Zend_Form_Element_Submit('rightSubmit');
		$button->setValue('UPLOAD FILE');
		$button->setAttribs(array('class'=>'comments_submit'));
		$button->setIgnore(true);
		$this->addElement($button);
	
	}
}
