<?php
class Needs_Comments
{

	public function getCommentById($id)
	{
		
	}

	public static function getCommentsNumberById($competitorId)
	{
		$return = '0';
		$model = new Default_Model_Comments();
		$select = $model->getMapper()->getDbTable()->select()
				->from($model->getMapper()->getDbTable(), array('id'=>'COUNT(id)'))
				->where('competitorId = ?', $competitorId)
				->where('status = ?', '1')	
				->where('NOT deleted');		
		if(($result = $model->fetchAll($select))) {
			$return = $result[0]->getId();
		}
		return $return;
	}
	
	public static function getParentsCommentsNumberById($competitorId)
	{
		$return = '0';
		$model = new Default_Model_Comments();
		$select = $model->getMapper()->getDbTable()->select()
				->from($model->getMapper()->getDbTable(), array('id'=>'COUNT(id)'))
				->where('parentId IS NULL')
				->where('competitorId = ?', $competitorId)
				->where('status = ?', '1')
				->where('NOT deleted');		
		if(($result = $model->fetchAll($select))) {
			$return = $result[0]->getId();
		}
		return $return;
	}

	public static function getAllCommentsById($competitorId,$status=null)
	{
		$model = new Default_Model_Comments();
		$select = $model->getMapper()->getDbTable()->select()
				->where('parentId IS NULL')
				->where('competitorId = ?', $competitorId)
				->where('NOT deleted');		
		if($status){
			$select->where('status = ?', '1');	
		}
				$select->order('created DESC');
		if(($result = $model->fetchAll($select))) {
			return $result;
		}
	}	

	public static function getChildComments($commentId,$status=null)
	{		
		$model = new Default_Model_Comments();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('comments'), array('id','name','comment','created'))
				->where('parentId = ?', $commentId)
				->where('NOT deleted');		
			if($status){
				$select->where('status = ?', '1');	
			}	
				$select->order('created ASC');
		$result = $model->fetchAll($select);	
		return $result;
	}
	
	public static function getLatestCommentsbyCompetitorId($competitorId,$limit=1)
	{		
		$model = new Default_Model_Comments();
		$select = $model->getMapper()->getDbTable()->select()
				->from(array('comments'), array('id','name','comment','created'))
				->where('competitorId = ?', $competitorId);			
				$select->where('status = ?', '1');	
				$select->order('created DESC')
						->where('NOT deleted');		
				$select->limit($limit);
		if($limit==1){
			$result = $model->fetchRow($select);
		}else{
			$result = $model->fetchAll($select);
		}	
				
		return $result;
	}
	
	public static function toHtml($galleryId, $comentsNr, $form)
	{			
			$commentsNr = self::getCommentsNumberById($galleryId);
			$comments = self::getAllCommentsById($galleryId);
			$html = "
			<div class=\"comentarii\">
				<div class=\"lasa_coment\">
					{$form}
				</div>";

			if($commentsNr > 0)
			{
				$html .= "
				<div class=\"header_comentarii\">
					<p>";
					if($comentsNr == '1')
					{
						$html .= "{$comentsNr} comentariu";
					}
					else
					{
						$html .= "{$comentsNr} comentarii";
					}
					$html .= "</p>
			</div>";
			}
			if($comments)
			{
				foreach($comments as $value)
				{
					$customer = TS_Statistics::customerModelById($value->getUserId(), array('id'=>'id', 'username'=>'username', 'avatar'=>'avatar'));
					$html .= "
				<div class=\"modul_comentariu\">
					<div class=\"left_comentariu\">";
					if(null != $customer->getAvatar())
					{
						$html .= "<img width=\"44\" height=\"44\" src=\"/media/avatar/small/{$customer->getAvatar()}\" alt=\"user\" />";
					}
					else
					{
						$html .= "<img width=\"44\" height=\"44\" src=\"/theme/default/images/user.gif\" alt=\"user\" />";
					}
					$datePosted = date('d/m/Y H:i', $value->getAdded());
					$html .= "</div>
					<div class=\"right_comentariu\">
						<p class=\"nume\">
							<a href=\"#\">{$value->getName()}</a> l <span>{$datePosted}</span></p>
						<p class=\"descriere\">{$value->getComment()}</p>
					</div>
					<div style=\"clear:both; width:1px\"></div>
				</div>";
				}
			}
		$html .= "</div>";
		echo $html;
	}
}