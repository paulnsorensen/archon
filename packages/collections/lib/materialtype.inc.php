<?php

abstract class Collections_MaterialType
{

   /**
    * Deletes MaterialType from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_Collections WHERE MaterialTypeID = ?', 'integer', MDB2_PREPARE_RESULT);

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


      if(!$_ARCHON->deleteObject($this, MODULE_MATERIALTYPES, 'tblCollections_MaterialTypes'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads MaterialType from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_MaterialTypes'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores MaterialType to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblCollections_MaterialTypes WHERE MaterialType = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->MaterialType, $this->ID);
      $checkqueryerror = "A MaterialType with the same Name already exists in the database";
      $problemfields = array('MaterialType');
      $requiredfields = array('MaterialType');

      if(!$_ARCHON->storeObject($this, MODULE_MATERIALTYPES, 'tblCollections_MaterialTypes', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }



      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_Collections WHERE MaterialTypeID = ?', 'integer', MDB2_PREPARE_RESULT);

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
    * Outputs MaterialType if MaterialType is cast to string
    *
    * @magic
    * @return string
    */
   public function toString()
   {
      return $this->getString('MaterialType');
   }

   /**
    * @var integer
    */
   public $ID = 0;
   /**
    * @var string
    */
   public $MaterialType = '';
}

$_ARCHON->mixClasses('MaterialType', 'Collections_MaterialType');
?>