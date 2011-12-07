<?php

abstract class Collections_Subject
{

   public function dirtyRelatedFindingAidCache()
   {
      global $_ARCHON;

      $ID = $this->ID;
      $arrSubjects = $_ARCHON->getChildSubjects($ID);

      static $collIDPrep = NULL;

      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT CollectionID FROM tblCollections_CollectionSubjectIndex WHERE SubjectID = ?', 'integer', MDB2_PREPARE_RESULT);

      $result = $collIDPrep->execute($ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows())
      {
         while($row = $result->fetchRow())
         {
            $collIDs[] = $row['CollectionID'];
         }
      }


      foreach($arrSubjects as $subject)
      {
         $result = $collIDPrep->execute($subject->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            while($row = $result->fetchRow())
            {
               $collIDs[] = $row['CollectionID'];
            }
         }
      }




      if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_SUBJECTS)
      {
         static $contentIDPrep = NULL;
         $contentIDPrep = $contentIDPrep ? $contentIDPrep : $_ARCHON->mdb2->prepare('SELECT tblCollections_Content.CollectionID FROM tblCollections_CollectionContentSubjectIndex JOIN tblCollections_Content ON tblCollections_CollectionContentSubjectIndex.CollectionContentID = tblCollections_Content.ID WHERE SubjectID = ?', 'integer', MDB2_PREPARE_RESULT);
         $result = $contentIDPrep->execute($ID);

         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            while($row = $result->fetchRow())
            {
               $collIDs[] = $row['CollectionID'];
            }
         }


         foreach($arrSubjects as $subject)
         {
            $result = $contentIDPrep->execute($subject->ID);
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            if($result->numRows())
            {
               while($row = $result->fetchRow())
               {
                  $collIDs[] = $row['CollectionID'];
               }
            }
         }
      }

      if(isset($collIDs))
      {
         FindingAidCache::setDirty($collIDs);
      }
   }

   /**
    * Deletes Subject from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      $this->dirtyRelatedFindingAidCache();

      if(!$this->callOverridden())
      {
         return false;
      }


      // Delete any references to the subject
      static $prep = NULL;
      $prep = $prep ? $prep : $_ARCHON->mdb2->prepare('DELETE FROM tblCollections_CollectionSubjectIndex WHERE SubjectID = ?', 'integer', MDB2_PREPARE_MANIP);
      $affected = $prep->execute($ID);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if(!$_ARCHON->deleteRelationship('tblCollections_BookSubjectIndex', 'SubjectID', $ID, MANY_TO_MANY))
      {
         return false;
      }


      if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_SUBJECTS)
      {
         if(!$_ARCHON->deleteRelationship('tblCollections_CollectionContentSubjectIndex', 'SubjectID', $ID, MANY_TO_MANY))
         {
            return false;
         }
      }

      return true;
   }

   public function dbStore()
   {
      global $_ARCHON;

      $ID = $this->ID;
      if(!$this->callOverridden())
      {
         return false;
      }

      $this->dirtyRelatedFindingAidCache();

      return true;
   }

   /**
    * Loads Collections from the database
    *
    * This function loads collections that fall under this subject
    *
    * @return boolean
    */
   public function dbLoadCollections()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Collections: Subject ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Collections: Subject ID must be numeric.");
         return false;
      }

      $this->Collections = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_CollectionSubjectIndex ON tblCollections_Collections.ID = tblCollections_CollectionSubjectIndex.CollectionID WHERE tblCollections_CollectionSubjectIndex.SubjectID = ? ORDER BY tblCollections_Collections.SortTitle";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Collections[$row['ID']] = New Collection($row);
      }
      $result->free();

      return true;
   }

   /**
    * @var Collection[]
    */
   public $Collections = array();

}

$_ARCHON->setMixinMethodParameters('Subject', 'Collections_Subject', 'dbDelete', NULL, MIX_OVERRIDE);
$_ARCHON->setMixinMethodParameters('Subject', 'Collections_Subject', 'dbStore', NULL, MIX_OVERRIDE);

$_ARCHON->mixClasses('Subject', 'Collections_Subject');
?>