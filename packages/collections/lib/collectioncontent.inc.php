<?php

abstract class Collections_CollectionContent
{

   /**
    * Deletes CollectionContent from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $this->dbLoad();

      if($this->CollectionID)
      {
         FindingAidCache::setDirty($this->CollectionID);
      }

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_COLLECTIONCONTENT, 'tblCollections_Content'))
      {
         return false;
      }

      // If no other content is contained by the parent, set the parent's ContainsContent to 0.
      if($this->ParentID)
      {
         static $checkParentPrep = NULL;
         if(!isset($checkParentPrep))
         {
            $query = "SELECT ID FROM tblCollections_Content WHERE ParentID = ?";
            $checkParentPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $checkParentPrep->execute($this->ParentID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if(!$result->numRows())
         {
            static $noContentPrep = NULL;
            if(!isset($noContentPrep))
            {
               $query = "UPDATE tblCollections_Content SET ContainsContent = 0 WHERE ID = ?";
               $noContentPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
            }
            $affected = $noContentPrep->execute($this->ParentID);
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }
         }
         $result->free();
      }

      // Delete any references to the content
      if(!$_ARCHON->deleteRelationship('tblCollections_UserFields', 'ContentID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      // decrement the sort order of anything greater than this current sort order
      $_ARCHON->shiftContentSortOrder($this->CollectionID, $this->ParentID, $this->SortOrder + 1, NULL, DOWN);


      return true;
   }

   /**
    * Loads CollectionContent from the database
    *
    * @return boolean
    */
   public function dbLoad($LoadObjects = true)
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_Content'))
      {
         return false;
      }

      if($LoadObjects)
      {
         $this->dbLoadObjects();
      }

      return true;
   }

   public function dbLoadObjects($LoadParent = true)
   {
      global $_ARCHON;

      $this->Collection = New Collection($this->CollectionID);
      $this->Collection->dbLoad();
      $_ARCHON->MemoryCache['Objects']['Collection'][$this->Collection->ID] = $this->Collection;

      $arrLevelContainers = $_ARCHON->getAllLevelContainers();
      $this->LevelContainer = $arrLevelContainers[$this->LevelContainerID];

      if($LoadParent)
      {
         if($this->ParentID != 0)
         {
            $this->Parent = New CollectionContent($this->ParentID);
            $this->Parent->dbLoad();
            $_ARCHON->MemoryCache['Objects']['CollectionContent'][$this->Parent->ID] = $this->Parent;
         }
      }
   }

   /**
    * Loads User-Defined Fields for CollectionContent from the database
    *
    * @return boolean
    */
   public function dbLoadContent($LoadAll = false)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load CollectionContent: CollectionContent ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load CollectionContent: CollectionContent ID must be numeric.");
         return false;
      }

      $arrLevelContainers = $_ARCHON->getAllLevelContainers();

      $this->Content = array();

      if($this->ContainsContent)
      {
         static $prep = NULL;
         if(!isset($prep))
         {
            $query = "SELECT tblCollections_Content.* FROM tblCollections_Content JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID WHERE ParentID = ? ORDER BY tblCollections_Content.SortOrder";
            $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $prep->execute($this->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            $objContent = New CollectionContent($row);
            $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];

            $this->Content[$objContent->ID] = $objContent;
         }
         $result->free();

         if($LoadAll)
         {
            foreach($this->Content as $objContent)
            {
               $objContent->dbLoadContent(true);
            }
         }
//         $_ARCHON->sortCollectionContentArray(&$this->Content);
      }

      if($_ARCHON->Error)
      {
         return false;
      }
      else
      {
         return true;
      }
   }

   /**
    * Loads User-Defined Fields for CollectionContent from the database
    *
    * @return boolean
    */
   public function dbLoadUserFields()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Userfields: CollectionContent ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Userfields: CollectionContent ID must be numeric.");
         return false;
      }

      $this->UserFields = array();

      if(!CONFIG_COLLECTIONS_ENABLE_USER_DEFINED_FIELDS)
      {
         return true;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT * FROM tblCollections_UserFields WHERE ContentID = ? ORDER By Title";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No user fields found, return.
         return true;
      }

      $arrEADElements = $_ARCHON->getAllEADElements();

      while($row = $result->fetchRow())
      {
         if($row['Value'])
         {
            $objUserField = New UserField($row);
            $objUserField->EADElement = $arrEADElements[$row['EADElementID']];

            $this->UserFields[] = $objUserField;
         }
      }
      $result->free();

      return true;
   }

   /**
    * Stores CollectionContent to the database
    *
    * @param mixed $ForceEnabled
    * @return boolean
    */
   public function dbStore($ForceEnabled = NULL)
   {
      global $_ARCHON;

      if($this->CollectionID)
      {
         FindingAidCache::setDirty($this->CollectionID);
      }

      $ID = $this->ID;
      if($this->ID > 0) // Transfers, etc. may require extra work.
      {
         static $prevPrep = NULL;
         if(!isset($prevPrep))
         {
            $prevQuery = 'SELECT CollectionID, ParentID, RootContentID, LevelContainerID, SortOrder, Enabled FROM tblCollections_Content WHERE ID = ?';
            $prevPrep = $_ARCHON->mdb2->prepare($prevQuery, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $prevPrep->execute($this->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $prevCollectionID = $row['CollectionID'];
         $prevParentID = $row['ParentID'];
         $prevRootContentID = $row['RootContentID'];
         $prevLevelContainerID = $row['LevelContainerID'];
         $prevSortOrder = $row['SortOrder'];
         $prevEnabled = $row['Enabled'];
      }


      // update child flag, in case it has become corrupt
      $this->ContainsContent = ($this->hasChildren()) ? 1 : 0;

      // set the sort order automatically if it isn't set
      if(!$this->SortOrder || !is_natural($this->SortOrder))
      {
         $this->SortOrder = $_ARCHON->getNextContentSortOrder($this->CollectionID, $this->ParentID, $this->ID);
      }

      if(!$this->LevelContainerIdentifier)
      {
         $this->LevelContainerIdentifier = $this->SortOrder;
      }

      // remove information that's not stored for physical only containers
      $physicalOnly = false;
      $this->LevelContainer = new LevelContainer($this->LevelContainerID);
      if($this->LevelContainer->dbLoad() && $this->LevelContainer->PhysicalContainer && !$this->LevelContainer->IntellectualLevel)
      {
         $physicalOnly = true;
         $this->Title = '';
         $this->Date = '';
         $this->Description = '';
      }

      $enableParents = false;

      // check if the parent is enabled
      if($this->ParentID)
      {
         if(!$this->Parent || $this->Parent->ID != $this->ParentID)
         {
            $this->Parent = New CollectionContent($this->ParentID);
            $this->Parent->dbLoad(false);
         }

         // if parent is disabled, then this object should also be disabled
         if(!$this->Parent->Enabled)
         {
            if($ForceEnabled && $this->Enabled == 1)
            {
               $enableParents = true;
            }
            else
            {
               $this->Enabled = 0;
            }
         }
      }

      $enabledChange = ($this->Enabled != $prevEnabled) ? true : false;


      $checkquery = "SELECT ID FROM tblCollections_Content WHERE LevelContainerID = ? AND LevelContainerIdentifier = ? AND ParentID = ? AND CollectionID = ? AND ID != ?";
      $checktypes = array('integer', 'text', 'integer', 'integer', 'integer');
      $checkvars = array($this->LevelContainerID, $this->LevelContainerIdentifier, $this->ParentID, $this->CollectionID, $this->ID);
      $checkqueryerror = "A CollectionContent with the same ContainerTypeAndNumberAndParentAndCollection already exists in the database";
      $problemfields = array('LevelContainerID', 'LevelContainerIdentifier', 'ParentID', 'CollectionID');
      $requiredfields = array('CollectionID', 'LevelContainerID', 'LevelContainerIdentifier');

      if(!$_ARCHON->storeObject($this, MODULE_COLLECTIONCONTENT, 'tblCollections_Content', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }


      static $sortOrderCheckPrep = NULL;
      if(!isset($sortOrderCheckPrep))
      {
         $query = "SELECT ID FROM tblCollections_Content WHERE CollectionID = ? AND ParentID = ? AND SortOrder = ? AND ID != ?";
         $sortOrderCheckPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }

      $sortorderresult = $sortOrderCheckPrep->execute(array($this->CollectionID, $this->ParentID, $this->SortOrder, $this->ID));
      if(PEAR::isError($sortorderresult))
      {
         trigger_error($sortorderresult->getMessage(), E_USER_ERROR);
      }

      $sortOrderConflict = ($sortorderresult->numRows() > 0) ? true : false;

      $sortorderresult->free();



      if($ID == 0) // Just added new content.
      {

         if($sortOrderConflict)
         {
            $_ARCHON->shiftContentSortOrder($this->CollectionID, $this->ParentID, $this->SortOrder, NULL, UP, $this->ID);
         }

         if($this->ParentID)
         {
            // Set ContainsContent flag for parent
            static $newSetContainsPrep = NULL;
            if(!isset($newSetContainsPrep))
            {
               $query = "UPDATE tblCollections_Content SET ContainsContent = '1' WHERE ID = ?";
               $newSetContainsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
            }
            $affected = $newSetContainsPrep->execute($this->ParentID);
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }

            static $newParentRootIDPrep = NULL;
            if(!isset($newParentRootIDPrep))
            {
               $query = "SELECT RootContentID FROM tblCollections_Content WHERE ID = ?";
               $newParentRootIDPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
            }
            $parentresult = $newParentRootIDPrep->execute($this->ParentID);
            if(PEAR::isError($parentresult))
            {
               trigger_error($parentresult->getMessage(), E_USER_ERROR);
            }

            $parentrow = $parentresult->fetchRow();
            $parentresult->free();

            if(is_natural($parentrow['RootContentID']))
            {
               $this->RootContentID = $parentrow['RootContentID'];
               static $newSetRootIDPrep = NULL;
               if(!isset($newSetRootIDPrep))
               {
                  $query = "UPDATE tblCollections_Content SET RootContentID = ? WHERE ID = ?";
                  $newSetRootIDPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
               }
               $affected = $newSetRootIDPrep->execute(array($this->RootContentID, $this->ID));
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }
            }
         }
         else // Content is its own root.
         {
            $this->RootContentID = $this->ID;
            static $newSetOwnRootPrep = NULL;
            if(!isset($newSetOwnRootPrep))
            {
               $query = "UPDATE tblCollections_Content SET RootContentID = ? WHERE ID = ?";
               $newSetOwnRootPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
            }
            $affected = $newSetOwnRootPrep->execute(array($this->RootContentID, $this->ID));
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }
         }
      }
      else
      {
         // no transfer has taken place
         if($prevCollectionID == $this->CollectionID && $prevParentID == $this->ParentID && $prevLevelContainerID == $this->LevelContainerID)
         {
            if($sortOrderConflict)
            {
               if($this->SortOrder > $prevSortOrder)
               {
                  // move down everything = old sort order + 1 to new sort order
                  $_ARCHON->shiftContentSortOrder($this->CollectionID, $this->ParentID, $prevSortOrder + 1, $this->SortOrder, DOWN, $this->ID);
               }
               elseif($this->SortOrder < $prevSortOrder)
               {
                  //move up everything = new sort order - old sort order -1
                  $_ARCHON->shiftContentSortOrder($this->CollectionID, $this->ParentID, $this->SortOrder, $prevSortOrder - 1, UP, $this->ID);
               }
               else // sort order could have been changed by another reordering?
               {
                  //try to reload sort order, and if it's still in conflict, move to end
                  static $reloadSortOrderPrep = NULL;
                  if(!isset($reloadSortOrderPrep))
                  {
                     $reloadSortOrderQuery = 'SELECT SortOrder FROM tblCollections_Content WHERE ID = ?';
                     $reloadSortOrderPrep = $_ARCHON->mdb2->prepare($reloadSortOrderQuery, 'integer', MDB2_PREPARE_RESULT);
                  }
                  $reloadSortOrderResult = $reloadSortOrderPrep->execute($this->ID);
                  if(PEAR::isError($reloadSortOrderResult))
                  {
                     trigger_error($reloadSortOrderResult->getMessage(), E_USER_ERROR);
                  }

                  $reloadSortOrderRow = $reloadSortOrderResult->fetchRow();
                  $reloadSortOrderResult->free();

                  $this->SortOrder = ($reloadSortOrderRow['SortOrder']) ? $reloadSortOrderRow['SortOrder'] : $this->SortOrder;

                  //check for conflict again
                  $sortorderresult = $sortOrderCheckPrep->execute(array($this->CollectionID, $this->ParentID, $this->SortOrder, $this->ID));
                  if(PEAR::isError($sortorderresult))
                  {
                     trigger_error($sortorderresult->getMessage(), E_USER_ERROR);
                  }

                  $sortOrderConflict = ($sortorderresult->numRows() > 0) ? true : false;

                  $sortorderresult->free();

                  if($sortOrderConflict)
                  {
                     $this->SortOrder = $_ARCHON->getNextContentSortOrder($this->CollectionID, $this->ParentID, $this->ID);
                     $this->dbUpdateSortOrder();
                  }
                  //else, things were already taken care of by a separate reordering
                  // do nothing.
               }
            }
            else
            {
               if($prevSortOrder != $this->SortOrder)
               {
                  //close gap where content used to reside
                  $_ARCHON->shiftContentSortOrder($this->CollectionID, $this->ParentID, $prevSortOrder + 1, NULL, DOWN, $this->ID);
               }
               // else no conflict --> do nothing -- or should we check anyway?
               //make sure the new sort order is not higher than it should be
               $highestSortOrder = $_ARCHON->getNextContentSortOrder($this->CollectionID, $this->ParentID, $this->ID);
               if($this->SortOrder > $highestSortOrder)
               {
                  $this->SortOrder = $highestSortOrder;
                  $this->dbUpdateSortOrder();
               }
            }
         }
         else
         {
            //close gap where content used to reside
            $_ARCHON->shiftContentSortOrder($prevCollectionID, $prevParentID, $prevSortOrder + 1, NULL, DOWN, $this->ID);


            // put at the end of new sorting order
            $this->SortOrder = $_ARCHON->getNextContentSortOrder($this->CollectionID, $this->ParentID, $this->ID);
            $this->dbUpdateSortOrder();
         }




         // Check if we have children
         static $oldParentCheckPrep = NULL;
         if(!isset($oldParentCheckPrep))
         {
            $query = "SELECT ID FROM tblCollections_Content WHERE ParentID = ?";
            $oldParentCheckPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $oldParentCheckPrep->execute($this->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         static $oldSetContainsPrep = NULL;
         if(!isset($oldSetContainsPrep))
         {
            $query = "UPDATE tblCollections_Content SET ContainsContent = ? WHERE ID = ?";
            $oldSetContainsPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
            if(PEAR::isError($oldSetContainsPrep))
            {
               trigger_error($oldSetContainsPrep->getMessage(), E_USER_ERROR);
            }
         }

         if($result->numRows() > 0)
         {
            $affected = $oldSetContainsPrep->execute(array(1, $this->ID));
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }
         }
         else
         {
            $affected = $oldSetContainsPrep->execute(array(0, $this->ID));
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }
         }
         $result->free();



         if($this->ParentID)
         {
            static $oldParentRootIDPrep = NULL;
            if(!isset($oldParentRootIDPrep))
            {
               $query = "SELECT RootContentID FROM tblCollections_Content WHERE ID = ?";
               $oldParentRootIDPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
            }
            $parentresult = $oldParentRootIDPrep->execute($this->ParentID);
            if(PEAR::isError($parentresult))
            {
               trigger_error($parentresult->getMessage(), E_USER_ERROR);
            }

            $parentrow = $parentresult->fetchRow();
            $parentresult->free();

            if(is_natural($parentrow['RootContentID']))
            {
               $this->RootContentID = $parentrow['RootContentID'];
               static $oldSetRootIDPrep = NULL;
               if(!isset($oldSetRootIDPrep))
               {
                  $query = "UPDATE tblCollections_Content SET RootContentID = ? WHERE ID = ?";
                  $oldSetRootIDPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
               }
               $affected = $oldSetRootIDPrep->execute(array($this->RootContentID, $this->ID));
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }
            }
         }
         else // Content is its own root.
         {
            $this->RootContentID = $this->ID;
            static $oldSetOwnRootPrep = NULL;
            if(!isset($oldSetOwnRootPrep))
            {
               $query = "UPDATE tblCollections_Content SET RootContentID = ? WHERE ID = ?";
               $oldSetOwnRootPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
            }
            $affected = $oldSetOwnRootPrep->execute(array($this->RootContentID, $this->ID));
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }
         }

         // If ParentID has changed, then we know we transferred
         if($prevParentID != $this->ParentID)
         {
            // Set ContainsContent flag for parent
            static $oldSetContainsPrep = NULL;
            if(!isset($oldSetContainsPrep))
            {
               $query = "UPDATE tblCollections_Content SET ContainsContent = ? WHERE ID = ?";
               $oldContainsPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
            }
            $affected = $oldSetContainsPrep->execute(array(1, $this->ParentID));
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }

            // Check to see if old parent still contains content
            // if not, unset ContainsContent flag
            static $oldCheckContainsPrep = NULL;
            if(!isset($oldCheckContainsPrep))
            {
               $query = "SELECT ID FROM tblCollections_Content WHERE ParentID = ?";
               $oldCheckContainsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
            }
            $result = $oldCheckContainsPrep->execute($prevParentID);
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            if($result->numRows() == 0)
            {
               $affected = $oldSetContainsPrep->execute(array(0, $prevParentID));
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }
            }
            $result->free();
         }

         // If RootContentID or CollectionID changed, we trasferred and need to fix child content
         if($prevRootContentID != $this->RootContentID || $prevCollectionID != $this->CollectionID)
         {
            @set_time_limit(90);

            // Permissions have been checked, so just use a stack to quickly update what's necessary
            static $childrenIDsPrep = NULL;
            if(!isset($childrenIDsPrep))
            {
               $query = "SELECT ID FROM tblCollections_Content WHERE ParentID = ?";
               $childrenIDsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
            }

            static $updateChildrenRootsPrep = NULL;
            if(!isset($updateChildrenRootsPrep))
            {
               $query = 'UPDATE tblCollections_Content SET CollectionID = ?, RootContentID = ? WHERE ID = ?';
               $updateChildrenRootsPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
            }

            $arrParentIDs = array($this->ID);
            while($recParentID = array_pop($arrParentIDs))
            {
               $childrenresult = $childrenIDsPrep->execute($recParentID);
               if(PEAR::isError($result))
               {
                  trigger_error($result->getMessage(), E_USER_ERROR);
               }

               while($childrow = $childrenresult->fetchRow())
               {
                  $affected = $updateChildrenRootsPrep->execute(array($this->CollectionID, $this->RootContentID, $childrow['ID']));
                  if(PEAR::isError($affected))
                  {
                     trigger_error($affected->getMessage(), E_USER_ERROR);
                  }

                  array_push($arrParentIDs, $childrow['ID']);
               }
               $childrenresult->free();
            }
         }
      }

      if(!$physicalOnly)
      {
         if(!empty($this->UserFields))
         {

            foreach($this->UserFields as $objUserField)
            {
               if(is_object($objUserField)) // for some reason they get passed as an array?
               {
                  $objUserField->ContentID = $this->ID;
                  $objUserField->Content = $this;

                  $objUserField->dbStore();
               }
            }
         }
      }
      else
      {
         //remove userfields if they exists
         $_ARCHON->deleteRelationship('tblCollections_UserFields', 'ContentID', $this->ID, MANY_TO_MANY);
      }

      // update parent enabled settings
      if($enableParents)
      {
         $arrParentContent = $_ARCHON->traverseCollectionContent($this->ID);

         static $setEnabledPrep = NULL;
         if(!isset($setEnabledPrep))
         {
            $query = "UPDATE tblCollections_Content SET Enabled = ? WHERE ID = ?";
            $setEnabledPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
         }

         foreach($arrParentContent as $ID => $objContent)
         {
            if($objContent->Enabled != 1)
            {
               $objContent->Enabled = 1;
               $affected = $setEnabledPrep->execute(array($objContent->Enabled, $objContent->ID));
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }
               unset($arrContent[$ID]);
            }
            else
            {
               unset($arrContent[$ID]); // make some attempt to conserve memory
            }
         }
      }

      // make sure the enabled flag gets propagated amongst children content
      if($enabledChange)
      {
         $this->dbPropagateEnabledSetting();
      }


      return true;
   }

   public function dbPropagateEnabledSetting()
   {
      global $_ARCHON;

      if(!$this->verifyStorePermissions())
      {
         $_ARCHON->declareError("Could not update enabled flag: Permission Denied.");
         return false;
      }

      if($this->ContainsContent)
      {

         $arrContent = $_ARCHON->getChildCollectionContent($this->ID, $this->CollectionID);

         static $setEnabledPrep = NULL;
         if(!isset($setEnabledPrep))
         {
            $query = "UPDATE tblCollections_Content SET Enabled = ? WHERE ID = ?";
            $setEnabledPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
         }

         foreach($arrContent as $ID => $objContent)
         {
            if($objContent->Enabled != $this->Enabled)
            {
               $objContent->Enabled = $this->Enabled;
               $affected = $setEnabledPrep->execute(array($objContent->Enabled, $objContent->ID));
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }
               $objContent->dbPropagateEnabledSetting();
               unset($arrContent[$ID]);
            }
            else
            {
               unset($arrContent[$ID]); // make some attempt to conserve memory
            }
         }
      }

      return true;
   }

   public function dbUpdateSortOrder()
   {
      global $_ARCHON;

      if(!$this->verifyStorePermissions())
      {
         $_ARCHON->declareError("Could not update sort order: Permission Denied.");
         return false;
      }

      static $setSortOrderPrep = NULL;
      if(!isset($setSortOrderPrep))
      {
         $query = "UPDATE tblCollections_Content SET SortOrder = ? WHERE ID = ?";
         $setSortOrderPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $setSortOrderPrep->execute(array($this->SortOrder, $this->ID));
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
   }

   public function dbUpdateRelatedSubjects($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONCONTENT, 'Subject', 'tblCollections_CollectionContentSubjectIndex', 'tblSubjects_Subjects', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   public function dbUpdateRelatedCreators($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_COLLECTIONCONTENT, 'Creator', 'tblCollections_CollectionContentCreatorIndex', 'tblCreators_Creators', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   public function dbLoadCreators()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Creators: CollectionContent ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Creators: CollectionContent ID must be numeric.");
         return false;
      }

      $this->Creators = array();

      if(!CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_CREATORS)
      {
         return true;
      }

      $query = "SELECT tblCreators_Creators.* FROM tblCreators_Creators JOIN tblCollections_CollectionContentCreatorIndex ON tblCreators_Creators.ID = tblCollections_CollectionContentCreatorIndex.CreatorID WHERE tblCollections_CollectionContentCreatorIndex.CollectionContentID = ? ORDER BY tblCreators_Creators.Name";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No creators found, return.
         $result->free();
         $prep->free();
         return true;
      }

      $arrCreatorTypes = $_ARCHON->getAllCreatorTypes();

      while($row = $result->fetchRow())
      {
         $objCreator = New Creator($row);
         $objCreator->CreatorType = $arrCreatorTypes[$objCreator->CreatorTypeID];

         $this->Creators[$row['ID']] = $objCreator;
      }
      $result->free();
      $prep->free();

      return true;
   }

   public function dbLoadSubjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Subjects: CollectionContent ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Subjects: CollectionContent ID must be numeric.");
         return false;
      }

      $this->Subjects = array();

      if(!CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_SUBJECTS)
      {
         return true;
      }

      $query = "SELECT tblSubjects_Subjects.* FROM tblSubjects_Subjects JOIN tblCollections_CollectionContentSubjectIndex ON tblSubjects_Subjects.ID = tblCollections_CollectionContentSubjectIndex.SubjectID WHERE tblCollections_CollectionContentSubjectIndex.CollectionContentID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No subjects found, return.
         $result->free();
         $prep->free();
         return true;
      }

      $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();

      while($row = $result->fetchRow())
      {
         // We can't add the subjects to the final array just yet
         // because the subjects need to be sorted based upon how
         // they will end up displaying (parent subjects will
         // be concatenated before child subjects).
         $objSubject = New Subject($row);
         $objSubject->SubjectType = $arrSubjectTypes[$objSubject->SubjectTypeID];

         $arrSorter[$objSubject->toString(LINK_NONE, true)] = $objSubject;

         // In case parents are used multiple times
         $objTransSubject = $objSubject;
         while($objTransSubject)
         {
            $_ARCHON->MemoryCache['Objects']['Subject'][$objTransSubject->ID] = $objTransSubject;
            $objTransSubject = $objTransSubject->Parent;
         }
      }
      $result->free();
      $prep->free();

      natcaseksort($arrSorter);

      if(!empty($arrSorter))
      {
         foreach($arrSorter as $objSubject)
         {
            $this->Subjects[$objSubject->ID] = $objSubject;
         }
      }

      return true;
   }

   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      // Check permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, DELETE))
      {
         return false;
      }

      if(!$this->ID)
      {
         return false;
      }

      if($this->ID && !is_natural($this->ID))
      {
         return false;
      }

      // If CollectionID is not present, try to load.
      // If the content has been somehow orphaned, we still want to go
      // ahead with the deletion as long as the user is not limited to a specific
      // repository, which is checked later.
      if(!$this->CollectionID)
      {
         $this->dbLoad();
      }

      if(!$this->Collection)
      {
         $this->Collection = New Collection($this->CollectionID);
         $this->Collection->dbLoad();
      }

      // Make sure user isn't dealing with a content from another repository if they're limited
//      if(($this->Collection->RepositoryID != $_ARCHON->Security->Session->User->RepositoryID) && $_ARCHON->Security->Session->User->RepositoryLimit)
      if(!$this->Collection->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete CollectionContent: CollectionContent may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   public function verifyStorePermissions()
   {
      global $_ARCHON;

      // Check permissions
      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE)))
      {
         return false;
      }


      // Make sure all required data is present.
      if(!$this->CollectionID)
      {
         return false;
      }

      if(!$this->Collection || $this->Collection->ID != $this->CollectionID)
      {
         $this->Collection = New Collection($this->CollectionID);
         $this->Collection->dbLoad();
      }

      // Make sure user isn't dealing with a content from another repository if they're limited
      if(!$this->Collection->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not store CollectionContent: CollectionContent may only be altered for the primary repository.");
         return false;
      }

      // If we are transferring from a different collection, make sure we
      // have the proper permissions to do so.
      if($this->ID > 0)
      {
         static $fromPrep = NULL;
         if(!isset($fromPrep))
         {
            $query = 'SELECT CollectionID FROM tblCollections_Content WHERE ID = ?';
            $fromPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $fromPrep->execute($this->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['CollectionID'])
         {
            return false;
         }


         if($row['CollectionID'] != $this->CollectionID)
         {
            $objTransferCollection = New Collection($row['CollectionID']);
            $objTransferCollection->dbLoad();

            if(!$objTransferCollection->verifyRepositoryPermissions())
            {
               $this->CollectionID = $row['CollectionID'];

               $_ARCHON->declareError("Could not transfer CollectionContent: CollectionContent may only be altered for the primary repository.");
               return false;
            }
         }
      }

      return true;
   }

   /**
    * Checks to see if we have child content
    *
    */
   public function hasChildren()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT ID FROM tblCollections_Content WHERE ParentID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $_ARCHON->mdb2->setLimit(1);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $hasChildren = false;

      if($result->numRows())
      {
         $hasChildren = true;
      }

      $result->free();

      return $hasChildren;
   }

   /**
    * Checks if content is pubicly enabled
    *
    * @return boolean
    */
   public function enabled()
   {
      global $_ARCHON;

      if(!$this->CollectionID)
      {
         $this->dbLoad();
      }

      $readPermissions = true;


      if(!$this->Collection || $this->Collection->ID != $this->CollectionID)
      {
         $this->Collection = New Collection($this->CollectionID);
         $this->Collection->dbLoad();
         // we should only load the collection once when iterating over several pieces of content, we will cache it
         $_ARCHON->MemoryCache['Objects']['Collection'][$this->Collection->ID] = $this->Collection;
      }

      if(!$this->Collection->enabled())
      {
         $readPermissions = false;
      }
      elseif(!$this->Enabled)
      {
         $readPermissions = false;


         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, READ)
                 || ($_ARCHON->Security->userHasAdministrativeAccess() && !CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS)
                 || (CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS && $_ARCHON->Security->verifyRepositoryPermissions($objCollection->RepositoryID)))
         {
            $readPermissions = true;
         }
      }

      return $readPermissions;
   }

   /**
    * Generates a formatted string of the CollectionContent object
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateLevelContainer[optional]
    * @param boolean $ConcatinateLevelContainerIdentifier[optional]
    * @param boolean $ConcatinateParentLevelContainer[optional]
    * @param boolean $ConcatinateParentLevelContainerIdentifier[optional]
    * @param string $Delimiter[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE, $ConcatinateLevelContainer = true, $ConcatinateLevelContainerIdentifier = true, $ConcatinateParentLevelContainer = false, $ConcatinateParentLevelContainerIdentifier = false, $Delimiter = ", ")
   {
      global $_ARCHON;

      if(!$this->CollectionID) // this will also load the enabled flag to be sure our permissions are correct
      {
         $this->dbLoad();
      }

//      if(!$this->Enabled)
//      {
//         $readPermissions = false;
//
//         if(!$this->Collection || $this->Collection->ID != $this->CollectionID)
//         {
//            $this->Collection = New Collection($this->CollectionID);
//            $this->Collection->dbLoad();
//            // we should only load the collection once when iterating over several pieces of content, we will cache it
//            $_ARCHON->MemoryCache['Objects']['Collection'][$this->Collection->ID] = $this->Collection;
//         }
//
//         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, READ)
//                 || !CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS
//                 || $_ARCHON->Security->verifyRepositoryPermissions($this->Collection->RepositoryID))
//         {
//            $readPermissions = true;
//         }
//      }
//      if($this->Enabled || $readPermissions)
      if($this->enabled())
      {
         // If data is not set, load content.
         if(($ConcatinateParentLevelContainerIdentifier || $ConcatinateParentLevelContainer) && $this->ParentID && !$this->Parent)
         {
            $this->dbLoad();
         }

         if($this->LevelContainerID && !$this->LevelContainer)
         {
            $this->LevelContainer = New LevelContainer($this->LevelContainerID);
            $this->LevelContainer->dbLoad();
         }

         if($_ARCHON->QueryStringURL)
         {
            $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         }

         $objTmp = $this;

         while($objTmp)
         {
            if((($objTmp->ID == $this->ID) && $ConcatinateLevelContainer)
                    || (($objTmp->ID != $this->ID) && $ConcatinateParentLevelContainer))
            {
               if($objTmp->LevelContainer->LevelContainer)
               {
                  $encoding_substring = $objTmp->LevelContainer->getString('LevelContainer');
               }

               if(((($objTmp->ID == $this->ID) && $ConcatinateLevelContainerIdentifier)
                       || (($objTmp->ID != $this->ID) && $ConcatinateParentLevelContainerIdentifier))
                       && $objTmp->LevelContainerIdentifier)
               {
                  //$encoding_substring .= ' ' . formatNumber($objTmp->LevelContainerIdentifier);
                  $encoding_substring .= ' ' . $objTmp->getString('LevelContainerIdentifier');
               }
            }
            elseif(((($objTmp->ID == $this->ID) && $ConcatinateLevelContainerIdentifier)
                    || (($objTmp->ID != $this->ID) && $ConcatinateParentLevelContainerIdentifier))
                    && $objTmp->LevelContainerIdentifier)
            {
               //$encoding_substring = formatNumber($objTmp->LevelContainerIdentifier);
               $encoding_substring = $objTmp->getString('LevelContainerIdentifier');
            }

            if(($objTmp->Title || $objTmp->Date) && $encoding_substring)
            {
               $encoding_substring .= ': ';
            }

            if($objTmp->Title)
            {
               $encoding_substring .= $objTmp->getString('Title');
            }

            if($objTmp->Date)
            {
               if($objTmp->Title)
               {
                  $encoding_substring .= ', ';
               }

               $encoding_substring .= $objTmp->getString('Date');
            }

            $String = ($MakeIntoLink == LINK_EACH && $this->ID) ? "<a href='?p=collections/findingaid&amp;id={$this->CollectionID}&amp;rootcontentid={$this->RootContentID}{$q}#id{$this->ID}'>$encoding_substring</a>" . $String : $encoding_substring . $String;
            $encoding_substring = '';

            if($objTmp->ParentID && ($ConcatinateParentLevelContainerIdentifier || $ConcatinateParentLevelContainer))
            {
               $String = $Delimiter . $String;

               $objTmp = $objTmp->Parent;
            }
            else
            {
               $objTmp = NULL;
            }
         }

         if($MakeIntoLink == LINK_TOTAL && $this->ID)
         {
            $String = "<a href='?p=collections/findingaid&amp;id={$this->CollectionID}{$q}'>$String</a>";
         }
      }
      else
      {

         $objInfoRestrictedPhrase = Phrase::getPhrase('informationrestricted', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $String = $objInfoRestrictedPhrase ? $objInfoRestrictedPhrase->getPhraseValue(ENCODE_HTML) : '[information restricted]';
      }

      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $this->ID)
      {


         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE))
         {
            $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
            $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

            $String .= "<a href='?p=admin/collections/collectioncontent&amp;collectionid={$this->CollectionID}&amp;parentid={$this->ParentID}&amp;id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
         }
         elseif(!$_ARCHON->Security->userHasAdministrativeAccess() && $this->enabled() && ($this->Collection->Repository->ResearchFunctionality & RESEARCH_COLLECTIONS))
         {
            $objRemovePhrase = Phrase::getPhrase('tostring_remove', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strRemove = $objRemovePhrase ? $objRemovePhrase->getPhraseValue(ENCODE_HTML) : 'Remove from your cart.';
            $objAddToPhrase = Phrase::getPhrase('tostring_addto', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strAddTo = $objAddToPhrase ? $objAddToPhrase->getPhraseValue(ENCODE_HTML) : 'Add to your cart.';

            $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();

            if($arrCart->Collections[$this->CollectionID]->Content[$this->ID])
            {
               $String .= "<a id='ccid".$this->ID."' class='research_delete' onclick='triggerResearchCartEvent(this, {collectionid:{$this->CollectionID},collectioncontentid:{$this->ID}}); return false;' href='#'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/removefromcart.gif' title='$strRemove' alt='$strRemove'/></a>";
            }
            else
            {
               $String .= "<a id='ccid".$this->ID."' class='research_add' onclick='triggerResearchCartEvent(this, {collectionid:{$this->CollectionID},collectioncontentid:{$this->ID}}); return false;' href='#'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/addtocart.gif' title='$strAddTo' alt='$strAddTo'/></a>";
            }
         }
      }

      return $String;
   }

   /** @var integer */
   public $ID = 0;
   /** @var integer */
   public $CollectionID = 0;
   /** @var integer */
   public $LevelContainerID = 0;
   /** @var string */
   public $LevelContainerIdentifier = '';
   /** @var string */
   public $Title = '';
   /** @var string */
   public $PrivateTitle = '';
   /** @var string */
   public $Date = '';
   /** @var string */
   public $Description = '';
   /** @var integer */
   public $RootContentID = 0;
   /** @var integer */
   public $ParentID = 0;
   /** @var integer */
   public $ContainsContent = 0;
   /**
    * @var integer
    */
   public $Enabled = 1;
   /**
    * @var Collection
    */
   public $Collection = NULL;
   /**
    * @var CollectionContent[]
    */
   public $Content = array();
   /**
    * @var CollectionContent
    */
   public $Parent = NULL;
   /**
    * @var LevelContainer
    */
   public $LevelContainer = NULL;
   /**
    * @var SortOrder
    */
   public $SortOrder = 0;
   /**
    * @var UserField[]
    */
   public $UserFields = array();
   /**
    * @var Subject[]
    */
   public $Subjects = array();
   /**
    * @var Creators[]
    */
   public $Creators = array();
   public $ToStringFields = array('ID', 'Title', 'LevelContainerID', 'Date');
}

$_ARCHON->mixClasses('CollectionContent', 'Collections_CollectionContent');
?>
