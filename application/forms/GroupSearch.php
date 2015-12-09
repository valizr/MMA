<?php
class Default_Form_GroupSearch extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'filterForm', 'class'=>''));
		$this->setAction(WEBROOT.'groups');
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
	
		// BEGIN: nume text
		$nameSearch = new Zend_Form_Element_Text('nameSearch');
		$nameSearch->setAttribs(array('class'=>'text large','placeholder'=>'Name'));
		$nameSearch->setRequired(false);
		$this->addElement($nameSearch);
		// END: nume text
		
		$search = new Zend_Form_Element_Submit('searchGroup');
		$search->setValue('Search');
		$search->setAttribs(array('class'=>'submit'));
		$search->setIgnore(true);
		$this->addElement($search);
	}
}