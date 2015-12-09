<?php
class Needs_Graph
{
	protected $_model;
	protected $_tree = array();
	protected $_fieldsRequired;
	protected $_returnType;
	protected $_fetchChilds;
	protected $_tenancyId;
	
	public function getTree()
	{
		return $this->_tree;
	}
	
	/**
	 * ifSubchild
	 * $returnType pentru constructorul clasei trebuie sa fie 'array' iar in array sa existe $array['id']
	 * @param int $childId
	 */
	public function ifSubchild($childId)
	{
		if($this->_returnType == 'array')
		foreach($this->_tree as $value)
		{
			if(isset($value['id']))
				if($value['id'] == $childId)
				{
					return true;
				}
		}
		return false;
	}
	
	/**
	 * 
	 * @param type $model
	 * @param type $includeRoot
	 * @param array $fieldsRequired exemplu: array('id', 'idParent')
	 * @param string $returnType = object / array
	 */
	public function __construct($model, $includeRoot = false, $fieldsRequired = NULL, $returnType = 'object', $fetchChilds = false, $tenancyId = NULL)
	{		
		$this->_model			= $model;
		$this->_fieldsRequired	= $fieldsRequired;
		$this->_returnType		= $returnType;
		$this->_fetchChilds		= $fetchChilds;
		$this->_tenancyId		= $tenancyId;
		if($includeRoot)
		{			
			if($this->_returnType == 'object')
			{
				array_push($this->_tree, $this->_model);
			}
			elseif($this->_returnType == 'array' && $this->_fieldsRequired != NULL)
			{				
				$subArray = array();
				foreach($this->_fieldsRequired as $value)
				{
					$subArray[$value] = $this->_model->$value;
				}				
				array_push($this->_tree, $subArray);
			}
		}		
		$this->graph($this->_model->getId());
	}

	private function graph($id)
	{
		$branch = $this->branch($id);
		if(null!= $branch){
			foreach($branch as $item)
			{
				$model = new $this->_model;
				$tableName = $model->getMapper()->getDbTable()->info();
				$select = $model->getMapper()->getDbTable()->select()
						->from(array('u' => $tableName['name']), $this->_fieldsRequired);						
				$select->where('u.id = ?', $item);			
				$graphItem = $model->fetchRow($select);
				
				if($graphItem)
				{
					if($this->_returnType == 'object')
					{
						array_push($this->_tree, $graphItem);
					}
					elseif($this->_returnType == 'array' && $this->_fieldsRequired != NULL)
					{
						$subArray = array();
						foreach($this->_fieldsRequired as $value)
						{
							$subArray[$value] = $graphItem->$value;
						}
						array_push($this->_tree, $subArray);
					}
				}
				if(!$this->_fetchChilds)
				{
					$this->graph($item);
				}
				
			}
		}
		return false;
	}

	private function branch($id)
	{	
		$model = new $this->_model;
		$tableName = $model->getMapper()->getDbTable()->info();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('u' => $tableName['name']), array('id','idParent'))
				->where('idParent = ?', $id)
				->where('NOT deleted');		
		$result = $this->_model->fetchAll($select);
		if($result)
		{
			$branch = array();
			foreach($result as $value){
				$branch[] = $value->getId();
			}
			return $branch;
		}
		return null;
	}
	
	public static function hasChild($model)
	{
		$tableName = $model->getMapper()->getDbTable()->info();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('u' => $tableName['name']), array('id','idParent'))
				->where('idParent = ?', $model->getId())
				->where('NOT deleted');
		$result = $model->fetchAll($select);
		if($result)
		{
			return true;
		}
		return false;
	}
	
	public function moveChildren($idParent)
	{
		if($this->_returnType == 'array'):
			foreach($this->_tree as $value)
			{
				if(isset($value['id'])):
					$modelChild = new $this->_model;
					$modelChild->find($value['id']);
					$modelChild->setIdParent($idParent);					
					$modelChild->save();
				endif;
					
			}	
		endif;
			
	}
}