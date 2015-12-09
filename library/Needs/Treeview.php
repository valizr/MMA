<?php    
Class Needs_Treeview
{
     public $queryArray;
     public $treeResult;
     public $optionArray;
     public $prefix;
     
     public function __construct($db, $tableName, $idField, $titleField, $parentIdField, $prefix = 'jQueryParentID', $optionArray=array())
    {
        $this->prefix = $prefix;
        $where = ' where';
		$where .= ' NOT deleted';
        $sql= $db->query('Select * From ' . $tableName . $where );
		
//      while ( $row = mysql_fetch_array($result,  MYSQL_ASSOC))
        while ($row = $sql->fetch())
        {
          //
          // Wrap the row array in a parent array, using the id as they key
          // Load the row values into the new parent array
          //
          $this->queryArray[$row['id']] = array(
            'id' => $row[$idField], 
            'title' => $row[$titleField], 
            'parent_id' => $row[$parentIdField]
          );
        }
		
		$optionArray['addSubLink'] = (!empty($optionArray['addSubLink']))?$optionArray['addSubLink']:'';
		$optionArray['editLink'] = (!empty($optionArray['editLink']))?$optionArray['editLink']:'';
		$optionArray['deleteLink'] = (!empty($optionArray['deleteLink']))?$optionArray['deleteLink']:'';
		$optionArray['subName'] = (!empty($optionArray['subName']))?$optionArray['subName']:'';
		$optionArray['mainLink'] = (!empty($optionArray['mainLink']))?$optionArray['mainLink']:'#';
			  
		$this->optionArray = $optionArray;
    }

// ----------------------------------------------------------------

//
// Create a method to generate a nested view of an array (looping through each array item)
//
   public function generate_tree_list($array, $parent = 0)
   {

      //
      // Reset the flag each time the function is called
      //
      $has_children = false;

      //
      // Loop through each item of the list array
      //
      foreach($array as $key => $value)
      {
        //
        // For the first run, get the first item with a parent_id of 0 (= root category)
        // (or whatever id is passed to the function)
        //
        // For every subsequent run, look for items with a parent_id matching the current item's key (id)
        // (eg. get all items with a parent_id of 2)
        //
        // This will return false (stop) when it find no more matching items/children
        //
        // If this array item's parent_id value is the same as that passed to the function
        // eg. [parent_id] => 0   == $parent = 0 (true)
        // eg. [parent_id] => 20  == $parent = 0 (false)
        //
		if ($value['parent_id'] == $parent) 
        {                   

          //
          // Only print the wrapper ('<ul>') if this is the first child (otherwise just print the item)      
          // Will be false each time the function is called again
          //
          if ($has_children === false)
          {
				//
				// Switch the flag, start the list wrapper, increase the level count
				//
				$has_children = true; 

				$this->treeResult .= " <ul class='parent insRootClose'>"  ;          
          } 

		  {
			   $afterLinks = '';
			   if($this->optionArray['addSubLink']){
				   $afterLinks.= ' - <a class="addSubcategory" href="'.$this->optionArray['addSubLink'].$value['id'].'">adauga '.$this->optionArray['subName'].'</a>';
			   }
			   if($this->optionArray['editLink'])
			   {
				   $afterLinks.= ' <a class="edit" href="'.$this->optionArray['editLink'].$value['id'].'">editeaza</a>';
			   }
			   if($this->optionArray['deleteLink'])
			   {
				 $afterLinks.= ' <a class="delete confirmDelete" href="'.$this->optionArray['deleteLink'].$value['id'].'">sterge</a>';  
			   }			   

			   $this->treeResult .= '<li><a href="'.$this->optionArray['mainLink'].'" class = "listingItem '.$this->prefix.'"' . " id='$this->prefix" . $value['id'] . "'" . ' rel="'.$value['id'].'">' . $value['title'].'</a>'.$afterLinks;
		  }               
     
          $this->generate_tree_list($array, $key); 
          //
          // Close the item
          //
          $this->treeResult .= '</li>';
        }
      }
	  
      //
      // If we opened the wrapper above, close it.
      //
      if ($has_children === true) $this->treeResult .= '</ul>';
    }  
	
	public function remove_resource_from_childs($array, $parent,$resourceId)
	{

		//
		// Reset the flag each time the function is called
		//
		$has_children = false;

		//
		// Loop through each item of the list array
		//
		foreach($array as $key => $value)
		{
		  //
		  // For the first run, get the first item with a parent_id of 0 (= root category)
		  // (or whatever id is passed to the function)
		  //
		  // For every subsequent run, look for items with a parent_id matching the current item's key (id)
		  // (eg. get all items with a parent_id of 2)
		  //
		  // This will return false (stop) when it find no more matching items/children
		  //
		  // If this array item's parent_id value is the same as that passed to the function
		  // eg. [parent_id] => 0   == $parent = 0 (true)
		  // eg. [parent_id] => 20  == $parent = 0 (false)
		  //
		  if ($value['parent_id'] == $parent) 
		  {                   

			//
			// Only print the wrapper ('<ul>') if this is the first child (otherwise just print the item)      
			// Will be false each time the function is called again
			//
			if ($has_children === false)
			{
				  //
				  // Switch the flag, start the list wrapper, increase the level count
				  //
				  $has_children = true;       
			} 

			{
				 //remove rescource for role 				
//				$data = array(
//					'roleId = ?'		 => $parent,
//					'resourceId = ?'	 => $resourceId,
//				);
//				$modelRR = new Default_Model_ResourceRole();
//				$whereTags = $modelRR->getMapper()->getDbTable()->getAdapter()
//													->quoteInto($data);               
//				$modelRR->getMapper()->getDbTable()->delete($whereTags);
				
			}               

			$this->generate_tree_list($array, $key); 			

		  }

		}
	}

	public function __destruct()
    {
        
    }

}
?>