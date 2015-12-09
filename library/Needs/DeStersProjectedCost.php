<?php
class Needs_ProjectedCost
{
	/**
	 * Returns the weekly cost by shop and week
	 * 
	 * @param (date) $dateFrom - format: 'YYYY-mm-dd';
	 * @param (date) $dateTo - format: 'YYYY-mm-dd';
	 * @param int $shopId
	 * @return \Default_Model_WeeklyCosts
	 */
	public static function getWeeklyCostbyShopIdAndDate($dateFrom,$dateTo,$shopId)
	{
		$modelWeeklyCosts = new Default_Model_WeeklyCosts();
		$selectFind = $modelWeeklyCosts->getMapper()->getDbTable()->select()
					->where('dateFrom = ?',	$dateFrom)
					->where('dateTo = ?',$dateTo)
					->where('idShop = ?',$shopId);
		$modelWeeklyCosts->fetchRow($selectFind);
		
		return $modelWeeklyCosts;
	}
	
	/**
	 * Calculate total gross sales between  2 dates
	 * 
	 * @param (date) $dateFrom - format: 'YYYY-mm-dd';
	 * @param (date) $dateTo - format: 'YYYY-mm-dd';
	 * @param int $shopId
	 * @return int - total gross sales between  2 dates
	 */
	public static function calculateTotalWeekGrossSales($dateFrom,$dateTo,$shopId)
	{
		$model = new Default_Model_DailySales();
		$selectAll = $model->getMapper()->getDbTable()->select()
					->from(array('ds'=>'daily_sales'),array('id'=>'SUM(ds.grossSales)'))
					->where('ds.date >= ?',$dateFrom)
					->where('ds.date <= ?',$dateTo)
					->where('ds.idShop = ?',$shopId);
		$model->fetchRow($selectAll);
		return $model->getId();
	}

	/**
	 * Calculate actual food/labor cost per week
	 * 
	 * @param (date) $dateFrom - format: 'YYYY-mm-dd';
	 * @param (date) $dateTo - format: 'YYYY-mm-dd';
	 * @param int $shopId
	 * @return int
	 */
	public static function calculateActualWeeklyCosts($dateFrom,$dateTo,$shopId)
	{
		$result = array();
		$result['labor'] = 0;
		$result['food'] = 0;
		$weeklyCost = self::getWeeklyCostbyShopIdAndDate($dateFrom, $dateTo, $shopId);		
		$weeklyLaborCost = $weeklyCost->getLaborCost();		
		$weeklyFoodCost = $weeklyCost->getFoodCost();
				
		$totalWeekGrossSales = self::calculateTotalWeekGrossSales($dateFrom, $dateTo, $shopId);
		
		if($totalWeekGrossSales)
		{
			$result['labor'] = number_format(($weeklyLaborCost*100)/$totalWeekGrossSales,2);
			$result['food'] = number_format(($weeklyFoodCost*100)/$totalWeekGrossSales,2);
		}
		return $result;
	}
}
