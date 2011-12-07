<?php

public function traverseCollectionContent($CollectionContentID)
   {
      if(!$CollectionContentID)
      {
         $this->declareError("Could not traverse CollectionContent: CollectionContent ID not defined.");
         return false;
      }

//      $objContent = New CollectionContent($CollectionContentID);
//      $objContent->dbLoad();
//      $arrContent[$objContent->ID] = $objContent;

      $arrContent = array();

      $query = "SELECT parent.* FROM tblCollections_Content AS node,tblCollections_Content AS parent WHERE node.Lft BETWEEN parent.Lft AND parent.Rgt AND node.ID = {$CollectionContentID} AND parent.CollectionID = node.CollectionID ORDER BY parent.Lft";
      $result = $this->mdb2->query($query);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
         $objContent = New CollectionContent($row);
         $objContent->dbLoadObjects(false);
         $arrContent[$objContent->ID] = $objContent;

      }
      $result->free();

      return $arrContent;
   }

?>
