<?php
class GeneratorController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
	}

	public function indexAction()
	{
		$form_generator = new Default_Form_Generator();
        $form_generator->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/generator.phtml'))));
        $this->view->plugin_form_generator = $form_generator;
			
		if($this->getRequest()->isPost())
		{			
			//$model=new Default_Model_Generator();
			//$action = $this->getRequest()->getPost('action');				
			if($form_generator->isValid($this->getRequest()->getPost())) 
			{
				$generatorModel=$this->getRequest()->getPost();
				
				$campPrimary='';
				$protected='';
				$saveData='';
				$getters_setters='';
				$db = Zend_Db_Table::getDefaultAdapter();
				$generatorTemp=$db->describeTable($generatorModel['tabel']);
				foreach ($generatorTemp as $camp){
					$protected.='protected $_'.$camp['COLUMN_NAME'].";\n\t";
					$saveData.='\''.$camp['COLUMN_NAME'].'\' => $value->get'.ucwords($camp['COLUMN_NAME']).'(),'."\n\t\t\t";
					$numeCamp = $camp['COLUMN_NAME'];
					$dataType = $camp['DATA_TYPE'];
					switch ($dataType){
						case 'int':
						case 'tinyint':
						case 'smallint':
							$data='(!empty($value))?(int) $value:\'0\';';
							$variabila='$value';
							break;
						case 'text':
						case 'varchar':
							$data='(!empty($string))?(string) strip_tags($string):null;';
							$variabila='$string';
							break;
						case 'date':
						case 'datetime':
							$data='(!empty($data) && strtotime($data)>0)?strtotime($data):null;';
							$variabila='$data';
							break;
					}					
$getters_setters.='
	public function set'.ucwords($camp['COLUMN_NAME']).'('.$variabila.')
	{
		$this->_'.$camp['COLUMN_NAME'].' = '.$data.'
		return $this;
	}

	public function get'.ucwords($camp['COLUMN_NAME']).'()
	{
		return $this->_'.$camp['COLUMN_NAME'].';
	}
	';
	
					$primary  = $camp['PRIMARY'];
					if ($primary==1) {
						$idPrimary='protected $_primary = \''.$numeCamp.'\';';
						$campPrimary = $numeCamp;
					}
					
				}
				$result = '<?php
class Default_Model_DbTable_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).' extends Zend_Db_Table_Abstract
{
	protected $_name    = \''.$generatorModel['tabel'].'\';
	'.$idPrimary.'
}';
				
				
$result2 = 'class Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).'
{
	'.$protected.'
	
	protected $_mapper;

	public function __construct(array $options = null)
	{
		if(is_array($options)) {
			$this->setOptions($options);
		}
	}

	public function __set($name, $value)
	{
		$method = \'set\' . $name;
		if((\'mapper\' == $name) || !method_exists($this, $method)) {
			throw new Exception(\'Invalid \'.$name.\' property \'.$method);
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = \'get\' . $name;
		if((\'mapper\' == $name) || !method_exists($this, $method)) {
			throw new Exception(\'Invalid \'.$name.\' property \'.$method);
		}
		return $this->$method();
	}

	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);
		foreach($options as $key => $value)
		{
			$method = \'set\' . ucfirst($key);
			if(in_array($method, $methods))
			{
				$this->$method($value);
			}
		}
		return $this;
	}
'.
$getters_setters.'
	
	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}
	
	public function getMapper()
	{
		if(null === $this->_mapper) {
			$this->setMapper(new Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).'Mapper());
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
			->where(\'NOT deleted\')
			->order(\'data DESC\');
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
			->from(array(\''.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).'\'),$fieldsArray)
			->where(\''.$campPrimary.' = ?\',$id)			
			->where(\'NOT deleted\')			
			->setIntegrityCheck(false);
        return $this->getMapper()->fetchRow($select,$this);		
	}
	
	public function getModelbyUrl($url,$fieldsArray = null)
	{
		$select = $this->getMapper()->getDbTable()->select();
			if($fieldsArray){
				$select->from(array(\'page\'),$fieldsArray);
			}			
			$select->where(\'url = ?\',$url)			
			->where(\'NOT deleted\')			
			->setIntegrityCheck(false);
        return $this->getMapper()->fetchRow($select,$this);		
	}

	public function save()
	{
		return $this->getMapper()->save($this);
	}
	
	public function delete()
	{
		if(null === ($id = $this->get'.ucwords($campPrimary).'())) {
			throw new Exception(\'Invalid record selected!\');
		}
		return $this->getMapper()->delete($this);
	}
	
}';

$result3 = 'class Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).'Mapper
{
	protected $_dbTable;

	public function setDbTable($dbTable)
	{
		if(is_string($dbTable)) {
			$dbTable = new $dbTable();
		}
		if(!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception(\'Invalid table data gateway provided\');
		}
		$this->_dbTable = $dbTable;
		return $this;
	}

	public function getDbTable()
	{
		if(null === $this->_dbTable) {
			$this->setDbTable(\'Default_Model_DbTable_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).'\');
		}
		return $this->_dbTable;
	}

	public function find($id, Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).' $model)
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
			$model = new Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).'();
			$model->setOptions($row->toArray())
					->setMapper($this);
			$entries[] = $model;
		}
		return $entries;
	}
	
	public function fetchRow($select, Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).' $model)
    {		
		$result=$this->getDbTable()->fetchRow($select);
        if(0 == count($result)) 
		{
            return;
        }      	
        $model->setOptions($result->toArray());
		return $model;
    }
	
	public function save(Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).' $value)
    {

        $data = array('.$saveData.'
        );
        
      if (null === ($id = $value->get'.ucwords($campPrimary).'()))
		{     
			$data[\'created\']	 = new Zend_Db_Expr(\'NOW()\');
            $id = $this->getDbTable()->insert($data);            
        } 
        return $id;
    }
	
    public function delete(Default_Model_'.str_replace(" ","",ucwords(str_replace("_"," ",$generatorModel['tabel']))).' $value)
    {    
		$id = $value->get'.ucwords($campPrimary).'();
		$data = array(					
			\'deleted\' => \'1\',			
		);
		$this->getDbTable()->update($data, array(\''.$campPrimary.' = ?\' => $id));
        return $id;
    }
}
';
				
$result=str_replace("<",'&lt;',$result);
$result=str_replace(">",'&gt;',$result);
$result2=str_replace("<",'&lt;',$result2);
$result2=str_replace(">",'&gt;',$result2);
$result3=str_replace("<",'&lt;',$result3);
$result3=str_replace(">",'&gt;',$result3);
print("<pre class=\"brush: php\">$result</pre>");
print("<br><br><pre class=\"brush: php\">$result2</pre>");
print("<br><br><pre class=\"brush: php\">$result3</pre>");
//echo nl2br($result);
				//$this->_flashMessenger->addMessage("<div class='notification success_msg  canhide'><p>Modificarile dumneavoastra au fost realizate cu succes!</p></div>");
				/*}
				else
				{
					$this->_flashMessenger->addMessage("<div class='notification error_msg canhide'><p>A aparut o eroare ! </p></div>");
				}
				$this->_redirect(WEBROOT.'/generator');*/
			}
		}
	}
}
