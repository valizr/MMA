<?php
class ShopsController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
		require_once APPLICATION_PUBLIC_PATH.'/library/tsThumb/ThumbLib.inc.php';
		
		$bootstrap = $this->getInvokeArg('bootstrap');
        if($bootstrap->hasResource('db')) {
        	$this->db = $bootstrap->getResource('db');
        }
	}
	
	public function indexAction()
	{		
		$params = array();		
		$name = $this->getRequest()->getParam('name');	
		
		$shop = new Default_Model_Shops();
		$select = $shop->getMapper()->getDbTable()->select()
						->where('NOT deleted');
						if(!empty($name)){
							$params['name'] = $name;
							$select->where('name LIKE ?','%'.$name.'%');
						}
						$select->order(array('name ASC'));		
		
		$result = $shop->fetchAll($select);
		if(NULL != $result)
		{
			$paginator = Zend_Paginator::factory($result);
			$paginator->setItemCountPerPage(10);
			$paginator->setCurrentPageNumber($this->_getParam('page'));
			$paginator->setPageRange(5);
			$this->view->result = $paginator;
			$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
			$this->view->totalItemCount = $paginator->getTotalItemCount();

			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination.phtml');
		}	
		
		$form = new Default_Form_ShopsSearch();
		$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/shops/shops-search.phtml'))));
		$this->view->form = $form;
		$this->view->search=$params;
		
		$formAdd =new Default_Form_Shops();	
		$formAdd->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/shops/add-shops.phtml'))));
		$this->view->formAdd = $formAdd;
		
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			if(!empty($post['action']) && $post['action'] == 'add'){
				if($formAdd->isValid($post)) 
				{
					$model=new Default_Model_Shops();
					$model->setOptions($formAdd->getValues());
					if($model->save())
					{
						//mesaj de succes
						$this->_flashMessenger->addMessage("<div class='success  canhide'><p>Shop successfully added<p></div>");
					}else{
						//mesaj de eroare
						$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The shop was not successfully saved<p></div>");
					}
					//redirect
					$this->_redirect(WEBROOT.'shops/index');	
				}
			}
		}
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$model = new Default_Model_Shops();
		if ($model->find($id)) {
			$form = new Default_Form_Shops();
			$form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/shops/edit-shops.phtml'))));
			$form->edit($model);
			$this->view->form = $form;

			if ($this->getRequest()->isPost()) {
				if ($this->getRequest()->getPost('submit')) {
					if ($form->isValid($this->getRequest()->getPost())) {
						$model->setOptions($form->getValues());
						if ($model->save()) {
							$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The shop was successfully modified<a href='javascript:;'>Close</a></p></div>");
						} else {
							$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The shop was not modified<a href='javascript:;'>Close</a></p></div>");
						}

						$this->_redirect(WEBROOT.'shops/index');
					}
				}
			}
		}
	}
	
	public function deleteAction() {
		$userId = NULL;
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null!=$authAccount) {
			if (null!=$authAccount->getId()) {
				$userId = $authAccount->getId();   
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$model = new Default_Model_Shops();
		if ($model->find($id)) {
			
				if ($model->delete()) {
					$this->_flashMessenger->addMessage("<div class='success  canhide'><p>The shop was successfully deleted!<a href='javascript:;'>Close</a></p></div>");
				} else {
					$this->_flashMessenger->addMessage("<div class='failure canhide'><p>The shop was not deleted!<a href='javascript:;'>Close</a></p></div>");
				}
			$this->_redirect('shops/index');
		}
	}
	public function statusAuditAction()
	{	
		$dailySales = new Default_Model_DailySales();
		$select = $dailySales->getMapper()->getDbTable()->select();
		$select->from(array('d'=>'daily_sales'),array('d.idShop','d.date','d.id'))
					->where('audited = ?',0)
					->order('date DESC');
		$result = $dailySales->fetchAll($select);

		$this->view->totalNr = Needs_Tools::getTotalNoAudit();
		if(NULL != $result)
		{
			$paginator = Zend_Paginator::factory($result);
			$paginator->setItemCountPerPage(10);
			$paginator->setCurrentPageNumber($this->_getParam('page'));
			$paginator->setPageRange(5);
			$this->view->result = $paginator;
			$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
			$this->view->totalItemCount = $paginator->getTotalItemCount();

			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination.phtml');
		}
	}
	public function statusDailySalesAction()
	{
		$curdate = date('Y-m-d');
		$sixMonths = strtotime($curdate.' - 6 months');
		$startDate = strtotime(START_DATE);
		
		if($startDate >= $sixMonths){
			$finalDate = date('Y-m-d', $startDate);
		}else{
			$finalDate = date('Y-m-d', $sixMonths);
		}
		$select = $this->db->query("select aa.Date,dss.date,dss.shopIds,dss.count,s.count as nr
					from (
						select curdate() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as Date
						from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
						cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
						cross join (select 0 as a union all select 1) as c
					) aa
					LEFT JOIN(SELECT ds.date,GROUP_CONCAT(s.id) as shopIds,COUNT(s.id) as count
					   FROM `daily_sales` as ds
					   LEFT JOIN( shops as s) ON(ds.idShop=s.id)
					   WHERE s.deleted = 0
								GROUP BY ds.date) as dss ON(dss.date=aa.Date)
					CROSS JOIN(select COUNT(shops.id) as  count FROM shops WHERE shops.deleted = 0) as s
					where TO_DAYS(aa.Date) > TO_DAYS('$finalDate') 
					HAVING (IFNULL(dss.count,0)=0 OR IFNULL(dss.count,0)<nr)
					ORDER BY aa.Date DESC");
		$result= $select->fetchAll();
		
		$shop = new Default_Model_Shops();
		$selectShops = $shop->getMapper()->getDbTable()->select()
						->from(array('s' => 'shops'), array('id'=>'s.id','name'=>'s.name'))
						->where('NOT s.deleted');
						$selectShops->order(array('id ASC'));		
		
		$resultShops = $shop->fetchAll($selectShops);
		
		$shopIds = array();
		$shopNames = array();
		foreach($resultShops as $value){
			$shopIds[] = $value->getId();
			$shopNames[$value->getId()] = $value->getName(); 
		}
		
		$this->view->arrayShopIds = $shopIds;
		$this->view->arrayShopNames = $shopNames;
		
		if(NULL != $result)
		{
			$paginator = Zend_Paginator::factory($result);
			$paginator->setItemCountPerPage(10);
			$paginator->setCurrentPageNumber($this->_getParam('page'));
			$paginator->setPageRange(5);
			$this->view->result = $paginator;
			$this->view->itemCountPerPage = $paginator->getItemCountPerPage();
			$this->view->totalItemCount = $paginator->getTotalItemCount();

			Zend_Paginator::setDefaultScrollingStyle('Sliding');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination.phtml');
		}
	}
}