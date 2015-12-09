<?php
class Default_Model_DbTable_RecurrentExpenses extends Zend_Db_Table_Abstract
{
	protected $_name    = 'recurrent_expenses';
	protected $_primary = 'id';
}
class Default_Model_RecurrentExpenses
{
	protected $_id;
        protected $_idMember;
        protected $_idGroup;
	protected $_name;
        protected $_price;
        protected $_date;
        protected $_datePaid;
        protected $_ufiles;
        protected $_type;
	protected $_created;
	protected $_modified;	
	protected $_deleted;
        protected $_recurrent;


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
        
        public function setRecurrent($id)
	{
		$this->_recurrent = (int) $id;
		return $this;
	}

	public function getRecurrent()
	{
		return $this->_recurrent;
	}
        
        public function setIdMember($value)
	{
		$this->_idMember = (int) $value;
		return $this;
	}

	public function getIdMember()
	{
		return $this->_idMember;
	}
        
        public function setIdGroup($value)
	{
		$this->_idGroup = (int) $value;
		return $this;
	}

	public function getIdGroup()
	{
		return $this->_idGroup;
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
		$this->_price = $value;
		return $this;
	}

	public function getPrice()
	{
		return $this->_price;
	}
	
        public function setDate($data)
        {
            $this->_date = $data;
            return $this;
        }

        public function getDatePaid()
        {
            return $this->_datePaid;
        }
        
        public function setDatePaid($data)
        {
            $this->_datePaid = $data;
            return $this;
        }

        public function getDate()
        {
            return $this->_date;
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
        
        public function setUfiles($value)
	{
        $this->_ufiles = (string) strip_tags($value);
        return $this;
	}

	public function getUfiles()
	{
		return $this->_ufiles;
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
			$this->setMapper(new Default_Model_RecurrentExpensesMapper());
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

class Default_Model_RecurrentExpensesMapper
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
			$this->setDbTable('Default_Model_DbTable_RecurrentExpenses');
		}
		return $this->_dbTable;
	}

	public function find($id, Default_Model_RecurrentExpenses $model)
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
			$model = new Default_Model_RecurrentExpenses();
			$model->setOptions($row->toArray())
					->setMapper($this);
			$entries[] = $model;
		}
		return $entries;
	}
	
	public function fetchRow($select, Default_Model_RecurrentExpenses $model)
	{
		$result=$this->getDbTable()->fetchRow($select);
		if(0 == count($result))
		{
			return;
		}
		$model->setOptions($result->toArray());
		return $model;
	}
	
	public function save(Default_Model_RecurrentExpenses $value)
    {
	    $data = array(
                'idMember'              => Zend_Registry::get('user')->getId(),
		'idGroup'		=> $value->getIdGroup(),
                'name'			=> $value->getName(),
                'price'			=> $value->getPrice(),
                'date'                  => $value->getDate(),
                'datePaid'              => $value->getDatePaid(),
                'type'			=> $value->getType(),
		'deleted'		=> '0',
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
			$action_done = ' '.$user_name.' a adaugat proiectul '.$product_name.' ';
			//$value->getIdUser() adaugat in baza de date ca 0
			Needs_Logs::DbLogTracking( Zend_Registry::get('user')->getId(), $id, 'products', 'adaugare', $action_done);
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
			$action_done = ''.$user_name.' modified the product '.$product_name.' ';
			Needs_Logs::DbLogTracking( Zend_Registry::get('user')->getId(), $id, 'products', 'editare', $action_done);
			//end logs action done
        }
		
		
        return $id;
    }
	
   public function delete(Default_Model_RecurrentExpenses $value)
    {    
		$id = $value->getId();
		$data = array(					
			'deleted' => '1',			
		);
		$this->getDbTable()->update($data, array('id = ?' => $id)); 
		
        //logs	action done		
		//$user_name = $value->getUserName()->getName().' '.$value->getUserName()->getSurname();
		$product_name = $value->getName();
		$action_done = ' User deleted the expense '.$product_name.' ';	
		Needs_Logs::DbLogTracking( 0, $id, 'expenses', 'stergere', $action_done);
		//end logs action done
		
        return $id;
    }
}