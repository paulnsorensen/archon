<?php
abstract class Core_Pattern
{
   /**
    * Deletes Pattern from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_PATTERNS, 'tblCore_Patterns'))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCore_PatternUnitTestIndex', 'PatternID', $ID, MANY_TO_MANY))
      {
         $_ARCHON->declareError("Error deleting UnitTests for Pattern.");
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCore_Configuration', 'PatternID', $ID, ONE_TO_MANY))
      {
         $_ARCHON->declareError("Error deleting UnitTests for Pattern.");
         return false;
      }

      return true;
   }





   /**
    * Loads Pattern
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCore_Patterns'))
      {
         return false;
      }

      return true;
   }





   /**
    * Stores Pattern to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblCore_Patterns WHERE Name = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->Name, $this->ID);
      $checkqueryerror = "A Pattern with the same Name already exists in the database";
      $problemfields = array('Name', 'Pattern');
      $requiredfields = array('Name', 'Pattern');

      if(!$_ARCHON->storeObject($this, MODULE_PATTERNS, 'tblCore_Patterns', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }



   /**
    * Loads Unit Tests for Pattern
    *
    * @return boolean
    */
   public function dbLoadUnitTests()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load UnitTests: Pattern ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load UnitTests: Pattern ID must be numeric.");
         return false;
      }

      $this->UnitTests = array();

      $query = "SELECT * FROM tblCore_PatternUnitTestIndex WHERE PatternID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $objUnitTest = New UnitTest($row);
         $this->UnitTests[$row['ID']] = $objUnitTest;
      }
      $result->free();
      $prep->free();

      return true;
   }




   /**
    * Attempts to match pattern to a subject
    *
    * @return boolean
    */
   public function match($Subject)
   {
      if(!$this->Pattern && !$this->dbLoad())
      {
         return false;
      }
      else
      {
         return preg_match($this->Pattern, $Subject);
      }
   }




   /**
    * Outputs Pattern as a string
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('Name');
   }

   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var integer
    */
   public $PackageID = 0;

   /**
    * @var string
    */
   public $Name = '';

   /**
    * @var string
    */
   public $Pattern = '';


   /**
    * @var object[]
    */
   public $UnitTests = NULL;
}

$_ARCHON->mixClasses('Pattern', 'Core_Pattern');
?>