<?php
class Default_Form_SearchComplexStatistics extends Zend_Form 
{
	function init()
	{
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAction(WEBROOT.'statistics');
		$this->addAttribs(array('id'=>'filterComplexForm', 'class'=>'')); 
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		
                //BEGIN:Id Category Expenses
                $idCategoryE = new Zend_Form_Element_Multiselect('idCategoryE');

                $options= array(/*''=>Zend_Registry::get('translate')->_('admin_menu_categories'),*/ '1'=>  strtoupper(Zend_Registry::get('translate')->_('admin_expenses')));
                $categories = new Default_Model_Groups();
                $select = $categories->getMapper()->getDbTable()->select()               
                                                        ->where('NOT deleted')
                                                        ->where('type=?',0)
                                                        ->order('name ASC');
                $result = $categories->fetchAll($select);
                if(NULL != $result) {
                    foreach($result as $value){
                        $options[$value->getId()] = $value->getName();
                    }
                }
                $idCategoryE->setMultiOptions($options);
                $idCategoryE->addValidator(new Zend_Validate_InArray(array_keys($options)));
                $idCategoryE->setAttribs(array('class'=>'rightAdd form_selector','id'=>'idCategoryE'));
                $idCategoryE->setRequired(false);
                $this->addElement($idCategoryE);
                //END:Id Category Expenses
                
                //BEGIN:Id Category Income
                $idCategoryI = new Zend_Form_Element_Multiselect('idCategoryI');

                $options= array(/*''=>Zend_Registry::get('translate')->_('admin_menu_categories'),*/ '2'=>  strtoupper(Zend_Registry::get('translate')->_('admin_income')));
                $categories = new Default_Model_Groups();
                $select = $categories->getMapper()->getDbTable()->select()               
                                                        ->where('NOT deleted')
                                                        ->where('type=?',1)
                                                        ->order('name ASC');
                $result = $categories->fetchAll($select);
                if(NULL != $result) {
                    foreach($result as $value){
                        $options[$value->getId()] = " ".$value->getName();
                    }
                }
                $idCategoryI->setMultiOptions($options);
                $idCategoryI->addValidator(new Zend_Validate_InArray(array_keys($options)));
                $idCategoryI->setAttribs(array('class'=>'rightAdd form_selector','id'=>'idCategoryI'));
                $idCategoryI->setRequired(false);
                $this->addElement($idCategoryI);
                //END:Id Category Income
                
                //BEGIN:Timeframe
                $timeframe = new Zend_Form_Element_Select('timeframe');

                $options= array('m'=>Zend_Registry::get('translate')->_('admin_monthly'), 'w'=>  Zend_Registry::get('translate')->_('admin_weekly'), 'd'=>  Zend_Registry::get('translate')->_('admin_daily'));
                $timeframe->setMultiOptions($options);
                $timeframe->addValidator(new Zend_Validate_InArray(array_keys($options)));
                $timeframe->setAttribs(array('class'=>'rightAdd validate[required] form_selector','id'=>'timeframe'));
                $timeframe->setRequired(true);
                $timeframe->setValue('m');
                $this->addElement($timeframe);
                //END:Timeframe
                
		// BEGIN: data
		$dataStart = new Zend_Form_Element_Text('dataStartC');
		$dataStart->setAttribs(array('class'=>'data_inceput_c validate[required]','placeholder'=>Zend_Registry::get('translate')->_('admin_from_date')));
		$dataStart->setRequired(true);
                $this->addElement($dataStart);
		
		$dataEnd = new Zend_Form_Element_Text('dataEndC');
		$dataEnd->setAttribs(array('class'=>'data_sfarsit_c validate[required]','placeholder'=>Zend_Registry::get('translate')->_('admin_to_date')));
		$this->addElement($dataEnd);
		// END: data

		$submit = new Zend_Form_Element_Submit('submitC');
		$submit->setValue(Zend_Registry::get('translate')->_('admin_menu_form_search'));
		$submit->setAttribs(array('class'=>'submit'));
		$submit->setRequired(true);
		$this->addElement($submit);
                
                $submitReportE = new Zend_Form_Element_Submit('submitReportE');
		$submitReportE->setAttribs(array('class'=>'submitReport'));
		$submitReportE->setRequired(true);
		$this->addElement($submitReportE);
                
                $submitReportI = new Zend_Form_Element_Submit('submitReportI');
		$submitReportI->setAttribs(array('class'=>'submitReport'));
		$submitReportI->setRequired(true);
		$this->addElement($submitReportI);
	}
}