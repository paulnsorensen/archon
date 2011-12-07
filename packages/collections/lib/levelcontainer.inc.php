<?php

abstract class Collections_LevelContainer
{

   /**
    * Deletes LevelContainer from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $query = "SELECT ID FROM tblCollections_Content WHERE LevelContainerID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_RESULT);
      $result = $prep->execute(array($this->ID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      if($result->numRows() > 0)
      {
         $_ARCHON->declareError("Could not delete LevelContainer. LevelContainer is linked to one or many CollectionContent.");
         return false;
      }

      if(!$_ARCHON->deleteObject($this, MODULE_LEVELCONTAINERS, 'tblCollections_LevelContainers'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads LevelContainer from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_LevelContainers'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores LevelContainer to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if(!$this->IntellectualLevel && $this->EADLevel)
      {
         $this->EADLevel = '';
      }

      $checkquery = "SELECT ID FROM tblCollections_LevelContainers WHERE LevelContainer = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->LevelContainer, $this->ID);
      $checkqueryerror = "A LevelContainer with the same Name already exists in the database";
      $problemfields = array('LevelContainer');
      $requiredfields = array('LevelContainer');

      if(!$_ARCHON->storeObject($this, MODULE_LEVELCONTAINERS, 'tblCollections_LevelContainers', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      if($this->PrimaryEADLevel)
      {
         static $primaryEADLevelPrep = NULL;
         if(!isset($primaryEADLevelPrep))
         {
            $query = "UPDATE tblCollections_LevelContainers SET PrimaryEADLevel = 0 WHERE EADLevel = ? AND ID != ?";
            $primaryEADLevelPrep = $_ARCHON->mdb2->prepare($query, array('text', 'integer'), MDB2_PREPARE_MANIP);
         }
         $affected = $primaryEADLevelPrep->execute(array($this->EADLevel, $this->ID));
         if(PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
      }

      return true;
   }

   /**
    * Outputs Container Type as string
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('LevelContainer');
   }

   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var string
    */
   public $LevelContainer = '';

   /**
    * @var integer
    */
   public $IntellectualLevel = 1;

   /**
    * @var integer
    */
   public $PhysicalContainer = 0;

   /**
    * @var string
    */
   public $EADLevel = '';

   /**
    * @var integer
    */
   public $PrimaryEADLevel = 0;

   /**
    * @var integer
    */
   public $GlobalNumbering = 0;

}

$_ARCHON->mixClasses('LevelContainer', 'Collections_LevelContainer');
?>