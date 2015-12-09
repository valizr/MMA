<?php
class Default_Form_Messages extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'messages', 'class'=>''));
		
		$action = new Zend_Form_Element_Hidden('action');
		$action->setValue('add');
		$this->addElement($action);
		
		//BEGIN:Users
		$idUserTo = new Zend_Form_Element_Select('idUserTo');
		$idUserTo->setLabel('To: ');	
		
		$options= array(''=>'Send to');		
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
		$idUserTo->setAttribs(array('class'=>'validate[required] form_selector','id'=>'idUserTo'));
		$idUserTo->setRequired(true);
		$this->addElement($idUserTo);
		//END:Users
		
		$subject = new Zend_Form_Element_Text('subject');
		$subject->setLabel('Subject');
		$subject->setAttribs(array('class'=>'rightAdd validate[required]','placeholder'=>'Subject'));
		$subject->setRequired(true);
		$this->addElement($subject);
		
		$message = new Zend_Form_Element_Textarea('message');
		$message->setLabel('Message');
		$message->setAttribs(array('class'=>'mess_textarea validate[required]', 'placeholder'=>'Message','style'=>'width:290px'));
		$message->setRequired(true);
		$this->addElement($message);
		
		$button = new Zend_Form_Element_Submit('rightSubmit');
		$button->setValue('SEND MESSAGE');
		$button->setAttribs(array('class'=>'comments_submit'));
		$button->setIgnore(true);
		$this->addElement($button);
	
	}
	
	public function reply(Default_Model_Messages $model)
	{
		$this->subject->setValue('Re: '.$model->getSubject());		
		$this->idUserTo->setValue($model->getIdUserFrom());
	}
}
