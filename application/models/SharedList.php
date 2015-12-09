<?php
class Default_Model_DbTable_SharedList extends Zend_Db_Table_Abstract
{
    protected $_name    = 'shared_list';
    protected $_primary = 'id';
}

class Default_Model_SharedList
{
    protected $_id;
    protected $_idUser;
    protected $_idFile;
    protected $_created;
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
     
    public function setIdUser($value)
    {
        $this->_idUser = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdUser()
    {
        return $this->_idUser;
    }
     
    public function setIdFile($value)
    {
        $this->_idFile = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdFile()
    {
        return $this->_idFile;
    }
     
    public function setCreated($data)
    {
        $this->_created = (!empty($data) && strtotime($data)>0)?strtotime($data):null;
        return $this;
    }
 
    public function getCreated()
    {
        return $this->_created;
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
            $this->setMapper(new Default_Model_SharedListMapper());
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
            ->order('id DESC');
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
     
    public function delete()
    {
        if(null === ($id = $this->getId())) {
            throw new Exception('Invalid record selected!');
        }
        return $this->getMapper()->delete($this);
    }
     
}

class Default_Model_SharedListMapper
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
            $this->setDbTable('Default_Model_DbTable_SharedList');
        }
        return $this->_dbTable;
    }
 
    public function find($id, Default_Model_SharedList $model)
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
            $model = new Default_Model_SharedList();
            $model->setOptions($row->toArray())
                    ->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
     
    public function fetchRow($select, Default_Model_SharedList $model)
    {      
        $result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result))
        {
            return;
        }      
        $model->setOptions($result->toArray());
        return $model;
    }
     
    public function save(Default_Model_SharedList $value)
    {
 
        $data = array(
            'idUser' => $value->getIdUser(),
            'idFile' => $value->getIdFile(),
            'deleted' => '0',
             
        );
         
      if (null === ($id = $value->getId()))
        {    
            $data['created']     = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);           
        }
        return $id;
    }
     
    public function delete(Default_Model_SharedList $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'deleted' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
}