<?php
abstract class Core_UserProfileField
{
   /**
    * Deletes UserProfileField from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_USERPROFILEFIELDS, 'tblCore_UserProfileFields'))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCore_UserUserProfileFieldIndex', 'UserProfileFieldID', $ID, MANY_TO_MANY))
      {
         return false;
      }
      
      if(!$_ARCHON->deleteRelationship('tblCore_UserProfileFieldCountryIndex', 'UserProfileFieldID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      
      return true;
   }





   /**
    * Loads UserProfileField
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCore_UserProfileFields'))
      {
         return false;
      }

      if($this->UserProfileFieldCategoryID)
      {
         $this->UserProfileFieldCategory = New UserProfileFieldCategory($this->UserProfileFieldCategoryID);
         $this->UserProfileFieldCategory->dbLoad();
      }

      if($this->PatternID)
      {
         $this->Pattern = New Pattern($this->PatternID);
         $this->Pattern->dbLoad();
      }

      return true;
   }





   /**
    * Loads Countries for UserProfileField
    *
    * @return boolean
    */
   public function dbLoadCountries()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Countries: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Countries: User ID must be numeric.");
         return false;
      }

//      $query = "SELECT tblCore_Countries.*, tblCore_UserProfileFieldCountryIndex.Required as Required FROM tblCore_Countries JOIN tblCore_UserProfileFieldCountryIndex ON tblCore_Countries.ID = tblCore_UserProfileFieldCountryIndex.CountryID WHERE tblCore_UserProfileFieldCountryIndex.UserProfileFieldID = ? ORDER BY tblCore_Countries.CountryName;";

      $query = "SELECT * FROM tblCore_UserProfileFieldCountryIndex WHERE UserProfileFieldID = ?;";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No userprofilefields found, return.
         $result->free();
         $prep->free();
         return true;
      }

      while($row = $result->fetchRow())
      {
         $this->Countries[$row['CountryID']] = New Country($row['CountryID']);
         $this->Countries[$row['CountryID']]->dbLoad();
         $this->Countries[$row['CountryID']]->Required = $row['Required'];

         // This is primarily for the administrative interface
         if($row['Required'])
         {
            $this->RequiredCountries[$row['CountryID']] = $this->Countries[$row['CountryID']];
         }
      }

      $result->free();
      $prep->free();

      return true;
   }






   /**
    * Stores UserProfileField to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if($this->DefaultValue && $this->InputType == 'timestamp' && !is_natural($this->DefaultValue))
      {
         if(($timeDefaultValue = strtotime($this->DefaultValue)) === false)
         {
            $_ARCHON->declareError("Could not update UserProfileField: strtotime() unable to parse default value '$this->DefaultValue'.");
            return false;
         }

         $this->DefaultValue = $timeDefaultValue;
      }

      $checkquery = "SELECT ID FROM tblCore_UserProfileFields WHERE UserProfileFieldCategoryID = ? AND UserProfileField = ? AND ID != ?";
      $checktypes = array('integer', 'text', 'integer');
      $checkvars = array($this->UserProfileFieldCategoryID, $this->UserProfileField, $this->ID);
      $checkqueryerror = "A UserProfileField with the same UserProfileFieldCategory and UserProfileField already exists in the database";
      $problemfields = array('PackageID', 'UserProfileFieldCategoryID', 'UserProfileField', 'InputType');
      $requiredfields = array('PackageID', 'UserProfileFieldCategoryID', 'UserProfileField', 'InputType');

      if(!$_ARCHON->storeObject($this, MODULE_USERPROFILEFIELDS, 'tblCore_UserProfileFields', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }







   public function dbUpdateRelatedCountries($arrRelatedCountryIDs, $arrRequiredCountryIDs = array())
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_USERPROFILEFIELDS, UPDATE))
      {
         $_ARCHON->declareError("Could not relate Country: Permission Denied.");
         return false;
      }
      elseif(!$this->ID)
      {
         $_ARCHON->declareError("Could not relate Country: UserProfileField ID not defined.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not relate Country: UserProfileField ID must be numeric.");
         return false;
      }

      if(!is_array($arrRequiredCountryIDs) || !$arrRequiredCountryIDs || $arrRequiredCountryIDs == array(0))
      {
         $arrRequiredCountryIDs = array();
      }

      if(!is_array($arrRelatedCountryIDs) || !$arrRelatedCountryIDs || $arrRequiredCountryIDs == array(0))
      {
         $arrRelatedCountryIDs = $arrRequiredCountryIDs;
      }


      // make sure IDs exist in both
      $arrRelatedCountryIDs = array_unique(array_merge($arrRelatedCountryIDs, $arrRequiredCountryIDs));

      $completeSuccess = true;


      static $currentPrep = NULL;
      if(!isset($currentPrep))
      {
         $query = "SELECT CountryID,Required FROM tblCore_UserProfileFieldCountryIndex WHERE UserProfileFieldID = ?";
         $currentPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }

      $result = $currentPrep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $arrCurrentRelatedCountryIDs = array();
      $arrCurrentRequiredCountryIDs = array();


      while($row = $result->fetchRow())
      {
         $arrCurrentRelatedCountryIDs[]=$row['CountryID'];
         if($row['Required'])
         {
            $arrCurrentRequiredCountryIDs[]=$row['CountryID'];
         }
      }
      $result->free();


      $arrNewRelatedCountryIDs = array_diff($arrRelatedCountryIDs, $arrCurrentRelatedCountryIDs);
      $arrNewUnrelatedCountryIDs = array_diff($arrCurrentRelatedCountryIDs, $arrRelatedCountryIDs);

      $arrNewRequiredCountryIDs = array_diff($arrRequiredCountryIDs, $arrCurrentRequiredCountryIDs);
      $arrNewOptionalCountryIDs = array_diff($arrCurrentRequiredCountryIDs, $arrRequiredCountryIDs);

      if($arrNewRelatedCountryIDs == array(0))
      {
         $arrNewRelatedCountryIDs = array();
      }

      if($arrNewRequiredCountryIDs == array(0))
      {
         $arrNewRequiredCountryIDs = array();
      }


//      static $existPrep = NULL;
      static $checkPrep = NULL;
      static $insertPrep = NULL;
      static $deletePrep = NULL;
      static $updatePrep = NULL;


      /* check if the arrays are full to avoid preparing statements that aren't used */

      if(!empty($arrNewRelatedCountryIDs))
      {
//         if (!isset($existPrep))
//         {
//            $query = "SELECT ID FROM tblCore_Countries WHERE ID = ?";
//            $existPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
//         }

         if(!isset($checkPrep))
         {
            $query = "SELECT ID FROM tblCore_UserProfileFieldCountryIndex WHERE UserProfileFieldID = ? AND CountryID = ?";
            $checkPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
         }

         if(!isset($insertPrep))
         {
            $query = "INSERT INTO tblCore_UserProfileFieldCountryIndex (UserProfileFieldID, CountryID, Required) VALUES (?, ?, ?)";
            $insertPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
         }
      }

      if(!empty($arrNewUnrelatedCountryIDs))
      {
         if(!isset($deletePrep))
         {
            $query = "DELETE FROM tblCore_UserProfileFieldCountryIndex WHERE UserProfileFieldID = ? AND CountryID = ?";
            $deletePrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
         }
      }

      if(!isset($checkPrep))
      {
         $query = "SELECT ID FROM tblCore_UserProfileFieldCountryIndex WHERE UserProfileFieldID = ? AND CountryID = ?";
         $checkPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }

      if(!isset($updatePrep))
      {
         $query = "UPDATE tblCore_UserProfileFieldCountryIndex SET Required = ? WHERE UserProfileFieldID = ? AND CountryID = ?";
         $updatePrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }



      foreach($arrNewRelatedCountryIDs as $key => $newRelatedCountryID)
      {

//         $result = $existPrep->execute($newRelatedCountryID);
//         if (PEAR::isError($result))
//         {
//            trigger_error($result->getMessage(), E_USER_ERROR);
//         }
//
//         $row = $result->fetchRow();
//         $result->free();
//
//         if(!$row['ID'])
//         {
//            $_ARCHON->declareError("Could not update UserProfileField: Country ID {$newRelatedCountryID} does not exist in the database.");
//            unset($arrNewRelatedCountryIDs[$key]);
//            $completeSuccess = false;
//            continue;
//         }


         $result = $checkPrep->execute(array($this->ID, $newRelatedCountryID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            $_ARCHON->declareError("Could not relate Country: Country ID {$newRelatedCountryID} already related to UserProfileField ID {$row['ID']}.");
            unset($arrNewRelatedCountryIDs[$key]);
            $completeSuccess = false;
            continue;
         }


         $requiredCountry = 0;

         if(($pkey = array_search($newRelatedCountryID, $arrNewRequiredCountryIDs)) !== false)
         {
            $requiredCountry = 1;
            unset($arrNewRequiredCountryIDs[$pkey]);
         }


         $affected = $insertPrep->execute(array($this->ID, $newRelatedCountryID, $requiredCountry));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $result = $checkPrep->execute(array($this->ID, $newRelatedCountryID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['ID'])
         {
            $_ARCHON->declareError("Could not relate Country: Unable to update the database table.");
            unset($arrNewRelatedCountryIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $_ARCHON->log('tblCore_UserProfileFieldCountryIndex', $row['ID']);
      }

      foreach($arrNewUnrelatedCountryIDs as $key => $newUnrelatedCountryID)
      {


         $result = $checkPrep->execute(array($this->ID, $newUnrelatedCountryID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $RowID = $row['ID'];

         if(!$row['ID'])
         {
            $_ARCHON->declareError("Could not unrelate Country: Country ID {$newUnrelatedCountryID} not related to UserProfileField ID {$this->ID}.");
            unset($arrNewUnrelatedCountryIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         if(($pkey = array_search($newUnrelatedCountryID, $arrNewOptionalCountryIDs)) !== false)
         {
            unset($arrNewOptionalCountryIDs[$pkey]);
         }

         $affected = $deletePrep->execute(array($this->ID, $newUnrelatedCountryID));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }

         $result = $checkPrep->execute(array($this->ID, $newUnrelatedCountryID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            $_ARCHON->declareError("Could not unrelate Country: Unable to update the database table.");
            unset($arrNewUnrelatedCountryIDs[$key]);
            $completeSuccess = false;
            continue;
         }
         else
         {

            $_ARCHON->log('tblCore_UserProfileFieldCountryIndex', $RowID);
         }

      }

      foreach($arrNewRequiredCountryIDs as $key => $newRequiredCountryID)
      {

         $result = $checkPrep->execute(array($this->ID, $newRequiredCountryID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $RowID = $row['ID'];

         if(!$row['ID'])
         {
            $_ARCHON->declareError("Could not update Country: Country ID {$newRequiredCountryID} not related to UserProfileField ID {$this->ID}.");
            unset($arrNewRequiredCountryIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $affected = $updatePrep->execute(array(1, $this->ID, $newRequiredCountryID));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
         else
         {
            $_ARCHON->log('tblCore_UserProfileFieldCountryIndex', $RowID);

         }
      }

      foreach($arrNewOptionalCountryIDs as $key => $newOptionalCountryID)
      {

         $result = $checkPrep->execute(array($this->ID, $newOptionalCountryID));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         $RowID = $row['ID'];

         if(!$row['ID'])
         {
            $_ARCHON->declareError("Could not update Country: Country ID {$newOptionalCountryID} not related to UserProfileField ID {$this->ID}.");
            unset($arrNewOptionalCountryIDs[$key]);
            $completeSuccess = false;
            continue;
         }

         $affected = $updatePrep->execute(array(0, $this->ID, $newOptionalCountryID));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
         else
         {
            $_ARCHON->log('tblCore_UserProfileFieldCountryIndex', $RowID);

         }
      }

      $_ARCHON->log("tblCore_UserProfileFields", $this->ID);

      return $completeSuccess;

   }







   /**
    * Outputs UserProfileField as a string
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('UserProfileField');
   }

   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var integer
    */
   public $PackageID = 0;

   /**
    * @var integer
    */
   public $UserProfileFieldCategoryID = 0;

   /**
    * @var integer
    */
   public $DisplayOrder = 1;

   /**
    * @var string
    */
   public $UserProfileField = '';

   /**
    * @var string
    */
   public $DefaultValue = '';

   /**
    * @var integer
    */
   public $Required = 0;

   /**
    * @var integer
    */
   public $UserEditable = 1;

   /**
    * @var string
    */
   public $InputType = '';

   /**
    * @var integer
    */
   public $PatternID = 0;

   /**
    * @var integer
    */
   public $Size = 30;

   /**
    * @var integer
    */
   public $MaxLength = 50;

   /**
    * @var string
    */
   public $ListDataSource = '';

   /**
    * @var UserProfileFieldCategory
    */
   public $UserProfileFieldCategory = NULL;

   /**
    * @var Pattern
    */
   public $Pattern = NULL;

   /**
    * @var Country[]
    */
   public $Countries = array();

   /**
    * @var Country[]
    */
   public $RequiredCountries = array();
}

$_ARCHON->mixClasses('UserProfileField', 'Core_UserProfileField');
?>