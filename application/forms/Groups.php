<?php
class Default_Form_Groups extends Zend_Form 
{
	function init()
	{
        // Set the method for the display form to POST
        $this->setMethod('post');
		$this->addAttribs(array('id'=>'addGroup', 'class'=>'')); 
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		
		$control = new Zend_Form_Element_Hidden('control');
		$control->setValue('addGroup');
		$this->addElement($control);
		
		// begin inputs
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array('class'=>'text validate[required] rightAdd','placeholder'=>Zend_Registry::get('translate')->_('admin_category_name')));
		$name->setRequired(true);
		$this->addElement($name);
                
                // begin inputs
		$color = new Zend_Form_Element_Text('color');
		$color->setAttribs(array('class'=>'text validate[required] rightAdd','placeholder'=>Zend_Registry::get('translate')->_('admin_color_for_charts')));
		$color->setRequired(true);
		$this->addElement($color);
                
                // begin inputs
		$type = new Zend_Form_Element_Select('type');
		
                $options= array(''=>Zend_Registry::get('translate')->_('admin_category_select_type'), '0' => Zend_Registry::get('translate')->_('admin_expenses'), '1' => Zend_Registry::get('translate')->_('admin_income'));
		$type->setMultiOptions($options);
		$type->addValidator(new Zend_Validate_InArray(array_keys($options)));

                $type->setAttribs(array('class'=>'select','id'=>'type'));
                $type->setRequired(true);
		$this->addElement($type);

        $submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue(Zend_Registry::get('translate')->_('admin_add'));
		$submit->setAttribs(array('class'=>'submit'));
		$submit->setIgnore(true);
		$this->addElement($submit);
	}
	
	function edit(Default_Model_Groups $model)
	{
		$idGroup = new Zend_Form_Element_Hidden('idGroup');
		$idGroup->setValue($model->getId());
		$this->addElement($idGroup);
		
//		$idProduct = new Zend_Form_Element_Multiselect('idProduct');
//		$idProductGroups = new Zend_Form_Element_Hidden('idProductGroups');
//		
//		//$options= array(''=>'Selectati Produsele Grupului');		
//		$pm = new Default_Model_Expenses();
//		$pg = new Default_Model_ProductGroups();
//		$selectpg = $pg->getMapper()->getDbTable()->select()
//				->where('NOT deleted')
//				->where('idGroup = ?',$model->getId())
//				->order('order ASC');
//		$resultpg = $pg->fetchAll($selectpg);
//		
//		$select = $pm->getMapper()->getDbTable()->select()
//												->where('NOT deleted');
//		$result = $pm->fetchAll($select);
//		if(NULL != $result)
//		{
//			$selectedProd=array();
//			foreach($result as $value){//all products
//				$gasit=false;
//				foreach ($resultpg as $key=>$valuepg){//all existing products from group
//					if ($value->getId()==$valuepg->getIdProduct()){
//						$optionspg[$key][$value->getId()] = $value->getName();
//						$gasit=true;
//						$selectedProd[$key]=$value->getId();
//					}
//				}
//				if ($gasit==false) $options[$value->getId()] = $value->getName();
//			}
//		}
//		$idProduct->addMultiOptions($options);
//		$idProduct->addValidator(new Zend_Validate_InArray(array_keys($options)));
//		$idProduct->setAttribs(array('class'=>'select_prodgroups','id'=>'select-from'));
//		$idProduct->setRequired(false);
//		$this->addElement($idProduct);
//		
//		
//		//$idProductGroups->addMultiOptions($optionspg);
//	//	$idProductGroups->setRegisterInArrayValidator(false);
//	//	$idProductGroups->setAttribs(array('class'=>'select','id'=>'select-to'));
//		$idProductGroups->setRequired(false);
//		$this->addElement($idProductGroups);
//		
//		$selectedProducts = new Zend_Form_Element_Hidden('selectedProducts');
//		ksort($selectedProd);		
//		$selectedProducts->setValue(implode(",",$selectedProd));
//		$this->addElement($selectedProducts);
//		//print_r ($optionspg);
//		//die();
//		if ($optionspg){
//			ksort($optionspg);
//			$rezOptionpg=array();
//			foreach($optionspg as $val) {
//				foreach($val as $key=>$value){
//				$rezOptionpg[$key]=$value;
//				}
//			  }
//			$this->idProductGroups->setValue(($rezOptionpg!='')?http_build_query($rezOptionpg):'');
//		}
		$this->name->setValue($model->getName());
                $this->type->setValue($model->getType());
                $this->color->setValue($model->getColor());
		$this->submit->setValue(Zend_Registry::get('translate')->_('admin_modify'));
	}	
	
	public function removeFields()
	{
		$this->removeElement('name');
	}
}
