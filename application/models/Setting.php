<?php
class Default_Model_DbTable_Setting extends Zend_Db_Table_Abstract
{
	protected $_name    = 'settings';
	protected $_primary = 'id';
}

class Default_Model_Setting
{
	protected $_id;

	protected $_name;
	protected $_const;
	protected $_value;
	protected $_dataAdaugare;

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
            throw new Exception('Invalid '.$name.' property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if(('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid '.$name.' property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if(in_array($method, $methods)) {
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
        $this->_name = (!empty($name))?(string) $name:null;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setConst($const)
    {
        $this->_const = (!empty($const))?(string) $const:null;
        return $this;
    }

    public function getConst()
    {
        return $this->_const;
    }

	public function setValue($value)
    {
        $this->_value = (!empty($value))?(string) $value:null;
        return $this;
    }

    public function getValue()
    {
        return $this->_value;
    }

	public function setDataAdaugare($date)
	{
		$this->_dataAdaugare = (!empty($date) && strtotime($date)>0)?strtotime($date):null;
		return $this;
	}

	public function getDataAdaugare()
	{
		return $this->_dataAdaugare;
	}
	
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if(null === $this->_mapper) {
            $this->setMapper(new Default_Model_SettingMapper());
        }
        return $this->_mapper;
    }

    public function delete()
    {
    	if(null === ($id = $this->getId())) {
    		throw new Exception("Invalid record selected!");
    	}
        return $this->getMapper()->delete($id);
    }

    public function find($id)
    {
        return $this->getMapper()->find($id, $this);
    }
	
    public function fetchRowByConst($const)
    {
        return $this->getMapper()->fetchRowByConst($const, $this);
    }

    public function fetchAll($select = null)
    {
        return $this->getMapper()->fetchAll($select);
    }
	
    public function save()
    {
        return $this->getMapper()->save($this);
    }
}

class Default_Model_SettingMapper
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
            $this->setDbTable('Default_Model_DbTable_Setting');
        }
        return $this->_dbTable;
    }

    public function find($id, Default_Model_Setting $model)
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
            $model = new Default_Model_Setting();
            $model->setOptions($row->toArray())
                  	->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
	
    public function fetchRowByConst($const, Default_Model_Setting $model)
    {
		$sql= $this->getDbTable()->select()->
                        where('const = ?',$const);

		$result=$this->getDbTable()->fetchRow($sql);

        if(0 == count($result)) 
		{
            return;
        }      	
        $model->setOptions($result->toArray());
		return $model;
    }

	public function save(Default_Model_Setting $model)
    {
        $data = array(			
			'name'					=> $model->getName(),
			'const'					=> $model->getConst(),
			'value'					=> $model->getValue(),
        );
		if (null === ($id = $model->getId()))
		{     
			$data['dataAdaugare']	 = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);            
        } 
		else 
		{        	
            $this->getDbTable()->update($data, array('id = ?' => $id));            
        }
        return $id;
    }

    public function delete($id)
    {
    	$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
        return $this->getDbTable()->delete($where);
    }
}