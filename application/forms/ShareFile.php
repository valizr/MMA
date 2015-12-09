<?php
class Default_Form_ShareFile extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'share-file', 'class'=>''));
		
		$action = new Zend_Form_Element_Hidden('action');
		$action->setValue('sharefile');
		$this->addElement($action);
		
		$idFile = new Zend_Form_Element_Hidden('idFile');
		$idFile->setValue(Zend_Controller_Front::getInstance()->getRequest()->getParam('id'));
		$this->addElement($idFile);
		
		
		//BEGIN:Users
		$idUserTo = new Zend_Form_Element_Select('idUserTo');
		$idUserTo->setLabel('To: ');	
		
		$options= array(''=>'Select user');		
		$shops = new Default_Model_Users();
		$select = $shops->getMapper()->getDbTable()->select()	
												->where('id != ?',Zend_Registry::get('user')->getId())
												->where('NOT deleted')
												->order('name DESC');
		$result = $shops->fetchAll($select);
		if(NULL != $result)
		{
			foreach($result as $value){
				$options[$value->getId()] = $value->getName();
			}
		}
		$idUserTo->addMultiOptions($options);
		$idUserTo->addValidator(new Zend_Validate_InArray(array_keys($options)));
		$idUserTo->setAttribs(array('class'=>'validate[required] form_selector_fm','id'=>'idUserTo'));
		$idUserTo->setRequired(true);
		$this->addElement($idUserTo);
		//END:Users
		
		$subject = new Zend_Form_Element_Text('subject');
		$subject->setLabel('Subject');
		$subject->setAttribs(array('class'=>'form_subject w_424 validate[required]','placeholder'=>'Subject'));
		$subject->setRequired(true);
		$this->addElement($subject);
		
		$message = new Zend_Form_Element_Textarea('message');
		$message->setLabel('Observations');
		$message->setAttribs(array('class'=>'form_textarea w_424 h_97 validate[required]', 'placeholder'=>'Observations','style'=>'width:290px'));
		$message->setRequired(true);
		$this->addElement($message);
		
		$button = new Zend_Form_Element_Submit('rightSubmit');
		$button->setValue('SEND');
		$button->setAttribs(array('class'=>'comments_submit'));
		$button->setIgnore(true);
		$this->addElement($button);
	
	}
}
