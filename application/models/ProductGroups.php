<?php
class Default_Model_DbTable_ProductGroups extends Zend_Db_Table_Abstract
{
	protected $_name    = 'product_groups';
	protected $_primary = 'id';
}
class Default_Model_ProductGroups
{
	protected $_id;	
	protected $_idProduct;
	protected $_idGroup;
        protected $_repeated;
	protected $_order;
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
		foreach($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if(in_array($method, $methods)) {
				$this->$method(stripcslashes($value));
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
	
	public function setIdProduct($value)
        {
            $this->_idProduct = $value;
            return $this;
        }

        public function getIdProduct()
        {
            return $this->_idProduct;
        }
	
	public function setOrder($value)
	{
		$this->_order = (!empty($value))?(int) $value:NULL;
		return $this;
	}

	public function getOrder()
	{
		return $this->_order;
	}
	
	public function setIdGroup($value)
        {
            $this->_idGroup = $value;
            return $this;
        }

        public function getIdGroup()
        {
            return $this->_idGroup;
        }
	
        public function setRepeated($value)
        {
            $this->_repeated = $value;
            return $this;
        }

        public function getRepeated()
        {
            return $this->_repeated;
        }
        
	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
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
	
	public function getMapper()
	{
		if(null === $this->_mapper) {
			$this->setMapper(new Default_Model_ProductGroupsMapper());
		}
		return $this->_mapper;
	}

	public function find($id)
	{
		return $this->getMapper()->find($id, $this);
	}

	public function fetchAll($select=null)
	{
		if(!$select){
			$select = $this->getMapper()->getDbTable()->select()				
			->where('NOT deleted')
			->order('order ASC');
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
	
	public function saveOrder()
	{
		$this->getMapper()->saveOrder($this);
	}
	
    public function delete()
	{
		if(null === ($id = $this->getId())) {
			throw new Exception('Invalid record selected!');
		}
		return $this->getMapper()->delete($this);
	}
}

class Default_Model_ProductGroupsMapper
{
	protected $_dbTable;

	public function setDbTable($dbTable)
	{
		if(is_string($dbTable))
		{
			$dbTable = new $dbTable();
		}
		if(!$dbTable instanceof Zend_Db_Table_Abstract)
		{
			throw new Exception('Invalid table data gateway provided');
		}
		$this->_dbTable = $dbTable;
		return $this;
	}

	public function getDbTable()
	{
		if(null === $this->_dbTable)
		{
			$this->setDbTable('Default_Model_DbTable_ProductGroups');
		}
		return $this->_dbTable;
	}

	public function find($id, Default_Model_ProductGroups $model)
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
			$model = new Default_Model_ProductGroups();
			$model->setOptions($row->toArray())
					->setMapper($this);
			$entries[] = $model;
		}
		return $entries;
	}
	
	public function fetchRow($select, Default_Model_ProductGroups $model)
	{
		$result=$this->getDbTable()->fetchRow($select);
		if(0 == count($result))
		{
			return;
		}
		$model->setOptions($result->toArray());
		return $model;
	}
	
	public function save(Default_Model_ProductGroups $value)
    {
	    $data = array(
			'idProduct'			=> $value->getIdProduct(),
			'idGroup'			=> $value->getIdGroup(),
                        'repeated'			=> $value->getRepeated(),
			'order'				=> ($value->getOrder() != NULL)?$value->getOrder():0,	
			'deleted'			=> '0',			
        );

		//if (null === ($id = $value->getId()))
        //{   
            $id = $this->getDbTable()->insert($data);           
       // }
        return $id;
    }
	public function saveOrder(Default_Model_ProductGroups $model)
    {
        $data = array(
            'order'	 => $model->getOrder()
        );

        if (null === ($id = $model->getId())) {
        	throw new Exception("Invalid data selected!");
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
        return $id;
    }
	public function delete(Default_Model_ProductGroups $value)
    {    
		$id = $value->getId();
		$data = array(					
			'deleted' => '1',			
		);
		$this->getDbTable()->update($data, array('id = ?' => $id)); 
		
        //logs	action done		
		//$user_name = $value->getUserName()->getName().' '.$value->getUserName()->getSurname();
		$product_name = $value->getName();
		$action_done = 'User deleted the group '.$product_name.' ';	
		Needs_Logs::DbLogTracking( 0, $id, 'expenses', 'stergere', $action_done);
		//end logs action done
		
        return $id;
    }
}