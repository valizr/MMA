<?php
class Default_Model_DbTable_UploadedFiles extends Zend_Db_Table_Abstract
{
    protected $_name    = 'uploaded_files';
    protected $_primary = 'id';
}

class Default_Model_UploadedFiles
{
    protected $_id;
    protected $_idMessage;
    protected $_idUser;
    protected $_name;
    protected $_type;
    protected $_module;
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
     
    public function setIdMessage($value)
    {
        $this->_idMessage = (!empty($value))?(int) $value:NULL;
        return $this;
    }
 
    public function getIdMessage()
    {
        return $this->_idMessage;
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
     
    public function setName($string)
    {
        $this->_name = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getName()
    {
        return $this->_name;
    }
     
    public function setType($string)
    {
        $this->_type = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getType()
    {
        return $this->_type;
    }
     
    public function setModule($string)
    {
        $this->_module = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getModule()
    {
        return $this->_module;
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
     
     
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
     
    public function getMapper()
    {
        if(null === $this->_mapper) {
            $this->setMapper(new Default_Model_UploadedFilesMapper());
        }
        return $this->_mapper;
    }
 
    public function find($id)
    {
        return $this->getMapper()->find($id, $this);
    }
 
    public function fetchAll($select = null){      
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
		return $this->getMapper()->delete($id);
    }
	public function getModelbyName($file)
	{
		$select = $this->getMapper()->getDbTable()->select()			
			->where('name = ?',$file);
        return $this->getMapper()->fetchRow($select,$this);
	}
}

class Default_Model_UploadedFilesMapper
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
            $this->setDbTable('Default_Model_DbTable_UploadedFiles');
        }
        return $this->_dbTable;
    }
 
    public function find($id, Default_Model_UploadedFiles $model)
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
            $model = new Default_Model_UploadedFiles();
            $model->setOptions($row->toArray())
                    ->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
     
    public function fetchRow($select, Default_Model_UploadedFiles $model)
    {      
        $result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result))
        {
            return;
        }      
        $model->setOptions($result->toArray());
        return $model;
    }
     
    public function save(Default_Model_UploadedFiles $value)
    {
 
        $data = array(
            'idMessage' => $value->getIdMessage(),
            'idUser'	=> $value->getIdUser(),
            'name'		=> $value->getName(),
            'type'		=> $value->getType(),
            'module'	=> $value->getModule(),
             
        );
         
      if (null === ($id = $value->getId()))
        {    
            $data['created']     = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);           
        }
		else{
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