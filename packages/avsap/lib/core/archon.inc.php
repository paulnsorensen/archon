<?php
abstract class AVSAP_Archon
{
   /**
    * Searches the AVSAPInstitution database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return AVSAPInstitutions[]
    */

   public function getAllAVSAPStorageFacilities()
   {
      return $this->loadTable("tblAVSAP_AVSAPStorageFacilities", "AVSAPStorageFacility", "Name");
   }

   public function searchAVSAPInstitutions($SearchQuery, $RepositoryID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
//      if(!$RepositoryID || (!is_array($RepositoryID) && !is_natural($RepositoryID)) || empty($RepositoryID))
//      {
//         return $this->searchTable($SearchQuery, 'tblAVSAP_AVSAPInstitutions', array('Name'), 'AVSAPInstitution', 'Name', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
//      }

      if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $wherequery = "RepositoryID = ?";
         $wheretypes = array('integer');
         $wherevars = array($RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $wherequery .= "RepositoryID IN (";
         $wherequery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $wherequery .= ")";

         $wheretypes = array_fill(0, count($RepositoryID), 'integer');
         $wherevars = $RepositoryID;
      }
      else
      {
         $wherequery = NULL;
         $wheretypes = array();
         $wherevars = array();
      }

      return $this->searchTable($SearchQuery, 'tblAVSAP_AVSAPInstitutions', array('Name'), 'AVSAPInstitution', 'Name', $wherequery, $wheretypes, $wherevars, NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the AVSAPStorageFacilities database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return AVSAPStorageFacilities[]
    */


   public function searchAVSAPStorageFacilities($SearchQuery, $RepositoryID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
//      if(is_natural($RepositoryID) && $RepositoryID == 0)
//      {
//         return $this->searchTable($SearchQuery, 'tblAVSAP_AVSAPStorageFacilities', array('Name'), 'AVSAPStorageFacility', 'Name', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
//      }

       if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $wherequery = "RepositoryID = ?";
         $wheretypes = array('integer');
         $wherevars = array($RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $wherequery .= "RepositoryID IN (";
         $wherequery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $wherequery .= ")";

         $wheretypes = array_fill(0, count($RepositoryID), 'integer');
         $wherevars = $RepositoryID;
      }
      else
      {
         $wherequery = NULL;
         $wheretypes = array();
         $wherevars = array();
      }


      return $this->searchTable($SearchQuery, 'tblAVSAP_AVSAPStorageFacilities', array('Name'), 'AVSAPStorageFacility', 'Name', $wherequery, $wheretypes, $wherevars, NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the AVSAPAssessments database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return AVSAPStorageFacilities[]
    */

   public function searchAVSAPAssessments($SearchQuery, $RepositoryID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
//      if(is_natural($RepositoryID) && $RepositoryID == 0)
//      {
//         return $this->searchTable($SearchQuery, 'tblAVSAP_AVSAPAssessments', array('Name'), 'AVSAPAssessment', 'Significance', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
//      }

       if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $wherequery = "RepositoryID = ?";
         $wheretypes = array('integer');
         $wherevars = array($RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $wherequery .= "RepositoryID IN (";
         $wherequery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $wherequery .= ")";

         $wheretypes = array_fill(0, count($RepositoryID), 'integer');
         $wherevars = $RepositoryID;
      }
      else
      {
         $wherequery = NULL;
         $wheretypes = array();
         $wherevars = array();
      }


      return $this->searchTable($SearchQuery, 'tblAVSAP_AVSAPAssessments', array('Name'), 'AVSAPAssessment', 'Significance', $wherequery, $wheretypes, $wherevars, NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the AVSAPAssessments database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return AVSAPStorageFacilities[]
    */

   public function searchAVSAPAssessmentsForPrint($data = NULL, $order = NULL, $repositoryID = 0)
   {
      $arrResult = array();
      if(!$this->mdb2)
      {
         return;
      }
      if($repositoryID == 0)
      {
         $repository = ">=  $repositoryID";
      }
      else
      {
         $repository = "=  $repositoryID";
      }
      $query = "SELECT tblAVSAP_AVSAPAssessments.*, tblCollections_Collections.Title,
                      tblAVSAP_AVSAPStorageFacilities.Name AS StorageName from tblAVSAP_AVSAPAssessments LEFT OUTER JOIN
                      tblCollections_Collections ON tblAVSAP_AVSAPAssessments.CollectionID = tblCollections_Collections.ID
                      LEFT OUTER JOIN tblAVSAP_AVSAPStorageFacilities on tblAVSAP_AVSAPAssessments.StorageFacilityID = tblAVSAP_AVSAPStorageFacilities.ID
                      where tblAVSAP_AVSAPAssessments.RepositoryID $repository order by ";

      if($data == 'Location')
      {
         $query .= "tblAVSAP_AVSAPStorageFacilities.Name " . $order;
      }
      elseif($data == 'Collections')
      {
         $query .= "tblCollections_Collections.Title " . $order;
      }
      elseif($data == 'Items')
      {
         $query .= "tblAVSAP_AVSAPAssessments.Name " . $order;
      }
      else
      {
         $query .= "tblAVSAP_AVSAPAssessments. ". $data . " $order";
      }

      $result = $this->mdb2->query($query);

      if (PEAR::isError($result))
      {
         echo $query;
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
//              $arrResult[$row['ID']] = New AVSAPAssessment($row);
              $arrResult[] = array($row['Title'],$row['Name'],$row['StorageName'],$row['Score'],$row['Format'],$row['Significance'],$row['Notes'],$row['SubAssessmentType']);
      }

      $result->free();

      return $arrResult;

   }


   public function getAVSAPSubAssessmentTypeList()
   {
      $arr = array(
              AVSAP_FILM => 'film',
              AVSAP_ACASSETTE => 'audiocassette',
              AVSAP_VCASSETTE => 'videocassette',
              AVSAP_VOPENREEL => 'openreelvideo',
              AVSAP_AOPENREEL => 'openreelaudio',
              AVSAP_OPTICAL => 'opticalmedia',
              AVSAP_WIREAUDIO => 'wireaudio',
              AVSAP_GROOVEDDISC => 'grooveddisc',
              AVSAP_GROOVEDCYL => 'groovedcylinder'
      );

      $arrList = array();

      foreach ($arr as $key => $phrase)
      {
         $strPhrase = $this->getPhrase($phrase, PACKAGE_AVSAP, MODULE_AVSAPASSESSMENTS, PHRASETYPE_ADMIN);
         $phrase = $strPhrase ? $strPhrase->getPhraseValue(ENCODE_HTML) : $phrase;
         $arrList[$key] = $phrase;
      }

      return $arrList;
   }

   public function getAVSAPFormatList($type = NULL)
   {
      $a = new AVSAPAssessment();
      $SubClass = $a->getSubAssessmentClass($type);

      if(!$SubClass)
      {
         return array();
      }

      $b = new $SubClass();
      $arr = $b->getFormatArray();

      $arrList = array();

      foreach ($arr as $key => $phrase)
      {
         $strPhrase = $this->getPhrase($phrase, PACKAGE_AVSAP, MODULE_AVSAPASSESSMENTS, PHRASETYPE_ADMIN);
         $phrase = $strPhrase ? $strPhrase->getPhraseValue(ENCODE_HTML) : $phrase;
         $arrList[$key] = $phrase;
      }

      return $arrList;
   }

   public function getAVSAPBaseList($type = NULL, $subassessmenttype = NULL)
   {
      $a = new AVSAPAssessment();
      $SubClass = $a->getSubAssessmentClass($subassessmenttype);

      if(!$SubClass)
      {
         return array();
      }
      $arr = array();
      $b = new $SubClass();
      if(!is_null($type))
      {
         $arr = $b->getBaseArray($type);
      }
      elseif($subassessmenttype == AVSAP_GROOVEDDISC || $subassessmenttype == AVSAP_GROOVEDCYL || $subassessmenttype == AVSAP_WIREAUDIO)
      {
         $arr = $b->getBaseArray($subassessmenttype);
      }
      $arrList = array();

      foreach ($arr as $key => $phrase)
      {
         $strPhrase = $this->getPhrase($phrase, PACKAGE_AVSAP, MODULE_AVSAPASSESSMENTS, PHRASETYPE_ADMIN);
         $phrase = $strPhrase ? $strPhrase->getPhraseValue(ENCODE_HTML) : $phrase;
         $arrList[$key] = $phrase;
      }

      return $arrList;

   }


   /**
    * Retrieves all AvSAP Scores from the database
    *
    * The returned array of Avsap objects
    * is sorted by Name and has IDs as keys.
    *
    * @return Avsap[]
    */
   public function getAllAvsapScores()
   {
      return $this->loadTable("tblAVSAP_AVSAPAssessments", "AVSAPAssessment", "Name");
   }

}


$_ARCHON->mixClasses('Archon', 'AVSAP_Archon');
?>