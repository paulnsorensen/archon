<?php
abstract class Core_UnitTest
{

   /**
    * Deletes UnitTest from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_PATTERNS, 'tblCore_PatternUnitTestIndex'))
      {
         return false;
      }

      return true;
   }




   /**
    * Loads LocationEntry from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCore_PatternUnitTestIndex'))
      {
         return false;
      }

      return true;
   }






   /**
    * Stores LocationEntry to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if($this->ExpectedResult != 1 && $this->ExpectedResult != 0)
      {
         $_ARCHON->declareError("Could not store UnitTest: ExpectedResult not defined.");
         return false;
      }

      if($this->Value == NULL || $this->Value == '')
      {
         $_ARCHON->declareError("Could not store UnitTest: Value not defined.");
         return false;
      }

      $checkquery = "SELECT ID FROM tblCore_PatternUnitTestIndex WHERE 'Value' = ? AND PatternID = ? AND ID != ?";
      $checktypes = array('text', 'integer', 'integer');
      $checkvars = array($this->Value, $this->PatternID, $this->ID);
      $checkqueryerror = "A UnitTest with the same Value and PatternID already exists in the database";
      $problemfields = array('PatternID', 'Value');
      $requiredfields = array('PatternID');

      if(!$_ARCHON->storeObject($this, MODULE_PATTERNS, 'tblCore_PatternUnitTestIndex', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

//        $_ARCHON->log("tblCollections_Collections", $this->PatternID);

      return true;
   }




   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_PATTERNS, UPDATE))
      {
         return false;
      }


      return true;
   }




   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_PATTERNS, UPDATE))
      {
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
   public $PatternID = 0;

   /**
    * @var integer
    */
   public $ExpectedResult = 1;

   /**
    * @var string
    */
   public $Value = '';



//    public $ToStringFields = array('ID', 'LocationID', 'ExtentUnitID', 'RangeValue', 'Section', 'Shelf', 'Extent', 'Content');
}

$_ARCHON->mixClasses('UnitTest', 'Core_UnitTest');
?>