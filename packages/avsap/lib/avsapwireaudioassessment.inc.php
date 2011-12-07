<?php
abstract class AVSAP_AVSAPWireAudioAssessment
{

   /**
    * Loads AVSAPWireAudioAssessment from the database
    *
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPWireAudioAssessments'))
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

      if(!$_ARCHON->deleteObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPWireAudioAssessments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores Wire Audio Assessment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblAVSAP_AVSAPWireAudioAssessments WHERE AssessmentID = ? AND ID != ?";
      $checktypes = array('integer', 'integer');
      $checkvars = array($this->AssessmentID, $this->ID);
      $checkqueryerror = "A SubAssessment with the same Assessment already exists in the database";
      $problemfields = array();
      $requiredfields = array();

      if(!$_ARCHON->storeObject($this, MODULE_AVSAPASSESSMENTS, 'tblAVSAP_AVSAPWireAudioAssessments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
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
      return array(1 => 'wireaudio');
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
      return 0;
   }
   


   public $ID = 0;

   public $AssessmentID = 0;

   public $RustLevel = '0.01';

}
$_ARCHON->mixClasses('AVSAPWireAudioAssessment', 'AVSAP_AVSAPWireAudioAssessment');

?>