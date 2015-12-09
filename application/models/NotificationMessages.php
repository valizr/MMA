<?php
class Default_Model_DbTable_NotificationMessages extends Zend_Db_Table_Abstract
{
    protected $_name    = 'notification_messages';
    protected $_primary = 'id';
}

class Default_Model_NotificationMessages
{
    protected $_id;
    protected $_idUser;
    protected $_idProject;
    protected $_subject;
    protected $_message;
    protected $_created;
    protected $_modified;
    protected $_deleted;
	protected $_status;
     
     
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
     
    public function setIdUser($value)
    {
        $this->_idUser = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdUser()
    {
        return $this->_idUser;
    }
	
	public function setStatus($value)
    {
        $this->_status = $value;
        return $this;
    }
 
    public function getStatus()
    {
        return $this->_status;
    }
     
    public function setIdProject($value)
    {
        $this->_idProject = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdProject()
    {
        return $this->_idProject;
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
        $this->_message = (!empty($string))?(string) strip_tags($string):null;
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
     
    public function setModified($data)
    {
        $this->_modified = (!empty($data) && strtotime($data)>0)?strtotime($data):null;
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
     
    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
     
    public function getMapper()
    {
        if(null === $this->_mapper) {
            $this->setMapper(new Default_Model_NotificationMessagesMapper());
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
            ->from(array('NotificationMessages'),$fieldsArray)
            ->where('id = ?',$id)           
            ->where('NOT deleted')          
            ->setIntegrityCheck(false);
        return $this->getMapper()->fetchRow($select,$this);      
    }
     
    public function getModelbyUrl($url,$fieldsArray = null)
    {
        $select = $this->getMapper()->getDbTable()->select();
            if($fieldsArray){
                $select->from(array('page'),$fieldsArray);
            }          
            $select->where('url = ?',$url)          
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
        return $this->getMapper()->delete($this);
    }
     
}

class Default_Model_NotificationMessagesMapper
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
            $this->setDbTable('Default_Model_DbTable_NotificationMessages');
        }
        return $this->_dbTable;
    }
 
    public function find($id, Default_Model_NotificationMessages $model)
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
            $model = new Default_Model_NotificationMessages();
            $model->setOptions($row->toArray())
                    ->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
     
    public function fetchRow($select, Default_Model_NotificationMessages $model)
    {      
        $result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result))
        {
            return;
        }
        $model->setOptions($result->toArray());
        return $model;
    }
     
    public function save(Default_Model_NotificationMessages $value)
    {
 
        $data = array(
            'idUser' => $value->getIdUser(),
            'idProject' => $value->getIdProject(),
            'subject' => $value->getSubject(),
            'message' => $value->getMessage(),
			'deleted' => '0',
        );
         
      if (null === ($id = $value->getId()))
        {    
            $data['created']     = new Zend_Db_Expr('NOW()');
            $id = $this->getDbTable()->insert($data);           
        }
		else 
		{    
            $data['modified']	 = new Zend_Db_Expr('NOW()');            
            $this->getDbTable()->update($data, array('id = ?' => $id));            
        }
        return $id;
    }
     
    public function delete(Default_Model_NotificationMessages $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'deleted' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        return $id;
    }
}