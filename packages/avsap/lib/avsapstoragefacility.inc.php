<?php
abstract class AVSAP_AVSAPStorageFacility
{
   /**
    * Deletes Storage Facility from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPSTORAGEFACILITIES, 'tblAVSAP_AVSAPStorageFacilities'))
      {
         return false;
      }

      return true;
   }





   /**
    * Loads Storage Facility from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPStorageFacilities'))
      {
         return false;
      }

      return true;
   }





   /**
    * Stores Storage Facility to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      // update the score from any new values
      $this->Score = $this->calculateScore();

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPStorageFacilities WHERE Name = ? AND RepositoryID = ? AND ID != ?";
      $checktypes = array('text', 'integer', 'integer');
      $checkvars = array($this->Name, $this->RepositoryID, $this->ID);
      $checkqueryerror="Could not store Storage Facility: A Storage Facility with the same Name and RepositoryID already exists in the database.";
      $problemfields = array('Name', 'RepositoryID');
      $requiredfields = array('Name', 'RepositoryID');


      if (!$_ARCHON->storeObject($this, MODULE_AVSAPSTORAGEFACILITIES, 'tblAVSAP_AVSAPStorageFacilities', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }


   public function adjTemperatureValues($SubAssessmentType)
   {
      if($SubAssessmentType == AVSAP_FILM)
      {
         switch($this->AvgTemp)
         {
            case(AVSAPTEMP_VERYLOW):
               $this->AvgTemp = '1.0';
               break;
            case(AVSAPTEMP_LOW):
               $this->AvgTemp = '0.8';
               break;
            case(AVSAPTEMP_MEDIUMLOW):
               $this->AvgTemp = '0.6';
               break;
            case(AVSAPTEMP_MEDIUMHIGH):
               $this->AvgTemp = '0.4';
               break;
            case(AVSAPTEMP_HIGH):
               $this->AvgTemp = '0.2';
               break;
         }
      }
   }

   /**
    * Calculate score based on Storage Facility
    *
    * @return integer
    */
   public function calculateScore()
   {

      $score = new AVSAPScore();
      $score->setType(AVSAP_CLASS_STORAGEFACILITY);
      $score->loadCoefficients();

      return $score->calculateScore($this) / $score->getTotalWeight() * 100;
   }


   public function getScore($SubAssessmentType)
   {

      $tmpAvgTemp = $this->AvgTemp; // save original temp value
      $this->adjTemperatureValues($SubAssessmentType);
      $score = $this->calculateScore();
      $this->AvgTemp = $tmpAvgTemp;

      return $score;
   }



   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_AVSAPSTORAGEFACILITIES, DELETE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete Assessment: Assessments may only be altered for the primary repository.");
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

      if($this->ID) // Old repository may be disallowed or maybe an empty object

      {
         static $prep = NULL;
         if(!isset($prep))
         {
            $query = "SELECT RepositoryID FROM tblAVSAP_AVSAPStorageFacilities WHERE ID = ?";
            $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $prep->execute($this->ID);
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($row = $result->fetchRow())
         {
            $prevRepositoryID = $row['RepositoryID'];
         }
         $result->free();

         if(!$prevRepositoryID || !$_ARCHON->Security->verifyRepositoryPermissions($prevRepositoryID))
         {
            return false;
         }

         if(!$this->RepositoryID)
         {
            $this->RepositoryID = $prevRepositoryID;
            return true; //no sense in re-running the same permissions test below
         }
      }

      if(!$this->RepositoryID || !$_ARCHON->Security->verifyRepositoryPermissions($this->RepositoryID))
      {
         return false;
      }


      return true;
   }




   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_AVSAPSTORAGEFACILITIES, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_AVSAPSTORAGEFACILITIES, UPDATE)))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not store Assessment: Assessments may only be altered for the primary repository.");
         return false;
      }

      return true;
   }




   /**
    * Generates a formatted string of the Storage Facility object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateParentBody[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE, $ConcatinateParentBody = false)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert AVSAPStorageFacility to string: AVSAPStorageFacility ID not defined.");
         return false;
      }

      if(!$this->Name)
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }

      $String = $this->getString('Name');

      return $String;
   }

   // These public variables correspond directly to the fields names in the tblAVSAP_AVSAPStorageFacilities table
   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var integer
    */
   public $LocationID = 0;

   /**
    * @var string
    */
   public $Name = '';

   /**
    * @var integer
    */

   public $RepositoryID = 0;

   public $AvgTemp = '0.01';

   public $TempVariance = '0.01';

   public $AvgHumidity = '0.01';

   public $HumidityVariance = '0.01';

   public $HasFireDetection = '0.01';

   public $HasFireSuppression = '0.01';

   public $HasWaterDetection = '0.01';

   public $MaterialsOnFloor = '0.01';

   public $SecurityLevel = '0.01';

   public $Score = '0.0';

   public $ToStringFields = array('ID', 'Name');

}

$_ARCHON->mixClasses('AVSAPStorageFacility', 'AVSAP_AVSAPStorageFacility');

?>