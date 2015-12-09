<?php
class Default_Model_DbTable_Resource extends Zend_Db_Table_Abstract
{
    protected $_name    = 'resource';
    protected $_primary = 'id';
}

class Default_Model_Resource
{
    protected $_id;
    protected $_idGroup;
    protected $_resourceGroup;
    protected $_description;
    protected $_resource;
    protected $_module;
    protected $_controller;
    protected $_action;
	protected $_visible;
	protected $_firstNode;
	protected $_inMeniu;
    protected $_deleted;
     
     
    protected $_mapper;
 
    public function __construct(array $options = null)
    {
        if(is_array($options)) {
            $this->setOptions($options);
        }
    }
 
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if(('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid '.$name.' property '.$method);
        }
        $this->$method($value);
    }
 
    public function __get($name)
    {
        $method = 'get' . $name;
        if(('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid '.$name.' property '.$method);
        }
        return $this->$method();
    }
 
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach($options as $key => $value)
        {
            $method = 'set' . ucfirst($key);
            if(in_array($method, $methods))
            {
                $this->$method($value);
            }
        }
        return $this;
    }
 
    public function setId($value)
    {
        $this->_id = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getId()
    {
        return $this->_id;
    }
     
    public function setIdGroup($value)
    {
		$group = new Default_Model_ResourceGroup();
		if($group->find($value)){
			$this->setResourceGroup($group);
		}
        $this->_idGroup = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdGroup()
    {
        return $this->_idGroup;
    }
     
    public function setResourceGroup(Default_Model_ResourceGroup $value)
    {
        $this->_resourceGroup = (!empty($value))? $value:NULL;
        return $this;
    }
 
    public function getResourceGroup()
    {
        return $this->_resourceGroup;
    }
     
    public function setDescription($string)
    {
        $this->_description = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getDescription()
    {
        return $this->_description;
    }
     
    public function setResource($string)
    {
        $this->_resource = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getResource()
    {
        return $this->_resource;
    }
     
    public function setModule($string)
    {
        $this->_module = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getModule()
    {
        return $this->_module;
    }
     
    public function setController($string)
    {
        $this->_controller = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getController()
    {
        return $this->_controller;
    }
     
    public function setAction($string)
    {
        $this->_action = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getAction()
    {
        return $this->_action;
    }
	
	public function setVisible($value)
    {
        $this->_visible = (!empty($value))?(int) $value:NULL;
        return $this;
    }
 
    public function getVisible()
    {
        return $this->_visible;
    }
     
	public function setFirstNode($value)
    {
        $this->_firstNode = (!empty($value))?(int) $value:NULL;
        return $this;
    }
 
    public function getFirstNode()
    {
        return $this->_firstNode;
    }
     
	public function setInMeniu($value)
    {
        $this->_inMeniu = (!empty($value))?(int) $value:NULL;
        return $this;
    }
 
    public function getInMeniu()
    {
        return $this->_inMeniu;
    }
     
     
    public function setDeleted($value)
    {
        $this->_deleted = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getDeleted()
    {
        return $this->_deleted;
    }
     
     
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
     
    public function getMapper()
    {
        if(null === $this->_mapper) {
            $this->setMapper(new Default_Model_ResourceMapper());
        }
        return $this->_mapper;
    }
 
    public function find($id)
    {
        return $this->getMapper()->find($id, $this);
    }
 
    public function fetchAll($select = null){
        if(!$select){
            $select = $this->getMapper()->getDbTable()->select()
            ->where('NOT deleted')
            ->order('data DESC');
        }
        return $this->getMapper()->fetchAll($select);
    }
     
    public function fetchRow($select =null)
    {
        return $this->getMapper()->fetchRow($select,$this);
    }
     
     
    public function getFieldsbyId($id,$fieldsArray)
    {
        $select = $this->getMapper()->getDbTable()->select()
            ->from(array('Resource'),$fieldsArray)
            ->where('id = ?',$id)           
            ->where('NOT deleted')          
            ->setIntegrityCheck(false);
        return $this->getMapper()->fetchRow($select,$this);      
    }
     
    public function getModelbyUrl($url,$fieldsArray = null)
    {
        $select = $this->getMapper()->getDbTable()->select();
            if($fieldsArray){
                $select->from(array('page'),$fieldsArray);
            }          
            $select->where('url = ?',$url)          
            ->where('NOT deleted')          
            ->setIntegrityCheck(false);
        return $this->getMapper()->fetchRow($select,$this);      
    }
 
    public function save()
    {
        return $this->getMapper()->save($this);
    }
     
    public function delete()
    {
        if(null === ($id = $this->getId())) {
            throw new Exception('Invalid record selected!');
        }
        return $this->getMapper()->delete($this);
    }
     
}

class Default_Model_ResourceMapper
{
    protected $_dbTable;
 
    public function setDbTable($dbTable)
    {
        if(is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if(!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
 
    public function getDbTable()
    {
        if(null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_Resource');
        }
        return $this->_dbTable;
    }
 
    public function find($id, Default_Model_Resource $model)
    {
        $result = $this->getDbTable()->find($id);
        if(0 == count($result)) {
            return;
        }
        $row = $result->current();
        $model->setOptions($row->toArray());
        return $model;
    }
 
    public function fetchAll($select)
    {
        $resultSet = $this->getDbTable()->fetchAll($select);
        $entries = array();
        foreach($resultSet as $row) {
            $model = new Default_Model_Resource();
            $model->setOptions($row->toArray())
                    ->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
     
    public function fetchRow($select, Default_Model_Resource $model)
    {      
        $result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result))
        {
            return;
        }      
        $model->setOptions($result->toArray());
        return $model;
    }
     
    public function save(Default_Model_Resource $value)
    {
 
        $data = array('id'	=> $value->getId(),
            'idGroup'		=> $value->getIdGroup(),
            'description'	=> $value->getDescription(),
            'resource'		=> $value->getResource(),
            'module'		=> $value->getModule(),
            'controller'	=> $value->getController(),
            'action'		=> $value->getAction(),
            'deleted'		=> $value->getDeleted(),
			'visible'		=> ($value->getVisible() != NULL)?$value->getVisible():0,
			'firstNode'		=> ($value->getFirstNode() != NULL)?$value->getFirstNode():0,
			'inMeniu'		=> ($value->getInMeniu() != NULL)?$value->getInMeniu():0,
             
        );
         
      if (null === ($id = $value->getId()))
        {    
            $data['created']     = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);           
        }
        return $id;
    }
     
    public function delete(Default_Model_Resource $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'deleted' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
}