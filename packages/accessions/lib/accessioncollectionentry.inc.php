<?php
abstract class Accessions_AccessionCollectionEntry
{
/**
 * Deletes AccessionCollectionEntry from the database
 *
 * @return boolean
 */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_ACCESSIONS, 'tblAccessions_AccessionCollectionIndex'))
      {
         return false;
      }

      $_ARCHON->log("tblAccessions_Accessions", $this->AccessionID);

      return true;
   }




   /**
    * Loads AccessionCollectionEntry from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAccessions_AccessionCollectionIndex'))
      {
         return false;
      }

      if($this->ClassificationID)
      {
         $this->Classification = New Classification($this->ClassificationID);
      }

      if($this->CollectionID)
      {
         $this->Collection = New Collection($this->CollectionID);
      }

      $this->Accession = New Accession($this->AccessionID);

      return true;
   }






   /**
    * Stores AccessionCollectionEntry to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $ID = $this->ID;

      // CollectionID or ClassificationID set?
      if(!$this->CollectionID && !$this->ClassificationID)
      {
         $_ARCHON->declareError("Could not store CollectionEntry: CollectionAndClassification ID not defined.");
         return false;
      }

      // Assume this collection is the primary collection.
      $this->PrimaryCollection = 1;

      // If a collection is already assigned as the primary collection, don't assign this collection as primary.
      static $primaryPrep = NULL;
      if(!isset($primaryPrep))
      {
         $query = "SELECT PrimaryCollection FROM tblAccessions_AccessionCollectionIndex WHERE AccessionID = ? AND PrimaryCollection = 1";
         $_ARCHON->mdb2->setLimit(1);
         $primaryPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $primaryPrep->execute($this->AccessionID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         if($row['PrimaryCollection'])
         {
            $this->PrimaryCollection = 0;
         }
      }
      $result->free();

      $checkquery = "SELECT ID FROM tblAccessions_AccessionCollectionIndex WHERE AccessionID = ? AND ClassificationID = ? AND CollectionID = ? AND ID != ?";
      $checktypes = array('integer', 'integer', 'integer', 'integer');
      $checkvars = array($this->AccessionID, $this->ClassificationID, $this->CollectionID, $this->ID);
      $checkqueryerror = "A CollectionEntry with the same CollectionOrClassification already exists in the database";
      $problemfields = array('AccessionID', 'ClassificationID', 'CollectionID');
      $requiredfields = array('AccessionID');

      if(!$_ARCHON->storeObject($this, MODULE_ACCESSIONS, 'tblAccessions_AccessionCollectionIndex', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      if(!$ID)
      {
         $_ARCHON->log("tblAccessions_Accessions", $this->AccessionID);
      }

      return true;
   }




   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete CollectionEntry: CollectionEntries may only be altered for the primary repository.");
         return false;
      }

      return true;
   }





   public function verifyRepositoryPermissions()
   {
   // TODO: This is for a collection. Edit to work with a CollectionEntry.
      global $_ARCHON;

      if(!$_ARCHON->Security->Session->User->RepositoryLimit)
      {
         return true;
      }

      if(!$this->AccessionID)
      {
         return false;
      }

      if(!$this->Accession)
      {
         $this->Accession = New Accession($this->AccessionID);
         $this->Accession->dbLoad();
      }

      if(!$this->Accession->verifyRepositoryPermissions())
      {
         return false;
      }

      // Make sure "new" collection is not limited
      if($this->CollectionID)
      {
         if(!$this->Collection)
         {
            $this->Collection = New Collection($this->CollectionID);
            $this->Collection->dbLoad();
         }

         // Make sure user isn't dealing with a content from another repository if they're limited

         //         if(!$this->Collection->verifyRepositoryPermissions())
         if(!$this->Collection->RepositoryID || array_key_exists($this->Collection->RepositoryID, $_ARCHON->Security->Session->User->Repositories) == false)
         {
            return false;
         }
      }

      // "Old" collection may be limited.
      if($this->ID)
      {
         static $prep = NULL;
         if(!isset($prep))
         {
            $query = "SELECT tblCollections_Collections.RepositoryID FROM tblCollections_Collections JOIN tblAccessions_AccessionCollectionIndex ON tblCollections_Collections.ID = tblAccessions_AccessionCollectionIndex.CollectionID WHERE tblAccessions_AccessionCollectionIndex.ID = ?";
            $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $prep->execute($this->ID);
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($row = $result->fetchRow())
         {
            $RepositoryID = $row['RepositoryID'];
         }
         $result->free();

         if(!$this->RepositoryID || array_key_exists($this->RepositoryID, $_ARCHON->Security->Session->User->Repositories) == false)
         {
            return false;
         }
      }

      return true;
   }






   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not store CollectionEntry: CollectionEntries may only be altered for the primary repository.");
         return false;
      }

      return true;
   }




   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var integer
    */
   public $AccessionID = 0;

   /**
    * @var integer
    */
   public $ClassificationID = 0;

   /**
    * @var integer
    */
   public $CollectionID = 0;

   /**
    * @var integer
    */
   public $PrimaryCollection = 0;

   /**
    * @var Accession
    */
   public $Accession = NULL;

   /**
    * @var Classification
    */
   public $Classification = NULL;

   /**
    * @var Collection
    */
   public $Collection = NULL;
}

$_ARCHON->mixClasses('AccessionCollectionEntry', 'Accessions_AccessionCollectionEntry');
?>