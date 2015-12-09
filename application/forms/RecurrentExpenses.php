<?php
class Default_Form_RecurrentExpenses extends Zend_Form 
{
	function init()
	{
        // Set the method for the display form to POST
        $this->setMethod('post');
		$this->addAttribs(array('id'=>'addExpense', 'class'=>'')); 
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		
		$control = new Zend_Form_Element_Hidden('control');
		$control->setValue('addExpense');
		$this->addElement($control);
		
		// begin inputs
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array('class'=>'text validate[required] rightAdd','placeholder'=>Zend_Registry::get('translate')->_('admin_recurrent_expense_description')));
		$name->setRequired(true);
		$this->addElement($name);

                $price = new Zend_Form_Element_Text('price');
		$price->setAttribs(array('class'=>'text validate[required] rightAdd','placeholder'=>Zend_Registry::get('translate')->_('admin_price')));
		$price->setRequired(true);
		$this->addElement($price);
                
                //BEGIN: Date
		$date = new Zend_Form_Element_Text('date');
		$date->setAttribs(array('class'=>'rightAdd dateSearch w_315','placeholder'=>'Date'));
		$date->setLabel('Select day');
		$this->addElement($date);
		// END: Date
                
		//BEGIN:Id Group
		$idGroup = new Zend_Form_Element_Select('idGroup');
		
		$options= array();		
		$pm = new Default_Model_Groups();
		$select = $pm->getMapper()->getDbTable()->select()				
                             ->where('NOT deleted')
                             ->order('name ASC');
		$result = $pm->fetchAll($select);
		if(NULL != $result)
		{
			foreach($result as $value){
				$options[$value->getId()] = $value->getName();
			}
		}	
		
		$idGroup->addMultiOptions($options);
		$idGroup->addValidator(new Zend_Validate_InArray(array_keys($options)));
		$idGroup->setAttribs(array('class'=>'select'));
		$idGroup->setRequired(false);
		$this->addElement($idGroup);
		//END:Id Group
		
        $submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue(Zend_Registry::get('translate')->_('admin_add_recurrent_expense'));
		$submit->setAttribs(array('class'=>'submit'));
		$submit->setIgnore(true);
		$this->addElement($submit);
	}
	
	function edit(Default_Model_RecurrentExpenses $model)
	{
		$expenseId = new Zend_Form_Element_Hidden('expenseId');
		$expenseId->setValue($model->getId());
		$this->addElement($expenseId);		
		
		$this->name->setValue($model->getName());
		$this->name->setLabel(Zend_Registry::get('translate')->_('admin_name'));
                
                $this->price->setValue($model->getPrice());
		$this->price->setLabel(Zend_Registry::get('translate')->_('admin_price'));
                
                $this->date->setValue(date('m/d/Y',strtotime($model->getDate())));
		$this->date->setLabel('Date');
		
		$productGroups = new Default_Model_ProductGroups();
		$select = $productGroups->getMapper()->getDbTable()->select()
				->where('idProduct=?',$model->getId())
                                ->where('repeated=?',1);
		$result = $productGroups->fetchAll($select);
		if(NULL != $result)
		{
			foreach($result as $value){
				$options[$value->getId()] = $value->getIdGroup();
			}
		}
		$this->idGroup->setValue($options);
		$this->idGroup->setLabel(Zend_Registry::get('translate')->_('category'));
		
		$this->submit->setValue(Zend_Registry::get('translate')->_('admin_edit_recurrent_expense'));
	}
	
	public function removeFields()
	{
		$this->removeElement('name');
	}
}
