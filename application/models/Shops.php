<?php
class Default_Model_DbTable_Shops extends Zend_Db_Table_Abstract
{
	protected $_name    = 'shops';
	protected $_primary = 'id';
}
class Default_Model_Shops
{
	protected $_id;	
	protected $_name;
	protected $_address;
	protected $_phoneNumber;
	protected $_company;
	protected $_cashBank;
	protected $_creditCardBank;
	protected $_cashRegistry;
	protected $_idGroup;
	protected $_status;
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
		$this->_name = (string) $value;
		return $this;
	}

	public function getName()
	{
		return $this->_name;
	}	
	
	public function setAddress($value)
	{
		$this->_address = (string) $value;
		return $this;
	}

	public function getAddress()
	{
		return $this->_address;
	}	
	
	public function setPhoneNumber($value)
	{
		$this->_phoneNumber = (string) $value;
		return $this;
	}

	public function getPhoneNumber()
	{
		return $this->_phoneNumber;
	}	
	
	public function setCompany($value)
	{
		$this->_company = (string) $value;
		return $this;
	}

	public function getCompany()
	{
		return $this->_company;
	}	
	
	public function setCashBank($value)
	{
		$this->_cashBank= (string) $value;
		return $this;
	}

	public function getCashBank()
	{
		return $this->_cashBank;
	}	
	
	public function setCreditCardBank($value)
	{
		$this->_creditCardBank= (string) $value;
		return $this;
	}

	public function getCreditCardBank()
	{
		return $this->_creditCardBank;
	}	
	public function setCashRegistry($value)
	{
		$this->_cashRegistry= (int) $value;
		return $this;
	}

	public function getCashRegistry()
	{
		return $this->_cashRegistry;
	}	
	
	public function setIdGroup($value)
	{
		$this->_idGroup = (string) $value;
		return $this;
	}

	public function getIdGroup()
	{
		return $this->_idGroup;
	}	
	
	public function setStatus($value)
	{
		$this->_status = (string) $value;
		return $this;
	}

	public function getStatus()
	{
		return $this->_status;
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
		if(null === $this->_mapper) {
			$this->setMapper(new Default_Model_ShopsMapper());
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

class Default_Model_ShopsMapper
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
			$this->setDbTable('Default_Model_DbTable_Shops');
		}
		return $this->_dbTable;
	}

	public function find($id, Default_Model_Shops $model)
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
			$model = new Default_Model_Shops();
			$model->setOptions($row->toArray())
					->setMapper($this);
			$entries[] = $model;
		}
		return $entries;
	}
	
	public function fetchRow($select, Default_Model_Shops $model)
	{
		$result=$this->getDbTable()->fetchRow($select);
		if(0 == count($result))
		{
			return;
		}
		$model->setOptions($result->toArray());
		return $model;
	}
	
	public function save(Default_Model_Shops $value)
    {
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null != $authAccount) {
			if (null != $authAccount->getId()) {
				$user = new Default_Model_Users();
				$user->find($authAccount->getId());
				
				$data = array(		

					'name'				=> $value->getName(),
					'address'			=> $value->getAddress(),
					'phoneNumber'		=> $value->getPhoneNumber(),
					'company'			=> $value->getCompany(),
					'cashBank'			=> $value->getCashBank(),
					'creditCardBank'	=> $value->getCreditCardBank(),
					'cashRegistry'		=> $value->getCashRegistry(),
					'status'			=> $value->getStatus(),		
					'deleted'			=> '0',			
				);

			   if (null === ($id = $value->getId()))
				{     		
					$id = $this->getDbTable()->insert($data);  
					
					//logs	action done		
//					$user_name = $user->name.' '.$user->surname;				
//					$shop_name = $value->getName();				
//					$action_done = ' '.$user_name.' a adaugat shop-ul '.$shop_name.' ';				
					//end logs action done
				} 
				else 
				{          
					$this->getDbTable()->update($data, array('id = ?' => $id));
					
					//logs	action done		
//					$user_name = $user->name.' '.$user->surname;				
//					$shop_name = $value->getName();				
//					$action_done = ' '.$user_name.' a editat shop-ul '.$shop_name.' ';				
					//end logs action done
				}
				return $id;
			}
		}
    }
	
   public function delete(Default_Model_Shops $value)
    {    
	    $auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null != $authAccount) {
			if (null != $authAccount->getId()) {
				$user = new Default_Model_Users();
				$user->find($authAccount->getId());
	   
				$id = $value->getId();
				$data = array(					
					'deleted' => '1',			
				);
				$this->getDbTable()->update($data, array('id = ?' => $id));    
				
				//logs	action done		
//				$user_name = $user->name;				
//				$shop_name = $value->getName();				
//				$action_done = ' '.$user_name.' a sters shop-ul '.$shop_name.' ';				
				//end logs action done
				
				return $id;
		}
		}
	}
}


