<?php 
class Needs_Roles
{	
	
	public static function hasAccess($roleId, $resourceConst,$displayNone = NULL)
	{	
		//if isAdmin no need for futher verification
		if(self::isAdmin($roleId))
		{
			if ($displayNone){
				return '';
			}		
			return true;
		}		
		
		//find resource
		$result = false;
		$resourceModel = new Default_Model_Resource();
		$select2 = $resourceModel->getMapper()->getDbTable()->select()
					->from(array('resource'),array('id'))
					->where('resource = ?',$resourceConst);
		$resourceModel->fetchRow($select2);
		if($resourceModel->getId() != null)
		{
			//find resource and role connection, if there is any
			$resourceRole = new Default_Model_ResourceRole;
			$select3 = $resourceRole->getMapper()->getDbTable()->select()
						->where('idResource = ?',$resourceModel->getId())
						->where('idRole = ?',$roleId);
			$resourceRole->fetchRow($select3);
			if($resourceRole->getId() != NULL)
			{
				$result = true;
			}
		}
		if($displayNone && !$result){
			$result = ' style="display:none"';
		}elseif ($displayNone){
			return '';
		}
		return $result;
	}
	

	public static function hasAccessFinancialbyProjectStatus($roleId,$statusId, $actionType)
	{	
		//if isAdmin no need for futher verification
		if(self::isAdmin($roleId))
		{				
			return true;
		}	
		
		//BEGIN:build resource const 
		//$resourceConstAll = '';
		$resourceConst = '';
		if($actionType == 'edit'){
			$resourceConst = 'modify_projects_status_'.$statusId;
			//$resourceConstAll = 'modify_projects_all_statuses';
		}elseif($actionType == 'view'){
			$resourceConst = 'view_projects_status_'.$statusId;
			//$resourceConstAll = 'view_all_status_projects';
		}
		//END:build resource const
		
		//if Is Financial - must see all projects by statuse (from 3 to 6) 
		if($this->hasAccess($roleId, $resourceConst) && $this->hasAccess($roleId, 'view_financial')){
			return true;
		}
		return false;	
	}
	
	public static function fetchProjectsByRoleAndStatus($userId,$roleId,$financiar=false,$conditions =array())
	{
		$conditions['userType'] = !empty($conditions['userType'])?$conditions['userType']:'p.deliveryDate';
		
		//subselect for roles and resources
		$resourceModel = new Default_Model_ResourceRole();
		$subSql =$resourceModel->getMapper()->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('resource_role'), array('existaId'=>'resource_role.id'))
				->joinInner(array('r'=>'resource'),'r.id=resource_role.idResource',array('r.resource'))
                ->where('resource_role.idRole= ?',$roleId);		
		
		
		
		$projects = new Default_Model_Projects();
		$select = $projects->getMapper()->getDbTable()->select()
					->setIntegrityCheck(false);
		if(!empty($conditions['count'])){			
			$select->from(array('p'=>'projects'),
						array('id'=>'p.id'));
		}else{
			$select->from(array('p'=>'projects'),
						array('p.id','p.idUser','p.idDepartment','p.idType','p.idProjectManager','p.idChiefDepartment','p.idProjectStatus','p.name','p.description',
							 'p.executionTerm','p.noVatValue','p.deadlinePayment','p.attachedFiles','p.startDate','delivery'=>'p.deliveryDate','p.created','p.deleted','deliveryDate'=>$conditions['userType']));
		
		}	
		
		$select->joinLeft(array('rr'=>new Zend_Db_Expr('(' . $subSql . ')')),'rr.resource=CONCAT(\'view_projects_status_\',p.idProjectStatus)',array('rr.existaId'));
		if(!$financiar){
			$select->where("p.idUser='".$userId."' OR p.`idProjectManager`='".$userId."' OR p.`idChiefDepartment`='".$userId."'");
		}
		if(!empty($conditions['name'])){
			$select->where('name LIKE ?','%'.$conditions['name'].'%');
		}
		if(!empty($conditions['adaugat_de'])){			
			$select->where('idUser = ?',$conditions['adaugat_de']);
		}
		if(!empty($conditions['idDepartment'])){	
			$select->where('idDepartment = ?',$conditions['idDepartment']);
		}
		if(!empty($conditions['status'])){	
			$select->where('idProjectStatus = ?',$conditions['status']);
		}
		if(!empty($conditions['month'])){
			$select->where('MONTH(deliveryDate) = ?',$conditions['month']);
		}
		if(!empty($conditions['date'])){
				$dataStr = strtotime($conditions['date']);
				$newDate = date('Y-m-d',$dataStr);
				if($financiar){
					$conditions['date'] = $newDate;
					$select->having('`deliveryDate` = ?',$newDate);
				}else{
					$conditions['date'] = $newDate;
					$select->where("`startDate` = '$newDate' OR `deliveryDate` = '$newDate'");
				}
			}
		if(!empty($conditions['dataStart'])){
				$dataStr = strtotime($conditions['dataStart']);
				$newDate = date('Y-m-d',$dataStr);
				if($financiar){
					$conditions['dataStart'] = $newDate;
					$select->having('`deliveryDate` >= ?',$newDate);
				}else{
					$conditions['dataStart'] = $newDate;
					$select->where("`startDate` >= '$newDate' OR `deliveryDate` >= '$newDate'");
				}
			}
		if(!empty($conditions['dataEnd'])){
				$dataStr = strtotime($conditions['dataEnd']);
				$newDate = date('Y-m-d',$dataStr);
				if($financiar){
					$conditions['dataEnd'] = $newDate;
					$select->having('`deliveryDate` <= ?',$newDate);
				}else{
					$conditions['dataEnd'] = $newDate;
					$select->where("`startDate` >= '$newDate' OR `deliveryDate` <= '$newDate'");					
				}
			}
			$select->where('NOT deleted')		
				   ->having('IFNULL(rr.existaId,0)>0') ;
			if(!empty($conditions['limit'])){
				$select->limit($conditions['limit']);
			}	
			
		if($conditions['pagination']){
			return $select;
		}	
			
		if(!empty($conditions['count'])){			
			$result = $projects->fetchAll($select);
		}else{
			$result = $projects->fetchAll($select);
		}
		return $result;
	}

	public static function projectStatistics($userId,$roleId,$financiar=false,$conditions =array())
	{
		
		//resource and role
		$resourceModel = new Default_Model_ResourceRole();
		$subSql =$resourceModel->getMapper()->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('resource_role'), array('existaId'=>'resource_role.id'))
				->joinInner(array('r'=>'resource'),'r.id=resource_role.idResource',array('r.resource'))
                ->where('resource_role.idRole= ?',$roleId);		
		
		//project subselect
		$subquery = "(SELECT ".$conditions['sum']." FROM `projects` AS pp WHERE (MONTH(p.`deliveryDate`) = MONTH(pp.`deliveryDate`) AND (YEAR(p.`deliveryDate`) = YEAR(pp.`deliveryDate`))
			AND NOT p.deleted	".$conditions['where']."))";

		//project select
		$projectsChart = new Default_Model_Projects();
		$selectChart = $projectsChart->getMapper()->getDbTable()->select()
					->setIntegrityCheck(false)
					->from(array('p'=>'projects'),
						  array("id" => new Zend_Db_Expr($subquery),
							  "name" => "CONCAT(DATE_FORMAT(".$conditions['userType'].", '%b'),'(',COUNT(p.id),')')",
							  'deliveryDate'=>$conditions['userType']));
		$selectChart->joinLeft(array('rr'=>new Zend_Db_Expr('(' . $subSql . ')')),'rr.resource=CONCAT(\'view_projects_status_\',p.idProjectStatus)',array('rr.existaId'));
		if(!$financiar){
			$selectChart->where("p.idUser='".$userId."' OR p.`idProjectManager`='".$userId."' OR p.`idChiefDepartment`='".$userId."'");
			$selectChart->where('p.deadlinePayment IS NOT NULL')
						->where('p.executionTerm IS NOT NULL');
		}
		$selectChart->where('NOT p.deleted')
					->where('YEAR(p.deliveryDate) = YEAR(NOW())');		
		$selectChart->group('MONTH(p.`deliveryDate`)')						
					->having('IFNULL(rr.existaId,0)>0')
					->order(array('MONTH(p.`deliveryDate`) ASC'));

		$resultChart = $projectsChart->fetchall($selectChart);
		return $resultChart;		
	}

	public static function hasAccesToProjectStatus($userId,$roleId,$projectId,$financiar=false)
	{
		$resourceModel = new Default_Model_ResourceRole();
		$subSql =$resourceModel->getMapper()->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('resource_role'), array('existaId'=>'resource_role.id'))
				->joinInner(array('r'=>'resource'),'r.id=resource_role.idResource',array('r.resource'))
                ->where('resource_role.idRole= ?',$roleId);		
		
		$projects = new Default_Model_Projects();
		$select = $projects->getMapper()->getDbTable()->select()
					->setIntegrityCheck(false)
					->from(array('p'=>'projects'),
						array('p.id','p.idUser','p.idDepartment','p.idType','p.idProjectManager','p.idChiefDepartment','p.idProjectStatus','p.name','p.description',
							 'p.executionTerm','p.noVatValue','p.deadlinePayment','p.attachedFiles','p.startDate','delivery'=>'p.deliveryDate','p.created','p.deleted','deliveryDate'=>$conditions['userType']));
		$select->joinLeft(array('rr'=>new Zend_Db_Expr('(' . $subSql . ')')),'rr.resource=CONCAT(\'view_projects_status_\',p.idProjectStatus)',array('rr.existaId'));
		if(!$financiar){
			$select->where("p.idUser='".$userId."' OR p.`idProjectManager`='".$userId."' OR p.`idChiefDepartment`='".$userId."'");
		}
		
			$select->where('NOT p.deleted')	
					->where('p.id = ?',$projectId)
				   ->having('IFNULL(rr.existaId,0)>0') ;
		$projects->fetchRow($select);
		if($projects->getId()){
			return true;
		}
		return false;
	}
	
	public static function hasAccessToStatusList($roleId)
	{	
		//if isAdmin no need for futher verification
		if(self::isAdmin($roleId))
		{				
			$projectsStatus = new Default_Model_ProjectStatus();
			$select = $projectsStatus->getMapper()->getDbTable()->select()				
												->where('NOT deleted');
			$result = $projectsStatus->fetchAll($select);		
		
			return $result;
		}
		
		
		$resourceModel = new Default_Model_ResourceRole();
		$subSql =$resourceModel->getMapper()->getDbTable()->select()
                ->setIntegrityCheck(false)
                ->from(array('resource_role'), array('existaId'=>'resource_role.id'))
				->joinInner(array('r'=>'resource'),'r.id=resource_role.idResource',array('r.resource'))
                ->where('resource_role.idRole= ?',$roleId);		
		
		$projectsStatus = new Default_Model_ProjectStatus();
		$select = $projectsStatus->getMapper()->getDbTable()->select()
					->setIntegrityCheck(false)
					->from(array('p'=>'project_status'),array('p.id','p.name'));
		$select->joinLeft(array('rr'=>new Zend_Db_Expr('(' . $subSql . ')')),'rr.resource=CONCAT(\'modify_projects_status_\',p.id)',array('rr.existaId'));	
		$select->where('NOT p.deleted')	
				->having('IFNULL(rr.existaId,0)>0') ;
		$result = $projectsStatus->fetchAll($select);		
		
		return $result;
	}

	public static function isAdmin($roleId)
	{
		//find resource		
		$resourceAdmin = new Default_Model_Role();
		$selectA = $resourceAdmin->getMapper()->getDbTable()->select()					
					->where('isAdmin = ?','1')
					->where('id = ?',$roleId);	
		$resourceAdmin->fetchRow($selectA);
		if($resourceAdmin->getId() != null)
		{
			return true;
		}
		
		return false;
	}

	public static function hasAccessbyId($roleId, $resourceId)
	{
		$result = false;
		if($roleId == 'guest'){
			return false;
		}
		//find resource and role connection, if there is any
		if(self::isAdmin($roleId))
		{
			return true;
		}	
		$resourceRole = new Default_Model_ResourceRole;
		$select3 = $resourceRole->getMapper()->getDbTable()->select()
					->where('idResource = ?',$resourceId)
					->where('idRole = ?',$roleId);
		$resourceRole->fetchRow($select3);
		if($resourceRole->getId() != NULL)
		{
			$result = true;
		}			
		return $result;
	}
	
	public static function getSelected($url,$text='selected')
	{
		$urlArray = array();
		$request = new Zend_Controller_Request_Http($url);
		Zend_Controller_Front::getInstance()->getRouter()->route($request);
		// Module name
		$urlArray['module'] = $request->getModuleName();
		// Controller name
		$urlArray['controller'] = $request->getControllerName();
		// Action name
		$urlArray['action'] = $request->getActionName();
		
		$currentArray = array();
		$currentRequest = Zend_Controller_Front::getInstance();
		$currentArray['module'] = $currentRequest->getRequest()->getModuleName();
		$currentArray['controller'] = $currentRequest->getRequest()->getControllerName();
		$currentArray['action'] = $currentRequest->getRequest()->getActionName();
		if(($urlArray['module'] == $currentArray['module']) && ($urlArray['controller'] == $currentArray['controller'])){
			return $text;
		}
	}
	

//	
	public static function getAllResources($coreRoleId,$isAdmin = false,$canAddResourceRole = false,$roleId = null,$searchText = null)
	{
		if($roleId == null){
			$roleId = $coreRoleId;
		}
		
		$return = '<input type="hidden" name="roleId" value="'.$roleId.'" />'; //hidden input to send the role id
		
		//select the logged in rol resources that he has access to
		$model = new Default_Model_Resource();
		$select = $model->getMapper()->getDbTable()->select()
					    ->from(array('r' => 'resource'),array('r.id','r.idGroup','r.description'));
		if(!self::isAdmin($coreRoleId))
		{
					$select->joinLeft(array('rr' => 'resource_role'),'r.id = rr.idResource',array('rid' => 'rr.id'))
					->where('rr.idRole = ?',$coreRoleId);
		}
		if($searchText != null){
					$select->where('r.description LIKE (?)','%'.$searchText.'%');
		}	
					$select->where('NOT r.deleted')
						   ->order(array('r.idGroup ASC','r.id ASC'));					
					$select->setIntegrityCheck(false);
//		echo 	$select;		
		$result = $model->fetchAll($select);
		
		if($result){
			$var = null;			
			foreach ($result as $value) {				
				//BEGIN:Display resource category
				if(($var == NULL || ($var != $value->getIdGroup())) && $value->getIdGroup()!= null){					
					if($var != null){
						$return .= '</div>';	
						$return .= '</div>';	
					}		
					$return .= "<div class='resourceRole'>
									<h3>{$value->getResourceGroup()->getName()}</h3>
									<div>";
				}
				//END:Display resource category
				
				//Check if role id has access to resource,and check;
				$checked = '';								
				if(self::hasAccessbyId($roleId, $value->getId()))
				{
					$checked = 'checked ';					
				}
				
				//If admin disable checkbox
				$readonly = '';
				if($canAddResourceRole == false || $roleId == $coreRoleId)
				{
					//$readonly = 'disabled="disabled"';
					$isAdmin = (self::isAdmin($coreRoleId))?true:false;
					if($isAdmin)
					{
						$checked = 'checked ';
					}	
				}else{
					if($isAdmin)
					{						
						$checked = 'checked ';
						//$readonly = 'disabled="disabled"';						
					}else{						
						//BEGIN:If parent role doesn't have the resource, disable checkbox
						//get parent roleId if there is any
						$role = new Default_Model_Role();
						$role->find($roleId);
						if(!self::isAdmin($role->getIdParent())){
							if($role->getIdParent() != null && !self::hasAccessbyId($role->getIdParent(), $value->getId()))
							{
								//$readonly = 'disabled="disabled"';
							}
						}
						
						//END:If parent role doesn't have the resource, disable checkbox
					}	
				}				
				
				$return .= '<div class="resourceName">
								<input type="checkbox" class="roleResource" value="'.$value->getId().'" name="roleResource[]" '.$checked.$readonly.' />'.$value->getDescription().'
						   </div>';
				$var = $value->getIdGroup();
			}
			$return .= '</div>';
			$return .= '</div>';
		}else{
			$return .='Nu a fost gasit nici un rezultat.';
		}
		return $return;
	}
	
	public static function checkIfSubRole($id,$childId,$allowMe = false)
	{
		if($allowMe == true && $id == $childId)
		{
			return true;
		}
		//check if child element					
		$role = new Default_Model_Role();
		$role->find($id);
		$graph = new Needs_Graph($role, false,array('parentId','id'),'array');
		if($graph->ifSubchild($childId))
		{
			return true;
		}
		return false;
	}	
	
	
	
	public static function tenancyAdmin($tenancyId)
	{
		$model = new Default_Model_Utilizatori();
		$tableName = $model->getMapper()->getDbTable()->info();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array($tableName['name']), array('id', 'tenancyId', 'nume', 'prenume', 'parentId'))
				->where('tenancyId = ?', $tenancyId)
				->where('parentId = ?', 1);
		$result = $model->fetchRow($select);
		if($result)
		{
			return $model;
		}
		return false;
	}
	
	public static function getTasksNumber($obiectiveId)
	{
		$model = new Default_Model_Task();
		$tableName = $model->getMapper()->getDbTable()->info();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array($tableName['name']), array('id'=> 'COUNT(id)'))
				->where('objectiveId = ?', $obiectiveId);				
		$model->fetchRow($select);
		if($model->getId() != NULL)
		{
			return $model->getId();
		}
		return false;
	}
	
	public static function fetchAllRoles()
	{
		$model = new Default_Model_Role();
		$select = $model->getMapper()->getDbTable()->select();
		$result = $model->fetchAll($select);
		if($result)
		{
			return $result;
		}
		return false;
	}	
	
	public static function fetchAllResourceGroups()
	{
		$modelAdminMenu = new Default_Model_ResourceGroup();
		$select = $modelAdminMenu->getMapper()->getDbTable()->select()
						->where('deleted = ?', 0)								
						->order('order ASC');
		$result = $modelAdminMenu->fetchAll($select);
		return $result;
	}
}