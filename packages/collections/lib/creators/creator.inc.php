<?php

abstract class Collections_Creator
{

   /**
    * Deletes Creator from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      //First delete the creator from the creators table by calling creator's package dbDelete
      if(!$this->callOverridden())
      {
         return false;
      }

      // Delete any references to the creator
      static $indexPrep = NULL;
      $indexPrep = $indexPrep ? $indexPrep : $_ARCHON->mdb2->prepare('DELETE FROM tblCollections_CollectionCreatorIndex WHERE CreatorID = ?', 'integer', MDB2_PREPARE_MANIP);
      $affected = $indexPrep->execute($ID);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $classificationsPrep = NULL;
      $classificationsPrep = $classificationsPrep ? $classificationsPrep : $_ARCHON->mdb2->prepare('UPDATE tblCollections_Classifications SET CreatorID = 0 WHERE CreatorID = ?', 'integer', MDB2_PREPARE_MANIP);
      $affected = $classificationsPrep->execute($ID);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }


      // Delete any references to the creator in the book index table.
      static $indexPrep = NULL;
      $indexPrep = $indexPrep ? $indexPrep : $_ARCHON->mdb2->prepare('DELETE FROM tblCollections_BookCreatorIndex WHERE CreatorID = ?', 'integer', MDB2_PREPARE_MANIP);
      $affected = $indexPrep->execute($ID);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }




      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT CollectionID FROM tblCollections_CollectionCreatorIndex WHERE CreatorID = ?', 'integer', MDB2_PREPARE_RESULT);
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

      if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_CREATORS)
      {
         static $contentIDPrep = NULL;
         $contentIDPrep = $contentIDPrep ? $contentIDPrep : $_ARCHON->mdb2->prepare('SELECT tblCollections_Content.CollectionID FROM tblCollections_CollectionContentCreatorIndex JOIN tblCollections_Content ON tblCollections_CollectionContentCreatorIndex.CollectionContentID = tblCollections_Content.ID WHERE CreatorID = ?', 'integer', MDB2_PREPARE_RESULT);
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

         if(!$_ARCHON->deleteRelationship('tblCollections_CollectionContentCreatorIndex', 'CreatorID', $ID, MANY_TO_MANY))
         {
            return false;
         }
      }

      if(isset($collIDs))
      {
         FindingAidCache::setDirty($collIDs);
      }



      return true;
   }

   public function dbStore()
   {
      global $_ARCHON;

      $ID = $this->ID;

      //First delete the creator from the creators table by calling creator's package dbDelete
      if(!$this->callOverridden())
      {
         return false;
      }

      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT CollectionID FROM tblCollections_CollectionCreatorIndex WHERE CreatorID = ?', 'integer', MDB2_PREPARE_RESULT);
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

      if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_CREATORS)
      {
         static $contentIDPrep = NULL;
         $contentIDPrep = $contentIDPrep ? $contentIDPrep : $_ARCHON->mdb2->prepare('SELECT tblCollections_Content.CollectionID FROM tblCollections_CollectionContentCreatorIndex JOIN tblCollections_Content ON tblCollections_CollectionContentCreatorIndex.CollectionContentID = tblCollections_Content.ID WHERE CreatorID = ?', 'integer', MDB2_PREPARE_RESULT);
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
      }

      if(isset($collIDs))
      {
         FindingAidCache::setDirty($collIDs);
      }


      return true;
   }

   /**
    * Loads Collections from the database
    *
    * This function loads collections that fall under this creator
    *
    * @return boolean
    */
   public function dbLoadCollections()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Collections: Creator ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Collections: Creator ID must be numeric.");
         return false;
      }

      $this->Collections = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_CollectionCreatorIndex ON tblCollections_Collections.ID = tblCollections_CollectionCreatorIndex.CollectionID WHERE tblCollections_CollectionCreatorIndex.CreatorID = ? ORDER BY tblCollections_Collections.SortTitle";
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
    * Loads Books from the database
    *
    * This function loads books that fall under this creator
    *
    * @return boolean
    */
   public function dbLoadBooks()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Book: Creator ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Book: Creator ID must be numeric.");
         return false;
      }

      $this->Books = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Books.* FROM tblCollections_Books JOIN tblCollections_BookCreatorIndex ON tblCollections_Books.ID = tblCollections_BookCreatorIndex.BookID WHERE tblCollections_BookCreatorIndex.CreatorID = ? ORDER BY tblCollections_Books.Title";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Books[$row['ID']] = New Book($row);
      }
      $result->free();

      return true;
   }

   /**
    * @var Collection[]
    */
   public $Collections = array();
   /**
    * @var Book[]
    */
   public $Books = array();
}

$_ARCHON->setMixinMethodParameters('Creator', 'Collections_Creator', 'dbDelete', NULL, MIX_OVERRIDE);
$_ARCHON->setMixinMethodParameters('Creator', 'Collections_Creator', 'dbStore', NULL, MIX_OVERRIDE);


$_ARCHON->mixClasses('Creator', 'Collections_Creator');
?>