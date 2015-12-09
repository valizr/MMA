<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoload()
    {	
		Zend_Loader_Autoloader::getInstance()->registerNamespace('Needs_');		
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default_',			
            'basePath'  => dirname(__FILE__),
        ));
        return $autoloader;
    }
}