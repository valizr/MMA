<?php
class Needs_Messages
{	
	public function getUnreadMessagesNumber()
	{
		$model = new Default_Model_Messages();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('u'=>'messages'),array('id'=>'COUNT(u.id)'))				
				->where('u.idUserTo = ?',Zend_Registry::get('user')->getId())
				->where('NOT u.deletedTo')
				->where('NOT u.trashedTo')
				->where('NOT u.read')				
				->order('u.created  DESC')				
				->setIntegrityCheck(false);
		$model->fetchRow($select);
		return $model->getId();
	}
	
	public function getInboxMessagesNumber()
	{
		$model = new Default_Model_Messages();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('u'=>'messages'),array('id'=>'COUNT(u.id)'))				
				->where('u.idUserTo = ?',Zend_Registry::get('user')->getId())
				->where('NOT u.deletedTo')
				->where('NOT u.trashedTo')
				->order('u.created  DESC')				
				->setIntegrityCheck(false);
		$model->fetchRow($select);
		return $model->getId();
	}
	
	public function getSentMessagesNumber()
	{
		$model = new Default_Model_Messages();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('u'=>'messages'),array('id'=>'COUNT(u.id)'))				
				->where('u.idUserFrom = ?',Zend_Registry::get('user')->getId())
				->where('NOT u.deletedFrom')
				->where('NOT u.trashedFrom')							
				->order('u.created  DESC')				
				->setIntegrityCheck(false);
		$model->fetchRow($select);
		return $model->getId();
	}
	
	public function getTrashMessagesNumber()
	{
		$model = new Default_Model_Messages();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('u'=>'messages'),array('id'=>'COUNT(u.id)'))				
				->where(""
						. "(u.idUserTo = '".Zend_Registry::get('user')->getId()."' AND u.trashedTo = 1 AND NOT u.deletedTo)  "
						. "OR "
						. "(u.idUserFrom = '".Zend_Registry::get('user')->getId()."'  AND u.trashedFrom = 1 AND NOT u.deletedFrom)")
				->order('u.created  DESC')				
				->setIntegrityCheck(false);
		$model->fetchRow($select);
		return $model->getId();
	}
}
