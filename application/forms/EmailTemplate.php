<?php
class Default_Form_EmailTemplate extends Zend_Form
{
	function init()
	{
		$this->setMethod('post');
		$this->addAttribs(array('id'=>'formTemplateAdd', 'class'=>''));
		
		$formNumber = new Zend_Form_Element_Hidden('formNumber');
		$this->addElement($formNumber);
				
		$const = new Zend_Form_Element_Hidden('const');
		$this->addElement($const);
	
		$subject = new Zend_Form_Element_Text('subject');
		$subject->setLabel(Zend_Registry::get('translate')->_('admin_email_templates_subject'));
		$subject->setAttribs(array('class'=>'text large validate[required]'));
		$subject->setRequired(true);
		$this->addElement($subject);
		
		$content = new Zend_Form_Element_Textarea('content');
		$content->setLabel(Zend_Registry::get('translate')->_('admin_email_templates_content'));
		$content->setAttribs(array('class'=>'text large validate[required]'));
		$content->setRequired(true);
		$this->addElement($content);
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue('');
		$submit->setAttribs(array('class'=>'submit'));
		$submit->setIgnore(true);
		$this->addElement($submit);
	}

	function edit(Default_Model_EmailTemplates $model,$nr = Null)
	{
		$this->formNumber->setValue($nr);	
		$this->const->setValue($model->getConst());	
		$this->subject->setValue($model->getSubject());				
		$this->content->setValue($model->getContent());		
		$this->submit->setValue(Zend_Registry::get('translate')->_('admin_modify'));
	}
}