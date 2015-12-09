<?php
class Default_Form_SearchLogs extends Zend_Form 
{
	function init()
	{
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAction(WEBROOT.'logs');
		$this->addAttribs(array('id'=>'searchLogs', 'class'=>'')); 
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		
		//BEGIN:Module
		$id = new Zend_Form_Element_Select('modul');
		
		$options= array(''=>'Selectati modulul');		
		$module = new Default_Model_Logs();
		$select = $module->getMapper()->getDbTable()->select()	
													->group('modul')
													->order('created DESC');
		$result = $module->fetchAll($select);
		if(NULL != $result)
		{
			foreach($result as $value){
				$options[$value->getModul()] = $value->getModul();
			}
		}	
		
		$id->addMultiOptions($options);
		$this->addElement($id);
		//END:Module
			
		//BEGIN:ActionType
		$id = new Zend_Form_Element_Select('actionType');
		
		$options= array(''=>'Selectati tipul actiunii');		
		$module = new Default_Model_Logs();
		$select = $module->getMapper()->getDbTable()->select()	
													->group('actionType')
													->order('created DESC');
		$result = $module->fetchAll($select);
		if(NULL != $result)
		{
			foreach($result as $value){
				$options[$value->getActionType()] = $value->getActionType();
			}
		}	
		
		$id->addMultiOptions($options);
		$this->addElement($id);
		//END:ActionType
		
		// BEGIN: data
		$data_inceput = new Zend_Form_Element_Text('data_inceput');
		$data_inceput->setAttribs(array('class'=>'data_inceput','placeholder'=>'Data inceput'));
		$this->addElement($data_inceput);
		
		$data_sfarsit = new Zend_Form_Element_Text('data_sfarsit');
		$data_sfarsit->setAttribs(array('class'=>'data_sfarsit','placeholder'=>'Data sfarsit'));
		$this->addElement($data_sfarsit);
		// END: data
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue('Cauta');
		$submit->setAttribs(array('class'=>'submit'));
		$submit->setIgnore(true);
		$this->addElement($submit);
	}
}
