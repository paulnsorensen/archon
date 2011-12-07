<?php


  public function dbLoadContent()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load CollectionContent: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load CollectionContent: Collection ID must be numeric.");
         return false;
      }

//      if(!is_natural($RootContentID) && $RootContentID != LOADCONTENT_NONE)
//      {
//         $_ARCHON->declareError("Could not load CollectionContent: RootContentID must be numeric.");
//         return false;
//      }

//      if($RootContentID == LOADCONTENT_NONE)
//      {
//         return true;
//      }

      $this->Content = array();

      static $contentPrep = NULL;
      if(!isset($contentPrep))
      {
//         $rootcontentidquery = " AND ((tblCollections_Content.RootContentID = ? OR ? = " . LOADCONTENT_ALL . ") OR tblCollections_Content.RootContentID = '0')";
//         $rootcontentidtypes = array('integer', 'integer');

         $query = "SELECT tblCollections_Content.* FROM tblCollections_Content WHERE tblCollections_Content.CollectionID = ? ORDER BY tblCollections_Content.Lft";
//         $contentPrep = $_ARCHON->mdb2->prepare($query, array_merge(array('integer'), $rootcontentidtypes), MDB2_PREPARE_RESULT);
         $contentPrep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_RESULT);
      }

//      $rootcontentidvars = array($RootContentID, $RootContentID);

      $result = $contentPrep->execute(array($this->ID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      // If there is no content found.
      if(!$result->numRows())
      {
         $result->free();
         return true;
      }

      $arrLevelContainers = $_ARCHON->getAllLevelContainers();
//      $arrCollectionContentVariables = get_object_vars(New CollectionContent());

      while($row = $result->fetchRow())
      {
         $this->Content[$row['ID']] = New CollectionContent($row);
      }
      $result->free();

//      $_ARCHON->sortCollectionContentArray(&$this->Content);

      // Now we need to establish parent-child relationships
      foreach($this->Content as $ID => $objContent)
      {
         $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];
         $objContent->Collection = $this;

         if($objContent->ParentID)
         {
            $this->Content[$objContent->ParentID]->Content[$ID] = $objContent;
            $objContent->Parent = $this->Content[$objContent->ParentID];
         }
      }

      reset($this->Content);
      if(CONFIG_COLLECTIONS_ENABLE_USER_DEFINED_FIELDS)
      {
         static $fieldsPrep = NULL;
         if(!isset($fieldsPrep))
         {
            $query = "SELECT tblCollections_UserFields.* FROM tblCollections_UserFields JOIN tblCollections_Content ON tblCollections_Content.ID = tblCollections_UserFields.ContentID WHERE tblCollections_Content.CollectionID = ?";
            $fieldsPrep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_RESULT);
         }
         $result = $fieldsPrep->execute(array($this->ID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            $arrEADElements = $_ARCHON->getAllEADElements();

            while($row = $result->fetchRow())
            {
               if($row['Value'])
               {
                  $objUserField = New UserField($row);
                  $objUserField->EADElement = $arrEADElements[$row['EADElementID']];

                  $this->Content[$row['ContentID']]->UserFields[$row['ID']] = $objUserField;
               }
            }
         }
         $result->free();
      }

      return true;
   }



?>
