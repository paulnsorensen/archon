<?php
abstract class AVSAP_AVSAPAudioCassetteAssessment
{
   /**
    * Loads AVSAPAudioCassetteAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPAudioCassetteAssessments'))
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

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPAudioCassetteAssessments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Audio Cassette Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPAudioCassetteAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPAudioCassetteAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
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
      $arrFormats[1] = 'cartaudiotapes';
      $arrFormats[2] = 'compactcassette';
      $arrFormats[3] = 'microcassette';
      $arrFormats[4] = '8trackaudiotape';
      $arrFormats[5] = 'digitalaudiotape';
      $arrFormats[6] = 'digitalcompactaudiocassette';
//      $arrFormats[7] = 'audiodatacartridge';

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
      $arrScores[1] = 0.5;
      $arrScores[2] = 0.63;
      $arrScores[3] = 0.5;
      $arrScores[4] = 0.5;
      $arrScores[5] = 0.25;
      $arrScores[6] = 0.5;
//      $arrScores[7] = 0.5;

      return $arrScores[$Format];
   }


   
   public $ID = 0;

   public $AssessmentID = 0;

   public $RecordProtection = '0.01';

   public $CartridgeCondition = '0.01';

   public $StickyShed = '0.01';

   public $WindQuality = '0.01';

   public $PlaybackSqueal = '0.01';

   public $CassetteLength = '0.01';

}
$_ARCHON->mixClasses('AVSAPAudioCassetteAssessment', 'AVSAP_AVSAPAudioCassetteAssessment');

?>