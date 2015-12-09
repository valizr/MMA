<?php
class Default_Model_DbTable_Messages extends Zend_Db_Table_Abstract
{
    protected $_name    = 'messages';
    protected $_primary = 'id';
}

class Default_Model_Messages
{
    protected $_id;
    protected $_idUserFrom;
    protected $_idUserTo;
    protected $_userFrom;
    protected $_userTo;	
    protected $_subject;
    protected $_message;
    protected $_read;
    protected $_created;
    protected $_trashedTo;
    protected $_trashedFrom;    
    protected $_deletedTo;
    protected $_deletedFrom;    
     
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
     
    public function setIdUserFrom($value)
    {
		$model=new Default_Model_Users();
		$model->find($value);
		if ($model->getId()!=NULL)
		{
			$this->setUserFrom($model);
		}	
        $this->_idUserFrom = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdUserFrom()
    {
        return $this->_idUserFrom;
    }
     
    public function setUserFrom(Default_Model_Users $value)
    {
        $this->_userFrom = (!empty($value))? $value:NULL;
        return $this;
    }
 
    public function getUserFrom()
    {
        return $this->_userFrom;
    }     
  
     
    public function setIdUserTo($value)
    {
		$model=new Default_Model_Users();
		$model->find($value);
		if ($model->getId()!=NULL)
		{
			$this->setUserTo($model);
		}	
        $this->_idUserTo = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdUserTo()
    {
        return $this->_idUserTo;
    }
	
	public function setUserTo(Default_Model_Users $value)
    {
        $this->_userTo = (!empty($value))? $value:NULL;
        return $this;
    }
 
    public function getUserTo()
    {
        return $this->_userTo;
    }
     
    public function setSubject($string)
    {
        $this->_subject = (!empty($string))?(string) strip_tags($string):null;
        return $this;
    }
 
    public function getSubject()
    {
        return $this->_subject;
    }
     
    public function setMessage($string)
    {
        $this->_message = (!empty($string))?(string) $string:null;
        return $this;
    }
 
    public function getMessage()
    {
        return $this->_message;
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
	
	public function setRead($value)
    {
        $this->_read = (!empty($value))?(int) $value:0;
        return $this;
    }
 
    public function getRead()
    {
        return $this->_read;
    }
     
    public function setTrashedTo($value)
    {
        $this->_trashedTo = (!empty($value))?(int) $value:0;
        return $this;
    }
 
    public function getTrashedTo()
    {
        return $this->_trashedTo;
    }
     
    public function setTrashedFrom($value)
    {
        $this->_trashedFrom = (!empty($value))?(int) $value:0;
        return $this;
    }
 
    public function getTrashedFrom()
    {
        return $this->_trashedFrom;
    }
     
    public function setDeletedTo($value)
    {
        $this->_deletedTo = (!empty($value))?(int) $value:0;
        return $this;
    }
 
    public function getDeletedTo()
    {
        return $this->_deletedTo;
    }
     
    public function setDeletedFrom($value)
    {
        $this->_deletedFrom = (!empty($value))?(int) $value:0;
        return $this;
    }
 
    public function getDeletedFrom()
    {
        return $this->_deletedFrom;
    }
     
     
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
     
    public function getMapper()
    {
        if(null === $this->_mapper) {
            $this->setMapper(new Default_Model_MessagesMapper());
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
            ->order('created DESC');
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
            ->from(array('messages'),$fieldsArray)
            ->where('id = ?',$id)           
            ->where('NOT deleted')          
            ->setIntegrityCheck(false);
        return $this->getMapper()->fetchRow($select,$this);      
    }   
 
    public function save()
    {
        return $this->getMapper()->save($this);
    }
     
    public function makeRead()
    {
        if(null === ($id = $this->getId())) {
            throw new Exception('Invalid record selected!');
        }
        return $this->getMapper()->makeRead($this);
    }
     
    public function trashTo()
    {
        if(null === ($id = $this->getId())) {
            throw new Exception('Invalid record selected!');
        }
        return $this->getMapper()->trashTo($this);
    }
     
    public function trashFrom()
    {
        if(null === ($id = $this->getId())) {
            throw new Exception('Invalid record selected!');
        }
        return $this->getMapper()->trashFrom($this);
    }
     
    public function deleteTo()
    {
        if(null === ($id = $this->getId())) {
            throw new Exception('Invalid record selected!');
        }
        return $this->getMapper()->deleteTo($this);
    }
     
    public function deleteFrom()
    {
        if(null === ($id = $this->getId())) {
            throw new Exception('Invalid record selected!');
        }
        return $this->getMapper()->deleteFrom($this);
    }
     
}

class Default_Model_MessagesMapper
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
            $this->setDbTable('Default_Model_DbTable_Messages');
        }
        return $this->_dbTable;
    }
 
    public function find($id, Default_Model_Messages $model)
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
            $model = new Default_Model_Messages();
            $model->setOptions($row->toArray())
                    ->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
     
    public function fetchRow($select, Default_Model_Messages $model)
    {      
        $result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result))
        {
            return;
        }      
        $model->setOptions($result->toArray());
        return $model;
    }
     
    public function save(Default_Model_Messages $value)
    {
 
        $data = array(
            'idUserFrom'	=> $value->getIdUserFrom(),
            'idUserTo'		=> $value->getIdUserTo(),
            'subject'		=> $value->getSubject(),
            'message'		=> $value->getMessage(),
            'deletedTo'		=> ($value->getDeletedTo() != null)?$value->getDeletedTo():0,
            'deletedFrom'	=> ($value->getDeletedFrom() != null)?$value->getDeletedFrom():0,
            'read'			=> ($value->getRead() != null)?$value->getRead():0
             
        );
         
      if (null === ($id = $value->getId()))
        {    
            $data['created']     = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);           
        }
        return $id;
    }
     
    public function makeRead(Default_Model_Messages $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'read' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
	
	//BEGIN:move to trash messages
    public function trashTo(Default_Model_Messages $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'trashedTo' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
	
    public function trashFrom(Default_Model_Messages $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'trashedFrom' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
	//END:move to trash messages
	
    public function deleteTo(Default_Model_Messages $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'deletedTo' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
	
    public function deleteFrom(Default_Model_Messages $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'deletedFrom' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
}