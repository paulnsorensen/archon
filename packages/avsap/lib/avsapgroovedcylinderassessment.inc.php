<?php
abstract class AVSAP_AVSAPGroovedCylinderAssessment
{

    /**
    * Loads AVSAPGroovedDiscCylinderAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPGroovedCylinderAssessments'))
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

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPGroovedCylinderAssessments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Grooved Cylinder Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPGroovedCylinderAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPGroovedCylinderAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
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
      return array(1 => 'groovedcylinder');
   }

   /**
    * Returns the Base/Composition list
    *
    * @return array
    */
   public function getBaseArray($type = NULL)
   {
      static $arrBase = array();

          $arrBase[1] = 'plastic';
          $arrBase[2] = 'wax';


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
      $arrScores[1][2] = 0;

      return $arrScores[$Format][$BaseComposition];
   }


   
   public $ID = 0;

   public $AssessmentID = 0;

   public $DustLevel = '0.01';

}
$_ARCHON->mixClasses('AVSAPGroovedCylinderAssessment', 'AVSAP_AVSAPGroovedCylinderAssessment');

?>
