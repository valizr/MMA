<?php
class Default_Model_DbTable_ResourceGroup extends Zend_Db_Table_Abstract
{
	protected $_name    = 'resource_group';
	protected $_primary = 'id';

}

class Default_Model_ResourceGroup
{
    protected $_id;
    protected $_name; 
    protected $_order; 
    protected $_iconClass; 
    protected $_deleted;
    
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
            throw new Exception('Invalid resource property');
        }
        $this->$method($value);
    }
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid resource property');
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
    
    public function setName($name)
    {
        $this->_name = (string) $name;
        return $this;
    }
    public function getName()
    {
        return $this->_name;
    }    
 
    public function setIconClass($name)
    {
        $this->_iconClass = (!empty($name))?(string) $name:NULL;
        return $this;
    }
    public function getIconClass()
    {
        return $this->_iconClass;
    }    
 
    public function setOrder($name)
    {
        $this->_order = (int) $name;
        return $this;
    }
    public function getOrder()
    {
        return $this->_order;
    }    
 
    public function setDeleted($value)
    {
    	$this->_deleted = (int) $value;
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
        if (null === $this->_mapper) {
            $this->setMapper(new Default_Model_ResourceGroupMapper());
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

class Default_Model_ResourceGroupMapper
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
            $this->setDbTable('Default_Model_DbTable_ResourceGroup');
        }
        return $this->_dbTable;
    }
    
    public function save(Default_Model_ResourceGroup $resource)
    {
        $data = array(
            'name'	 			=> $resource->getName(),        	
            'order'	 			=> $resource->getOrder(),        	
            'iconClass'	 		=> $resource->getIconClass(),        	
        	'deleted'			=> '0',
        );

        if (null === ($id = $resource->getId())) {
            $this->getDbTable()->insert($data);
            $id = $this->getDbTable()->lastInsertId();
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
        return $id;
    }
    
    public function find($id, Default_Model_ResourceGroup $resource)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $resource -> setOptions($row->toArray());
		return $resource;
    }

    public function fetchAll($select)
    {
        $resultSet = $this->getDbTable()->fetchAll($select);
        
        $entries   = array();
        foreach ($resultSet as $row) {
            $resource = new Default_Model_ResourceGroup();
            $resource -> setOptions($row->toArray())
            	  -> setMapper($this);
            $entries[] = $resource;
        }
        return $entries;
    }
	
	public function fetchRow($select,Default_Model_ResourceGroup $resource)
    {		
		$result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result)) 
		{
            return;
        }      	
        $resource->setOptions($result->toArray());
		return $resource;
    }
    
    public function delete(Default_Model_ResourceGroup $value)
    {
    	$id = $value->getId();
		$data = array(					
			'deleted' => '1',			
		);
		$this->getDbTable()->update($data, array('id = ?' => $id));     
        
        return $id;
    }
}