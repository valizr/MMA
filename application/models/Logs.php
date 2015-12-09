<?php
class Default_Model_DbTable_Logs extends Zend_Db_Table_Abstract
{
    protected $_name    = 'logs';
    protected $_primary = 'id';
}

class Default_Model_Logs
{
    protected $_id;
    protected $_idUser;
    protected $_idTarget;
    protected $_modul;
    protected $_actionType;
    protected $_actionDone;
    protected $_created;
     
     
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
     
    public function setIdUser($value)
    {
        $this->_idUser = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdUser()
    {
        return $this->_idUser;
    }
     
    public function setIdTarget($value)
    {
        $this->_idTarget = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdTarget()
    {
        return $this->_idTarget;
    }
     
    public function setModul($string)
    {
        $this->_modul = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getModul()
    {
        return $this->_modul;
    }
     
    public function setActionType($string)
    {
        $this->_actionType = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getActionType()
    {
        return $this->_actionType;
    }
     
    public function setActionDone($string)
    {
        $this->_actionDone = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getActionDone()
    {
        return $this->_actionDone;
    }
     
    public function setCreated($string)
    {
        $this->_created = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getCreated()
    {
        return $this->_created;
    }
     
     
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
     
    public function getMapper()
    {
        if(null === $this->_mapper) {
            $this->setMapper(new Default_Model_LogsMapper());
        }
        return $this->_mapper;
    }
 
    public function find($id)
    {
        return $this->getMapper()->find($id, $this);
    }
 
    public function fetchAll($select = null){
        if(!$select){
            $select = $this->getMapper()->getDbTable()->select();
        }
        return $this->getMapper()->fetchAll($select);
    }
     
    public function fetchRow($select =null)
    {
        return $this->getMapper()->fetchRow($select,$this);
    }
 
    public function save()
    {
        return $this->getMapper()->save($this);
    }
}

class Default_Model_LogsMapper
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
            $this->setDbTable('Default_Model_DbTable_Logs');
        }
        return $this->_dbTable;
    }
 
    public function find($id, Default_Model_Logs $model)
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
            $model = new Default_Model_Logs();
            $model->setOptions($row->toArray())
                    ->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
     
    public function fetchRow($select, Default_Model_Logs $model)
    {      
        $result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result))
        {
            return;
        }      
        $model->setOptions($result->toArray());
        return $model;
    }
	
    public function save(Default_Model_Logs $value)
    {
 
        $data = array('id' => $value->getId(),
            'idUser'		=> $value->getIdUser(),
            'idTarget'		=> $value->getIdTarget(),
            'modul'			=> $value->getModul(),
            'actionType'	=> $value->getActionType(),
            'actionDone'	=> $value->getActionDone(),           
        );
         
      if (null === ($id = $value->getId()))
        {    
            $data['created']     = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);           
        }
        return $id;
    }
}