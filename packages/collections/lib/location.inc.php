<?php

abstract class Collections_Location
{

   /**
    * Deletes Location from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      $query = "SELECT ID FROM tblCollections_CollectionLocationIndex WHERE LocationID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_RESULT);
      $result = $prep->execute(array($ID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      if($result->numRows() > 0)
      {
         $_ARCHON->declareError("Could not delete Location. LocationEntries exist for one or many Collections.");
         return false;
      }


      if(defined('PACKAGE_ACCESSIONS'))
      {
         $query = "SELECT ID FROM tblAccessions_AccessionLocationIndex WHERE LocationID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_RESULT);
         $result = $prep->execute(array($ID));
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         if($result->numRows() > 0)
         {
            $_ARCHON->declareError("Could not delete Location. LocationEntries exist for one or many Accessions.");
            return false;
         }
      }

      if(!$_ARCHON->deleteObject($this, MODULE_LOCATIONS, 'tblCollections_Locations'))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCollections_LocationRepositoryIndex', 'LocationID', $ID, MANY_TO_MANY))
      {
         return false;
      }

//      if(!$_ARCHON->deleteRelationship('tblCollections_CollectionLocationIndex', 'LocationID', $ID, MANY_TO_MANY))
//      {
//         return false;
//      }
//
//      if(defined('PACKAGE_ACCESSIONS'))
//      {
//         if(!$_ARCHON->deleteRelationship('tblAccessions_AccessionLocationIndex', 'LocationID', $ID, MANY_TO_MANY))
//         {
//            return false;
//         }
//      }

      return true;
   }

   /**
    * Loads Location from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_Locations'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads Collections from the database
    *
    * This function loads collections that fall under this location
    *
    * @return boolean
    */
   public function dbLoadCollections()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Collections for Location: Location ID not defined.");
         return false;
      }

      $this->Collections = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_CollectionLocationIndex ON tblCollections_Collections.ID = tblCollections_CollectionLocationIndex.CollectionID WHERE tblCollections_CollectionLocationIndex.LocationID = ? ORDER BY tblCollections_Collections.SortTitle";
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
    * Loads Repositories for Location from the database
    *
    * @return boolean
    */
   public function dbLoadRepositories()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Repositories: Location ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Repositories: Location ID must be numeric.");
         return false;
      }

      $this->Repositories = array();

      $query = "SELECT tblCore_Repositories.* FROM tblCore_Repositories JOIN tblCollections_LocationRepositoryIndex ON tblCore_Repositories.ID = tblCollections_LocationRepositoryIndex.RepositoryID WHERE tblCollections_LocationRepositoryIndex.LocationID = ? ORDER BY tblCore_Repositories.Name";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);

      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         return true;
      }

      while($row = $result->fetchRow())
      {
         $this->Repositories[$row['ID']] = New Repository($row);
         $this->RelatedRepositoryIDs[] = $row['ID'];
      }

      $result->free();
      $prep->free();

      return true;
   }

   /**
    * Stores Location to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if($this->ID == 0)
      {
         $this->RepositoryLimit = ($_ARCHON->Security->Session->User->RepositoryLimit) ? true : $this->RepositoryLimit;
      }
      else
      {
         if(!$this->RepositoryLimit && $_ARCHON->Security->Session->User->RepositoryLimit)
         {
            $_ARCHON->declareError("Could not store Location: Locations may only be altered for the primary repository.");
            return false;
         }
      }

      if($this->RepositoryLimit && (empty($this->RelatedRepositoryIDs) || $this->RelatedRepositoryIDs == array(0)))
      {
         $_ARCHON->declareError("Could not store Location: The Location must be related to at least one Repository if RepositoryLimit is enabled.");
         return false;
      }

      $checkquery = "SELECT ID FROM tblCollections_Locations WHERE Location = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->Location, $this->ID);
      $checkqueryerror = "A Location with the same Name already exists in the database";
      $problemfields = array('Location');
      $requiredfields = array('Location');

      if(!$_ARCHON->storeObject($this, MODULE_LOCATIONS, 'tblCollections_Locations', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      if(!$this->RepositoryLimit)
      {
         $this->RelatedRepositoryIDs = array(0);
      }

      if(!$this->dbUpdateRelatedRepositories($this->RelatedRepositoryIDs))
      {
         return false;
      }

      return true;
   }

   public function dbUpdateRelatedRepositories($arrRelatedIDs, $Action = NULL)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_LOCATIONS, 'Repository', 'tblCollections_LocationRepositoryIndex', 'tblCore_Repositories', $arrRelatedIDs, $Action))
      {
         return false;
      }

      return true;
   }

   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_LOCATIONS, DELETE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete Location: Locations may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_LOCATIONS, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_LOCATIONS, UPDATE)))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not store Location: Locations may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   public function verifyRepositoryPermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->Session->User->RepositoryLimit)
      {
         return true;
      }

      if($this->ID) // Old repository may be disallowed.
      {
         static $prep = NULL;
         if(!isset($prep))
         {
            $query = "SELECT RepositoryID FROM tblCollections_LocationRepositoryIndex WHERE LocationID = ?";
            $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $prep->execute($this->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            if(!$_ARCHON->Security->verifyRepositoryPermissions($row['RepositoryID']))
            {
               $result->free();
               return false;
            }
         }
         $result->free();
      }

      foreach($this->RelatedRepositoryIDs as $RepositoryID)
      {
         if(!$_ARCHON->Security->verifyRepositoryPermissions($RepositoryID))
         {
            return false;
         }
      }
      return true;
   }

   /**
    * Generates a formatted string of the Location object
    *
    * @todo Custom Formatting
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('Location');
   }

   /** @var integer */
   public $ID = 0;
   /** @var string */
   public $Location = '';
   /** @var string */
   public $Description = '';
   /** @var Collection[] */
   public $Collections = array();
   public $RepositoryLimit = 0;
   public $Repositories = array();
   public $RelatedRepositoryIDs = array();
}

$_ARCHON->mixClasses('Location', 'Collections_Location');
?>