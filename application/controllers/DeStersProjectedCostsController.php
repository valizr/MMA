<?php
class ProjectedCostsController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();		
	}	
	
	
	public function indexAction()
	{
		$date = date('Y-m-d');
		$thisWeekStartDate	=  Needs_Tools::getWeekDaysByDate($date,$type='start');
		$thisWeekEndDay		=  Needs_Tools::getWeekDaysByDate($date,$type='end');	
		
		$dateFrom	= ($this->getRequest()->getParam('dateFrom') != null) ? $this->getRequest()->getParam('dateFrom'):$thisWeekStartDate;
		$dateTo		= ($this->getRequest()->getParam('dateTo') != null) ? $this->getRequest()->getParam('dateTo'):$thisWeekEndDay;	
		
		//check if dates are week start date and week end date
		$weekStartDate	=  Needs_Tools::getWeekDaysByDate($dateFrom,$type='start');
		$weekEndDay		=  Needs_Tools::getWeekDaysByDate($dateFrom,$type='end');	
		if($dateFrom != $weekStartDate || $dateTo != $weekEndDay)
		{
			$this->_redirect(WEBROOT.'projected-costs');
		}
		
		$form = new Default_Form_ProjectedCosts();		
		$form->showHistory($dateFrom,$dateTo);
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/projected-costs/add.phtml'))));
		
		$this->view->form = $form;			
		
		if($this->getRequest()->isPost())
		{
			if($form->isValid($this->getRequest()->getPost())) 
			{
				$success = false;
				$post = $this->getRequest()->getPost();
				$idManager	= Zend_Registry::get('user')->getId();
				$result		= Needs_Tools::fetchDistrictManagerShops($idManager);
				if($result)
				{					
					$model = new Default_Model_ProjectedCost();
					$model->setOptions($post);						
					$model->setIdUser($idManager);							
					if($id = $model->save())
					{
						foreach ($result as $value){
							$modelPCS = new Default_Model_ProjectedCostShops();
							$modelPCS->setIdProjectedCost($id);
							$modelPCS->setIdShop($value->getId());
							$modelPCS->setFoodCost($post['foodCost_'.$value->getId()]);
							$modelPCS->setLaborCost($post['laborCost_'.$value->getId()]);
							$modelPCS->save();
						}
					}
				}
				if($success){
					$this->_flashMessenger->addMessage("<div class='success canhide'><p>Projected costs was successfully saved!</p><a href='javascript:;'></a></div>");
				}else{
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Some error occurred while saving projected cost!</p><a href='javascript:;'></a></div>");
				}
				$this->_redirect(WEBROOT.'projected-costs/index/dateFrom/'.$dateFrom.'/dateTo/'.$dateTo);
			}
		}
	}
}	

