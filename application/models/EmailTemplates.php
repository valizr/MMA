<?php
class Default_Model_DbTable_EmailTemplates extends Zend_Db_Table_Abstract
{
	protected $_name    = 'email_templates';
	protected $_primary = 'id';
}

class Default_Model_EmailTemplates
{
	protected $_id;	
	protected $_type;
	protected $_const;
	protected $_subject;
	protected $_content;

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
		if(('mapper' == $name) || !method_exists($this, $method))
		{
			throw new Exception('Invalid '.$name.' property '.$method);
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if(('mapper' == $name) || !method_exists($this, $method))
		{
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

	public function setConst($value)
	{
		$this->_const = (string) strip_tags($value);
		return $this;
	}

	public function getConst()
	{
		return $this->_const;
	}
	
	public function setType($value)
	{
		$this->_type = (string) strip_tags($value);
		return $this;
	}

	public function getType()
	{
		return $this->_type;
	}
	
	public function setSubject($value)
	{
		$this->_subject = (string) strip_tags($value);
		return $this;
	}

	public function getSubject()
	{
		return $this->_subject;
	}
	
	public function setContent($content)
	{
		$this->_content = (string) $content;
		return $this;
	}

	public function getContent()
	{
		return $this->_content;
	}
	
	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	public function getMapper()
	{
		if(null === $this->_mapper) {
			$this->setMapper(new Default_Model_EmailTemplatesMapper());
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
    		throw new Exception("Invalid record selected!");
    	}
        return $this->getMapper()->delete($id);
    }
}

class Default_Model_EmailTemplatesMapper
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
			$this->setDbTable('Default_Model_DbTable_EmailTemplates');
		}
		return $this->_dbTable;
	}

	public function find($id, Default_Model_EmailTemplates $model)
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
			$model = new Default_Model_EmailTemplates();
			$model->setOptions($row->toArray())
					->setMapper($this);
			$entries[] = $model;
		}
		return $entries;
	}
	
	public function fetchRow($select, Default_Model_EmailTemplates $model)
    {		
		$result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result)) 
		{
            return;
        }      	
        $model->setOptions($result->toArray());
		return $model;
    }
	
	public function save(Default_Model_EmailTemplates $value)
    {
        $data = array(				
			'type'			=> $value->getType(),
			'const'			=> $value->getConst(),
			'subject'		=> $value->getSubject(),
			'content'		=> $value->getContent(),
        );
        
        if (null === ($id = $value->getId()))
		{        	
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
