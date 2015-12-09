<?php
class Default_Model_DbTable_Comments extends Zend_Db_Table_Abstract
{
    protected $_name    = 'comment';
    protected $_primary = 'id';
}

class Default_Model_Comments
{
    protected $_id;
    protected $_idProject;
    protected $_idParent;
    protected $_idShop;
    protected $_idUserFrom;
	protected $_userFrom;
    protected $_idUserTo;
    protected $_description;
    protected $_created;
    protected $_modified;
    protected $_deleted;
     
	protected $_projectName;
     
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
     
    public function setIdProject($value)
    {
		$model=new Default_Model_Projects();
		$model->find($value);
		if ($model->getId()!=NULL)
			$this->setProjectName($model);
        $this->_idProject = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdProject()
    {
        return $this->_idProject;
    }
     
	public function setProjectName(Default_Model_Projects $value)
    {
        $this->_projectName = $value;
        return $this;
    }
 
    public function getProjectName()
    {
        return $this->_projectName;
    }
	
    public function setIdParent($value)
    {
        $this->_idParent = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdParent()
    {
        return $this->_idParent;
    }
     
    public function setIdShop($value)
    {
        $this->_idShop = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdShop()
    {
        return $this->_idShop;
    }
     
    public function setIdUserFrom($value)
    {
		$model=new Default_Model_Users();
		$model->find($value);
		if ($model->getId()!=NULL)
			$this->setUserFrom($model);
        $this->_idUserFrom = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdUserFrom()
    {
        return $this->_idUserFrom;
    }
	
	public function setUserFrom(Default_Model_Users $value)
    {
        $this->_userFrom = $value;
        return $this;
    }
 
    public function getUserFrom()
    {
        return $this->_userFrom;
    }
     
    public function setIdUserTo($value)
    {
        $this->_idUserTo = (!empty($value))?(int) $value:'0';
        return $this;
    }
 
    public function getIdUserTo()
    {
        return $this->_idUserTo;
    }
     
    public function setDescription($string)
    {
        $this->_description = (!empty($string))?(string) strip_tags($string):null;;
        return $this;
    }
 
    public function getDescription()
    {
        return $this->_description;
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
        $this->_modified = (!empty($date) && strtotime($date)>0)?strtotime($date):null;
        return $this;
    }
 
    public function getModified()
    {
        return $this->_modified;
    }
     
    public function setDeleted($value)
    {
        $this->_deleted = (!empty($value))?(int) $value:'0';
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
            $this->setMapper(new Default_Model_CommentsMapper());
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
            ->where('NOT deleted');
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
            ->from(array('Comment'),$fieldsArray)
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

class Default_Model_CommentsMapper
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
            $this->setDbTable('Default_Model_DbTable_Comments');
        }
        return $this->_dbTable;
    }
 
    public function find($id, Default_Model_Comments $model)
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
            $model = new Default_Model_Comments();
            $model->setOptions($row->toArray())
                    ->setMapper($this);
            $entries[] = $model;
        }
        return $entries;
    }
     
    public function fetchRow($select, Default_Model_Comments $model)
    {      
        $result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result))
        {
            return;
        }      
        $model->setOptions($result->toArray());
        return $model;
    }
     
    public function save(Default_Model_Comments $value)
    {
		$auth = Zend_Auth::getInstance();
		$authAccount = $auth->getStorage()->read();
		if (null != $authAccount) {
			if (null != $authAccount->getId()) {
				$user = new Default_Model_Users();
				$user->find($authAccount->getId());
				
				$data = array(
					'id' => $value->getId(),
					'idProject' => $value->getIdProject(),
					'idParent' => $value->getIdParent(),
					'idShop' => '3',
					'idUserFrom' => $user->id,
					'idUserTo' => $value->getIdUserTo(),
					'description' => $value->getDescription(),
					'deleted' => '0',
				);
				
				$action_done = '';	 
				if (null === ($id = $value->getId())) {
					$data['created'] = new Zend_Db_Expr('NOW()');
					$id = $this->getDbTable()->insert($data);
					
					//logs	action done		
					$user_name = $user->name.' '.$user->surname;				
					$project_name = $value->getProjectName()->getName();				
					$action_done = ' '.$user_name.' a adaugat un comentariu la proiectul '.$project_name.' ';				
					//end logs action done
				}
				Needs_Logs::DbLogTracking( $user->id, $value->getIdProject(), 'comments', 'adaugare', $action_done);
				return $id;
			}
		}
    }

    public function saveOrder(Default_Model_Comments $model)
    {
        $data = array(
            'order'  => $model->getOrder()
        );
 
        if (null === ($id = $model->getId())) {
            throw new Exception("Invalid data selected!");
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
        return $id;
    }
     
    public function delete(Default_Model_Comments $value)
    {   
        $id = $value->getId();
        $data = array(                 
            'deleted' => '1',           
        );
        $this->getDbTable()->update($data, array('id = ?' => $id));
        $this->getDbTable()->update($data, array('idParent = ?' => $id));
        return $id;
    }
}