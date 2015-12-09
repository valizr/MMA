<?php
class Default_Form_Users extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'addUser', 'class'=>''));
		$this->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
	
		$action = new Zend_Form_Element_Hidden('action');
		$action->setValue('add');
		$this->addElement($action);
		
		// BEGIN: Name
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array('class'=>'text large rightAdd','placeholder'=>Zend_Registry::get('translate')->_('admin_full_name'),'id'=>'name'));
		$name->setRequired(true);
		$this->addElement($name);
		// END: Name
                
                // BEGIN: Account Name
		$accountName = new Zend_Form_Element_Text('accountName');
		$accountName->setAttribs(array('class'=>'text large rightAdd','placeholder'=>Zend_Registry::get('translate')->_('admin_account_name'),'id'=>'accountName'));
		$accountName->setRequired(true);
		$this->addElement($accountName);
		// END: Account Name

		// BEGIN: Email
		$email = new Zend_Form_Element_Text('email');
		$email->setAttribs(array('class'=>'text large rightAdd','placeholder'=>Zend_Registry::get('translate')->_('admin_email_address'),'id'=>'email'));
		$validatorEmail = new Zend_Validate_Db_NoRecordExists('users', 'email');
		$email->addValidator($validatorEmail);
		$email->setRequired(true);
		$this->addElement($email);
		// END: Email

		//BEGIN:Level
		$idLevel = new Zend_Form_Element_Select('idRole');
		
		$options= array(''=>Zend_Registry::get('translate')->_('admin_select_user_level'));		
		$levels = new Default_Model_Role();
		$select = $levels->getMapper()->getDbTable()->select()				
				->where('id != ?',1)
				->where('NOT deleted')
				->order('id DESC');
		$result = $levels->fetchAll($select);
		if(NULL != $result)
		{
			foreach($result as $value){
				$options[$value->getId()] = $value->getName();
			}
		}
		$idLevel->addMultiOptions($options);
		$idLevel->addValidator(new Zend_Validate_InArray(array_keys($options)));
		$idLevel->setAttribs(array('class'=>'rightAdd','id'=>'idRole'));
		$idLevel->setRequired(false);
		$this->addElement($idLevel);
//END:Level
		
		$add = new Zend_Form_Element_Submit('add');
		$add->setValue(Zend_Registry::get('translate')->_('admin_add_user'));
		$add->setAttribs(array('class'=>'submit','id'=>''));
		$add->setIgnore(true);
		$this->addElement($add);
	}
	function edit(Default_Model_Users $model)
	{
		$this->name->setValue($model->getName());
		$this->name->setLabel(Zend_Registry::get('translate')->_('admin_name'));
                
                $this->accountName->setValue($model->getAccountName());
		$this->accountName->setLabel(Zend_Registry::get('translate')->_('admin_account_name'));
		
		$this->email->setValue($model->getEmail());	
		$this->email->setLabel(Zend_Registry::get('translate')->_('admin_email'));
		$emailValidateDbNotExists = $this->email->getValidator('Zend_Validate_Db_NoRecordExists');
		$emailValidateDbNotExists->setExclude(array('field'=>'email', 'value'=>$model->getEmail()));
		
		
		
		$this->idRole->setValue($model->getIdRole());
		$this->idRole->setLabel(Zend_Registry::get('translate')->_('admin_level'));
		
		$this->add->setValue(Zend_Registry::get('translate')->_('admin_edit_user'));
	}
}