<?php
class AjaxController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->getHelper('layout')->disableLayout();
        $bootstrap = $this->getInvokeArg('bootstrap');
        if($bootstrap->hasResource('db')) {
        	$this->db = $bootstrap->getResource('db');
        }

       	$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
    }
	
	public function saveProductsOrderAction()
	{
		$ordersString = $this->getRequest()->getParam('order');
		$idGroup = $this->getRequest()->getParam('id');
		$products = explode(',',$ordersString);
		
		$arrayAll = array();
		//Needs_Tools::DeleteLegaturibyGroup( $groupId );

		$modelGroupAll = new Default_Model_ProductGroups();
		$selectAll = $modelGroupAll->getMapper()->getDbTable()->select()
												->where('idGroup = ?', $idGroup);
		$resultsAll = $modelGroupAll->fetchAll($selectAll);
		foreach ($resultsAll as $value){
		$arrayAll[] = $value->getIdProduct(); //all the products that are currently in the table	
		}
		$resultDiff = array_diff($arrayAll, $products);
		foreach ($resultDiff as $deleteProd){
		Needs_Tools::DeleteLegaturi( $deleteProd ); //here we delete the prods that were before in the table, but are not anymore in the new list of prods
		}

		foreach ($products as $key=>$value) 
		{
			$model = new Default_Model_ProductGroups();
			$select = $model->getMapper()->getDbTable()->select()
						->where('idProduct = ?',$value)
						->where('idGroup = ?',$idGroup);
			$model->fetchRow($select);
			if($model->getId() != NULL){
				$id=$model->getId();
				$model->find($id);
				$model->setOrder($key);			
				$model->saveOrder();
			}
		}
	}
	
	public function editProductsAction()
	{
		$productsString = $this->getRequest()->getParam('products');
		$idGroup = $this->getRequest()->getParam('id');
		$products = explode(',',$productsString);
		foreach ($products as $key => $idProductGroups) {//all products that need to be in the table
			$modelGroup = new Default_Model_ProductGroups();
			$modelGroup->setIdProduct($idProductGroups);
			$modelGroup->setIdGroup($idGroup);

			$modelGroupFind = new Default_Model_ProductGroups();
			$selectFind = $modelGroupFind->getMapper()->getDbTable()->select()
					->where('idProduct = ?',
							$idProductGroups)
					->where('idGroup = ?',
					$idGroup);
			$modelGroupFind->fetchRow($selectFind);
			if ($modelGroupFind->getId() == NULL) { //existing product added in the database
				$modelGroup->setOrder($key);
				$modelGroup->save();
			}
		}
	}
	
	public function weeklyCostsAction()
	{
		$result = array();
		$result["laborCost"]='';
		$result["foodCost"]='';
		$dateFrom = $this->getRequest()->getParam('dateFrom');
		$dateTo = $this->getRequest()->getParam('dateTo');
		$idUser = $this->getRequest()->getParam('idUser');
		$modelWeeklyCosts = new Default_Model_WeeklyCosts();
		$selectFind = $modelWeeklyCosts->getMapper()->getDbTable()->select()
					->where('dateFrom = ?',
							$dateFrom)
					->where('dateTo = ?',
							$dateTo)
					->where('idUser = ?',
					$idUser);
			$modelWeeklyCosts->fetchRow($selectFind);
			
			if ($modelWeeklyCosts->getId() !== NULL) { //existing weekly cost added in the database
				$result["idWeeklyCosts"]=$modelWeeklyCosts->getId();
				$result["laborCost"]=$modelWeeklyCosts->getLaborCost();
				$result["foodCost"]=$modelWeeklyCosts->getFoodCost();
			}
	echo Zend_Json_Encoder::encode($result);	
	}
//	public function savePageOrderAction()
//	{
//		$ordersString = $this->getRequest()->getParam('order');
//		$orders =  explode(',',$ordersString);
//		foreach ($orders as $key=>$value) 
//		{
//			$model = new Default_Model_Page();
//			$model->find($value);
//			$model->setOrder($key);			
//			$model->saveOrder();			
//		}
//	}
	
	//BEGIN:PICTURES UPLOAD
	public function uploadProjectFilesAction()
	{
		$result = array();
		$result['success'] = 0;
		$result['message'] = 'Some error occured! Please try again later!';
		if (!empty($_FILES)) {
                        $width=1024;
                        $height=1024;
			$fileName = '';
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = APPLICATION_PUBLIC_PATH.'/media/temps';            
			
			$info = pathinfo($_FILES['Filedata']['name']);	
			$info['extension'] = strtolower($info['extension']);
			$hashkey = "_".date('d-m-Y');
                        //$hashkey = md5(sha1(uniqid(mt_rand(10000, 90000).microtime(true), true)));
			//$hashkey = "_".substr($hashkey, 0, 6);
                       
			$shorteFilename = substr($info['filename'], 0,30);
			$fileName = $shorteFilename.$hashkey.".".$info['extension'];	//new name		
			$targetFile = rtrim($targetPath,'/') . '/' . $fileName;		// new path

			// Validate the file type
			//@move_uploaded_file($tempFile,$targetFile);
			//@chmod($targetFile, 0777);
			//save in Admin Temp Files
            $resize = new Needs_Resize($tempFile);
            $resize->resizeTo(1024, 1024);
            $resize->saveImage($targetFile);
                        
			$tempFiles = new Default_Model_TempFiles();
			$tempFiles->setUserId(Zend_Registry::get('user')->getId());
			$tempFiles->setFileName($fileName);               
			$tempFiles->setType('project_files'); 
			$tempFiles->setFileSize(Needs_Tools::findsize($targetFile)); 
			$tempFiles->setFileType($info['extension']);
			$tempId = $tempFiles->save();

			if($tempId){
				$result['success'] = 1;
				$result['fileId'] = $tempId;
			}
		}
		echo Zend_Json_Encoder::encode($result);
	}

    
    public function showProjectUploadsAction() 
	{
		$return     = '';
		$fileId     = (int)  $this->getRequest()->getParam('fileId');
		$fileType   =  $this->getRequest()->getParam('fileType');
       	$tempFiles = new Default_Model_TempFiles();		
		if($tempFiles->find($fileId)){			
			$return .= '<tr class="defaultShow_'.$fileId.'">
							<td class="product_col1">	
								';	
            if(empty($fileType) || $fileType != 'file'){
                $return .= '     <div>
                                    <img src="'.WEBROOT.'/media/temps/'.$tempFiles->getFileName().'" width="80"/>'
                                . '</div>';
            }else{
                 $return .= '     <a href="'.WEBROOT.'/media/temps/'.$tempFiles->getFileName().'" target="_blank">'.$tempFiles->getFileName().'</a>';//$tempFiles->getFileType()
            }
			
			$return .= '	</td>
					<td><a href="javascript:;" class="deleteDefault" rel="'.$fileId.'" data-saved="Yes" title="Delete">
						<img alt="Delete" src="'.WEBROOT.'/theme/admin/images/delete.gif">
                                            </a>
					</td>
				</tr>';			
		}
		echo Zend_Json_Encoder::encode($return);
	}
	
	
	public function showSavedExpensesFileAction() //show file in edit section of expenses
	{
		$return = '';
		$expenseId = (int)  $this->getRequest()->getParam('expenseId');
		//$projectType =  ($this->getRequest()->getParam('type'))?$this->getRequest()->getParam('type'):'project_files';
                if($expenseId)
		{
			$projectFiles = Needs_Tools::getGallerybyId($expenseId,false);
			if($projectFiles)
			{
				foreach ($projectFiles as $value) 
				{
					
					if( file_exists( APPLICATION_PUBLIC_PATH.'/media/files/'.$value->getName())) {
						
						$return .= '<tr class="defaultSavedShow_'.$value->getId().'" >
										';	

						$return .= '	<td class="product_col1">
									<a href="'.WEBROOT.'media/files/'.$value->getName().'" target="_blank">'.$value->getName().'</a> 
								</td>';

						$return .= 	'   <td>
											<a href="javascript:;" class="deleteSavedDefault" rel="'.$value->getId().'">
												<img alt="Delete" src="'.WEBROOT.'theme/admin/images/delete.gif">
											</a>';
						$return .= '	</td>
									</tr>'; 
					}
					
				}
			}
		}
		echo Zend_Json_Encoder::encode($return);
	}	

	public function saveFileNameAction() //show saved files in view section
	{
		$return = '';
		$id = (int)  $this->getRequest()->getParam('id');
		if($id)
		{
			$fileName=Needs_Tools::checkDuplicatedFileName($newFileName,$newFileExt,$id);
			if($fileName)
			{
				$model = new Default_Model_UploadedFiles();
				$model->find($id);
				$oldfile=$model->getName();
				$model->setName($fileName.".".$model->getType());
				if($model->save())
				{
					$return = $fileName;
					rename(APPLICATION_PUBLIC_PATH.'/media/files/'.$oldfile, APPLICATION_PUBLIC_PATH.'/media/files/'.$fileName.'.'.$model->getType());
				}else{
					$return = "Error modifying file";
				}	
			}
		}
		echo $return;
	}
	
	public function saveFileDescAction() //show saved files in view section
	{
		$return = '';
		$id = (int)  $this->getRequest()->getParam('id');
		if($id)
		{
			$post = $this->getRequest()->getPost();
			$model = new Default_Model_FileManager();
			$model->find($id);
			$model->setDescription($post['update_value']);
			if($model->save())
			{	
				$return = $post['update_value'];
			}else{
				$return = "Error modifying file description";
			}
		}
		echo $return;
	}
        
        public function saveExpenseincomeNameAction() //show saved files in view section
	{
		$return = '';
		$id = (int)  $this->getRequest()->getParam('id');
		$newFileName=(string)$this->getRequest()->getParam('filename');
		$newFileExt=(string)$this->getRequest()->getParam('ext');
		
		if($id)
		{
			$post = $this->getRequest()->getPost();
			$model = new Default_Model_Expenses();
			$model->find($id);
			$model->setName($post['update_value']);
			if($model->save())
			{	
				$return = $post['update_value'];
			}else{
				$return = "Error modifying description";
			}
		}
		echo $return;
	}
	
	public function saveExpenseincomePriceAction() //show saved files in view section
	{
		$return = '';
		$id = (int)  $this->getRequest()->getParam('id');
		if($id)
		{
			$post = $this->getRequest()->getPost();
			$model = new Default_Model_Expenses();
			$model->find($id);
			$model->setPrice($post['update_value']);
			if($model->save())
			{	
				$return = $post['update_value'];
			}else{
				$return = "Error modifying value";
			}
		}
		echo number_format($return, 2);
	}
        
        public function saveRecurrentexpenseincomeNameAction() //show saved files in view section
	{
		$return = '';
		$id = (int)  $this->getRequest()->getParam('id');
		$newFileName=(string)$this->getRequest()->getParam('filename');
		$newFileExt=(string)$this->getRequest()->getParam('ext');
		
		if($id)
		{
			$post = $this->getRequest()->getPost();
			$model = new Default_Model_RecurrentExpenses();
			$model->find($id);
			$model->setName($post['update_value']);
			if($model->save())
			{	
				$return = $post['update_value'];
			}else{
				$return = "Error modifying description";
			}
		}
		echo $return;
	}
	
	public function saveRecurrentexpenseincomePriceAction() //show saved files in view section
	{
		$return = '';
		$id = (int)  $this->getRequest()->getParam('id');
		if($id)
		{
			$post = $this->getRequest()->getPost();
			$model = new Default_Model_RecurrentExpenses();
			$model->find($id);
			$model->setPrice($post['update_value']);
			if($model->save())
			{	
				$return = $post['update_value'];
			}else{
				$return = "Error modifying value";
			}
		}
		echo number_format($return, 2);
	}
	
	public function viewSavedMessageFilesAction() //show saved files in view section
	{
		$return = '';
		$id = (int)  $this->getRequest()->getParam('messageId');
		$type =  ($this->getRequest()->getParam('type'))?$this->getRequest()->getParam('type'):NULL;
		
		if($id)
		{
			$projectFiles = Needs_Tools::getGallerybyMessageId($id);
			if($projectFiles)
			{
				
				foreach ($projectFiles as $value) 
				{
					if( file_exists(APPLICATION_PUBLIC_PATH.'/media/files/'.$value->getName())) {
						$return .= '<tr class="defaultSavedShow_'.$value->getId().'" >';	

						$return .= '<td>
										<a href="'.WEBROOT.'/media/files/'.$value->getName().'" target="_blank">'.$value->getName().'</a> 
									</td>';
						
						$return .= '</tr>'; 
					}
				}
			}
		}
		echo Zend_Json_Encoder::encode($return);	
	}
	
	public function deleteExpenseSavedUploadsAction() //delete already uploaded files in edit section
	{
		$return = 0;
		$fileId =  $this->getRequest()->getParam('fileId');
		$picModel = new Default_Model_UploadedFiles();
		if($picModel->find($fileId))
		{
			@unlink(APPLICATION_PUBLIC_PATH.'/media/files/'.$picModel->getName());
			if($picModel->delete()){
				$return = 1;
			}	
		}
		echo Zend_Json_Encoder::encode($return);	
		
	}
	
	public function deleteProjectUploadAction()
	{
		$return = '';
		$fileId =  $this->getRequest()->getParam('fileId');		
		$tempFiles = new Default_Model_TempFiles();		
		if($tempFiles->find($fileId)){
			$return = $tempFiles->getType();
			$tempFile = APPLICATION_PUBLIC_PATH.'/media/temps/'.$tempFiles->getFileName();
			@unlink($tempFile);
			$tempFiles->delete();			
		}		
		echo Zend_Json_Encoder::encode($return);		
	}
	
	//BEGIN:ROLES	
	public function showChildsAction()
	{
		$optionArray = array();
		$webRoot = $this->view->baseUrl();	
		if(Needs_Roles::hasAccess(Zend_Registry::get('user')->getIdRole(),'adaugare_rol')){
			$optionArray['addSubLink'] = $webRoot.'/role/add/id/';
			$optionArray['subName'] = 'subrol';	
		}
		if(Needs_Roles::hasAccess(Zend_Registry::get('user')->getIdRole(),'editare_rol')){
			$optionArray['editLink'] = $webRoot.'/role/edit/id/';
		}
		if(Needs_Roles::hasAccess(Zend_Registry::get('user')->getIdRole(),'stergere_rol')){
			$optionArray['deleteLink'] = $webRoot.'/role/delete/id/';
		}
		$response = '';
		$id = $this->getRequest()->getParam('id');
		$parent = $this->getRequest()->getParam('parent');
		
		$role = new Default_Model_Role();
		$role->find($id);
		
		//if first, show parent node	
		$showParent = ($parent == 'true') ? true : false;
		$graph = new Needs_Graph($role, $showParent,array('idParent','id','name'),'object',true);
		$childRoles = $graph->getTree();	
		if($childRoles)
		{			
			$last = count($childRoles)-1;
			$response .= "<div class='show-users'>"  ;
			foreach ($childRoles as $key =>$value)
			{
				$first = ($value->getId() == Zend_Registry::get('user')->getIdRole())?true:false;
				$paddingFirst = (!$first)?'20px':'0';		
				
				$isFirst = $key == 0 ? 'first' : '';
				$isLast = $last == $key ? 'last' : '';										
				$hasChild = (Needs_Graph::hasChild($value) && !$first) ? true : false ;
				
				$afterLinks = '';
				if($hasChild):
					$afterLinks .= "							
							<a id='jsColapse-{$value->getId()}' class='jsColapse' rel='{$value->getId()}' href='javascript:;' title='Colapse'></a>							
					";
				endif;
				$afterLinks .= "<a class='user-info listingItem roleListing' href='javascript:;' rel='{$value->getId()}' title='Informatii'></a>";
				if(!empty($optionArray['addSubLink'])){
					$afterLinks.= '<a class="user-add-child" href="'.$optionArray['addSubLink'].$value->getId().'" title="Adauga '.$optionArray['subName'].'"></a>';
				}
				if(!empty($optionArray['editLink']) && Zend_Registry::get('user')->getIdRole() != $value->getId())
				{
					$afterLinks.= ' <a class="user-edit" href="'.$optionArray['editLink'].$value->getId().'" title = "Editare"></a>';
				}
				if(!empty($optionArray['deleteLink']) && Zend_Registry::get('user')->getIdRole() != $value->getId())
				{
					$afterLinks.= ' <a class="user-delete confirmDelete" href="'.$optionArray['deleteLink'].$value->getId().'" title="Stergere"></a>';  
				}				
				
				
				$response .="<div id='user-{$value->getId()}' class='user {$isFirst} {$isLast} listingDiv' style='margin-left: {$paddingFirst}'>
							<a class='listingItem roleListing fl' href='javascript:;' rel='{$value->getId()}'>{$value->getName()}</a>	
							<div class='fr'>															
								<div class='user-actions'>
									{$afterLinks}
								</div>
							</div>
						 ";
					
					$response .= "
						<div class='clear'></div>
					";
					$response .= "
					</div>
					";
					if($hasChild):
						$response .= "<div class='child-element' id='load-child-{$value->getId()}'></div>";
					endif;
				
			}
			$response .= '</div>';			
			
		}
		echo Zend_Json_Encoder::encode($response);
		
	}
	
	public function showResourceAction()
	{
		$id			= $this->getRequest()->getParam('id');
		$coreId		= $this->getRequest()->getParam('coreId');
		$searchtext	= $this->getRequest()->getParam('searchtext');
		//BEGIN:get all resources and categories	
	
//		$canAddResourceRole = false;
//		if(Needs_Tools::hasAccess($coreId,'setare_drept_rol')){
			$canAddResourceRole = true;
//		}

		$allResources = Needs_Roles::getAllResources($coreId,false,$canAddResourceRole,$id,$searchtext);
		echo Zend_Json_Encoder::encode($allResources);
		//END:get all resources and categories
				
	}
	
	public function saveResourceAction()
	{
		//check if the auth user has acces to this modul
//		if(!Needs_Tools::hasAccess($myUser = Zend_Registry::get('user')->getRoleId(),'setare_drept_rol')){
//			die('No access!');
//		}
		
		$resourceId = $this->getRequest()->getParam('resourceId');
		$roleId = $this->getRequest()->getParam('roleId');
		$actions = $this->getRequest()->getParam('actions');		
		
		$return = 'Error occured';
		
		//BEGIN:save or delete			
		if($actions == 'add')
		{			
			$modelRR = new Default_Model_ResourceRole();
			$select3 = $modelRR->getMapper()->getDbTable()->select()
						->where('idResource = ?',$resourceId)
						->where('idRole = ?',$roleId);
			$modelRR->fetchRow($select3);
			if($modelRR->getId() == NULL)
			{
				$model = new Default_Model_ResourceRole();
				$model->setIdResource($resourceId);
				$model->setIdRole($roleId);					
				if($model->save())
				{					
					$return = 'Successfully added';
				}				
			}else{
				$return = 'Already in database';
			}			
		}elseif($actions == 'remove'){
			$model = new Default_Model_ResourceRole();
			$select3 = $model->getMapper()->getDbTable()->select()
						->where('idResource = ?',$resourceId)
						->where('idRole = ?',$roleId);
			$model->fetchRow($select3);
			if($model->getId() != NULL)
			{				
				if($model->delete())
				{
					//remove the resource from all child elements					
					$role = new Default_Model_Role();
					$role->find($roleId);					
					$graph = new Needs_Graph($role, false,array('idParent','id'),'array');
					$childRoles = $graph->getTree();				
					foreach ($childRoles as $value)
					{
						$condition = array(
									'idRole = ?' => $value['id'],
									'idResource = ?' => $resourceId
						 );						
						 $this->db->delete('resource_role', $condition);
					}
					$return = 'Successfully deleted';
				}
			}			
		}
		
		echo Zend_Json_Encoder::encode($return);
		//END:save or delete
				
	}

	public function shareFileAction()
	{
		//BEGIN:select all sub childs of the current logged in user, that contains '$searchtext'
		$id = $this->getRequest()->getParam('id');
		$filename=$this->getRequest()->getParam('filename');
		
		$formshare = new Default_Form_ShareFile();
        $formshare->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/share-file/share-file.phtml'))));
		echo ($formshare);		
	}
}