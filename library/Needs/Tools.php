<?php
class Needs_Tools
{
    function cronjob ($runHours = 'month') {
        $updatedRE=false;        
        //Get Time
        $now = date('Y-m-d');//data de azi
        //Convert Time
        $model=new Default_Model_RecurrentExpenses();
        $select = $model->getMapper()->getDbTable()->select()
            ->where('NOT `deleted`');

        $result = $model->fetchAll($select);
        if ($result){
            foreach ($result as $values){
                $date=(($values->getDatePaid())?$values->getDatePaid():$values->getDate());//1 mai 2014
                $day=date('d',strtotime($date));
                $feb = (date('L', strtotime($date))?29:28); //an bisect
                $days=array(0,31,$feb,31,30,31,30,31,31,30,31,30,31,31);//nr zile pe luna
                //$month=array(1,2,3,4,5,6,7,8,9,10,11,12);
                $newdate = strtotime ( '+'.$days[date('n', strtotime($date))].' days' , strtotime ( $date ) ) ;
                if ($day<29 && $day>1){
                    $newdate=date ( 'Y-m' , $newdate )."-$day";
                } else {                    
                    if (date('n', strtotime($date))==1){//january
                        if ($feb==29)
                            $newdate=date ( 'Y-02' )."-".(($day==1)?'01':'29');
                        else
                            $newdate=date ( 'Y-02' )."-".(($day==1)?'01':'28');
                    }else{
                        if ($days[date('n', strtotime($date))]>$days[date('n', strtotime($date))+1] && $day>1)
                            $newdate=date ( 'Y-m-d' , strtotime ( '+'.$days[date('n', strtotime($date))+1].' days' , strtotime ( $date ) ) );
                        else
                            $newdate=date ( 'Y-m-d' , $newdate );
                    }
                    
                    //$newdate=date ( 'Y-m-d' , $newdate );
                }
                if ($newdate<=date('Y-m-d')){
                    $model->find($values->getId());
                    $model->setDatePaid($newdate);
                    if($model->save())
                    {
                        $modelExpenses=new Default_Model_Expenses();
                        $modelExpenses->setIdMember($model->getIdMember());
                        $modelExpenses->setName($model->getName());
                        $modelExpenses->setPrice($model->getPrice());
                        $modelExpenses->setType($model->getType());
                        $modelExpenses->setDate($model->getDatePaid());
                        if ($expenseId = $modelExpenses->save()){
                            $productGroups = new Default_Model_ProductGroups();
                            $select = $productGroups->getMapper()->getDbTable()->select()
                                            ->where('idProduct=?',$model->getId())
                                            ->where('repeated=?',1);
                            $result = $productGroups->fetchRow($select);
                            if(NULL != $result)
                            {
                                    $idGroup = $result->getIdGroup();
                                    $productGroups->setIdProduct($expenseId);
                                    $productGroups->setIdGroup($idGroup);
                                    $productGroups->setId('');
                                    $productGroups->setRepeated(0);
                                    $productGroups->save();
                             }
                        }
                        $updatedRE = true;
                    }
                }
            }
        }
        return $updatedRE;
    }
    public static function resize($width, $height){
	/* Get original image x y*/
	list($w, $h) = getimagesize($_FILES['image']['tmp_name']);
	/* calculate new image size with ratio */
	$ratio = max($width/$w, $height/$h);
	$h = ceil($height / $ratio);
	$x = ($w - $width / $ratio) / 2;
	$w = ceil($width / $ratio);
	/* new file name */
	$path = 'uploads/'.$width.'x'.$height.'_'.$_FILES['image']['name'];
	/* read binary data from image file */
	$imgString = file_get_contents($_FILES['image']['tmp_name']);
	/* create image from string */
	$image = imagecreatefromstring($imgString);
	$tmp = imagecreatetruecolor($width, $height);
	imagecopyresampled($tmp, $image,
  	0, 0,
  	$x, 0,
  	$width, $height,
  	$w, $h);
	/* Save image */
	switch ($_FILES['image']['type']) {
		case 'image/jpeg':
			imagejpeg($tmp, $path, 100);
			break;
		case 'image/png':
			imagepng($tmp, $path, 0);
			break;
		case 'image/gif':
			imagegif($tmp, $path);
			break;
		default:
			exit;
			break;
	}
	return $path;
	/* cleanup memory */
	imagedestroy($image);
	imagedestroy($tmp);
    }
    
    public static function resize_image($source_image, $destination_width, $destination_height, $type = 0) {
    // $type (1=crop to fit, 2=letterbox)
    
    $thumb = new Imagick($source_image);

$thumb->resizeImage($destination_width,$destination_height,Imagick::FILTER_LANCZOS,1, TRUE);
$thumb->writeImage($source_image);

//$thumb->destroy();     
        
    
            
//        $source_width = imagesx($source_image);
//    $source_height = imagesy($source_image);
//    $source_ratio = $source_width / $source_height;
//    $destination_ratio = $destination_width / $destination_height;
//    if ($type == 1) {
//        // crop to fit
//        if ($source_ratio > $destination_ratio) {
//            // source has a wider ratio
//            $temp_width = (int)($source_height * $destination_ratio);
//            $temp_height = $source_height;
//            $source_x = (int)(($source_width - $temp_width) / 2);
//            $source_y = 0;
//        } else {
//            // source has a taller ratio
//            $temp_width = $source_width;
//            $temp_height = (int)($source_width / $destination_ratio);
//            $source_x = 0;
//            $source_y = (int)(($source_height - $temp_height) / 2);
//        }
//        $destination_x = 0;
//        $destination_y = 0;
//        $source_width = $temp_width;
//        $source_height = $temp_height;
//        $new_destination_width = $destination_width;
//        $new_destination_height = $destination_height;
//    } else {
//        // letterbox
//        if ($source_ratio < $destination_ratio) {
//            // source has a taller ratio
//            $temp_width = (int)($destination_height * $source_ratio);
//            $temp_height = $destination_height;
//            $destination_x = (int)(($destination_width - $temp_width) / 2);
//            $destination_y = 0;
//        } else {
//            // source has a wider ratio
//            $temp_width = $destination_width;
//            $temp_height = (int)($destination_width / $source_ratio);
//            $destination_x = 0;
//            $destination_y = (int)(($destination_height - $temp_height) / 2);
//        }
//        $source_x = 0;
//        $source_y = 0;
//        $new_destination_width = $temp_width;
//        $new_destination_height = $temp_height;
//    }
//    $destination_image = imagecreatetruecolor($destination_width, $destination_height);
//    if ($type > 1) {
//        imagefill($destination_image, 0, 0, imagecolorallocate ($destination_image, 0, 0, 0));
//    }
//    imagecopyresampled($destination_image, $source_image, $destination_x, $destination_y, $source_x, $source_y, $new_destination_width, $new_destination_height, $source_width, $source_height);
    return $thumb;
} 

	public static function findAdmins($id=NULL){
		$modelu = new Default_Model_Users();
		if (!isset($id)){
		$select = $modelu->getMapper()->getDbTable()->select()
			->from(array('u'=>'users'),array('u.email'))
			->joinLeft(array('r'=>'role'), 'u.`idRole` = r.`id`',array(''))
			->where('r.`isAdmin` = ?', '1')
			->setIntegrityCheck(false);
		}else{
		$select = $modelu->getMapper()->getDbTable()->select()
			->from(array('u'=>'users'),array('u.id'))
			->joinLeft(array('r'=>'role'), 'u.`idRole` = r.`id`',array(''))
			->where('r.`isAdmin` = ?', '1')
			->setIntegrityCheck(false);
		}
		$result=$modelu->fetchAll($select);
		$return=null;
		if ($result){
			foreach ($result as $res){
				if (!isset($id)){
					$array[]=$res->getEmail();
				}else{
					$array[]=$res->getId();
				}
			}
			$return=$array;
		}
		return $return;
	}
	
	public static function DeleteLegaturi($productId,$repeated=0){
		$productGroups=new Default_Model_ProductGroups();
		//$where = $productGroups->getMapper()->getDbTable()->getAdapter()->quoteInto('idProduct = ?', $productId);
                $condition = array(
                    'idProduct = ?' => $productId,
                    'repeated = ?' => $repeated
                );                      
		return $productGroups->getMapper()->getDbTable()->delete($condition);
	}
	
	/**
	 * Delete gift cards by number
	 * 
	 * @param array $giftCardsNumbers
	 * @return bool
	 */
	public static function DeleteGiftCardsbyNumbers($giftCardsNumbers){
		$productGroups=new Default_Model_DailySalesGift();
		$where = $productGroups->getMapper()->getDbTable()->getAdapter()->quoteInto('idSale IN (?)', $giftCardsNumbers);
		return $productGroups->getMapper()->getDbTable()->delete($where);
	}
	
	public static function DeleteLegaturibyGroup($groupId){
		$productGroups=new Default_Model_ProductGroups();
		$where = $productGroups->getMapper()->getDbTable()->getAdapter()->quoteInto('idGroup = ?', $groupId);
		return $productGroups->getMapper()->getDbTable()->delete($where);
	}
	
	public static function notifications($id){
		$notification=new Default_Model_NotificationTo();
			$select = $notification->getMapper()->getDbTable()->select()
				->from(array('n' => 'notification_to'), array('n.*'))
                            ->where("NOT n.deleted")
							->where("n.status=?",'1')
							->where("n.idUserTo=?",$id);
			$result = $notification->fetchAll($select);
			$notifications=(count($result)!=0)?count($result):0;
			
			$notification=new Default_Model_NotificationMessages();
			$select = $notification->getMapper()->getDbTable()->select()
				->from(array('n' => 'notification_to'), array('n.*'))
				->joinLeft(array('nm'=>'notification_messages'), 'n.`idNotification` = nm.`id`',array('nm.*'))
                            ->where("NOT n.deleted")
							->where("n.status=?",'1')
							->where("n.idUserTo=?",$id)
							->limit(3)
							->setIntegrityCheck(false);
			$result = $notification->fetchAll($select);
			$res_notificari='<ul>';
			foreach ($result as $rez){
				$res_notificari.="<li><a href='".WEBROOT."notifications/details/id/".$rez->getId()."'>".$rez->getSubject()."</a></li>";
			}
			if ($notifications>0) $res_notificari.="<a href=\"".WEBROOT."notifications\" class=\"notificari\">Toate Notificarile</a>";
				else
					$res_notificari.="<li>Nu aveti notificari</li>";
			$res_notificari.="</ul>";
			$result_array=array($res_notificari,$notifications);
			return $result_array;
	}
	
	public static function paginatorToModel($paginator,$modelName)
	{		
		$entries = array();
		foreach($paginator as $row) {
			$model = new $modelName();
			$model->setOptions($row);
			$entries[] = $model;
		}
		return $entries;
	}
	

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
	
	public static function getLanguages(){
		$model = new Default_Model_Languages();
		$select = $model->getMapper()->getDbTable()->select()
				->where('NOT deleted')
				->order('default DESC');
		$result = $model->fetchAll($select);
		if($result){
			return $result;
		}
		return false;
	}
	
	public static function getDefaultLanguage(){
		$model = new Default_Model_Languages();
		$select = $model->getMapper()->getDbTable()->select()
				->where('NOT deleted')
				->where('`default` = ?','1');
		$model->fetchRow($select);
		if($model){
			return $model;
		}
		return false;
	}
	
	public static function getAdminLanguages(){
		$adminLanguageArray = array('ro','en','hu','de');
		return $adminLanguageArray;
	}
	
	public static function getTempFiles($types)
	{
		$type = (array) $types;
		$model = new Default_Model_TempFiles();
		$select = $model->getMapper()->getDbTable()->select()					
				->where('type IN (?)',$type)			
				->where('userId = ?',  Zend_Registry::get('user')->getId());	
		$result = $model->fetchAll($select);
		return $result;
	}

	public static function size($path){
		$bytes = sprintf('%u', filesize($path));
		if($bytes > 0){
			$unit = intval(log($bytes, 1024));
			$units = array('B', 'KB', 'MB', 'GB');
			if(array_key_exists($unit, $units) === true){
				return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
			}
		}
		return $bytes;
	}
	
	public static function findsize($path){
		$bytes = filesize($path);
		if($bytes > 0){
			$unit = intval(log($bytes, 1024));
			$units = array('B', 'KB', 'MB', 'GB');
			if(array_key_exists($unit, $units) === true){
				return number_format($bytes / pow(1024, $unit),2)." ".$units[$unit];
			} 
		}
		return number_format($bytes,2);
	}
	
	
	/**
	 * Sends SMTP or normal emails
	 * 
	 * @param array $emailArray must have the following fields:
	 * - toEmail
	 * - toName
	 * - fromEmail
	 * - fromName
	 * - content
	 * - subject
	 * 
	 * Optional:
	 * - SMTP_USERNAME
	 * - SMTP_PASSWORD
	 * - SMTP_PORT
	 * - SMTP_URL
	 */
	public static function sendEmail($emailArray)
	{
		$emailArray['toEmail']		= (!empty($emailArray['toEmail']))?$emailArray['toEmail']:'';
		$emailArray['toName']		= (!empty($emailArray['toName']))?$emailArray['toName']:'';
		$emailArray['fromEmail']	= (!empty($emailArray['fromEmail']))?$emailArray['fromEmail']:'';
		$emailArray['fromName']		= (!empty($emailArray['fromName']))?$emailArray['fromName']:'';
		$emailArray['content']		= (!empty($emailArray['content']))?$emailArray['content']:'';
		$emailArray['subject']		= (!empty($emailArray['subject']))?$emailArray['subject']:'';
		
		if((!empty($emailArray['SMTP_USERNAME'])) && (!empty($emailArray['SMTP_PASSWORD'])) && (!empty($emailArray['SMTP_PORT'])) && (!empty($emailArray['SMTP_URL'])))
		{
			$config = array(
				'auth'		=> 'login',
				'username'	=> $emailArray['SMTP_USERNAME'],
				'password'	=> $emailArray['SMTP_PASSWORD'],
				'port'		=> $emailArray['SMTP_PORT']
			);
			$transport = new Zend_Mail_Transport_Smtp($emailArray['SMTP_URL'], $config);
		}

		$toEmail	= $emailArray['toEmail'];
		$toName		= $emailArray['toName'];
		$fromEmail	= $emailArray['fromEmail'];
		$fromName	= $emailArray['fromName'];
		$contents	= $emailArray['content'];
		
		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyHtml($contents);
		$mail->setFrom($fromEmail, $fromName);
		$mail->addTo($toEmail, $toName);
		$mail->setSubject($emailArray['subject']);
		
		try {
			if((!empty($emailArray['SMTP_USERNAME'])) && (!empty($emailArray['SMTP_PASSWORD'])))
			{
				if($mail->send($transport))
				{
					return true;
				}		
			}
			else 
			{
				if($mail->send())
				{
					return true;	
				}
			}
		} catch (Exception $exc) {
			echo $exc->getTraceAsString();
		}
		return false;
	}

	/**
	 * Return all the Child Elements
	 * @param (int) $parentId
	 * @param model $model
	 * @return $result - array of objects
	 */
	public static function getChildElements($parentId,$model,$noOrder = null)
	{
		$model = new $model;
		$select = $model->getMapper()->getDbTable()->select()
		->where('NOT deleted')
		->where('idParent = ?',$parentId);
		if($noOrder){
			$select->order('created DESC');
		}else{
			$select->order('order ASC');
		}	
		
		$result = $model->fetchAll($select);
		return $result;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param type $options
	 */
	public static function includeTemplate($name, $options = NULL)
	{
		$variables = new stdClass();
		if($options){
			$variables = json_decode(json_encode($options), FALSE); // convert array to object using built in functions
		}
		$variables->webroot = WEBROOT;
		$templatesLocation = 'templates/';
		try{
			include $templatesLocation.$name.'.phtml';
		}catch(Exception $e){
			/**
			 * #toDo
			 * error handling
			 */
		}
	}
	
	/**
	 * Fetch all the pictures from a gallery by page Id	 * 
	 * @param (bool) $nameArray - if true fetch an array with the names 
	 * @return $result
	 */
	public static function getGallerybyId($projectId,$nameArray = false)
	{			
		$model = new Default_Model_UploadedFiles();
		$select = $model->getMapper()->getDbTable()->select();
						if($nameArray){
							$select->from(array('uploaded_files'),array('name'));
						}
							$select->where('idMessage = ?',$projectId)//idMessage holds the expense/income id.									
							->order('id ASC')
							->setIntegrityCheck(false);		
		$result = $model->fetchAll($select);
		if($nameArray == true){
			if($result)
			{
				$picturesArray = array();
				foreach ($result as $value) {
					$picturesArray[] = $value->getName();
				}				
				return $picturesArray;
			}			
		}
		return $result;
	}
	
	/**
	 * Fetch all the files  by message Id
	 * @param (int) $messageId
	 * @return $result
	 */
	public static function getGallerybyMessageId($messageId)
	{			
		$model = new Default_Model_UploadedFiles();
		$select = $model->getMapper()->getDbTable()->select();						
							$select->where('idMessage = ?',$messageId);									
							$select->where('module = ?','messages')									
							->order('id ASC')
							->setIntegrityCheck(false);		
		$result = $model->fetchAll($select);
		return $result;
	}	

	public static function getUserById($id)
	{
		$model  = new Default_Model_Users();		 
		$model->find($id);		
		return $model->getName();		
	}
	
	public static function getGroupName($id)
	{
		$model  = new Default_Model_Groups();		 
		$model->find($id);		
		return $model->getName();		
	}	

	public static function getShopById($shopId){
		
		$model = new Default_Model_Shops();
		$model->find($shopId);
		
		return $model->getName();
	}
	
	public static function getLevelById($levelId){
		
		$model = new Default_Model_Role();
		$model->find($levelId);
		
		return $model->getName();
	}
	
	public static function DeleteLegaturiDistrictManager($managerId){
		$districtManager=new Default_Model_DistrictManagerShops();
		$where = $districtManager->getMapper()->getDbTable()->getAdapter()->quoteInto('idUser = ?', $managerId);
		return $districtManager->getMapper()->getDbTable()->delete($where);
	}
	
	public static function getShopByUser($userId,$roleId = 0,$shopId = 0){
		
		$model = new Default_Model_Shops();
		if($roleId == 14){
			$names = array();
			$select = $model->getMapper()->getDbTable()->select()
						->from(array('s'=>'shops'),array('s.*'))
						->joinLeft(array('su'=>'district_manager_shops'),'s.id = su.idShop',array('suid'=>'su.id'))
						->where('NOT s.deleted')
						->where('su.idUser = ?', $userId)
						->setIntegrityCheck(false);
		
			$result = $model->fetchAll($select);
			if($result){
				foreach($result as $value){
					$names[] = $value->getName();
				}
			}
			return implode(', ' , $names);
		}else{
			$model->find($shopId);
		}
		
		
		return $model->getName();
	}
	/**
	 * 
	 * @param type $idShop
	 * @return type
	 */
	public static function getProductsByShop($idShop = Null){
		$idShop = (!empty($idShop))?$idShop:Zend_Registry::get('user')->getIdShop();
		$shop = new Default_Model_Shops();
		$selectGroup = $shop->getMapper()->getDbTable()->select()
						->from(array('s'=>'shops'),array('s.idGroup'))
						->where('s.id = ?',$idShop)
						->setIntegrityCheck(false);
		$shop->fetchRow($selectGroup);
			
		$products = new Default_Model_Expenses();
		$select = $products->getMapper()->getDbTable()->select()
				->from(array('p'=>'expenses'),array('p.id','p.name','p.deleted'));
		$select->joinLeft(array('pg' => 'product_groups'), 'p.`id` = pg.`idProduct`',array('gid'=>'pg.idGroup'))
						->where('NOT p.deleted')
						->where('pg.idGroup = ?', $shop->getIdGroup())
						->order('pg.order ASC')
						->setIntegrityCheck(false);
		
		$result = $products->fetchAll($select);
		
		return $result;
		
	}
	public static function getRegistryNrByShop($idShop = Null)
	{		
		$shops = new Default_Model_Shops();
		$idShop = (!empty($idShop))?$idShop:Zend_Registry::get('user')->getIdShop();
		$select = $shops->getMapper()->getDbTable()->select()
			->from(array('s'=>'shops'),array('s.cashRegistry'))
						->where('s.id = ?', $idShop)
						->setIntegrityCheck(false);
		
		$shops->fetchRow($select);
		
		return $shops->getCashRegistry();
		
	}
	
	public static function fetchDistrictManagerShops($idManager)
	{
		$idShops = new Default_Model_Shops();
		$select = $idShops->getMapper()->getDbTable()->select()
				->from(array('s'=>'shops'),array('*'))
				->joinLeft(array('dms'=>'district_manager_shops'),'dms.idShop = s.id',array('dmsid'=>'dms.id'))
				->where('dms.idUser = ? ',$idManager)
				->setIntegrityCheck(false);
		$result = $idShops->fetchAll($select);
		
		return $result;
	}
	
	public static function getSaleIdByDateAndShopId($date,$shopId)
	{
		$idShops = new Default_Model_DailySales();
		$select = $idShops->getMapper()->getDbTable()->select()
				->where('date = ? ',$date)
				->where('idShop = ? ',$shopId);
		$result = $idShops->fetchRow($select);
		
		return $result;
	}
		
	public static function checkIfDailySaleCompleted($date,$idShop)
	{
		$model = new Default_Model_DailySales();
		$select = $model->getMapper()->getDbTable()->select()
							->where('date = ?',$date)
							->where('idShop = ?',$idShop);
		$model->fetchRow($select);
		return $model;
	}
	/**
	 * 
	 * @param type $idSale
	 * @param type $idProduct
	 * @return result
	 */
	public static function getSaleProductsById($idSale,$idProduct)
	{
		$model = new Default_Model_DailySalesProducts();
		$select = $model->getMapper()->getDbTable()->select()
							->where('idSale = ?',$idSale)
							->where('idProduct = ?',$idProduct);
		$result = $model->fetchRow($select);
		
		return $result;
	}
	
	/**
	 * Finds a Projected Cost by Week Start Date and Week End Date
	 * 
	 * @param (date) $dateFrom - format: yyyy-mm-ddd - week start date
	 * @param (date) $dateTo - format: yyyy-mm-ddd - week end date
	 * @return Default_Model_ProjectedCost|boolean
	 */
	public static function findProjectedCostPerWeek($dateFrom,$dateTo)
	{
		$idUser =  Zend_Registry::get('user')->getId();		
		$model = new Default_Model_ProjectedCost();
		$select = $model->getMapper()->getDbTable()->select()
				->where('idUser = ? ',$idUser)
				->where('dateFrom = ? ',$dateFrom)
				->where('dateTo = ? ',$dateTo);
		echo $select;
		$model->fetchRow($select);
		if($model->getId() != null){
			return $model;
		}
		return false; 
	}
	
	public static function findProjectedCostforDashboard($dateFrom,$dateTo,$id_shop)
	{

		$idUser =  Zend_Registry::get('user')->getId();
		$model = new Default_Model_ProjectedCost();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('pc'=>'projected_cost'),array('*'))
				->joinLeft(array('pcs'=>'projected_cost_shops'), 'pc.`id` = pcs.`idProjectedCost`',array('pcs.idShop','pcs.foodCost','pcs.laborCost'))
				->where('pc.dateFrom = ? ',$dateFrom)
				->where('pc.dateTo = ? ',$dateTo)
				->where('pcs.idShop = ? ',$id_shop)
				->setIntegrityCheck(false);
		$model->fetchRow($select);
		if($model->getId() != null){
			return $model;
		}
		return false; 
	}
	
	/**
	 * Returns Projected Cost by idShop and idProjectedCost
	 * 
	 * @param (int) $idShop
	 * @param (int) $idProjectedCost
	 * @return boolean|\Default_Model_ProjectedCostShops
	 */
	public static function findProjectedCostByShopPerWeek($idShop,$idProjectedCost)
	{
		$model = new Default_Model_ProjectedCostShops();
		$select = $model->getMapper()->getDbTable()->select()
				->where('idShop = ? ',$idShop)
				->where('idProjectedCost = ? ',$idProjectedCost);
		$model->fetchRow($select);
		return $model;
	}
	
//	function showProjectedDashboard($dateFromValue=NULL,$dateToValue=NULL)
//	{
//		$resultInfo=array();
//		$idUser	= Zend_Registry::get('user')->getId();
//
//		$idShop = new Default_Model_Users();
//		$select = $idShop->getMapper()->getDbTable()->select()
//				->from(array('u'=>'users'),array('*'))
//				->where('u.id = ? ',$idUser)
//				->setIntegrityCheck(false);
//		$result = $idShop->fetchRow($select);
//
//		$recordExist = Needs_Tools::findProjectedCostforDashboard($dateFromValue,$dateToValue,$result->getIdShop());
//		
//		if($result)
//		{
//			if($recordExist)
//			{
//				$pcShop = Needs_Tools::findProjectedCostByShopPerWeek($result->getIdShop(), $recordExist->getId());
//			}
//				
//			// BEGIN: Labor cost and food cost
//			
//			if($recordExist){
//				$resultInfo["idShop"]=$result->getIdShop();
//				$resultInfo["laborCost"]=$pcShop->getLaborCost();
//				$resultInfo["foodCost"]=$pcShop->getFoodCost();
//			}		
//			// END: Labor cost and food cost
//		}
//		return $resultInfo;
//	}
        
        public static function showIncomeExpensesDashboard($year, $month)
	{
		$resultInfo=array();
		$idUser	= Zend_Registry::get('user')->getId();

		$expenses = new Default_Model_Expenses();
		$select = $expenses->getMapper()->getDbTable()->select()
			->from(array('p'=>'expenses'),array('price'=>'SUM(p.price)'))
			->where('NOT p.deleted')
			->where('YEAR(date)=?',$year)
                        ->where('MONTH(date)=?',$month)
			->where('p.idMember = ? ',$idUser)
                        ->group('p.type')
			->setIntegrityCheck(false);
                
		$result = $expenses->fetchAll($select);

//		if($result)
//		{
//			if($recordExist)
//			{
//				$pcShop = Needs_Tools::findProjectedCostByShopPerWeek($result->getIdShop(), $recordExist->getId());
//			}
//				
//			// BEGIN: Labor cost and food cost
//			
//			if($recordExist){
//				$resultInfo["idShop"]=$result->getIdShop();
//				$resultInfo["laborCost"]=$pcShop->getLaborCost();
//				$resultInfo["foodCost"]=$pcShop->getFoodCost();
//			}		
//			// END: Labor cost and food cost
//		}
		return $result;
	}
	
        public static function showExpensesDashboardbyDate($from='', $to='', $cat='', $name = NULL)
	{
		$resultInfo=array();
		$idUser	= Zend_Registry::get('user')->getId();

		$expenses = new Default_Model_Expenses();
		$select = $expenses->getMapper()->getDbTable()->select()
			->from(array('p'=>'expenses'),array('price'=>'SUM(p.price)'));
                        if ($cat!='') {
                            $select->joinLeft(array('pg'=>'product_groups'),'p.id=pg.idProduct',array())
                                   ->where('pg.idGroup=?',$cat);
                        }
        $select->where('NOT p.deleted')
			->where('p.idMember = ? ',$idUser)
            ->where('p.type=?',0);
                if ($from!='') $select->where('p.date>=?',date('Y-m-d',strtotime($from)));
                if ($to!='') $select->where('p.date<=?',date('Y-m-d',strtotime($to)));
                if ($name != '') $select->where('name like \'%'.$name.'%\'');

        $result = $expenses->fetchRow($select);
                $amount=$result->getPrice();
		return $amount;
	}
        
        public static function showIncomeDashboardbyDate($from='', $to='', $cat='')
	{
		$resultInfo=array();
		$idUser	= Zend_Registry::get('user')->getId();

		$expenses = new Default_Model_Expenses();
		$select = $expenses->getMapper()->getDbTable()->select()
			->from(array('p'=>'expenses'),array('price'=>'SUM(p.price)'));
                        if ($cat!='') {
                            $select->joinLeft(array('pg'=>'product_groups'),'p.id=pg.idProduct',array())
                                   ->where('pg.idGroup=?',$cat);
                        }
		$select->where('NOT p.deleted')			
			->where('p.idMember = ? ',$idUser)
                        ->where('p.type=?',1);
                if ($from!='') $select->where('p.date>=?',date('Y-m-d',strtotime($from)));
                if ($to!='') $select->where('p.date<=?',date('Y-m-d',strtotime($to)));
                
		$result = $expenses->fetchRow($select);
		$amount=$result->getPrice();
		return $amount;
	}
        
        public function weeks($ladate2,$ladate3) {
                $start_week= date("W",strtotime($ladate2));
                $end_week= date("W",strtotime($ladate3));
                $number_of_weeks= $end_week - $start_week;

                $weeks=array();
                $weeks[]=$start_week;
                $increment_date=$ladate2;
                $i="1";

                if ($number_of_weeks<0){
                    $start_year=date("Y",strtotime($ladate2));
                    $last_week_of_year= date("W",strtotime("$start_year-12-28"));
                    $number_of_weeks=($last_week_of_year-$start_week)+$end_week;
                }

                while ($i<=$number_of_weeks)
                {
                    $increment_date=date("Y-m-d", strtotime($ladate2. " +$i week"));
                    $weeks[]=date("W",strtotime($increment_date));

                    $i=$i+1;
                }
                return $weeks;
        }
        
        public static function showExpensesDashboardbyDateCat($from='', $to='', $categoryExpenses=array(), $timeframe)
	{
            $resultInfo=array();
            $idUser     = Zend_Registry::get('user')->getId();
            $expenses   = new Default_Model_Expenses();
            $diff = abs(strtotime($to) - strtotime($from));
            $nr_days=$diff/86400;
            if ($nr_days<7) $timeframe='d';
            //if ($nr_days>40) $timeframe='w';
            //if ()
            switch ($timeframe){
                case "m":
                    $y1 = date('Y',strtotime($from));
                    $y2 = date('Y',strtotime($to));
                    
                    $m1 = date('m',strtotime($from));
                    $m2 = date('m',strtotime($to));
                    $nrLuni=($y2-$y1)*12 +($m2-$m1)+1;

                    $feb = (date('L', strtotime($from))?29:28); //an bisect
                    $daysOfMonth=array(0,31,$feb,31,30,31,30,31,31,30,31,30,31,31);//nr zile pe luna
                    $months=array();

                    $firstMonth=1;
                    for ($i=1;$i<=$nrLuni;$i++) {
                        if ($firstMonth==1) $startDay=date('d', strtotime($from));
                        else $startDay='01';
                        if ($firstMonth==$nrLuni) $endDay=date('d', strtotime($to));
                        else $endDay=$daysOfMonth[($m1+$i==13)?12:(($m1+$i-1)%12)];
                        $firstMonth++;                        
                        $yearDate=$y1+floor(($m1+$i-2)/12);
                        $monthDate=($m1+$i==13)?12:((strlen(($m1+$i-1)%12)==1)?'0'.(($m1+$i-1)%12):(($m1+$i-1)%12));
                        
                        $select = $expenses->getMapper()->getDbTable()->select()
                            ->from(array('e'=>'expenses'),array('price'=>'SUM(e.price)'))
                            ->where('NOT e.deleted')
                            ->where('e.idMember = ? ',$idUser)
                            ->where('e.type=?',0)
                            ->where('e.date>=?',$yearDate."-".$monthDate."-".$startDay)
                            ->where('e.date<=?',$yearDate."-".$monthDate."-".$endDay);
                        if ($categoryExpenses[0]!=1) {//if we want to see specific categories, not all the expenses
                           $select->joinLeft(array('pg'=>'product_groups'), 'e.`id` = pg.`idProduct`',array(''))
                                  ->joinLeft(array('g'=>'groups'), 'g.`id` = pg.`idGroup`',array('g.id','g.name', 'ufiles'=>'g.color'))
                                  ->where('g.id IN (?)',$categoryExpenses)
                                  ->group('g.id')
                                  ->order('g.name')
                                  ->setIntegrityCheck(false);
                        }
                        $result = $expenses->fetchAll($select);//get results for all categories of expenses that exist for this week
                        foreach($result as $values){
                            $valueName=$values->getName();
                            $valuePrice=$values->getPrice();
                            $valueUfiles=$values->getUfiles();
                            
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][0]=(!empty($valueName)?$values->getName():Zend_Registry::get('translate')->_('admin_expenses'));
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][1]=date('M',strtotime($yearDate."-".$monthDate."-".$startDay))." ".$yearDate;
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][2]=(!empty($valuePrice)?$values->getPrice():0);
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][3]=(!empty($valueUfiles)?$values->getUfiles():'#d43c2c');//color of the category
                        }
                    }
                    return($returnValues);                    
                    break;
                case "w":
                    $weeks=array();
                    $weeks = Self::weeks($from,$to);
                    $temp_week='';
                    $contor_saptamani=0;
                    $year=date('Y',strtotime($from));
                    foreach ($weeks as $week){
                        if ($temp_week){
                            if ($week<$temp_week) $year++;//it means that this week is from the next year: why 1 is less than last week (52)
                        }
                        $temp_week=$week;
                        $timestamp = mktime( 0, 0, 0, 1, 1,  $year ) + ( ($week-1) * 7 * 24 * 60 * 60 );
                        $timestamp_for_monday = $timestamp - 86400 * ( date( 'N', $timestamp ) - 1 );
                        $first_dow = (($contor_saptamani==0)?$from:date( 'Y-m-d', $timestamp_for_monday ));
                        $monday=date( 'Y-m-d', $timestamp_for_monday );
                        $last_dow=($contor_saptamani==(count($weeks)-1))?$to:date('Y-m-d',strtotime($monday)+24*3600*6);
                        $contor_saptamani++;
                        $select = $expenses->getMapper()->getDbTable()->select()
                            ->from(array('e'=>'expenses'),array('price'=>'SUM(e.price)'))
                            ->where('NOT e.deleted')			
                            ->where('e.idMember = ? ',$idUser)
                            ->where('e.type=?',0)
                            ->where('e.date>=?',$first_dow)
                            ->where('e.date<=?',$last_dow);
                        if ($categoryExpenses[0]!=1) {//if we want to see specific categories, not all the expenses
                           $select->joinLeft(array('pg'=>'product_groups'), 'e.`id` = pg.`idProduct`',array(''))
                                  ->joinLeft(array('g'=>'groups'), 'g.`id` = pg.`idGroup`',array('g.id','g.name', 'ufiles'=>'g.color'))
                                  ->where('g.id IN (?)',$categoryExpenses)
                                  ->group('g.id')
                                  ->order('g.name')
                                  ->setIntegrityCheck(false);
                        }
                        $result = $expenses->fetchAll($select);//get results for all categories of expenses that exist for this week
                        foreach($result as $values){
                            $valueName=$values->getName();
                            $valuePrice=$values->getPrice();
                            $valueUfiles=$values->getUfiles();
                            
                            $returnValues[$week][$values->getId()][0]=(!empty($valueName)?$values->getName():Zend_Registry::get('translate')->_('admin_expenses'));
                            $returnValues[$week][$values->getId()][1]=$first_dow."/".$last_dow;
                            $returnValues[$week][$values->getId()][2]=(!empty($valuePrice)?$values->getPrice():0);
                            $returnValues[$week][$values->getId()][3]=(!empty($valueUfiles)?$values->getUfiles():'#d43c2c');//color of the category
                        }
                    }

                    return($returnValues);
                    break;
                case "d":
                    for ($i=0;$i<$nr_days;$i++){
                    $date=date('Y-m-d',strtotime($from.' +'.$i.' days'));    
                    $select = $expenses->getMapper()->getDbTable()->select()
                            ->from(array('e'=>'expenses'),array('price'=>'SUM(e.price)'))
                            ->where('NOT e.deleted')
                            ->where('e.idMember = ? ',$idUser)
                            ->where('e.type=?',0)
                            ->where('e.date=?',$date);
                        if ($categoryExpenses[0]!=1) {//if we want to see specific categories, not all the expenses
                           $select->joinLeft(array('pg'=>'product_groups'), 'e.`id` = pg.`idProduct`',array(''))
                                  ->joinLeft(array('g'=>'groups'), 'g.`id` = pg.`idGroup`',array('g.id','g.name', 'ufiles'=>'g.color'))
                                  ->where('g.id IN (?)',$categoryExpenses)
                                  ->group('g.id')
                                  ->order('g.name')
                                  ->setIntegrityCheck(false);
                        }
                        $result = $expenses->fetchAll($select);//get results for all categories of expenses that exist for this week
                        foreach($result as $values){
                            $valueName=$values->getName();
                            $valuePrice=$values->getPrice();
                            $valueUfiles=$values->getUfiles();
                            
                            $returnValues[$date][$values->getId()][0]=(!empty($valueName)?$values->getName():Zend_Registry::get('translate')->_('admin_expenses'));
                            $returnValues[$date][$values->getId()][1]=$date;
                            $returnValues[$date][$values->getId()][2]=(!empty($valuePrice)?$values->getPrice():0);
                            $returnValues[$date][$values->getId()][3]=(!empty($valueUfiles)?$values->getUfiles():'#d43c2c');//color of the category
                        }
                    }
                    return($returnValues); 
                    break;                
            }
	}
        
    public static function showIncomeDashboardbyDateCat($from='', $to='', $categoryIncome=array(), $timeframe)
	{
            $resultInfo=array();
            $idUser     = Zend_Registry::get('user')->getId();
            $expenses   = new Default_Model_Expenses();
            $diff = abs(strtotime($to) - strtotime($from));
            $nr_days=$diff/86400;
            if ($nr_days<7) $timeframe='d';
            //if ($nr_days>40) $timeframe='w';
            //if ()
            switch ($timeframe){
                case "m":
                    $y1 = date('Y',strtotime($from));
                    $y2 = date('Y',strtotime($to));
                    
                    $m1 = date('m',strtotime($from));//4
                    $m2 = date('m',strtotime($to));
                    $nrLuni=($y2-$y1)*12 +($m2-$m1)+1;//13

                    $feb = (date('L', strtotime($from))?29:28); //an bisect
                    $daysOfMonth=array(0,31,$feb,31,30,31,30,31,31,30,31,30,31,31);//nr zile pe luna
                    $months=array();

                    $firstMonth=1;
                    for ($i=1;$i<=$nrLuni;$i++) {
                        if ($firstMonth==1) $startDay=date('d', strtotime($from));
                        else $startDay='01';
                        if ($firstMonth==$nrLuni) $endDay=date('d', strtotime($to));
                        else $endDay=$daysOfMonth[($m1+$i==13)?12:(($m1+$i-1)%12)];
                        $firstMonth++;                        
                        $yearDate=$y1+floor(($m1+$i-2)/12);
                        $monthDate=($m1+$i==13)?12:((strlen(($m1+$i-1)%12)==1)?'0'.(($m1+$i-1)%12):(($m1+$i-1)%12));
                        
                        $select = $expenses->getMapper()->getDbTable()->select()
                            ->from(array('e'=>'expenses'),array('price'=>'SUM(e.price)'))
                            ->where('NOT e.deleted')
                            ->where('e.idMember = ? ',$idUser)
                            ->where('e.type=?',1)
                            ->where('e.date>=?',$yearDate."-".$monthDate."-".$startDay)
                            ->where('e.date<=?',$yearDate."-".$monthDate."-".$endDay);
                        if ($categoryIncome[0]!=2) {//if we want to see specific categories, not all the expenses
                           $select->joinLeft(array('pg'=>'product_groups'), 'e.`id` = pg.`idProduct`',array(''))
                                  ->joinLeft(array('g'=>'groups'), 'g.`id` = pg.`idGroup`',array('g.id','g.name', 'ufiles'=>'g.color'))
                                  ->where('g.id IN (?)',$categoryIncome)
                                  ->group('g.id')
                                  ->order('g.name')
                                  ->setIntegrityCheck(false);
                        }
                        $result = $expenses->fetchAll($select);//get results for all categories of expenses that exist for this week
                        foreach($result as $values){
                            $valueName=$values->getName();
                            $valuePrice=$values->getPrice();
                            $valueUfiles=$values->getUfiles();
                            
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][0]=(!empty($valueName)?$values->getName():Zend_Registry::get('translate')->_('admin_income'));
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][1]=date('M',strtotime($yearDate."-".$monthDate."-".$startDay))." ".$yearDate;
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][2]=(!empty($valuePrice)?$values->getPrice():0);
                            $returnValues[date('MY',strtotime($yearDate."-".$monthDate."-".$startDay))][$values->getId()][3]=(!empty($valueUfiles)?$values->getUfiles():'#58a87d');//color of the category
                            //print_r($returnValues);
                        }
                    }
                    //print_r($returnValues);
                    return($returnValues);
                    break;
                case "w":
                    $weeks=array();
                    $weeks = Self::weeks($from,$to);
                    $temp_week='';
                    $contor_saptamani=0;
                    $year=date('Y',strtotime($from));
                    foreach ($weeks as $week){
                        if ($temp_week){
                            if ($week<$temp_week) $year++;//it means that this week is from the next year: why 1 is less than last week (52)
                        }
                        $temp_week=$week;
                        $timestamp = mktime( 0, 0, 0, 1, 1,  $year ) + ( ($week-1) * 7 * 24 * 60 * 60 );
                        $timestamp_for_monday = $timestamp - 86400 * ( date( 'N', $timestamp ) - 1 );
                        $first_dow = (($contor_saptamani==0)?$from:date( 'Y-m-d', $timestamp_for_monday ));
                        $monday=date( 'Y-m-d', $timestamp_for_monday );
                        $last_dow=($contor_saptamani==(count($weeks)-1))?$to:date('Y-m-d',strtotime($monday)+24*3600*6);
                        $contor_saptamani++;
                        $select = $expenses->getMapper()->getDbTable()->select()
                            ->from(array('e'=>'expenses'),array('price'=>'SUM(e.price)'))
                            ->where('NOT e.deleted')			
                            ->where('e.idMember = ? ',$idUser)
                            ->where('e.type=?',1)
                            ->where('e.date>=?',$first_dow)
                            ->where('e.date<=?',$last_dow);
                        if ($categoryIncome[0]!=2) {//if we want to see specific categories, not all the expenses
                           $select->joinLeft(array('pg'=>'product_groups'), 'e.`id` = pg.`idProduct`',array(''))
                                  ->joinLeft(array('g'=>'groups'), 'g.`id` = pg.`idGroup`',array('g.id','g.name', 'ufiles'=>'g.color'))
                                  ->where('g.id IN (?)',$categoryIncome)
                                  ->group('g.id')
                                  ->order('g.name')
                                  ->setIntegrityCheck(false);
                        }
                        $result = $expenses->fetchAll($select);//get results for all categories of expenses that exist for this week
                        foreach($result as $values){
                            $valueName=$values->getName();
                            $valuePrice=$values->getPrice();
                            $valueUfiles=$values->getUfiles();
                            
                            $returnValues[$week][$values->getId()][0]=(!empty($valueName)?$values->getName():Zend_Registry::get('translate')->_('admin_income'));;
                            $returnValues[$week][$values->getId()][1]=$first_dow."/".$last_dow;
                            $returnValues[$week][$values->getId()][2]=(!empty($valuePrice)?$values->getPrice():0);
                            $returnValues[$week][$values->getId()][3]=(!empty($valueUfiles)?$values->getUfiles():'#58a87d');//color of the category
                        }
                    }

                    return($returnValues);
                    break;
                case "d":
                    for ($i=0;$i<$nr_days;$i++){
                    $date=date('Y-m-d',strtotime($from.' +'.$i.' days'));    
                    $select = $expenses->getMapper()->getDbTable()->select()
                            ->from(array('e'=>'expenses'),array('price'=>'SUM(e.price)'))
                            ->where('NOT e.deleted')
                            ->where('e.idMember = ? ',$idUser)
                            ->where('e.type=?',1)
                            ->where('e.date=?',$date);
                        if ($categoryIncome[0]!=2) {//if we want to see specific categories, not all the expenses
                           $select->joinLeft(array('pg'=>'product_groups'), 'e.`id` = pg.`idProduct`',array(''))
                                  ->joinLeft(array('g'=>'groups'), 'g.`id` = pg.`idGroup`',array('g.id','g.name', 'ufiles'=>'g.color'))
                                  ->where('g.id IN (?)',$categoryIncome)
                                  ->group('g.id')
                                  ->order('g.name')
                                  ->setIntegrityCheck(false);
                        }
                        $result = $expenses->fetchAll($select);//get results for all categories of expenses that exist for this week
                        foreach($result as $values){
                            $valueName=$values->getName();
                            $valuePrice=$values->getPrice();
                            $valueUfiles=$values->getUfiles();
                            
                            $returnValues[$date][$values->getId()][0]=(!empty($valueName)?$values->getName():Zend_Registry::get('translate')->_('admin_income'));
                            $returnValues[$date][$values->getId()][1]=$date;
                            $returnValues[$date][$values->getId()][2]=(!empty($valuePrice)?$values->getPrice():0);
                            $returnValues[$date][$values->getId()][3]=(!empty($valueUfiles)?$values->getUfiles():'#58a87d');//color of the category
                        }
                    }
                    return($returnValues);
                    break;                
            }
	}
        
        public static function showRecurrentExpensesDashboardbyDate($from='', $to='')
	{
		$resultInfo=array();
		$idUser	= Zend_Registry::get('user')->getId();

		$expenses = new Default_Model_RecurrentExpenses();
		$select = $expenses->getMapper()->getDbTable()->select()
			->from(array('p'=>'recurrent_expenses'),array('price'=>'SUM(p.price)'))
			->where('NOT p.deleted')			
			->where('p.idMember = ? ',$idUser)
                        ->where('p.type=?',0);
                if ($from!='') $select->where('p.date>=?',date('Y-m-d',strtotime($from)));
                if ($to!='') $select->where('p.date<=?',date('Y-m-d',strtotime($to)));
		$result = $expenses->fetchRow($select);
                $amount=($result->getPrice())?$result->getPrice():0;
		return $amount;
	}
        
        public static function showRecurrentIncomeDashboardbyDate($from='', $to='')
	{
		$resultInfo=array();
		$idUser	= Zend_Registry::get('user')->getId();

		$expenses = new Default_Model_RecurrentExpenses();
		$select = $expenses->getMapper()->getDbTable()->select()
			->from(array('p'=>'recurrent_expenses'),array('price'=>'SUM(p.price)'))
			->where('NOT p.deleted')			
			->where('p.idMember = ? ',$idUser)
                        ->where('p.type=?',1);
                if ($from!='') $select->where('p.date>=?',date('Y-m-d',strtotime($from)));
                if ($to!='') $select->where('p.date<=?',date('Y-m-d',strtotime($to)));
                
		$result = $expenses->fetchRow($select);
		$amount=$result->getPrice();
		return $amount;
	}
        
	/**
	 * Return the weeks end/start date by $date
	 * 
	 * @param (date) $date - format 'YYYY-mm-dd'
	 * @param (string) $type - values: 'start' or 'end'
	 * @return (date) $thisWeekDate - format 'YYYY-mm-dd'
	 */
	public static function getWeekDaysByDate($date,$type='start')
	{
		$unixDate = strtotime($date);
		if($type == 'start'){
			$thisWeekDate	=  date('Y-m-d', mktime(0, 0, 0, date('m',$unixDate), date('d',$unixDate)-date('w',$unixDate), date('Y',$unixDate)));			
		}else{
			$thisWeekDate	=  date('Y-m-d', mktime(0, 0, 0, date('m',$unixDate), date('d',$unixDate)-date('w',$unixDate)+6, date('Y',$unixDate)));	
		}
		return $thisWeekDate;
	}	

	public static function getGiftCardsBySaleAndDate($idSale)
	{
		$model = new Default_Model_DailySalesGift();
		$select = $model->getMapper()->getDbTable()->select()
							->where('idSale = ?',$idSale);
		$result = $model->fetchAll($select);
		
		return $result;
	}
	
	public static function checkDuplicatedFileName($filename,$ext,$oldId = Null)
	{
		$i = 2;
		$duplicatedFileName = true;
		$finalFileName = $filename;
		while ($duplicatedFileName) {
			$modelNew = new Default_Model_UploadedFiles;
			$modelNew->getModelbyName($finalFileName.".".$ext);
			if(!empty($oldId)){
				if($modelNew->getId() && $modelNew->getId() != $oldId){
					$finalFileName = $filename.'-'.$i;
				}else{
					$duplicatedFileName = false;
				}
			}else{
				if($modelNew->getId())
				{
					$finalFileName = $filename.'-'.$i;
				}else{				
					$duplicatedFileName = false;
				}
			}
			$i++;
		}
		return $finalFileName;
	}
	/**
	 * 
	 * @param type $idSale - the id of the sale
	 * @return all errors reported for the specified idSale
	 */
	public static function fetchDailySalesErrors($idSale)
	{
		$model = new Default_Model_DailySalesError();
		$select = $model->getMapper()->getDbTable()->select()
							->where('idSale = ?',$idSale);
		$result = $model->fetchAll($select);
		return $result;
	}
	/**
	 * 
	 * @return number of reports that have not been audited
	 */
	public function getTotalNoAudit()
	{
		$model = new Default_Model_DailySales();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('d'=>'daily_sales'),array('id'=>'COUNT(d.id)'))
				->where('audited = ?', 0)										
				->setIntegrityCheck(false);
		$model->fetchRow($select);
		return $model->getId();
	}
	
		/**
	 * Fetch a Daily Sales Product by SalesId and ProductId
	 * 
	 * @param int $idSales
	 * @param int $productId
	 * 
	 * @return object $model -  Default_Model_DailySalesProducts
	 */
	public static function fetchDailySalesProductbySalesId($idSales,$productId)
	{
		$model = new Default_Model_DailySalesProducts();
		$select = $model->getMapper()->getDbTable()->select()
							->where('idProduct = ?',$productId)
							->where('idSale = ?',$idSales);
		$model->fetchRow($select);
		return $model;
	}
}
