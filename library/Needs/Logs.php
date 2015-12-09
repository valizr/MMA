<?php 
class Needs_Logs
{
	/**
	 * Face scriere in baza de date la o actiune de tip tracking
	 * 
	 * @param int $userId	Id-ul userului care a facut actiunea
	 * @param string $modul		Modulul in care s-a facut actiunea
	 * @param string $actionType	Tipul actiunii
	 * @param string $actionDone	Text ce arata ce s-a modificat, obligatoriu sa contina tag-ul @userId@, optional @targetId@
	 * @param int $targetId		Id-ul persoanei,obiectului asupra care s-a facut modificarea
	 */
	public function DbLogTracking( $userId, $targetId, $modul, $actionType, $actionDone )
	{
		$logs = new Default_Model_Logs();
		$logs->setIdUser($userId);
		$logs->setIdTarget($targetId);
		$logs->setModul($modul);
		$logs->setActionType($actionType);
		$logs->setActionDone($actionDone);
		$id = $logs->save();
		return $id;
	}
}