<?php
class Default_Form_SearchStatistics extends Zend_Form 
{
	function init()
	{
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAction(WEBROOT.'statistics');
		$this->addAttribs(array('id'=>'filterForm', 'class'=>'')); 
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		
		// BEGIN: data
		$dataStart = new Zend_Form_Element_Text('dataStart');
		$dataStart->setAttribs(array('class'=>'data_inceput','placeholder'=>Zend_Registry::get('translate')->_('admin_from_date')));
		$this->addElement($dataStart);
		
		$dataEnd = new Zend_Form_Element_Text('dataEnd');
		$dataEnd->setAttribs(array('class'=>'data_sfarsit','placeholder'=>Zend_Registry::get('translate')->_('admin_to_date')));
		$this->addElement($dataEnd);
		// END: data
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue(Zend_Registry::get('translate')->_('admin_menu_form_search'));
		$submit->setAttribs(array('class'=>'submit'));
		$submit->setIgnore(true);
		$this->addElement($submit);
	}
}
