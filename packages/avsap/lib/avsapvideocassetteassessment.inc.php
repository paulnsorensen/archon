<?php
abstract class AVSAP_AVSAPVideoCassetteAssessment
{

   /**
    * Loads AVSAPVideoCassetteAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPVideoCassetteAssessments'))
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

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPVideoCassetteAssessments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Video Cassette Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPVideoCassetteAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPVideoCassetteAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
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
      $arrFormats[1] = 'minidv';
      $arrFormats[2] = 'dvcpro';
      $arrFormats[3] = 'vhs';
      $arrFormats[4] = 'betacam';
      $arrFormats[5] = 'dvcam';
      $arrFormats[6] = 'digitalbetacam';
      $arrFormats[7] = '8mm_video';
      $arrFormats[8] = 'umatic';
      $arrFormats[9] = 'betamax';
      $arrFormats[10] = 'd2';
      $arrFormats[11] = 'd3';
      $arrFormats[12] = 'othercassettevideo';

      return $arrFormats;
   }

   /**
    * Returns the Base/Composition list
    *
    * @return array
    */
   public function getBaseArray($type = NULL)
   {

      return array(-1 => 'default');
   }

   /**
    * Returns the score for each Format
    *
    * @return array
    */
   public function getFormatScore($Format, $BaseComposition)
   {
      static $arrScores = array();
      $arrScores[1] = 0.75;
      $arrScores[2] = 0.63;
      $arrScores[3] = 0.63;
      $arrScores[4] = 0.38;
      $arrScores[5] = 0.63;
      $arrScores[6] = 0.63;
      $arrScores[7] = 0.63;
      $arrScores[8] = 0.5;
      $arrScores[9] = 0.5;
      $arrScores[10] = 0.38;
      $arrScores[11] = 0.38;
      $arrScores[12] = 0.38;

      return $arrScores[$Format];
   }



   public $ID = 0;

   public $AssessmentID = 0;

   public $RecordProtection = '0.01';

   public $CartridgeCondition = '0.01';

   public $StickyShed = '0.01';

   public $WindQuality = '0.01';

   public $PlaybackSqueal = '0.01';

}
$_ARCHON->mixClasses('AVSAPVideoCassetteAssessment', 'AVSAP_AVSAPVideoCassetteAssessment');

?>