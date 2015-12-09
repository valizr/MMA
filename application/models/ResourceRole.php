<?php
class Default_Model_DbTable_ResourceRole extends Zend_Db_Table_Abstract
{
	protected $_name    = 'resource_role';
	protected $_primary = 'id';
}

class Default_Model_ResourceRole
{
    protected $_id;
    protected $_idRole;
    protected $_idResource;
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
     
    public function setIdRole($value)
    {
        $this->_idRole = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdRole()
    {
        return $this->_idRole;
    }
     
    public function setIdResource($value)
    {
        $this->_idResource = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdResource()
    {
        return $this->_idResource;
    }
     
    public function setCreated($data)
    {
        $this->_created = (!empty($date) && strtotime($date)>0)?strtotime($date):null;
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
            $this->setMapper(new Default_Model_ResourceRoleMapper());
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
            ->from(array('ResourceRole'),$fieldsArray)
            ->where('id = ?',$id)           
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
		return $this->getMapper()->delete($id);
    }
}    
class Default_Model_ResourceRoleMapper
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
			$this->setDbTable('Default_Model_DbTable_ResourceRole');
		}
		return $this->_dbTable;
	}

	public function find($id, Default_Model_ResourceRole $model)
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
			$model = new Default_Model_ResourceRole();
			$model->setOptions($row->toArray())
					->setMapper($this);
			$entries[] = $model;
		}
		return $entries;
	}

	public function fetchRow($select, Default_Model_ResourceRole $model)
	{      
		$result=$this->getDbTable()->fetchRow($select);
		if(0 == count($result))
		{
			return;
		}      
		$model->setOptions($result->toArray());
		return $model;
	}

	public function save(Default_Model_ResourceRole $value)
	{

		$data = array('id' => $value->getId(),
			'idRole' => $value->getIdRole(),
			'idResource' => $value->getIdResource(),
			'created' => $value->getCreated(),

		);

	  if (null === ($id = $value->getId()))
		{    
			$data['created']     = new Zend_Db_Expr('NOW()');
			$id = $this->getDbTable()->insert($data);           
		}
		return $id;
	}

    public function delete($id)
    {
    	$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
        return $this->getDbTable()->delete($where);
    }
}