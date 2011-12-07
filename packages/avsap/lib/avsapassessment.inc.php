<?php
abstract class AVSAP_AVSAPAssessment
{

   /**
    * AVSAPAssessment Constructor
    *
    * @
    */

   public function construct()
   {
      global $_ARCHON;

      $SubAssessmentClass = $this->getSubAssessmentClass();
      if($SubAssessmentClass)
      {
         $this->SubAssessment = New $SubAssessmentClass($_REQUEST['subassessment']);
         $this->SubAssessment->ID = $this->getSubAssessmentID();
         $this->SubAssessment->AssessmentID = $this->ID;
      }
   }

   /**
    * Retrieves SubAssessmentType from the database
    *
    * @return integer
    */

   public function getSubAssessmentType()
   {
      global $_ARCHON;

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT SubAssessmentType FROM tblAVSAP_AVSAPAssessments WHERE ID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $result->free();

      return $row['SubAssessmentType'];
   }





   /**
    * Deletes Assessment from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;


      // Load SubAssessmentType so the SubAssessment knows the table from which to delete
      $this->SubAssessmentType = $this->getSubAssessmentType();

      if(!$this->dbDeleteSubAssessment())
      {
         $_ARCHON->declareError("Failed to delete SubAssessment");
         return false;
      }

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPAssessments'))
      {
         return false;
      }

      return true;
   }





   /**
    * Loads Assessment from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPAssessments'))
      {
         return false;
      }

      $this->dbLoadSubAssessment();

      return true;
   }




   /**
    * Loads Assessment from the database
    *
    * @return boolean
    */
   public function dbLoadSubAssessment()
   {
      global $_ARCHON;


      $SubAssessmentClass = $this->getSubAssessmentClass();

      if(!$SubAssessmentClass)
      {
         return false;
      }


      $SubAssessmentID = $this->getSubAssessmentID();

      if(!$SubAssessmentID)
      {
         return false;
      }


      $this->SubAssessment = new $SubAssessmentClass($SubAssessmentID);

      $this->SubAssessment->dbLoad();

      return true;
   }

   /**
    * Deletes SubAssessment from the database
    *
    * @return boolean
    */
   public function dbDeleteSubAssessment($oldSubAssessmentType = NULL)
   {
      global $_ARCHON;

      // This is used when the user changes the subassessment type
      if($oldSubAssessmentType)
      {
         $SubAssessmentClass = $this->getSubAssessmentClass($oldSubAssessmentType);

         if(!$SubAssessmentClass)
         {
            return true;
         }

         $SubAssessmentID = $this->getSubAssessmentID($oldSubAssessmentType);

         if(!$SubAssessmentID)
         {
            return true;
         }

         $oldSubAssessment = new $SubAssessmentClass($SubAssessmentID);

         return $oldSubAssessment->dbDelete();

      } else
      {

         if(!$this->SubAssessment)
         {
            $SubAssessmentClass = $this->getSubAssessmentClass();

            if(!$SubAssessmentClass)
            {
               return true;
            }

            $SubAssessmentID = $this->getSubAssessmentID();

            if(!$SubAssessmentID)
            {
               return true;
            }

            $this->SubAssessment = new $SubAssessmentClass($SubAssessmentID);
         }

         return $this->SubAssessment->dbDelete();
      }
   }

   /**
    * Stores Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      // Do required checks before a possible subassessment deletion
      $error = NULL;
      if(!$this->Name || $this->Name == '')
      {
         $error = "A Name is required";
      }
      elseif(!is_natural($this->RepositoryID) || $this->RepositoryID == 0)
      {
         $error = "A Repository is required";
      }
      elseif(!is_natural($this->StorageFacilityID) || $this->StorageFacilityID == 0)
      {
         $error = "A Storage Facility is required";
      }
      elseif(!is_natural($this->SubAssessmentType) || $this->SubAssessmentType == 0)
      {
         $error = "A Format is required";
      }
      elseif(!is_natural($this->Format) || $this->Format == 0)
      {
         $error = "A Format is required";
      }
      elseif(!$this->BaseComposition || ($this->BaseComposition != -1 && $this->BaseComposition < 0))
      {
         $error = "A Format Base/Composition is required";
      }

      if($error)
      {
         $_ARCHON->declareError("Could not store AvSAPAssessment: ".$error);
         return false;
      }

      // Checks to see if the SubAssessment changes
      $storedSubAssessmentType = $this->getSubAssessmentType();

      if($storedSubAssessmentType && $storedSubAssessmentType != $this->SubAssessmentType)
      {
         //delete the old one
         $this->dbDeleteSubAssessment($storedSubAssessmentType);

         // if the SubAssessmentType is set back to 0 (none) then make sure the object is gone after dbDeleting.
         if(!$this->SubAssessmentType)
         {
            $this->SubAssessment = NULL;
         }
      }

      // update the score from any new values
      $this->Score = $this->calculateScore();

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPAssessments WHERE Name = ? AND RepositoryID = ? AND ID != ?";
      $checktypes = array('text', 'integer', 'integer');
      $checkvars = array($this->Name, $this->RepositoryID, $this->ID);
      $checkqueryerror = "An Assessment with the same Name and RepositoryID already exists in the database";
      $problemfields = array('Name', 'RepositoryID', 'StorageFacilityID', 'SubAssessmentType');
      $requiredfields = array('Name', 'RepositoryID', 'StorageFacilityID', 'SubAssessmentType');

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      $SubAssessmentID = $this->getSubAssessmentID();


      /* Load the SubAssessment if it exists but hasn't been loaded yet -- this code may never be executed */
      if($this->SubAssessmentType && !$this->SubAssessment)
      {
         if($SubAssessmentID)
         {
            $SubAssessmentClass = $this->getSubAssessmentClass();
            if($SubAssessmentClass)
            {
               $this->SubAssessment = New $SubAssessmentClass($_REQUEST['subassessment']);
               $this->SubAssessment->ID = $SubAssessmentID;
            }
         } else
         {
            $SubAssessmentClass = $this->getSubAssessmentClass();
            if($SubAssessmentClass)
            {
               $this->SubAssessment = New $SubAssessmentClass($_REQUEST['subassessment']);
               $this->SubAssessment->ID = $SubAssessmentID;
               $this->SubAssessment->AssessmentID = $this->ID;
            }
         }
      }
      /* end useless code */


      if($this->SubAssessment)
      {
         // set the SubAssessmentID because it isn't passed through the interface
         if(!$this->SubAssessment->ID)
         {
            $this->SubAssessment->ID = $SubAssessmentID; // if this is new, it will remain zero because getSubAssessmentID returns 0 on not found
         }

         // make sure the AssessmentID is set to the correct ID
         if(!$this->SubAssessment->AssessmentID)
         {
            $this->SubAssessment->AssessmentID = $this->ID;
         }

         if(!$this->SubAssessment->dbStore())
         {
            return false;
         }

         $this->dbLoadSubAssessment(); // why?
      }

      //TODO: // do check get_class to make sure type and class are still consistent -- maybe, not sure this is needed

      return true;
   }

   /**
    * Retrieves SignificancePhrase from the database
    *
    * @param int $val[optional]
    * @return Significance[]
    */

   public function getSignificancePhrase($val= NULL)
   {

      global $_ARCHON;

      $ScoreValue='';
      $objLowPhrase = Phrase::getPhrase('low', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strLowName = $objLowPhrase ? $objLowPhrase->getPhraseValue(ENCODE_HTML) : 'Low';
      $objFairPhrase = Phrase::getPhrase('medium', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strFairName = $objFairPhrase ? $objFairPhrase->getPhraseValue(ENCODE_HTML) : 'Medium';
      $objGoodPhrase = Phrase::getPhrase('high', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strGoodName = $objGoodPhrase ? $objGoodPhrase->getPhraseValue(ENCODE_HTML) : 'High';


      if($val == AVSAPVAL_FAIR)
      {
         $ScoreValue=$strFairName;
      }
      else if($val == AVSAPVAL_GOOD)
      {
         $ScoreValue = $strGoodName;
      }
      else if($val == AVSAPVAL_BAD)
      {
         $ScoreValue = $strLowName;
      }

      return array($ScoreValue, $val);
   }

   /**
    * Retrieves FormatPhrase from the database
    *
    * @param int $val[optional]
    * @return Format[]
    */
   public function getFormatPhrase($val= NULL)
   {

      global $_ARCHON;

      //Make sure subassessment is loaded, and if not, load it
      if(!$this->SubAssessment)
      {
         $this->dbLoadSubAssessment();
      }

      $FormatValue = array();
      $FormatValue = $this->SubAssessment->getFormatArray();
      foreach ($FormatValue as $key => $phrase)
      {
         $strPhrase = Phrase::getPhrase($phrase, PACKAGE_AVSAP, MODULE_AVSAPASSESSMENTS, PHRASETYPE_ADMIN);
         $phrase = $strPhrase ? $strPhrase->getPhraseValue(ENCODE_HTML) : $phrase;
         $FormatValue[$key] = $phrase;
      }

      return $FormatValue[$val];
   }

   /**
    * Retrieves SubAssessmentID from the database
    *
    * @param int $val[optional]
    * @return integer
    */
   public function getSubAssessmentID($SubAssessmentType = NULL)
   {
      global $_ARCHON;

      $strTypeName = '';

      if(!$SubAssessmentType)
      {
         $SubAssessmentType = $this->SubAssessmentType;
      }

      switch($SubAssessmentType)
      {
         case(AVSAP_FILM):
            $strTypeName = "Film";
            break;
         case(AVSAP_ACASSETTE):
            $strTypeName = "AudioCassette";
            break;
         case(AVSAP_VCASSETTE):
            $strTypeName = "VideoCassette";
            break;
         case(AVSAP_VOPENREEL):
            $strTypeName = "OpenReelVideo";
            break;
         case(AVSAP_AOPENREEL):
            $strTypeName = "OpenReelAudio";
            break;
         case(AVSAP_OPTICAL):
            $strTypeName = "OpticalMedia";
            break;
         case(AVSAP_WIREAUDIO):
            $strTypeName = "WireAudio";
            break;
         case(AVSAP_GROOVEDDISC):
            $strTypeName = "GroovedDisc";
            break;
         case(AVSAP_GROOVEDCYL):
            $strTypeName = "GroovedCylinder";
            break;
         case(AVSAP_GENERAL):
            return 0;
            break;
         default:
            $_ARCHON->declareError("Could not get SubAssessmentID. SubAssessmentType is not valid.");
            return 0;
      }

      static $preps = array();
      if(!isset($preps[$strTypeName]))
      {
         $table = "tblAVSAP_AVSAP{$strTypeName}Assessments";
         $query = "SELECT ID FROM {$_ARCHON->mdb2->quoteIdentifier($table)} WHERE AssessmentID = ?";
         $preps[$strTypeName] = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $preps[$strTypeName]->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $result->free();
      if(is_natural($row['ID']))
      {
         return $row['ID'];
      }else
      {
         return 0;
      }
   }

   /**
    * Retrieves SubAssessmentClass from the database
    *
    * @param int $SubAssessmentType[optional]
    * @return SubAssessmentClass
    */
   public function getSubAssessmentClass($SubAssessmentType = NULL)
   {

      if(!$SubAssessmentType)
      {
         $SubAssessmentType = $this->SubAssessmentType;
      }

      switch($SubAssessmentType)
      {
         case(AVSAP_FILM):
            return "AVSAPFilmAssessment";
            break;
         case(AVSAP_ACASSETTE):
            return "AVSAPAudioCassetteAssessment";
            break;
         case(AVSAP_VCASSETTE):
            return "AVSAPVideoCassetteAssessment";
            break;
         case(AVSAP_VOPENREEL):
            return "AVSAPOpenReelVideoAssessment";
            break;
         case(AVSAP_AOPENREEL):
            return "AVSAPOpenReelAudioAssessment";
            break;
         case(AVSAP_OPTICAL):
            return "AVSAPOpticalMediaAssessment";
            break;
         case(AVSAP_WIREAUDIO):
            return "AVSAPWireAudioAssessment";
            break;
         case(AVSAP_GROOVEDDISC):
            return "AVSAPGroovedDiscAssessment";
            break;
         case(AVSAP_GROOVEDCYL):
            return "AVSAPGroovedCylinderAssessment";
            break;
         default:
            return NULL;

      }
   }

   /**
    * Calculate score based on assessment
    *
    * @return integer
    */

   public function calculateScore()
   {
      if(!$this->SubAssessmentType || !is_natural($this->SubAssessmentType))
      {
         return 0;
      }
      if(!$this->StorageFacilityID || !is_natural($this->StorageFacilityID))
      {
         return 0;
      }

      $score = new AVSAPScore();
      $score->setType(AVSAP_CLASS_ASSESSMENT, $this->SubAssessmentType);
      $score->loadCoefficients();

      $objStorageFacility = new AVSAPStorageFacility($this->StorageFacilityID);
      $objStorageFacility->dbLoad();
      $this->StorageFacilityScore = $objStorageFacility->getScore($this->SubAssessmentType) / 100;

      if($this->SubAssessment)
      {
         $this->FormatScore = $this->SubAssessment->getFormatScore($this->Format, $this->BaseComposition);
      }
      return $score->calculateScore($this) / $score->getTotalWeight() * 100;
   }






   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_AVSAPASSESSMENTS, DELETE))
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
            $query = "SELECT RepositoryID FROM tblAVSAP_AVSAPAssessments WHERE ID = ?";
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

      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_AVSAPASSESSMENTS, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_AVSAPASSESSMENTS, UPDATE)))
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
    * Generates a formatted string of the Assessment object
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
         $_ARCHON->declareError("Could not convert AVSAPAssessment to string: AVSAPAssessment ID not defined.");
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



   public $ID = 0;

   public $SubAssessmentType = 0;

   public $SubAssessment = NULL;

   public $Name = '';

   //public $AssessmentIdentifier = '';

//   public $CollectionName = '';

   public $CollectionID = 0;

   public $CollectionContentID = 0;

   public $StorageFacilityID = 0;

   public $Format = 0;

   public $BaseComposition = 0;

   public $UniqueMaterial = '0.01';

   public $OriginalMaterial = '0.01';

   public $IsPlayed = '0.01';

   public $HasPlaybackEquip = '0.01';

   public $RecentlyPlayedBack = '0.01';

   public $HasConditionInfo = '0.01';

   public $OrientedCorrectly = '0.01';

   public $AppropriateContainer = '0.01';

   public $Labeling = '0.01';

   public $PhysicalDamage = '0.01';

   public $MoldLevel = '0.01';

   public $PestDamage = '0.01';

   public $Significance = '0.01';

   public $StorageFacilityScore = NULL;

   public $FormatScore = NULL;

   public $Score = '0.0';

   public $Notes = '';

   public $RepositoryID = 0;

   public $ToStringFields = array('ID', 'Name');

}

$_ARCHON->mixClasses('AVSAPAssessment', 'AVSAP_AVSAPAssessment');

?>
