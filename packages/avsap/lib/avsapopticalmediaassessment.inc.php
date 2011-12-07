<?php
abstract class AVSAP_AVSAPOpticalMediaAssessment
{

   /**
    * Loads AVSAPOpticalMediaAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPOpticalMediaAssessments'))
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

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPOpticalMediaAssessments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Optical Media Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPOpticalMediaAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPOpticalMediaAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
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
      static $arrFormats = array();
      $arrFormats[1] = 'cd';
      $arrFormats[2] = 'dvd';
      $arrFormats[3] = 'laserdisc';
      $arrFormats[4] = 'minidisc';

      return $arrFormats;
   }

   /**
    * Returns the Base/Composition list
    *
    * @return array
    */
   public function getBaseArray($type = NULL)
   {
      $arrBase = array();
      if($type == 1 || $type ==2)
      {
         $arrBase[1] = 'factorypressed';
         $arrBase[2] = 'recordable';
      }
      else
      {
         $arrBase[-1] = 'default';
      }
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
      $arrScores[1][1] = 1.0;
      $arrScores[1][2] = 0.5;
      $arrScores[2][1] = 1.0;
      $arrScores[2][2] = 0.5;
      $arrScores[3][-1] = 0.5;
      $arrScores[4][-1] = 0.25;

      return $arrScores[$Format][$BaseComposition];
   }



   public $ID = 0;

   public $AssessmentID = 0;

   public $LaserRot = '0.01';//

   public $PerformedChecksum = '0.01';//

}
$_ARCHON->mixClasses('AVSAPOpticalMediaAssessment', 'AVSAP_AVSAPOpticalMediaAssessment');

?>