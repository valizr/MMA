<?php
class Default_Model_DbTable_Groups extends Zend_Db_Table_Abstract
{
	protected $_name    = 'groups';
	protected $_primary = 'id';
}
class Default_Model_Groups
{
	protected $_id;	
	protected $_name;
        protected $_type;
        protected $_color;
        protected $_price;//for statistics showing pie of expenses and income
	//protected $_idProductGroups;
	protected $_created;
	protected $_modified;	
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
	
	public function setName($value)
	{
		$this->_name = (string) strip_tags($value);
		return $this;
	}

	public function getName()
	{
		return $this->_name;
	}
	
	public function setColor($value)
	{
		$this->_color = (string) strip_tags($value);
		return $this;
	}

	public function getColor()
	{
		return $this->_color;
	}
	
	public function setType($value)
	{
		$this->_type = (int) $value;
		return $this;
	}

	public function getType()
	{
		return $this->_type;
	}
        
        public function setPrice($value)
	{
		$this->_price = (int) $value;
		return $this;
	}

	public function getPrice()
	{
		return $this->_price;
	}
        
	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	public function setCreated($date)
	{
		$this->_created = (!empty($date) && strtotime($date)>0)?strtotime($date):null;
		return $this;
	}

	public function getCreated()
	{
		return $this->_created;
	}
	
	public function setModified($date)
    {
        $this->_modified = $date;
        return $this;
    }

    public function getModified()
    {
        return $this->_modified;
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
			$this->setMapper(new Default_Model_GroupsMapper());
		}
		return $this->_mapper;
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

class Default_Model_GroupsMapper
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
			$this->setDbTable('Default_Model_DbTable_Groups');
		}
		return $this->_dbTable;
	}

	public function find($id, Default_Model_Groups $model)
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
			$model = new Default_Model_Groups();
			$model->setOptions($row->toArray())
					->setMapper($this);
			$entries[] = $model;
		}
		return $entries;
	}
	
	public function fetchRow($select, Default_Model_Groups $model)
	{
		$result=$this->getDbTable()->fetchRow($select);
		if(0 == count($result))
		{
			return;
		}
		$model->setOptions($result->toArray());
		return $model;
	}
	
	public function save(Default_Model_Groups $value)
    {
	    $data = array(
			'name'  	=> $value->getName(),
                        'type'		=> $value->getType(),
                        'color'         => $value->getColor(),
			'deleted'	=> '0',			
        );
	   $action_done = '';
       if (null === ($id = $value->getId()))
		{     
			$data['created']	 = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);			
			
			//logs	action done		
			//$user_name = $value->getUserName()->getName().' '.$value->getUserName()->getSurname();
			$user_name="User";
			$product_name = $value->getName();
			$action_done = ' '.$user_name.' added the group '.$product_name.' ';
			//$value->getIdUser() adaugat in baza de date ca 0
			Needs_Logs::DbLogTracking( 0, $id, 'groups', 'adaugare', $action_done);
			//end logs action done
        } 
		else 
		{    
		    $data['modified']	 = new Zend_Db_Expr('NOW()');            
            $this->getDbTable()->update($data, array('id = ?' => $id)); 
			//logs	action done
			//$user_name = $value->getUserName()->getName().' '.$value->getUserName()->getSurname();	
			$product_name = $value->getName();
			$user_name="User";
			$action_done = ''.$user_name.' modified the group '.$product_name.' ';
			Needs_Logs::DbLogTracking( 0, $id, 'groups', 'editare', $action_done);
			//end logs action done
        }
        return $id;
    }
	
   public function delete(Default_Model_Groups $value)
    {    
		$id = $value->getId();
		$data = array(					
			'deleted' => '1',			
		);
		$this->getDbTable()->update($data, array('id = ?' => $id)); 
		
        //logs	action done		
		//$user_name = $value->getUserName()->getName().' '.$value->getUserName()->getSurname();
		$product_name = $value->getName();
		$action_done = ' User deleted the group '.$product_name.' ';	
		Needs_Logs::DbLogTracking( 0, $id, 'groups', 'stergere', $action_done);
		//end logs action done
		
        return $id;
    }
}