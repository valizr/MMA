<?php
class DailySalesController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}
	
	public function indexAction()
	{	
		$date = ($this->getRequest()->getParam('date') ? $this->getRequest()->getParam('date') : date('Y-m-d'));
		
		$form = new Default_Form_DailySales();
		$form->showHistory($date);
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/daily-sales/add.phtml'))));
		$this->view->form = $form;

		$errorForm = new Default_Form_DailySalesErrors();
		$errorForm->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/daily-sales-errors/add.phtml'))));
		$this->view->formError = $errorForm;
		
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
		
			if(!empty($post['action']) && $post['action'] == 'add'){
			
				if($form->isValid($this->getRequest()->getPost())) 
				{
					$post = $this->getRequest()->getPost();
					$modelSave=new Default_Model_DailySales();
					$modelSave->setOptions($form->getValues());	
					
					$modelSave->setIdShop(Zend_Registry::get('user')->getShop()->getId());
					$modelSave->setOverShortNotification($post['overshort']);
					if($this->getRequest()->getParam('date')){
						$modelSave->setDate($this->getRequest()->getParam('date'));
					}else{
						$modelSave->setDate(date('Y-m-d'));
					}
					
					$arrayGiftNr = array();
					$arrayGiftVal = array();
					
					foreach($post['giftNr'] as $key => $value){ 
   
						$arrayGiftNr[]		= $post['giftNr'][$key]; 
						$arrayGiftVal[]		= $post['giftVal'][$key]; 
					}
					if( $id = $modelSave->save())
					{
						foreach($arrayGiftNr as $key=>$value){
							$model = new Default_Model_DailySalesGift();
							$model->setIdSale($id);
							$model->setNumber($arrayGiftNr[$key]);
							$model->setValue($arrayGiftVal[$key]);
							$model->save();
						}
						
						$products = Needs_Tools::getProductsByShop();
						$registryNumber = Needs_Tools::getRegistryNrByShop(); 
						foreach($products as $value){
							$model = new Default_Model_DailySalesProducts();
							$model->setIdSale($id);
							$model->setIdShop(Zend_Registry::get('user')->getShop()->getId());
							$model->setIdProduct($value->getId());							
							$model->setRegister1($post['register1_'.$value->getId()]);							
							
							if($registryNumber == 2){	
								$model->setRegister2($post['register2_'.$value->getId()]);								
							}
							$model->save();
						}
						//mesaj de succes
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Report was added successfully<a href='javascript:;'></a><p></div>");
					}else{
						//mesaj de eroare
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Report was not added<a href='javascript:;'></a><p></div>");
					}
					$this->_redirect(WEBROOT.'daily-sales/index/date/'.$modelSave->getDate());
				}
			}elseif(($post['action']) && $post['action'] == 'errorReport'){
				if($errorForm->isValid($this->getRequest()->getPost())) 
				{
					$post = $this->getRequest()->getPost();
					$model=new Default_Model_DailySalesError();
					$model->setOptions($errorForm->getValues());
					
					$date = $post['year'].'-'.$post['month'].'-'.$post['day'];					
					$shopId = Zend_Registry::get('user')->getShop()->getId();
					$userId = Zend_Registry::get('user')->getId();
					$saleId = Needs_Tools::getSaleIdByDateAndShopId($date,$shopId);
					if($saleId){
						$model->setIdSale($saleId->getId());
						$model->setIdUser($userId);
						$model->setDate($date);
						
						if( $id = $model->save())
						{
							//mesaj de succes
							$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The error report was successfully sent !<a href='javascript:;'></a><p></div>");
						}else{
							//mesaj de eroare
							$this->_flashMessenger->addMessage("<div class='failure canhide'><p>There was an error while sending the report. Please try again !<a href='javascript:;'></a><p></div>");
						}
					}else{
						$strdate = strtotime($date);
						$newDate = date('m-d-Y',$strdate);
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>No report registered for ' $newDate ' ! Please select a different date !<a href='javascript:;'></a><p></div>");
					}
					$this->_redirect(WEBROOT.'daily-sales/index/date/'.$date);	
				}
			}
		}
	}	
	
	public function auditAction()
	{
		$date = ($this->getRequest()->getParam('date') ? $this->getRequest()->getParam('date') : date('Y-m-d'));
		$idShop = ($this->getRequest()->getParam('idShop') ? $this->getRequest()->getParam('idShop') :NULL);
		
		$form = new Default_Form_AuditSearch();
		$form->auditSearch();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/daily-sales/search.phtml'))));
		$this->view->form = $form;


		if(!empty($date) && !empty($idShop)){
			$dateNew = date('Y-m-d',strtotime($date));

			$dailySales = Needs_Tools::checkIfDailySaleCompleted($dateNew,$idShop);
			if($dailySales->getId() != NULL){
				$errors = Needs_Tools::fetchDailySalesErrors($dailySales->getId());
				$this->view->resultError = $errors;

				$formAudit = new Default_Form_DailySaleAudit();
				$formAudit->show($dateNew, $idShop);
				$formAudit->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/daily-sales/audit.phtml'))));
				$this->view->formAudit = $formAudit;
			}else{
				//mesaj de eroare
				$dateShow = date('m-d-Y', strtotime($date));
				$shop = Needs_Tools::getShopById($idShop);
				$this->_flashMessenger->addMessage("<div class='failure canhide'><p>There is no report registered on ' $dateShow ' in shop ' $shop ' ! Please select another date !<a href='javascript:;'></a><p></div>");

				$this->_redirect(WEBROOT.'daily-sales/audit');
			}	
			
			$post = $this->getRequest()->getPost();
			if(!empty($post['action']) && $post['action'] == 'addAudit'){
				if($formAudit->isValid($post)) 
				{
					$post = $this->getRequest()->getPost();
					$dailySales->setOptions($formAudit->getValues());	

					$dailySales->setOverShortNotification($post['overshort']);
					$dailySales->setAudited(1);
					$dailySales->setDateAudited(date('Y-m-d'));

					$dailySales->setDate($post['dateSave']);

					$arrayGiftNr = array();
					$arrayGiftVal = array();

					foreach($post['giftNr'] as $key => $value){ 
						$arrayGiftNr[]		= $post['giftNr'][$key]; 
						$arrayGiftVal[]		= $post['giftVal'][$key]; 
					}
					
					if($dailySales->save())
					{						
						//BEGIN:GIFT CARDS EDIT
						foreach($arrayGiftNr as $key=>$value){
							$model = new Default_Model_DailySalesGift();						
							$model->setIdSale($dailySales->getId());
							$model->setNumber($arrayGiftNr[$key]);
							$model->setValue($arrayGiftVal[$key]);
							$model->save();
						}
						//END:GIFT CARDS EDIT						

						$products = Needs_Tools::getProductsByShop($idShop);
						$registryNumber = Needs_Tools::getRegistryNrByShop($idShop);
						foreach($products as $value){
							//find daily sales products by saleId ad prodId
							$model = Needs_Tools::fetchDailySalesProductbySalesId($dailySales->getId(),$value->getId());
							$model->setIdSale($dailySales->getId());
							$model->setIdShop($idShop);
							$model->setIdProduct($value->getId());
							$model->setRegister1($post['register1_'.$value->getId()]);

							if($registryNumber == 2){	
								$model->setRegister2($post['register2_'.$value->getId()]);
							}
							$model->save();
						}
						//mesaj de succes
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Report was saved successfully<a href='javascript:;'></a><p></div>");
					}else{
						//mesaj de eroare
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>Report was not saved<a href='javascript:;'></a><p></div>");
					}
					$this->_redirect(WEBROOT.'daily-sales/audit/date/'.$date.'/idShop/'.$idShop);
				}
			}
		}
	}	
	
}