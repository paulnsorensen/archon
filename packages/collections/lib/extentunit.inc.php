<?php

abstract class Collections_ExtentUnit
{

   /**
    * Deletes ExtentUnit from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_Collections WHERE ExtentUnitID = ?', 'integer', MDB2_PREPARE_RESULT);

      $result = $collIDPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows())
      {
         while($row = $result->fetchRow())
         {
            $collIDs[] = $row['ID'];
         }
      }

      if(isset($collIDs))
      {
         FindingAidCache::setDirty($collIDs);
      }


      if(!$_ARCHON->deleteObject($this, MODULE_EXTENTUNITS, 'tblCollections_ExtentUnits'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads ExtentUnit from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_ExtentUnits'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores ExtentUnit to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblCollections_ExtentUnits WHERE ExtentUnit = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->ExtentUnit, $this->ID);
      $checkqueryerror = "A ExtentUnit with the same Name already exists in the database";
      $problemfields = array('ExtentUnit');
      $requiredfields = array('ExtentUnit');

      if(!$_ARCHON->storeObject($this, MODULE_EXTENTUNITS, 'tblCollections_ExtentUnits', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }


      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_Collections WHERE ExtentUnitID = ?', 'integer', MDB2_PREPARE_RESULT);

      $result = $collIDPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows())
      {
         while($row = $result->fetchRow())
         {
            $collIDs[] = $row['ID'];
         }
      }

      if(isset($collIDs))
      {
         FindingAidCache::setDirty($collIDs);
      }


      return true;
   }

   /**
    * Outputs ExtentUnit if ExtentUnit is cast to string
    *
    * @magic
    * @return string
    */
   public function toString()
   {
      return $this->getString('ExtentUnit');
   }

   /**
    * @var integer
    */
   public $ID = 0;
   /**
    * @var string
    */
   public $ExtentUnit = '';
}

$_ARCHON->mixClasses('ExtentUnit', 'Collections_ExtentUnit');
?>