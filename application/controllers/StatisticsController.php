<?php
class StatisticsController extends Zend_Controller_Action{
	public function init(){
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->view->message = $this->_flashMessenger->getMessages();
                require_once APPLICATION_PUBLIC_PATH."/library/PHPExcel/Classes/PHPExcel.php";
	}
	
	public function indexAction()
	{
		// BEGIN:FILTERS
		$filters = array(); //array with variables to send to pagination (filters)	
		// END:FILTERS
        $params	= array();
		
		if($this->getRequest()->getParam('dataStart')){
			$params['dataStart'] = $this->getRequest()->getParam('dataStart');
		}
        if($this->getRequest()->getParam('dataEnd')){
			$params['dataEnd'] = $this->getRequest()->getParam('dataEnd');
		}
		
        $formsearch = new Default_Form_SearchStatistics();
		$formsearch->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/statistics/search.phtml'))));
		$this->view->formsearch = $formsearch;
                
        if (empty($params)) {
            $params['dataStart']=date('Y-m-01');
            $params['dataEnd']='';
        }
		$this->view->search=$params;
                
        $formsearchcomplex = new Default_Form_SearchComplexStatistics();
		$formsearchcomplex->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/statistics/search-complex.phtml'))));
		$this->view->formsearchcomplex = $formsearchcomplex;
                
                //START: Expenses PIE
		$groups = new Default_Model_Groups();
		$select = $groups->getMapper()->getDbTable()->select()
				->from(array('g'=>'groups'),
					array('g.id','g.name','g.color','g.created','g.deleted'))
				->joinLeft(array('pg' => 'product_groups'), 'g.id = pg.idGroup', array())
                                ->joinLeft(array('p' => 'expenses'), 'p.id = pg.idProduct', array('price'=>'SUM(p.price)'))
                                ->where('NOT g.deleted')
                                ->where('p.type=?',0)
                                ->group('g.id')
                                ->setIntegrityCheck(false);
                if(!empty($params['dataStart'])){
                    $select->where('p.date >= ?',$params['dataStart']);
                }
                if(!empty($params['dataEnd'])){
                    $select->where('p.date <= ?',$params['dataEnd']);
                }
                if (empty($params['dataStart']) && empty($params['dataEnd'])){
                    $select->where('p.date>=?',date('Y-m-01'));
                }
                $resultPieExpenses=$groups->fetchAll($select);
                $this->view->resultPieExpenses=$resultPieExpenses;
                //END: Expenses PIE
                
                //START: Income PIE
		$groups2 = new Default_Model_Groups();
		$select2 = $groups2->getMapper()->getDbTable()->select()
				->from(array('g'=>'groups'),
                                    array('g.id','g.name','g.color','g.created','g.deleted'))
				->joinLeft(array('pg' => 'product_groups'), 'g.id = pg.idGroup', array())
                                ->joinLeft(array('p' => 'expenses'), 'p.id = pg.idProduct', array('price'=>'SUM(p.price)'))
                                ->where('NOT g.deleted')
                                ->where('p.type=?',1)
                                ->group('g.id')
                                ->setIntegrityCheck(false);
                if(!empty($params['dataStart'])){
                    $select2->where('p.date >= ?',$params['dataStart']);
                }
                if(!empty($params['dataEnd'])){
                    $select2->where('p.date <= ?',$params['dataEnd']);
                }
                if (empty($params['dataStart']) && empty($params['dataEnd'])){
                    $select2->where('p.date>=?',date('Y-m-01'));
                }
                $resultPieIncome=$groups2->fetchAll($select2);
                $this->view->resultPieIncome=$resultPieIncome;
                //END: Income PIE
                
                //START current month expenses / income / left
                
                $resultIncomeExpense = Needs_Tools::showIncomeExpensesDashboard(date('Y'), date('m'));
                if ($resultIncomeExpense){
                    $value=array();
                    foreach ($resultIncomeExpense as $values){
                        $value[] = $values->getPrice();
                    }
                    if (isset($value[1])){
                    $this->view->incomeAmount=$value[1];
                    }else $this->view->incomeAmount=0;
                    if (isset($value[0])){
                    $this->view->expensesAmount=$value[0];
                    }else $this->view->expensesAmount=0;
                }else{
                    $this->view->incomeAmount=0;
                    $this->view->expensesAmount=0;
                }
                //END current month expenses / income / left
        
                $categoryNames=array();
                $categoryRes=array();
                $monthly_stats=array();
                $line_color=array();
                $categoryNamesi=array();
                $categoryResi=array();
                $monthly_statsi=array();
                $line_colori=array();
                $i=0;
                
        if($this->getRequest()->getParam('idCategoryE')){
			$params['idCategoryE'] = $this->getRequest()->getParam('idCategoryE');
		}
        if($this->getRequest()->getParam('idCategoryI')){
			$params['idCategoryI'] = $this->getRequest()->getParam('idCategoryI');
		}
        if($this->getRequest()->getParam('dataStartC')){
			$params['dataStartC'] = $this->getRequest()->getParam('dataStartC');
		}
        if($this->getRequest()->getParam('dataEndC')){
			$params['dataEndC'] = $this->getRequest()->getParam('dataEndC');
		}
        if($this->getRequest()->getParam('timeframe')){
			$params['timeframe'] = $this->getRequest()->getParam('timeframe');
		}
                
                if (isset($params['dataStartC']) || isset($params['dataEndC']) || isset($params['idCategoryE']) || isset($params['idCategoryI'])){
                    $value=array();
                    if (empty($params['idCategoryE'])) $params['idCategoryE'][0]=1;//expenses "category"
                    if (empty($params['idCategoryI'])) $params['idCategoryI'][0]=2;//income "category"
                    $resultExpense = Needs_Tools::showExpensesDashboardbyDateCat($params['dataStartC'], $params['dataEndC'], $params['idCategoryE'], $params['timeframe']);
                    
                    $resultIncome = Needs_Tools::showIncomeDashboardbyDateCat($params['dataStartC'], $params['dataEndC'], $params['idCategoryI'], $params['timeframe']);
                    
                    $totalAmountCat=array();
                    foreach ($resultExpense as $Expense =>$values){
                        //echo($Expense."<br>");
                        //print_r($values);
                        foreach ($values as $cat => $value){
                            //echo $cat."<br>";
                            //print_r($value);
                            $totalAmountCat[$cat]=(isset($totalAmountCat[$cat])?$totalAmountCat[$cat]+$value[2]:$value[2]);
                            $categoryRes[$cat]=$value[0];
                            $monthly_stats[$value[1]][$cat]=$value[2];//monthly_stat[saptamana de forma data1/data2]=cheltuieli din sapt respectiva
                            $line_color[$cat] = $value[3];//culoarea liniei pt categoria asta
                        }
                    }
                    foreach ($categoryRes as $key => $value){
                        $categoryNames[$key]=$value."-".$totalAmountCat[$key];
                    }
                    asort($categoryNames);//order alphabetically
                    
                    $totalAmountCati=array();
                    foreach ($resultIncome as $Income =>$valuesi){
                        //echo($Income."<br>");
                        //print_r($values);
                        foreach ($valuesi as $cati => $valuei){
                            //echo $cat."<br>";
                            //print_r($value);
                            $totalAmountCati[$cati]=(isset($totalAmountCati[$cati])?$totalAmountCati[$cati]+$valuei[2]:$valuei[2]);
                            $categoryResi[$cati]=$valuei[0];
                            $monthly_statsi[$valuei[1]][$cati]=$valuei[2];//monthly_stat[saptamana de forma data1/data2]=cheltuieli din sapt respectiva
                            $line_colori[$cati] = $valuei[3];//culoarea liniei pt categoria asta
                        }
                    }
                    foreach ($categoryResi as $keyi => $valuei){
                        $categoryNamesi[$keyi]=$valuei."-".$totalAmountCati[$keyi];
                    }
                    asort($categoryNamesi);//order alphabetically
                    
                }else{
//                    $categoryNames[0]='Expenses';
//                    $categoryNames[1]='Income';
//                //for ($i=1;$i>=0;$i--){//show chart for last year as well.
//                    for ($j=1;$j<(date('n')+1);$j++){
//                        if (strlen($j)==1) $m="0".$j;
//                        else $m=$j;
//                        $resultIncomeExpense = Needs_Tools::showIncomeExpensesDashboard(date('Y')-$i, $m);
//                        if ($resultIncomeExpense){
//                            $value=array();
//                            foreach ($resultIncomeExpense as $values){
//                                $value[] = $values->getPrice();
//                            }
//                            if (isset($value[1])){
//                            $monthly_stats[$m.'-'.(date('Y')-$i)][1]=$value[1];
//                            }else $monthly_stats[$m.'-'.(date('Y')-$i)][1]=0;
//                            if (isset($value[0])){
//                            $monthly_stats[$m.'-'.(date('Y')-$i)][0]=$value[0];
//                            }else $monthly_stats[$m.'-'.(date('Y')-$i)][0]=0;
//                        }else{
//                            $monthly_stats[$m.'-'.(date('Y')-$i)][0]=0;
//                            $monthly_stats[$m.'-'.(date('Y')-$i)][1]=0;
//                        }                
//                    }
//                    $line_color[0]='#d43c2c';
//                    $line_color[1]='#58a87d';
//                //}//this shows income and expenses together when first loading the page
                 //here i separate expenses and income each with its own graph.
                    $categoryNames[0]=Zend_Registry::get('translate')->_('admin_expenses');
                    $categoryNamesi[0]=Zend_Registry::get('translate')->_('admin_income');
                //for ($i=1;$i>=0;$i--){//show chart for last year as well.
                    for ($j=1;$j<(date('n')+1);$j++){
                        if (strlen($j)==1) $m="0".$j;
                        else $m=$j;
                        $resultIncomeExpense = Needs_Tools::showIncomeExpensesDashboard(date('Y')-$i, $m);
                        if ($resultIncomeExpense){
                            $value=array();
                            foreach ($resultIncomeExpense as $values){
                                $value[] = $values->getPrice();
                            }
                            if (isset($value[1])){
                            $monthly_statsi[$m.'-'.(date('Y')-$i)][0]=$value[1];
                            }else $monthly_statsi[$m.'-'.(date('Y')-$i)][0]=0;
                            
                            if (isset($value[0])){
                            $monthly_stats[$m.'-'.(date('Y')-$i)][0]=$value[0];
                            }else $monthly_stats[$m.'-'.(date('Y')-$i)][0]=0;
                        }else{
                            $monthly_stats[$m.'-'.(date('Y')-$i)][0]=0;
                            $monthly_statsi[$m.'-'.(date('Y')-$i)][0]=0;
                        }
                        $monthly_stats[$m.'-'.(date('Y')-$i)][1]=$m.'-'.(date('Y')-$i);//monthlystats[1]=the date
                        $monthly_statsi[$m.'-'.(date('Y')-$i)][1]=$m.'-'.(date('Y')-$i);//monthlystats[1]=the date
                    }
                    $line_color[0]='#d43c2c';
                    $line_colori[0]='#58a87d';
                //}
                    
                }
                
                if ($this->getRequest()->isPost()) {
                    $formData = $this->getRequest()->getPost();
                    if ($formsearchcomplex->isValid($formData)) {
                        $formsearchcomplex->populate($formData);
                    }
                }

                if($this->getRequest()->isPost()) {
                    if($this->getRequest()->getPost('submitReportE')) {                        
			$monthly_stats=array_filter($monthly_stats);
                        
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Money-Management-System")
				 ->setLastModifiedBy("Money-Management-System")
				 ->setTitle("Form")
				 ->setSubject(Zend_Registry::get('translate')->_('admin_expenses'))
				 ->setDescription(Zend_Registry::get('translate')->_('admin_expenses_chart'))
				 ->setKeywords("")
				 ->setCategory("");
                        $col=0;
                        $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValueByColumnAndRow(0,1,'');
                        $col++;
			foreach ($categoryNames as $keys => $values){
                            foreach ($monthly_stats as $monthlyStat => $valuesms){
                                if (!array_key_exists($keys,$valuesms)) $valuesms[$keys]=0;
                            }
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValueByColumnAndRow($col,1,$values);
                            $col++;
                        }
                        $row=2;
                        foreach ($monthly_stats as $monthlyStat => $values){//for each period: days/week intervals/months
                            $rowDate	= ($monthlyStat!='') ? $monthlyStat : '-';
                            $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValueByColumnAndRow(0,$row,$rowDate);
                            $col=1;
                            foreach ($categoryNames as $keys => $value) {
                                if (array_key_exists($keys, $values)) $rowAmount=$values[$keys];
                                    else $rowAmount=0;
                                $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValueByColumnAndRow($col,$row, $rowAmount);
                                $col++;
                            }
                            $row++;
                        }
                        
			$objPHPExcel->getActiveSheet()->setTitle(Zend_Registry::get('translate')->_('admin_expenses'));
			$objPHPExcel->setActiveSheetIndex(0);

			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.date('Y-m-d-H-i-s').'.xls"');
							/*header('Cache-Control: max-age=0');*/
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->getHelper('layout')->disableLayout();
		    }
                    //generate excel report for income
                    if($this->getRequest()->getPost('submitReportI')) {                        
			$monthly_stats=array_filter($monthly_stats);
                        
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Money-Management-System")
				 ->setLastModifiedBy("Money-Management-System")
				 ->setTitle("Form")
				 ->setSubject(Zend_Registry::get('translate')->_('admin_income'))
				 ->setDescription(Zend_Registry::get('translate')->_('admin_income_chart'))
				 ->setKeywords("")
				 ->setCategory("");
                        $col=0;
                        $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValueByColumnAndRow(0,1,'');
                        $col++;
			foreach ($categoryNamesi as $keys => $values){
                            foreach ($monthly_statsi as $monthlyStat => $valuesms){
                                if (!array_key_exists($keys,$valuesms)) $valuesms[$keys]=0;
                            }
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValueByColumnAndRow($col,1,$values);
                            $col++;
                        }
                        $row=2;
                        foreach ($monthly_statsi as $monthlyStat => $values){//for each period: days/week intervals/months
                            $rowDate	= ($monthlyStat!='') ? $monthlyStat : '-';
                            $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValueByColumnAndRow(0,$row,$rowDate);
                            $col=1;
                            foreach ($categoryNamesi as $keys => $value) {
                                if (array_key_exists($keys, $values)) $rowAmount=$values[$keys];
                                    else $rowAmount=0;
                                $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValueByColumnAndRow($col,$row, $rowAmount);
                                $col++;
                            }
                            $row++;
                        }
                        
			$objPHPExcel->getActiveSheet()->setTitle(Zend_Registry::get('translate')->_('admin_expenses'));
			$objPHPExcel->setActiveSheetIndex(0);

			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.date('Y-m-d-H-i-s').'.xls"');
							/*header('Cache-Control: max-age=0');*/
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->getHelper('layout')->disableLayout();
		    }
                }
                
                
                
                $this->view->monthlyStats=array_filter($monthly_stats);
                $this->view->lineColor=$line_color;
                $this->view->categoryNames=$categoryNames;
                
                $this->view->monthlyStatsi=array_filter($monthly_statsi);
                $this->view->lineColori=$line_colori;
                $this->view->categoryNamesi=$categoryNamesi;
                
	}
	
	public function detailsAction()
	{
		$id		= $this->getRequest()->getParam('id');
		$type	= $this->getRequest()->getParam('type');
		$this->view->type = $type;
		
		$loggedInUserId = Zend_Registry::get('user')->getId();
		$modelMesaj = new Default_Model_Messages();
		if($modelMesaj->find($id) && ($modelMesaj->getIdUserFrom() == $loggedInUserId || $modelMesaj->getIdUserTo() == $loggedInUserId) )
		{
			//BEGIN:mark as read			
			if($modelMesaj->getRead() == 0){
				$model = new Default_Model_Messages();
				$model->find($id);
				$model->makeRead();
			}
			//END:mark as read
			
			$this->view->result = $modelMesaj;
		}
	}
}