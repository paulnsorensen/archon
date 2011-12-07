<?php
abstract class AVSAP_AVSAPFilmAssessment
{
   /**
    * Loads AVSAPFilmAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPFilmAssessments'))
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

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPFilmAssessments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Film Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPFilmAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPFilmAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
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
      $arrFormats[1] = '8mm';
      $arrFormats[2] = 'super8mm';
      $arrFormats[3] = '9_5mm';
      $arrFormats[4] = '16mm';
      $arrFormats[5] = '35mm';
      $arrFormats[6] = 'otherfilm';

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
         $arrBase[1] = 'acetate';
         $arrBase[3] = 'polyester';

      }
      elseif ($type == 5)
      {
         $arrBase[1] = 'acetate';
         $arrBase[2] = 'nitrate';
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
      $arrScores[2][-1] = 0.5;
      $arrScores[3][-1] = 0.25;

      $arrScores[4][1] = 0.38; //0.63
      $arrScores[4][3] = 0.88;

      $arrScores[5][2] = 0.0;
      $arrScores[5][1] = 0.38; //0.5
      $arrScores[5][3] = 0.88;

      $arrScores[6][-1] = 0.0;

      return $arrScores[$Format][$BaseComposition];
   }



   public $ID = 0;

   public $AssessmentID = 0;

   public $OnCore = '0.01';

   public $InColor = '0.01';

   public $SoundtrackType = '0.01';

   public $HasLeader = '0.01';

   public $FilmDecay = '1.0';

   public $FilmType = '0.01';

   public $Shrinkage = '0.01';

   public $SpliceIntegrity = '0.01';

   public $MagStockBreakdown = '1.0';


}
$_ARCHON->mixClasses('AVSAPFilmAssessment', 'AVSAP_AVSAPFilmAssessment');

?>