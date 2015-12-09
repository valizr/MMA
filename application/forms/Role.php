<?php
class Default_Form_Role extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'formDepartamentAdd', 'class'=>''));
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		
		
		$parentName = new Zend_Form_Element_Text('parentName');
		$parentName->setLabel('Rol Parinte');
		$parentName->setAttribs(array('class'=>'text large required','readonly'=>'readonly'));	
		$this->addElement($parentName);	
		
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Nume');
		$name->setAttribs(array('class'=>'validate[required] text large required'));
		$name->setRequired(true);
		$this->addElement($name);		

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue('Adaugare');
		$submit->setAttribs(array('class'=>'submit'));
		$submit->setIgnore(true);
		$this->addElement($submit);
	}

	function edit(Default_Model_Role $model)
	{		
		$this->name->setValue($model->getName());		
		$this->submit->setValue('Modificare');
	}
}