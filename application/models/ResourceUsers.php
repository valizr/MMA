<?php
class Default_Model_DbTable_ResourceUsers extends Zend_Db_Table_Abstract
{
	protected $_name    = 'resource_users';
	protected $_primary = 'id';
}

class Default_Model_ResourceUsers
{
    protected $_id;
    protected $_userId;
    protected $_resourceId;
	
    protected $_mapper;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid  property');
        }
        $this->$method($value);
    }
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid property');
        }
        return $this->$method();
    }
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setId($id)
    {
        $this->_id = (int) $id;
        return $this;
    }
    public function getId()
    {
        return $this->_id;
    }
 
    public function setUserId($userId)
    {
        $this->_userId = (int) $userId;
        return $this;
    }
    public function getUserId()
    {
        return $this->_userId;
    }
 
    public function setResourceId($resourceId)
    {
        $this->_resourceId = (int) $resourceId;
        return $this;
    }
    public function getResourceId()
    {
        return $this->_resourceId;
    }
 
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper(new Default_Model_ResourceUsersMapper());
        }
        return $this->_mapper;
    }
    
    public function save()
    {
        return $this->getMapper()->save($this);
    }
    public function find($id)
    {
        return $this->getMapper()->find($id, $this);
    }
    public function fetchAll($select=null)
    {
        return $this->getMapper()->fetchAll($select);
    }
	
	public function fetchRow($select =null)
    {
        return $this->getMapper()->fetchRow($select,$this);
    }
	
    public function delete()
    {
    	if (null === ($id = $this->getId())) {
    		throw new Exception("Invalid record selected!");
    	}
        return $this->getMapper()->delete($id);
    }
}

class Default_Model_ResourceUsersMapper
{
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_ResourceUsers');
        }
        return $this->_dbTable;
    }
    
    public function save(Default_Model_ResourceUsers $role)
    {
        $data = array(
            'userId'		 => $role->getUserId(),
            'resourceId'	 => $role->getResourceId(),
        );

        if (null === ($id = $role->getId())) {
            $id = $this->getDbTable()->insert($data);           
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
        return $id;
    }
    
    public function find($id, Default_Model_ResourceUsers $role)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $role -> setOptions($row->toArray());
		return $role;
    }

    public function fetchAll($select)
    {
        $resultSet = $this->getDbTable()->fetchAll($select);
        
        $entries   = array();
        foreach ($resultSet as $row) {
            $role = new Default_Model_ResourceUsers();
            $role -> setOptions($row->toArray())
            	  -> setMapper($this);
            $entries[] = $role;
        }
        return $entries;
    }
	
	public function fetchRow($select,Default_Model_ResourceUsers $resource)
    {		
		$result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result)) 
		{
            return;
        }      	
        $resource->setOptions($result->toArray());
		return $resource;
    }
    
    public function delete($id)
    {
    	$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
        return $this->getDbTable()->delete($where);
    }
}