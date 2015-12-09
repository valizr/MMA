<?php
class Default_Form_Generator extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'intrebare_form', 'class'=>''));		
		
		$nume = new Zend_Form_Element_Text('tabel');
		$nume->setLabel('Nume Tabel');
		$nume->setAttribs(array('class'=>'text_input validate[required]', 'placeholder'=>trim(Zend_Registry::get('translate')->_('Nume Tabel')), 'data-prompt-position'=>'topRight:-187'));
		$nume->setRequired(true);
		$this->addElement($nume);		

		$button = new Zend_Form_Element_Submit('button');
		$button->setValue(trim(Zend_Registry::get('translate')->_('Generate Model')));
		$button->setAttribs(array('class'=>'question_submit'));
		$button->setIgnore(true);
		$this->addElement($button);
	
	}	
}
