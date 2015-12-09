<?php
class App_Controller_Plugin extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {	
		if(!empty($_POST['PHPSESSID'])){
			session_id($_POST['PHPSESSID']); 
		}	
		
		// GET MODULE/CONTROLLER/ACTION
		$module			= $request->getModuleName();
		$controller		= $request->getControllerName();
		$action			= $request->getActionName();

        $auth	 = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session());
		
		// SEND MODULE/CONTROLLER/ACTION
		$layout	= Zend_Layout::getMvcInstance();
		$layout->getView()->module              = $module;
		$layout->getView()->controller		= $controller;
		$layout->getView()->action		= $action;
		
		// Read ini file
		$options['nestSeparator'] = '.';
		$iniSettings = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV, $options);
		$db = new Zend_Db_Adapter_Pdo_Mysql(array(
			'host'     => $iniSettings->resources->db->params->host,
			'username' => $iniSettings->resources->db->params->username,
			'password' => $iniSettings->resources->db->params->password,
			'dbname'   => $iniSettings->resources->db->params->dbname
		));
		Zend_Registry::set('DB', $db);
		
		// BEGIN: Translate
		setlocale(LC_ALL, 'en_US.UTF-8');		
		Zend_Registry::set('lang', 'ro');
		Zend_Registry::set('lang_id', '1');			
		
		$adminLang = 'ro';
		$translate = new Zend_Translate('csv', 'data/lang/'.$adminLang.'.csv', $adminLang);
		$translate->setLocale($adminLang);	

		Zend_Registry::set('translate', $translate);
		// END: Translate
		
		$acl = new Zend_Acl();
		$acl->add(new Zend_Acl_Resource('default:auth'));
		$acl->add(new Zend_Acl_Resource('default:index'));
		
		//BEGIN:ROLES
		$acl->addRole(new Zend_Acl_Role('guest'));
		$acl->allow('guest', 'default:auth', 'login');
		$acl->allow('guest', 'default:auth', 'index');		
		
		$roles = Needs_Roles::fetchAllRoles();
		if($roles){
			foreach ($roles as $value) {
				$acl->addRole(new Zend_Acl_Role($value->getId()));
				$acl->deny($value->getId(), 'default:auth', 'login');				
			}
		}		
		//END:ROLES		

		$accountRole = 'guest';	        
		if ($auth->hasIdentity()) {			
			$accountAuth = $auth->getStorage()->read();	
            Zend_Registry::set('user', $accountAuth);
			if($accountAuth){
				$accountRole = $accountAuth->getIdRole();

				$isAdmin = false;		
				if(Needs_Roles::isAdmin($accountAuth->getIdRole()))
				{
					$isAdmin = true;			
				}
				Zend_Registry::set('isAdmin', $isAdmin);

				//BEGIN:NOTIFICARI
				$result_array=Needs_Tools::notifications($accountAuth->getId());
				Zend_Layout::getMvcInstance()->assign('notifications', $result_array[1]);
				Zend_Layout::getMvcInstance()->assign('notification_results', $result_array[0]);
				//BEGIN:NOTIFICARI
			}	

		}
		
		//BEGIN:SETTING
		$settingsAll = new Default_Model_Setting();
		$select = $settingsAll->getMapper()->getDbTable()->select();
		$resultSettings = $settingsAll->fetchAll($select);
		if(null != $resultSettings)
		{
			foreach ($resultSettings as $value)
			{
				defined(strtoupper($value->getConst())) || define(strtoupper($value->getConst()), $value->getValue());
			}
		}
		//END:SETTING
	
    	switch($module){    		
			//front-end	
			default:				
				$layout->setLayout('admin');				
				
				//if ($auth->hasIdentity()) {
					
					//BEGIN:MENIU+RESOURCES
					$arrResources = array('default:index','default:auth');

					$resourcesGroup = Needs_Roles::fetchAllResourceGroups();
					if($resourcesGroup){	
						foreach($resourcesGroup as $key=>$modelMenu)
						{
							//fetch resources by resource group
							$submenu = new Default_Model_Resource();
							$select = $submenu->getMapper()->getDbTable()->select()
										->where('deleted = ?', 0)
										->where('idGroup = ?', $modelMenu->getId())
										;
							$arrSubMenu = $submenu->fetchAll($select);
							foreach($arrSubMenu as $submenu) {
								if($submenu->getController() == NULL)
								{
									continue;
								}	
								
								$modul = ($submenu->getModule() != NULL)?$submenu->getModule():'default';						
								$resource = $modul.':'.$submenu->getController().':'.$submenu->getAction();	
								
								//chack if has access
								$hasaccess = Needs_Roles::hasAccessbyId($accountRole, $submenu->getId());
								//check if resource is already made
								if(!in_array($resource,$arrResources)) {
									//add resource to acl and to $arrResources
									$acl->add(new Zend_Acl_Resource($resource));
									$arrResources[] = $resource;
									if($hasaccess){
										//allow on modul:controller (resource)		
										$acl->allow($accountRole, $resource);
									}
								}							
								//BEGIN:allow on action						
//								if($hasaccess){
//									//echo $resource.','.$submenu->getAction().'<br/>';
//									$acl->allow($accountRole, $resource, $submenu->getAction());									
//								}else{
//									//$acl->deny($accountRole, $resource,$submenu->getAction());			
//								}	
								//END:allow on action	
								if($submenu->getInMeniu()){
									//BEGIN:TOP MENIU	
									$visible = $submenu->getVisible()?true:false;	
									if($submenu->getFirstNode()){
										$pages[$key] = array(
												'label'			=> $modelMenu->getName(),
												'title'			=> $modelMenu->getName(),
												'module'		=> $modul,
												'controller'	=> $submenu->getController(),	
												'action'		=> $submenu->getAction(),	
												'resource'		=> $resource,
												'class'			=> $modelMenu->getIconClass(),
												'visible'		=> $visible,
											);
									}
									//END:TOP MENIU

									//BEGIN:SUBMENIU
									$label = $submenu->getDescription();						

									$pages[$key]['pages'][] = array(
														'label'			=> $label,
														'title'			=> $label,
														'module'		=> $modul,
														'controller'	=> $submenu->getController(),	
														'action'		=> $submenu->getAction(),													
														'resource'		=> $resource,
														'visible'		=> $visible,
			//											'params'		=> $arrParams
													);

									//END:SUBMENIU	
								}		
						}					
					}					
				//}	
				//allow on index if logged in
				if ($auth->hasIdentity()) {		
					$acl->allow($accountRole,'default:index:index');	
					$acl->deny($accountRole, 'default:auth', 'login');
					$acl->deny($accountRole);
				}
				//END:MENIU+RESOURCES
			}
			
			// Create container from array
			$container = new Zend_Navigation($pages);
			$layout->getView()->navigation($container)->setAcl($acl)->setRole($accountRole);
			$layout->getView()->headTitle('Admin', 'SET');

			$stylesheets = $layout->getView()->headLink();
			$stylesheets->prependStylesheet(WEBROOT.'theme/front/css/style.css');
			$stylesheets->prependStylesheet(WEBROOT.'theme/front/css/vali.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/front/js/jquery-ui/css/custom-theme/jquery-ui-1.10.4.custom.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/admin/js/uploadify/uploadify.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/admin/js/fancybox/jquery.fancybox.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/front/js/validation/validationEngine.jquery.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/front/css/shThemeDefault.css')
						->appendStylesheet(WEBROOT . '/theme/front/js/jquery-uniform/css/uniform.default.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/front/css/ana.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/front/css/shCoreDefault.min.css');
			$stylesheets->appendStylesheet(WEBROOT . 'theme/front/css/shThemejqPlot.min.css');
                        $stylesheets->appendStylesheet(WEBROOT . 'theme/front/css/spectrum.css');

			$javascripts = $layout->getView()->headScript();
			$javascripts->prependFile(WEBROOT.'theme/admin/js/jquery-1.8.3.min.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/jquery-ui/js/jquery-ui-1.10.4.custom.min.js');
			$javascripts->appendFile(WEBROOT.'theme/admin/js/uploadify/jquery.uploadify.min.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/validation/jquery.validationEngine.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/validation/jquery.validationEngine-en.js');
			$javascripts->appendFile(WEBROOT.'theme/admin/js/jquery.livequery.js');
			$javascripts->appendFile(WEBROOT.'theme/admin/js/tipsy.js');
			$javascripts->appendFile(WEBROOT . 'theme/front/js/fancybox/source/jquery.fancybox.pack.js')
						->appendFile(WEBROOT . 'theme/front/js/jquery-uniform/jquery.uniform.js');	
			$javascripts->appendFile(WEBROOT.'theme/front/js/shCore.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/shBrushPhp.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/scripts.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/jquery.jqplot.min.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/jqplot.dateAxisRenderer.min.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/jqplot.barRenderer.min.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/jqplot.categoryAxisRenderer.min.js');
			$javascripts->appendFile(WEBROOT.'theme/front/js/jquery.editinplace.js');
                        $javascripts->appendFile('http://www.google.com/jsapi');
                        $javascripts->appendFile(WEBROOT.'theme/front/js/spectrum.js');


//						
			switch($controller) {
				case 'error':
					switch($action) {
						case 'error' :
							$layout->setLayout('error');
							break;
						default:
							break;
					}
					break;
				case 'iframe':
					$layout->setLayout('iframe');
					break;
				case 'auth':					
					$layout->setLayout('auth');
					switch($action) {
						case 'login' :
							$layout->getView()->headTitle('Login', 'SET');
							if(!$acl->isAllowed($accountRole,'default:auth', 'login')) {									
								$this->_response->setRedirect(WEBROOT.'index');

							}
							break;
						default:
							break;
					}
					break;					
				default :
					$layout->setLayout('layout');
					if(!$acl->isAllowed($accountRole,$module.':'.$controller.':'.$action)) {					
						$this->_response->setRedirect(WEBROOT . 'auth/login');
					}
					break;
			}
			break;
		}
	}
}