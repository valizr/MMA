<?php
class Default_Form_Comments extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'comments_form', 'class'=>''));
		
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Comment text');
		$description->setAttribs(array('class'=>'mess_textarea validate[required]', 'placeholder'=>trim(Zend_Registry::get('translate')->_('Comment text')), 'data-prompt-position'=>'topLeft:6'));
		$description->setRequired(true);
		$this->addElement($description);
		
		$button = new Zend_Form_Element_Submit('button');
		$button->setValue(trim(Zend_Registry::get('translate')->_('Send Comment')));
		$button->setAttribs(array('class'=>'comments_submit'));
		$button->setIgnore(true);
		$this->addElement($button);
	
	}	
}
