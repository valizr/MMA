<?php
class Default_Model_DbTable_TempFiles extends Zend_Db_Table_Abstract
{
	protected $_name    = 'temp_files';
	protected $_primary = 'id';
}

class Default_Model_TempFiles
{
    protected $_id;	
	protected $_userId;
	protected $_fileName;
	protected $_type;
	protected $_fileType;
	protected $_fileSize;
	protected $_created;	
	
    protected $_mapper;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid TempFiles property: '.$method);
        }
        $this->$method($value);
    }
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid TempFiles property: '.$method);
        }
        return $this->$method();
    }
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
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

    public function setUserId($value)
    {
        $this->_userId = (int) $value;
        return $this;
    }
	
    public function getUserId()
    {		
        return $this->_userId;
    } 
   
    public function setFileName($fileName)
    {
        $this->_fileName = (string) $fileName;
        return $this;
    }
	
    public function getFileName()
    {
        return $this->_fileName;
    }
	
    public function setType($type)
    {
        $this->_type = (string) $type;
        return $this;
    }
	
    public function getType()
    {
        return $this->_type;
    }
	
    public function setFileType($type)
    {
        $this->_fileType = (!empty($type))?(string) $type:NULL;
        return $this;
    }
	
    public function getFileType()
    {
        return $this->_fileType;
    }
	
	public function setFileSize($value)
    {
        $this->_fileSize = $value;
        return $this;
    }
	
    public function getFileSize()
    {		
        return $this->_fileSize;
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
	

    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }
	
    public function getMapper()
    {
        if (null === $this->_mapper) {
            $this->setMapper(new Default_Model_TempFilesMapper());
        }
        return $this->_mapper;
    }
    
    public function save()
    {
        return $this->getMapper()->save($this);
    }
	
    public function find($id)
    {
        return $this->getMapper()->find($id, $this);
    }
	
    public function fetchAll($select=null)
    {
        return $this->getMapper()->fetchAll($select);
    }
	
    public function delete()
    {
    	if(null === ($id = $this->getId())) {
    		throw new Exception("Invalid record selected!");
    	}
        return $this->getMapper()->delete($id);
    }
}

class Default_Model_TempFilesMapper
{
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_TempFiles');
        }
        return $this->_dbTable;
    }
    
    public function save(Default_Model_TempFiles $model)
    {
		
        $data = array(
            'userId'		=> $model->getUserId(),           
            'fileName'		=> $model->getFileName(),           
            'type'			=> $model->getType(),           
            'fileType'		=> $model->getFileType(),
			'fileSize'		=> $model->getFileSize(),
        );
				
        if (null === ($id = $model->getId())) {
		   $data['created']	 = new Zend_Db_Expr('NOW()');
           $id= $this->getDbTable()->insert($data);          
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
        return $id;
    }
    
    public function find($id, Default_Model_TempFiles $model)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $model->setOptions($row->toArray());
		return $model;
    }	

    public function fetchAll($select)
    {
        $resultSet = $this->getDbTable()->fetchAll($select);
        
        $entries   = array();
        foreach ($resultSet as $row) {
            $role = new Default_Model_TempFiles();
            $role -> setOptions($row->toArray())
            	  -> setMapper($this);
            $entries[] = $role;
        }
        return $entries;
    }
    
    public function delete($id)
    {
    	$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
        return $this->getDbTable()->delete($where);
    }
}
