<?php
class Default_Form_FileManagerSearch extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'filterForm', 'class'=>''));
		
		$action = new Zend_Form_Element_Hidden('action');
		$action->setValue('search');
		$this->addElement($action);			
		
		$searchTxt = new Zend_Form_Element_Text('searchTxt');
		$searchTxt->setLabel('Search text');
		$searchTxt->setAttribs(array('class'=>'rightAdd validate[required]','placeholder'=>'File Name','style'=>'width:200px;'));
		$searchTxt->setRequired(true);
		$this->addElement($searchTxt);		
	
		$search = new Zend_Form_Element_Submit('search');
		$search->setValue('Filter');
		$search->setAttribs(array('class'=>'submit','id'=>''));
		$search->setIgnore(true);
		$this->addElement($search);
	
	}	
	
}
