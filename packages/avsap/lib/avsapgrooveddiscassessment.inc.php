<?php
abstract class AVSAP_AVSAPGroovedDiscAssessment
{

    /**
    * Loads AVSAPGroovedDiscAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPGroovedDiscAssessments'))
      {
         return false;
      }

      return true;
   }



   /**
    * Deletes Assessment from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPGroovedDiscAssessments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Grooved Disc Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPGroovedDiscAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPGroovedDiscAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }

    /**
    * Returns the Format list
    *
    * @return array
    */
   public function getFormatArray()
   {
      return array(1 => 'grooveddisc');
   }

   /**
    * Returns the Base/Composition list
    *
    * @return array
    */
   public function getBaseArray($type = NULL)
   {
      static $arrBase = array();

          $arrBase[1] = 'laminate';
          $arrBase[2] = 'vinyl';
          $arrBase[3] = 'shellac';

      return $arrBase;
   }

   /**
    * Returns the score for each Format
    *
    * @return array
    */
   public function getFormatScore($Format, $BaseComposition)
   {
      static $arrScores = array();
      $arrScores[1][1] = 0.13;
      $arrScores[1][2] = 1.0;
      $arrScores[1][3] = 0.13;


      return $arrScores[$Format][$BaseComposition];

   }



   public $ID = 0;

   public $AssessmentID = 0;

   public $HasInnerSleeve = '0.01';

   public $CoreMaterial = '1.0';

   public $AcidDeposits = '1.0';

   public $DustLevel = '0.01';

}
$_ARCHON->mixClasses('AVSAPGroovedDiscAssessment', 'AVSAP_AVSAPGroovedDiscAssessment');

?>
