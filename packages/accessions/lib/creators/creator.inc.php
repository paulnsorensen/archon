<?php
abstract class Accessions_Creator
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
      //then delete references in Accessions Creator Index
      if(!$_ARCHON->deleteRelationship('tblAccessions_AccessionCreatorIndex', 'CreatorID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads Accessions from the database
    *
    * This function loads accessions that fall under this creator
    *
    * @return boolean
    */
   public function dbLoadAccessions()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Accessions: Creator ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Accessions: Creator ID must be numeric.");
         return false;
      }

      $this->Accessions = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblAccessions_Accessions.* FROM tblAccessions_Accessions JOIN tblAccessions_AccessionCreatorIndex ON tblAccessions_Accessions.ID = tblAccessions_AccessionCreatorIndex.AccessionID WHERE tblAccessions_AccessionCreatorIndex.CreatorID = ? ORDER BY tblAccessions_Accessions.Title";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Accessions[$row['ID']] = New Accession($row);
      }
      $result->free();

      return true;
   }


   /**
    * @var Accession[]
    */
   public $Accessions = array();

}

$_ARCHON->setMixinMethodParameters('Creator', 'Accessions_Creator', 'dbDelete', NULL, MIX_OVERRIDE);

$_ARCHON->mixClasses('Creator', 'Accessions_Creator');
?>