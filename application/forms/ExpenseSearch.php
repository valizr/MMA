<?php
class Default_Form_ExpenseSearch extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'filterForm', 'class'=>''));
		$this->setAction(WEBROOT.'expenses');
	
		// BEGIN: nume text
		$nameSearch = new Zend_Form_Element_Text('nameSearch');
		$nameSearch->setAttribs(array('class'=>'text large','placeholder'=>Zend_Registry::get('translate')->_('admin_name')));
		$nameSearch->setRequired(false);
		$this->addElement($nameSearch);
		// END: nume text
		
		// BEGIN: prenume text
		$idGroupSearch = new Zend_Form_Element_Select('idGroupSearch');
		
		$options= array(''=>'Select expense');		
		$groups = new Default_Model_Groups();
		$select = $groups->getMapper()->getDbTable()->select()				
					->where('type=?',0)
                                        ->where('NOT deleted')
					->order('name ASC');
		$result = $groups->fetchAll($select);
		if(NULL != $result)
		{
			foreach($result as $value){
				$options[$value->getId()] = $value->getName();
			}
		}
		$idGroupSearch->addMultiOptions($options);
		$idGroupSearch->addValidator(new Zend_Validate_InArray(array_keys($options)));
		$idGroupSearch->setAttribs(array('class'=>'select uniformSelect filter_selector'));
		$idGroupSearch->setRequired(false);
		$this->addElement($idGroupSearch);
		// END: prenume text
		
                // BEGIN: fromDate text
		$fromDate = new Zend_Form_Element_Text('fromDate');
		$fromDate->setAttribs(array('class'=>'text large','placeholder'=>Zend_Registry::get('translate')->_('admin_from_date')));
		$fromDate->setRequired(false);
		$this->addElement($fromDate);
		// END: fromDate text
                
                // BEGIN: toDate text
		$toDate = new Zend_Form_Element_Text('toDate');
		$toDate->setAttribs(array('class'=>'text large','placeholder'=>Zend_Registry::get('translate')->_('admin_to_date')));
		$toDate->setRequired(false);
		$this->addElement($toDate);
		// END: toDate text
                
		$search = new Zend_Form_Element_Submit('searchProduct');
		$search->setValue('Filter');
		$search->setAttribs(array('class'=>'submit'));
		$search->setIgnore(true);
		$this->addElement($search);
	}
}