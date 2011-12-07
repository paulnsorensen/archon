<?php
abstract class AVSAP_AVSAPOpenReelAudioAssessment
{

   /**
    * Loads AVSAPOpenReelAudioAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPOpenReelAudioAssessments'))
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

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPOpenReelAudioAssessments'))
      {
         return false;
      }

      return true;
   }


   /**
    * Stores Open Reel Audio Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPOpenReelAudioAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPOpenReelAudioAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
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
      $arrFormats[1] = '2in_openreel';
      $arrFormats[2] = '1in_openreel';
      $arrFormats[3] = '0_5in_openreel';
      $arrFormats[4] = '0_25in_openreel';

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

      if($type == 4)
      {
         $arrBase[1] = 'paper';
         $arrBase[2] = 'acetate';
         $arrBase[3] = 'polyester';
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
      $arrScores[1][-1] = 0.38;
      $arrScores[2][-1] = 0.38;
      $arrScores[3][-1] = 0.25;
      $arrScores[4][1] = 0.38;
      $arrScores[4][2] = 0.63;
      $arrScores[4][3] = 0.63;

      return $arrScores[$Format][$BaseComposition];
   }



   public $ID = 0;

   public $AssessmentID = 0;

   public $HasLeader = '0.01';

   public $TapeDecay = '1.0';

   public $StickyShed = '0.01';

   public $WindQuality = '0.01';

   public $PlaybackSqueal = '0.01';

   public $SpliceIntegrity = '0.01';

}
$_ARCHON->mixClasses('AVSAPOpenReelAudioAssessment', 'AVSAP_AVSAPOpenReelAudioAssessment');

?>