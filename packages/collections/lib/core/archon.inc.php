<?php

abstract class Collections_Archon
{

   /**
    * Returns the number of Collections in the database
    *
    * If $Alphabetical is set to true, an array will be returned with keys of
    * a-z, #, and * each holding the count for Collection SortTitles starting
    * with that character.  # represents all collections starting with a number,
    * and * holds the total count of all collections.
    *
    * @param boolean $Alphabetical[optional]
    * @param boolean $ExcludeDisabledCollections[optional]
    * @param integer $RepositoryID[optional]
    * @return integer|Array
    */
   public function countCollections($Alphabetical = false, $ExcludeDisabledCollections = false, $RepositoryID = 0)
   {
      if(!$this->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
      {
         $ExcludeDisabledCollections = true;
      }

      if($ExcludeDisabledCollections)
      {
         $Conditions = "Enabled = '1'";
      }

      if($RepositoryID && !is_array($RepositoryID) && is_natural($RepositoryID))
      {
         $Conditions .= $Conditions ? " AND RepositoryID = ?" : "RepositoryID = ?";
         $ConditionsTypes = array('integer');
         $ConditionsVars = array($RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $Conditions .= $Conditions ? " AND RepositoryID IN (" : "RepositoryID IN (";
         $Conditions .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $Conditions .= ")";

         $ConditionsTypes = array_fill(0, count($RepositoryID), 'integer');
         $ConditionsVars = $RepositoryID;
      }
      else
      {
         $ConditionsTypes = array();
         $ConditionsVars = array();
      }

      if($Alphabetical)
      {
         if($Conditions)
         {
            $Conditions = 'AND ' . $Conditions;
         }

         $arrIndex = array();
         $sum = 0;

         $prep = $this->mdb2->prepare("SELECT ID FROM tblCollections_Collections WHERE (SortTitle LIKE '0%' OR SortTitle LIKE '1%' OR SortTitle LIKE '2%' OR SortTitle LIKE '3%' OR SortTitle LIKE '4%' OR SortTitle LIKE '5%' OR SortTitle LIKE '6%' OR SortTitle LIKE '7%' OR SortTitle LIKE '8%' OR SortTitle LIKE '9%') $Conditions", $ConditionTypes, MDB2_PREPARE_RESULT);
         $result = $prep->execute($ConditionsVars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $arrIndex['#'] = $result->numRows();
         $sum += $arrIndex['#'];

         $result->free();
         $prep->free();

         $prep = $this->mdb2->prepare("SELECT ID FROM tblCollections_Collections WHERE SortTitle LIKE ? $Conditions", array_merge(array('text'), $ConditionsTypes), MDB2_PREPARE_RESULT);
         for($i = 65; $i < 91; $i++)
         {
            $char = chr($i);

            $result = $prep->execute(array_merge(array("$char%"), $ConditionsVars));
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $arrIndex[$char] = $result->numRows();
            $arrIndex[encoding_strtolower($char)] = & $arrIndex[$char];
            $sum += $arrIndex[$char];

            $result->free();
         }
         $prep->free();

         $arrIndex['*'] = $sum;

         return $arrIndex;
      }
      else
      {
         if($Conditions)
         {
            $Conditions = 'WHERE ' . $Conditions;
         }

         $prep = $this->mdb2->prepare("SELECT ID FROM tblCollections_Collections $Conditions", $ConditionsTypes, MDB2_PREPARE_RESULT);
         $result = $prep->execute($ConditionsVars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $returnVal = $result->numRows();
         $result->free();
         $prep->free();

         return $returnVal;
      }
   }

   /**
    * Returns the number of Books in the database
    *
    * If $Alphabetical is set to true, an array will be returned with keys of
    * a-z, #, and * each holding the count for Books SortTitles starting
    * with that character.  # represents all collections starting with a number,
    * and * holds the total count of all collections.
    *
    * @param boolean $Alphabetical[optional]
    * @return integer|Array
    */
   public function countBooks($Alphabetical = false)
   {

      if($Alphabetical)
      {

         $arrIndex = array();
         $sum = 0;

         $query = "SELECT ID FROM tblCollections_Books WHERE (Title LIKE '0%' OR Title LIKE '1%' OR Title LIKE '2%' OR Title LIKE '3%' OR Title LIKE '4%' OR Title LIKE '5%' OR Title LIKE '6%' OR Title LIKE '7%' OR Title LIKE '8%' OR Title LIKE '9%')";
         $result = $this->mdb2->query($query);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $arrIndex['#'] = $result->numRows();
         $sum += $arrIndex['#'];

         $result->free();
         //$prep->free();

         $prep = $this->mdb2->prepare('SELECT ID FROM tblCollections_Books WHERE Title LIKE ?', 'text', MDB2_PREPARE_RESULT);
         for($i = 65; $i < 91; $i++)
         {
            $char = chr($i);

            //  $query = "SELECT ID FROM tblCollections_Books WHERE Title LIKE '$char%'";
            $result = $prep->execute("$char%");
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $arrIndex[$char] = $result->numRows();
            $result->free();
            $arrIndex[encoding_strtolower($char)] = & $arrIndex[$char];
            $sum += $arrIndex[$char];
         }
         $prep->free();

         $arrIndex['*'] = $sum;

         return $arrIndex;
      }
      else
      {

         $query = "SELECT ID FROM tblCollections_Books";
         $result = $this->mdb2->query($query);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $numRows = $result->numRows();
         $result->free();

         return $numRows;
      }
   }

   public function createEmailDetailsForCart()
   {

      $cart = $this->Security->Session->ResearchCart->getCart();

      if($cart && $cart->Collections)
      {

         foreach($cart->Collections as $CollectionID => $arrObjs)
         {
            foreach($arrObjs->Content as $ContentID => $obj)
            {
               if($obj instanceof Collection)
               {
                  $objCollection = $obj;
                  unset($objContent);
               }
               else
               {
                  $objCollection = $obj->Collection;
                  $objContent = $obj;
               }

               if(CONFIG_COLLECTIONS_SEARCH_BY_CLASSIFICATION && $objCollection->ClassificationID && $objCollection->ClassificationID != $PrevClassificationID)
               {
                  $details .= "{$objCollection->Classification->toString(LINK_NONE, true, false, true, false)}/$objCollection->CollectionIdentifier ";
                  $details .= $objCollection->Classification->toString(LINK_NONE, false, true, false, true, '/') . " -- ";
               }
               else
               {
                  $details .= "$objCollection->CollectionIdentifier ";
               }

               $details .= $objCollection->toString(LINK_NONE) . ". ";


               if($objContent)
               {
                  $details .= $objContent->toString(LINK_NONE, true, true, true, true, ', ');
               }

               $details .= "\n\n";
            }
         }
         return $details;
      }
   }

   /**
    * Creates an formatted string from an array of LocationEntry objects
    *
    * @param LocationEntry[] $arrLocationEntries
    * @param string $Delimiter[optional]
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateFieldNames[optional]
    * @param string $SubDelimiter[optional]
    * @return string
    */
   public function createStringFromLocationEntryArray($arrLocationEntries, $Delimiter = ', ', $MakeIntoLink = LINK_NONE, $ConcatinateFieldNames = true, $SubDelimiter = ", ")
   {
      if(empty($arrLocationEntries))
      {
         $this->declareError("Could not create LocationEntry String: No IDs specified.");
         return false;
      }

      $objLast = end($arrLocationEntries);

      foreach($arrLocationEntries as $objLocationEntry)
      {
         $string .= $objLocationEntry->toString($MakeIntoLink, $ConcatinateFieldNames, $SubDelimiter, true);

         if($objLocationEntry->ID != $objLast->ID)
         {
            $string .= $Delimiter;
         }
      }

      return $string;
   }

   public function createStringFromBookArray($arrBooks, $Delimiter = ', ', $MakeIntoLink = LINK_NONE)
   {
      if(empty($arrBooks))
      {
         $this->declareError("Could not create Book String: No IDs specified.");
         return false;
      }

      $objLast = end($arrBooks);

      foreach($arrBooks as $objBook)
      {
         $string .= $objBook->toString($MakeIntoLink);

         if($objBook->ID != $objLast->ID)
         {
            $string .= $Delimiter;
         }
      }

      return $string;
   }

   /**
    * Creates an formatted string from an array of UserField objects
    *
    * @param UserField[] $arrUserFields
    * @param string $Delimiter[optional]
    * @param integer $MakeIntoLink[optional]
    * @param string $ConcatinateTitles[optional]
    * @return string
    */
   public function createStringFromUserFieldArray($arrUserFields, $Delimiter = ', ', $MakeIntoLink = LINK_NONE, $ConcatinateTitles = true)
   {
      if(empty($arrUserFields))
      {
         $this->declareError("Could not create Userfield String: No IDs specified.");
         return false;
      }

      $objLast = end($arrUserFields);

      foreach($arrUserFields as $objUserField)
      {
         $string .= $objUserField->toString($MakeIntoLink, $ConcatinateTitles);

         if($objUserField->ID != $objLast->ID)
         {
            $string .= $Delimiter;
         }
      }

      return $string;
   }

   /**
    * Retrieves all Classifications from the database
    *
    * The returned array of Classification objects
    * is nested such that the objects will have their
    * Classification[] member variable populated with their
    * children, which allows for a complete tree structure.
    *
    * @return Classification[]
    */
   public function getAllClassifications()
   {
      $arrClassifications = $this->loadTable("tblCollections_Classifications", "Classification", "ParentID, ClassificationIdentifier");

      if(!empty($arrClassifications))
      {
         foreach($arrClassifications as &$objClassification)
         {
            if($objClassification->ParentID)
            {
               $arrClassifications[$objClassification->ParentID]->Classifications[$objClassification->ID] = $objClassification;
               $objClassification->Parent = $arrClassifications[$objClassification->ParentID];
            }
         }

         uasort($arrClassifications, create_function('$a,$b', 'return strnatcmp($a->ClassificationIdentifier, $b->ClassificationIdentifier);'));
         reset($arrClassifications);
      }

      return $arrClassifications;
   }

   public function getCollectionList($Conditions = NULL)
   {
      return $this->loadObjectList("tblCollections_Collections", "Collection", "Title", "SortTitle");
   }

   /**
    * Retrieves all Collections from the database
    *
    * The returned array of Collection objects
    * is sorted by SortTitle and has IDs as keys.
    *
    * Please realize that calling this function could take
    * a very long time if there are a lot of collections.
    *
    * Also, this function does NOT load content or retrieve
    * related information like subjects, creators, etc.
    *
    * @param boolean $MakeIntoIndex[optional]
    * @param boolean $ExcludeDisabledCollections[optional]
    * @param integer $RepositoryID[optional]
    * @return Collection[]
    */
   public function getAllCollections($MakeIntoIndex = false, $ExcludeDisabledCollections = false, $RepositoryID = 0)
   {
      if(!$this->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
      {
         $ExcludeDisabledCollections = true;
      }

      if($ExcludeDisabledCollections)
      {
         $Conditions = "Enabled = '1'";
      }

      $ConditionsTypes = array();
      $ConditionsVars = array();

      if($RepositoryID && !is_array($RepositoryID) && is_natural($RepositoryID))
      {
         $Conditions .= $Conditions ? " AND RepositoryID = '$RepositoryID'" : "RepositoryID = '$RepositoryID'";
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $Conditions .= $Conditions ? " AND RepositoryID IN (" : "RepositoryID IN (";
         $Conditions .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $Conditions .= ")";

         $ConditionsTypes = array_fill(0, count($RepositoryID), 'integer');
         $ConditionsVars = $RepositoryID;
      }

      $arrCollections = $this->loadTable("tblCollections_Collections", "Collection", "SortTitle", $Conditions, $ConditionsTypes, $ConditionsVars);

      if($MakeIntoIndex)
      {
         $arrIndex = array();

         if(!empty($arrCollections))
         {
            foreach($arrCollections as &$objCollection)
            {
               $strCollection = $objCollection->SortTitle;

               if(is_natural($strCollection{0}))
               {
                  $arrIndex['#'][$objCollection->ID] = $objCollection;
               }

               $arrIndex[encoding_strtolower($strCollection{0})][$objCollection->ID] = $objCollection;
            }

            ksort($arrIndex);
         }

         return $arrIndex;
      }
      else
      {
         return $arrCollections;
      }
   }

   /**
    * Retrieves all Books from the database
    *
    * The returned array of Books objects
    * is sorted by Title and has IDs as keys.
    *
    * @return Books[]
    */
   public function getAllBooks()
   {
      return $this->loadTable("tblCollections_Books", "Book", "Title");
   }

   /**
    * Retrieves all Descriptive Rules from the database
    *
    * The returned array of DescriptiveRules objects
    * is sorted by DescriptiveRulesLong and has IDs as keys.
    *
    * @return DescriptiveRules[]
    */
   public function getAllDescriptiveRules()
   {
      return DescriptiveRules::getAllDescriptiveRules();
   }

   /**
    * Retrieves all EAD Elements from the database
    *
    * The returned array of EADElement objects
    * is sorted by EADElement and has IDs as keys.
    *
    * @return EADElement[]
    */
   public function getAllEADElements()
   {
      return EADElement::getAllEADElements();
   }

   /**
    * Retrieves all Extent Units from the database
    *
    * The returned array of ExtentUnit objects
    * is sorted by ExtentUnit and has IDs as keys.
    *
    * @return ExtentUnit[]
    */
   public function getAllExtentUnits()
   {
      return $this->loadTable("tblCollections_ExtentUnits", "ExtentUnit", "ExtentUnit");
   }

   /**
    * Retrieves all Level/Containers from the database
    *
    * The returned array of LevelContainer objects
    * is sorted by LevelContainer and has IDs as keys.
    *
    * @return LevelContainer[]
    */
   public function getAllLevelContainers()
   {
      return $this->loadTable("tblCollections_LevelContainers", "LevelContainer", "LevelContainer");
   }

   /**
    * Retrieves all Locations from the database
    *
    * The returned array of Locations objects
    * is sorted by Location and has IDs as keys.
    *
    * @return Location[]
    */
   public function getAllLocations($ReturnList = false)
   {
      if($ReturnList)
      {
         return $this->loadTable("tblCollections_Locations", "Location", "Location");
      }
      else
      {
         return $this->loadTable("tblCollections_Locations", "Location", "Location");
      }
   }

   /**
    * Retrieves all Material Types from the database
    *
    * The returned array of MaterialType objects
    * is sorted by MaterialType and has IDs as keys.
    *
    * @return MaterialType[]
    */
   public function getAllMaterialTypes()
   {
      return $this->loadTable("tblCollections_MaterialTypes", "MaterialType", "MaterialType");
   }

   /**
    * Retrieves all ResearchAppointmentFields from the database
    *
    * The returned array of ResearchAppointmentField objects
    * is sorted by PackageID, ResearchAppointmentField
    * and has IDs as keys.
    *
    * @return ResearchAppointmentField[]
    */
   public function getAllResearchAppointmentFields()
   {
      return $this->loadTable("tblCollections_ResearchAppointmentFields", "ResearchAppointmentField", "DisplayOrder, Name", NULL, array(), array());
   }

   /**
    * Retrieves all ResearchAppointmentPurposes from the database
    *
    * The returned array of ResearchAppointmentPurpose objects
    * is sorted by Name and has IDs as keys.
    *
    * @return ResearchAppointmentPurpose[]
    */
   public function getAllResearchAppointmentPurposes()
   {
      return $this->loadTable("tblCollections_ResearchAppointmentPurposes", "ResearchAppointmentPurpose", "ResearchAppointmentPurpose");
   }

   /**
    * Retrieves all ResearcherTypes from the database
    *
    * The returned array of ResearcherType objects
    * is sorted by Name and has IDs as keys.
    *
    * @return ResearcherType[]
    */
   public function getAllResearcherTypes()
   {
      return $this->loadTable("tblCollections_ResearcherTypes", "ResearcherType", "ResearcherType");
   }

   /**
    * Retrieves child Classifications for Classification specified by $ID
    *
    * @param integer $ID[optional]
    * @return Classification[]
    */
   public function getChildClassifications($ID = 0, $OnlyToStringFields = true)
   {
      if(!is_natural($ID))
      {
         $this->declareError("Could not get Child Classifications: Classification ID must be numeric.");
         return false;
      }

      if($OnlyToStringFields)
      {
         $vars = $this->getClassVars('Classification');
         $toStringFields = $vars['ToStringFields'];
      }
      else
      {
         $toStringFields = array();
      }

      $arrClassifications = $this->loadTable("tblCollections_Classifications", "Classification", "ClassificationIdentifier, Title", "ParentID = ?", array('integer'), array($ID), false, $toStringFields);

      if(!empty($arrClassifications))
      {
         uasort($arrClassifications, create_function('$a,$b', 'return strnatcmp($a->ClassificationIdentifier, $b->ClassificationIdentifier);'));
         reset($arrClassifications);
      }

      return $arrClassifications;
   }

   /**
    * Retrieves child CollectionContent for CollectionContent specified by $ContentID
    *
    * $CollectionID is required whenever $ContentID = 0
    *
    * @param integer $ContentID
    * @param integer $CollectionID[optional]
    * @return CollectionContent[]
    */
   public function getChildCollectionContent($CollectionContentID, $CollectionID = 0)
   {
      $CollectionContentID = $CollectionContentID ? $CollectionContentID : 0;

      if(!$CollectionContentID && !$CollectionID)
      {
         $this->declareError("Could not get Child CollectionContent: CollectionContentIDAndCollectionID not defined.");
         return false;
      }
      elseif(!is_natural($CollectionContentID) || !is_natural($CollectionID))
      {
         $this->declareError("Could not get Child CollectionContent: CollectionContentIDAndCollectionID must be numeric.");
         return false;
      }

      $collectiontypes = array();
      $collectionvars = array();
      if($CollectionID)
      {
         $collectionquery = "AND tblCollections_Content.CollectionID = ?";
         $collectiontypes[] = 'integer';
         $collectionvars[] = $CollectionID;
      }

      $arrContent = array();

      $arrLevelContainers = $this->getAllLevelContainers();

      $query = "SELECT tblCollections_Content.* FROM tblCollections_Content JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID WHERE tblCollections_Content.ParentID = ? $collectionquery ORDER BY tblCollections_Content.SortOrder";

      static $preps = array();
      if(!isset($preps[$query]))
      {
         $preps[$query] = $this->mdb2->prepare($query, array_merge(array('integer'), $collectiontypes), MDB2_PREPARE_RESULT);
      }

      $result = $preps[$query]->execute(array_merge(array($CollectionContentID), $collectionvars));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $objContent = New CollectionContent($row);
         $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];

         $arrContent[$objContent->ID] = $objContent;
      }
      $result->free();

//      $this->sortCollectionContentArray(&$arrContent);

      reset($arrContent);

      return $arrContent;
   }

   /**
    * Retrieves an array containing Classification objects for each ID in $arrIDs
    *
    * @param integer[] $arrIDs
    * @return Classification[]
    */
   public function getClassificationArrayFromIDArray($arrIDs)
   {
      if(empty($arrIDs))
      {
         $this->declareError("Could not get Classification Array: No IDs specified.");
         return false;
      }

      if(!is_array($arrIDs))
      {
         $this->declareError("Could not get Classification Array: Argument is not an array.");
         return false;
      }

      foreach($arrIDs as $ID)
      {
         if(is_natural($ID) && $ID >= 0)
         {
            $Condition .= "ID = '$ID' OR ";
         }
      }

      // Chop off the trailing OR
      $Condition = encoding_substr($Condition, 0, encoding_strlen($Condition) - 3);

      $arrClassifications = $this->loadTable("tblCollections_Classifications", 'Classification', 'ClassificationIdentifier, Title', $Condition);

      if(!empty($arrClassifications))
      {
         foreach($arrClassifications as &$objClassification)
         {
            if($objClassification->ParentID)
            {
               $objClassification->Parent = New Classification($objClassification->ParentID);
               $objClassification->dbLoad();
            }

            if($objClassification->CreatorID)
            {
               $objClassification->Creator = New Creator($objClassification->CreatorID);
               $objClassification->dbLoad();
            }
         }
      }

      reset($arrClassifications);

      return $arrClassifications;
   }

   /**
    * Returns Classification ID for given Record Group Number.
    * Classifications in the number are separated by '/'.
    *
    * @param string $RecordGroupNumber
    * @return integer
    */
   public function getClassificationIDForNumber($RecordGroupNumber)
   {
      global $_ARCHON;

      $ClassificationID = 0;
      static $preps = array();

      if($RecordGroupNumber != '')
      {
         $arrClassifications = explode("/", $RecordGroupNumber);

         $ClassificationID = 0;

         foreach($arrClassifications as $ClassificationIdentifier)
         {
            $prevClassificationID = $ClassificationID;

            $prevIDquery = "AND ParentID = ?";
            $prevIDtypes = array('integer');
            $prevIDvars = array($prevClassificationID);

            // Will find 001 if somebody has typed either 1 or 001.
            if(is_natural($ClassificationIdentifier))
            {
               $minLengthQuery = " OR ClassificationIdentifier = ?";
               $minLengthTypes = array('text');
               $minLengthVars = array(str_pad($ClassificationIdentifier, CONFIG_COLLECTIONS_CLASSIFICATION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT));
            }
            else
            {
               $minLengthQuery = '';
               $minLengthTypes = array();
               $minLengthVars = array();
            }

            $query = "SELECT ID FROM tblCollections_Classifications WHERE (ClassificationIdentifier = ?$minLengthQuery) $prevIDquery";
            $types = array_merge(array('text'), $minLengthTypes, $prevIDtypes);
            $vars = array_merge(array($ClassificationIdentifier), $minLengthVars, $prevIDvars);

            if(!isset($preps[$query]))
            {
               $preps[$query] = $this->mdb2->prepare($query, $types, MDB2_PREPARE_RESULT);
            }
            $result = $preps[$query]->execute($vars);
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $row = $result->fetchRow();
            $result->free();

            $ClassificationID = $row['ID'];
            if(!$ClassificationID)
            {
               $ClassificationID = 0;
               break;
            }
         }
      }

      return $ClassificationID;
   }

   /**
    * Returns CollectionContentID when passed the CollectionID, LevelContainerID, LevelContainerIdentifier, and ParentID
    *
    * @param integer $CollectionID
    * @param integer $LevelContainerID
    * @param integer $LevelContainerIdentifier
    * @param integer $ParentID[optional]
    * @return integer
    */
   public function getCollectionContentIDFromData($CollectionID, $LevelContainerID, $LevelContainerIdentifier, $ParentID = 0)
   {
      if(!$CollectionID)
      {
         $this->declareError("Could not get CollectionContentID: Collection ID not defined.");
         return false;
      }

      if(!$LevelContainerID)
      {
         $this->declareError("Could not get CollectionContentID: LevelContainer ID not defined.");
         return false;
      }

      if(!$LevelContainerIdentifier)
      {
         $this->declareError("Could not get CollectionContentID: Container Label not defined.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $this->declareError("Could not get CollectionContentID: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($LevelContainerID))
      {
         $this->declareError("Could not get CollectionContentID: LevelContainer ID must be numeric.");
         return false;
      }

      if(!is_natural($ParentID))
      {
         $this->declareError("Could not get CollectionContentID: Parent ID must be numeric.");
         return false;
      }

      $query = "SELECT ID FROM tblCollections_Content WHERE CollectionID = ? AND LevelContainerID = ? AND LevelContainerIdentifier = ? AND ParentID = ?";
      static $prep = NULL;
      if(!isset($prep))
      {
         $this->mdb2->setLimit(1);
         $prep = $this->mdb2->prepare($query, array('integer', 'integer', 'text', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute(array($CollectionID, $LevelContainerID, $LevelContainerIdentifier, $ParentID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Returns CollectionID for given Record Series Number.
    * Classifications and Collection Identifiers are separated
    * by '/'s in the number.
    *
    * @param string $RecordSeriesNumber
    * @return integer
    */
   public function getCollectionIDForNumber($RecordSeriesNumber)
   {
      global $_ARCHON;

      if(!$RecordSeriesNumber)
      {
         return 0;
      }

      static $preps = array();

      $CollectionID = 0;

      $arrClassifications = explode("/", $RecordSeriesNumber);

      $ClassificationID = 0;

      while(count($arrClassifications) > 1)
      {
         $ClassificationIdentifier = array_shift($arrClassifications);
         $prevClassificationID = $ClassificationID;

         $prevIDquery = "AND ParentID = ?";
         $prevIDtypes = array('integer');
         $prevIDvars = array($prevClassificationID);

         // Will find 001 if somebody has typed either 1 or 001.
         if(is_natural($ClassificationIdentifier))
         {
            $minLengthQuery = " OR ClassificationIdentifier = ?";
            $minLengthTypes = array('text');
            $minLengthVars = array(str_pad($ClassificationIdentifier, CONFIG_COLLECTIONS_CLASSIFICATION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT));
         }
         else
         {
            $minLengthQuery = '';
            $minLengthTypes = array();
            $minLengthVars = array();
         }

         $query = "SELECT ID FROM tblCollections_Classifications WHERE (ClassificationIdentifier = ?$minLengthQuery) $prevIDquery";
         $types = array_merge(array('text'), $minLengthTypes, $prevIDtypes);
         $vars = array_merge(array($ClassificationIdentifier), $minLengthVars, $prevIDvars);

         if(!isset($preps[$query]))
         {
            $preps[$query] = $this->mdb2->prepare($query, $types, MDB2_PREPARE_RESULT);
         }
         $result = $preps[$query]->execute($vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            $ClassificationID = $row['ID'];
         }
         else
         {
            array_unshift($arrClassifications, $ClassificationIdentifier);
            break;
         }
      }

      $CollectionIdentifier = implode('/', $arrClassifications);

      if(is_natural($CollectionIdentifier))
      {
         $minLengthQuery = " OR CollectionIdentifier = ?";
         $minLengthTypes = array('text');
         $minLengthVars = array(str_pad($CollectionIdentifier, CONFIG_COLLECTIONS_COLLECTION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT));
      }
      else
      {
         $minLengthQuery = '';
         $minLengthTypes = array();
         $minLengthVars = array();
      }

      $query = "SELECT ID FROM tblCollections_Collections WHERE ClassificationID = ? AND (CollectionIdentifier = ?$minLengthQuery);";
      $types = array_merge(array('integer', 'text'), $minLengthTypes);
      $vars = array_merge(array($ClassificationID, $CollectionIdentifier), $minLengthVars);

      if(!isset($preps[$query]))
      {
         $preps[$query] = $this->mdb2->prepare($query, $types, MDB2_PREPARE_RESULT);
      }
      $result = $preps[$query]->execute($vars);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      return $row['ID'] ? $row['ID'] : 0;
   }

   /**
    * Retrieves an array of Collection objects that begin with
    * the character specified by $Char
    *
    * @param string $Char
    * @param boolean $ExcludeDisabledCollections[optional]
    * @param integer $RepositoryID[optional]
    * @return Collection[]
    */
   public function getCollectionsForChar($Char, $ExcludeDisabledCollections = false, $RepositoryID = 0, $Fields = array())
   {
      if(!$this->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
      {
         $ExcludeDisabledCollections = true;
      }


      if(!$Char)
      {
         $this->declareError("Could not get Collections: Character not defined.");
         return false;
      }

      $arrCollections = array();

      $andTypes = array();
      $andVars = array();
      if($ExcludeDisabledCollections)
      {
         $andquery = " AND Enabled = '1'";
      }

      if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $andquery .= " AND (tblCollections_Collections.RepositoryID = ?)";
         array_push($andTypes, 'integer');
         array_push($andVars, $RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $andquery .= " AND RepositoryID IN (";
         $andquery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $andquery .= ")";

         $andTypes = array_merge($andTypes, array_fill(0, count($RepositoryID), 'integer'));
         $andVars = array_merge($andVars, $RepositoryID);
      }


//      if($RepositoryID && is_natural($RepositoryID))
//      {
//         $andquery .= " AND RepositoryID = ?";
//         array_push($andTypes, 'integer');
//         array_push($andVars, $RepositoryID);
//      }

      if(!empty($Fields) && is_array($Fields))
      {
         $tmpCollection = new Collection();
         $badFields = array_diff($Fields, array_keys(get_object_vars($tmpCollection)));
         if(!empty($badFields))
         {
            $this->declareError("Could not load Collections: Field(s) '" . implode(',', $badFields) . "' do not exist in Class Collection.");
            return false;
         }

         $selectFields = implode(',', $Fields);
      }


      $selectFields = ($selectFields) ? $selectFields : '*';

      if($Char == '#')
      {
         $query = "SELECT {$selectFields} FROM tblCollections_Collections WHERE (SortTitle LIKE '0%' OR SortTitle LIKE '1%' OR SortTitle LIKE '2%' OR SortTitle LIKE '3%' OR SortTitle LIKE '4%' OR SortTitle LIKE '5%' OR SortTitle LIKE '6%' OR SortTitle LIKE '7%' OR SortTitle LIKE '8%' OR SortTitle LIKE '9%') $andquery ORDER BY SortTitle";
      }
      else
      {
         $query = "SELECT {$selectFields} FROM tblCollections_Collections WHERE SortTitle LIKE '{$this->mdb2->escape($Char, true)}%' $andquery ORDER BY SortTitle";
      }

      $prep = $this->mdb2->prepare($query, $andTypes, MDB2_PREPARE_RESULT);
      $result = $prep->execute($andVars);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrCollections[$row['ID']] = New Collection($row);
      }
      $result->free();
      $prep->free();

      return $arrCollections;
   }

   /**
    * Retrieves an array of Book objects that begin with
    * the character specified by $Char
    *
    * @param string $Char
    * @param integer $BookID[optional]
    * @return Book[]
    */
   public function getBooksForChar($Char)
   {

      if(!$Char)
      {
         $this->declareError("Could not get Books: Character not defined.");
         return false;
      }

      $arrBooks = array();


      if($Char == '#')
      {
         $query = "SELECT * FROM tblCollections_Books WHERE (Title LIKE '0%' OR Title LIKE '1%' OR Title LIKE '2%' OR Title LIKE '3%' OR Title LIKE '4%' OR Title LIKE '5%' OR Title LIKE '6%' OR Title LIKE '7%' OR Title LIKE '8%' OR Title LIKE '9%') ORDER BY Title";
      }
      else
      {
         $query = "SELECT * FROM tblCollections_Books WHERE Title LIKE '{$this->mdb2->escape($Char, true)}%' ORDER BY Title";
      }
      $result = $this->mdb2->query($query);

      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrBooks[$row['ID']] = New Book($row);
      }
      $result->free();


      return $arrBooks;
   }

   /**
    * Retrieves an array of Collection objects that are organized
    * under the Classification specified by $ID
    *
    * @param integer $ID
    * @param boolean $ExcludeDisabledCollections[optional]
    * @return Collection[]
    */
   public function getCollectionsForClassification($ClassificationID, $ExcludeDisabledCollections = false)
   {
      if(!$ClassificationID)
      {
         $this->declareError("Could not get Collections: Classification ID not defined.");
         return false;
      }

      if(!is_natural($ClassificationID))
      {
         $this->declareError("Could not get Collections: Classification ID must be numeric.");
         return false;
      }

      $arrCollections = array();

      if($ExcludeDisabledCollections)
      {
         $andquery = "AND Enabled = '1'";
      }
      $andtypes = array();
      $andvars = array();

      $arrCollections = array();

      $query = "SELECT * FROM tblCollections_Collections WHERE ClassificationID = ? $andquery";
      $prep = $this->mdb2->prepare($query, array_merge(array('integer'), $andtypes), MDB2_PREPARE_RESULT);
      $result = $prep->execute(array_merge(array($ClassificationID), $andvars));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrCollections[$row['ID']] = New Collection($row);
      }
      $result->free();
      $prep->free();

      uasort($arrCollections, create_function('$a,$b', 'return strnatcmp($a->CollectionIdentifier, $b->CollectionIdentifier);'));

      /* while($row = $this->db->fetch_array($result))
        {
        $arrSorter[$row['CollectionIdentifier']] = New Collection($row);
        }

        if(!empty($arrSorter))
        {
        natksort($arrSorter);

        foreach($arrSorter as &$objCollection)
        {
        $arrCollections[$objCollection->ID] = $objCollection;
        }
        } */
      return $arrCollections;
   }

   /**
    * Returns LevelContainerID value
    * when passed the string value
    * for a container type.
    *
    * @param string $String
    * @return integer
    */
   public function getLevelContainerIDFromString($String)
   {
      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCollections_LevelContainers WHERE LevelContainer LIKE ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($String);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Returns EADElementID value
    * when passed the string value
    * for an EAD element type.
    *
    * @param string $String
    * @return integer
    */
   public function getEADElementIDFromString($String)
   {
      return EADElement::getEADElementIDFromString($String);
   }

   /**
    * Returns an array containing detailed extent information derived from
    * entries in the location index.  Each element in the array will be an object
    * with two member variables:
    *
    * float Extent
    * ExtentUnit ExtentUnit
    *
    * @param int $LocationID
    * @return array
    */
   public function getExtentForLocation($LocationID)
   {
      if(!$LocationID)
      {
         $this->declareError("Could not get Collections: Location ID not defined.");
         return false;
      }

      if(!is_natural($LocationID))
      {
         $this->declareError("Could not get Collections: Location ID must be numeric.");
         return false;
      }

      $arrObjects = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT Extent, ExtentUnitID FROM tblCollections_CollectionLocationIndex, tblCollections_ExtentUnits WHERE tblCollections_CollectionLocationIndex.ExtentUnitID = tblCollections_ExtentUnits.ID AND LocationID = ? ORDER BY tblCollections_ExtentUnits.ExtentUnit";
         $prep = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($LocationID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows() > 0)
      {
         $arrExtentUnits = $this->getAllExtentUnits();
      }

      while($row = $result->fetchRow())
      {
         $arrObjects[$row['ExtentUnitID']]->Extent += $row['Extent'];
      }
      $result->free();

      // We will do the ExtentUnits assignment outside of the loop
      // in order to be more efficent (in the case there are many rows).
      if(!empty($arrObjects))
      {
         foreach($arrObjects as $ExtentUnitID => &$obj)
         {
            $obj->ExtentUnit = $arrExtentUnits[$ExtentUnitID];
         }
      }

      return $arrObjects;
   }

   /**
    * Returns ExtentUnitID value
    * when passed the string value
    * for a container type.
    *
    * @param string $String
    * @return integer
    */
   public function getExtentUnitIDFromString($String)
   {
      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCollections_ExtentUnits WHERE ExtentUnit LIKE ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($String);

      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Returns MaterialTypeID value
    * when passed the string value
    * for a container type.
    *
    * @param string $String
    * @return integer
    */
   public function getMaterialTypeIDFromString($String)
   {
      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare('SELECT ID FROM tblCollections_MaterialTypes WHERE MaterialType LIKE ?', 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($String);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Returns next container number when passed the CollectionID, ParentID, and LevelContainerID
    *
    * @param integer $CollectionID
    * @param integer $LevelContainerID
    * @param integer $ParentID[optional]
    * @return integer
    */
   public function getNextLevelContainerIdentifier($CollectionID, $LevelContainerID, $ParentID = 0)
   {
      if(!$CollectionID)
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Collection ID not defined.");
         return false;
      }

      if(!$LevelContainerID)
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: LevelContainer ID not defined.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($LevelContainerID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: LevelContainer ID must be numeric.");
         return false;
      }

      if(!is_natural($ParentID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Parent ID must be numeric.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         //$query = "SELECT LevelContainerIdentifier FROM tblCollections_Content WHERE CollectionID = ? AND LevelContainerID = ? AND ParentID = ? ORDER BY LevelContainerIdentifier DESC";
         $query = "SELECT LevelContainerIdentifier FROM tblCollections_Content WHERE CollectionID = ? AND LevelContainerID = ? AND ParentID = ? ORDER BY SortOrder DESC";
         $this->mdb2->setLimit(1);
         $prep = $this->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute(array($CollectionID, $LevelContainerID, $ParentID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      return $row['LevelContainerIdentifier'] + 1;
   }

   public function getNextContentSortOrder($CollectionID, $ParentID = 0, $ExcludeID = NULL)
   {
      if(!$CollectionID)
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Collection ID not defined.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($ParentID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Parent ID must be numeric.");
         return false;
      }
      if($ExcludeID != NULL && !is_natural($ExcludeID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: ID must be numeric.");
         return false;
      }


      static $prep = NULL;
      static $exIDprep = NULL;
//      static $listprep = NULL;
//      static $exIDlistprep = NULL;
//      if ($LevelContainerID)
//      {
      if(!$ExcludeID)
      {
         if(!isset($prep))
         {
            $query = "SELECT MAX(SortOrder) AS MaxSortOrder FROM tblCollections_Content WHERE CollectionID = ? AND ParentID = ?";
            $this->mdb2->setLimit(1);
            $prep = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
         }
         $result = $prep->execute(array($CollectionID, $ParentID));
      }
      else
      {
         if(!isset($exIDprep))
         {
            $query = "SELECT MAX(SortOrder) AS MaxSortOrder FROM tblCollections_Content WHERE CollectionID = ? AND ParentID = ? AND ID != ?";
            $this->mdb2->setLimit(1);
            $exIDprep = $this->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
         }

         $result = $exIDprep->execute(array($CollectionID, $ParentID, $ExcludeID));
      }

      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      return (int) ($row['MaxSortOrder']) + 1;
//      } else
//      {
//         if (!$ExcludeID)
//         {
//            if (!isset($listprep))
//            {
//               $query = "SELECT LevelContainerID, MAX(SortOrder) AS MaxSortOrder FROM tblCollections_Content WHERE CollectionID = ? AND ParentID = ? GROUP BY LevelContainerID";
//               $listprep = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
//            }
//            $result = $listprep->execute(array($CollectionID, $ParentID));
//         } else
//         {
//            if (!isset($exIDlistprep))
//            {
//               $query = "SELECT LevelContainerID, MAX(SortOrder) AS MaxSortOrder FROM tblCollections_Content WHERE CollectionID = ? AND ParentID = ? AND ID != ? GROUP BY LevelContainerID";
//               $exIDlistprep = $this->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
//            }
//            $result = $exIDlistprep->execute(array($CollectionID, $ParentID, $ExcludeID));
//         }
//         if (PEAR::isError($result))
//         {
//            trigger_error($result->getMessage(), E_USER_ERROR);
//         }
//
//         $arrMaxSortOrders = array();
//
//         while ($row = $result->fetchRow())
//         {
//            $arrMaxSortOrders[$row['LevelContainerID']] = $row['MaxSortOrder'] + 1;
//         }
//         $result->free();
//
//         return $arrMaxSortOrders;
//      }
   }

   public function getCollectionContentLevel($CollectionID, $ParentID = 0, $ExcludeID = NULL)
   {
      if(!$CollectionID)
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Collection ID not defined.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($ParentID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: Parent ID must be numeric.");
         return false;
      }
      if($ExcludeID != NULL && !is_natural($ExcludeID))
      {
         $this->declareError("Could not get NextLevelContainerIdentifier: ID must be numeric.");
         return false;
      }

      $arrContent = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT * FROM tblCollections_Content WHERE CollectionID = ? AND ParentID = ? ORDER BY SortOrder";
         $prep = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute(array($CollectionID, $ParentID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $objContent = New CollectionContent($row);

         $arrContent[$objContent->ID] = $objContent;
      }
      $result->free();

      if($ExcludeID)
      {
         unset($arrContent[$ExcludeID]);
      }

      return $arrContent;
   }

   /**
    * Retrieves sibling CollectionContent for CollectionContent specified by $ID
    *
    * $CollectionID is required whenever $ID = 0
    *
    * @param integer $ID
    * @return CollectionContent[]
    */
   public function getSiblingCollectionContent($ID)
   {
      if(!$ID)
      {
         $this->declareError("Could not get Sibling CollectionContent: CollectionContent ID not defined.");
         return false;
      }
      elseif(!is_natural($ID))
      {
         $this->declareError("Could not get Sibling CollectionContent: CollectionContent ID must be numeric.");
         return false;
      }

      $objContent = New CollectionContent($ID);

      if(!$objContent->dbLoad())
      {
         $this->declareError("Could not get Sibling CollectionContent: There was already an error.");
         return false;
      }

      $arrContent = array();

      $arrLevelContainers = $this->getAllLevelContainers();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Content.* FROM tblCollections_Content JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID WHERE tblCollections_Content.ParentID = ? AND tblCollections_Content.CollectionID = ? ORDER BY tblCollections_Content.SortOrder";
         $prep = $this->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute(array($objContent->ParentID, $objContent->CollectionID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $objContent = New CollectionContent($row);
         $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];

         $arrContent[$objContent->ID] = $objContent;
      }
      $result->free();

//      $this->sortCollectionContentArray(&$arrContent);

      reset($arrContent);

      return $arrContent;
   }

   /**
    * Searches the Classification database
    *
    * @param string $SearchQuery
    * @param integer $ParentID[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Classification[]
    */
   public function searchClassifications($SearchQuery, $ParentID = NULL, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $ConditionANDTypes = array();
      $ConditionANDVars = array();
      if(isset($ParentID) && is_natural($ParentID))
      {
         $ConditionAND = "ParentID = ?";
         $ConditionANDTypes[] = 'integer';
         $ConditionANDVars[] = $ParentID;
      }

      $arrClassifications = $this->searchTable($SearchQuery, 'tblCollections_Classifications', 'Title', 'Classification', 'ClassificationIdentifier, Title', $ConditionAND, $ConditionANDTypes, $ConditionANDVars, NULL, array(), array(), $Limit, $Offset);

      if(!empty($arrClassifications))
      {
         uasort($arrClassifications, create_function('$a,$b', 'return strnatcmp($a->ClassificationIdentifier, $b->ClassificationIdentifier);'));
         reset($arrClassifications);
      }

      return $arrClassifications;
   }

   /**
    * Searches the CollectionContent database
    *
    * @param string $SearchQuery
    * @param integer $ParentID[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Classification[]
    */
   public function searchCollectionContent($SearchQuery, $SearchFlags = SEARCH_COLLECTIONCONTENT, $CollectionID = 0, $RepositoryID = 0, $ParentID = NULL, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $arrContent = array();

      if(!($SearchFlags & SEARCH_COLLECTIONCONTENT))
      {
         return $arrContent;
      }

      //TODO: implement content level enable/disable check
      $enabledquery = " AND (";
      if($SearchFlags & SEARCH_ENABLED_COLLECTIONCONTENT)
      {
         $enabledquery .= "tblCollections_Collections.Enabled = '1'";

         if($SearchFlags & SEARCH_DISABLED_COLLECTIONCONTENT)
         {
            $enabledquery .= " OR tblCollections_Collections.Enabled = '0'";
         }
      }
      else
      {
         $enabledquery = "tblCollections_Collections.Enabled = '0'";
      }
      $enabledquery .= ")";
      $enabledtypes = array();
      $enabledvars = array();

      if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
      {
         $limitparams = array($Limit, $Offset);
      }
      elseif(is_natural($Offset) && $Offset > 0)
      {
         $limitparams = array(4294967295, $Offset);
      }
      elseif(is_natural($Limit) && $Limit > 0)
      {
         $limitparams = array($Limit);
      }
      else
      {
         $limitparams = array(4294967295);
      }

      $arrWords = $this->createSearchWordArray($SearchQuery);
      $textquery = '';
      $texttypes = array();
      $textvars = array();

      if(!empty($arrWords))
      {
         $i = 0;
         foreach($arrWords as $word)
         {
            $i++;
            if($word{0} == "-")
            {
               $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
               $textquery .= "(tblCollections_Content.Title NOT LIKE ? AND tblCollections_Content.Description NOT LIKE ?)";
               array_push($texttypes, 'text', 'text');
               array_push($textvars, "%$word%", "%$word%");
            }
            else
            {
               $textquery .= "(tblCollections_Content.Title LIKE ? OR tblCollections_Content.Description LIKE ?)";
               array_push($texttypes, 'text', 'text');
               array_push($textvars, "%$word%", "%$word%");
            }

            if($i < count($arrWords))
            {
               $textquery .= " AND ";
            }
         }
      }
      else
      {
         $textquery = "tblCollections_Content.Title LIKE '%%' OR tblCollections_Content.Title IS NULL";
      }

      // If our query is just a number, try to match it
      // directly to an ID from the Collections table.
      $idquery = "";
      $idtypes = array();
      $idvars = array();
      if(is_natural($SearchQuery))
      {
         $idquery = " OR (tblCollections_Content.ID = ?)";
         $idtypes[] = 'integer';
         $idvars[] = $SearchQuery;
      }

      $subquery = "";
      $subtypes = array();
      $subvars = array();


      if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $subquery .= " AND (tblCollections_Collections.RepositoryID = ?)";
         $subtypes[] = 'integer';
         $subvars[] = $RepositoryID;
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $subquery .= " AND RepositoryID IN (";
         $subquery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $subquery .= ")";

         $subtypes = array_merge($subtypes, array_fill(0, count($RepositoryID), 'integer'));
         $subvars = array_merge($subvars, $RepositoryID);
      }


      if(isset($ParentID) && is_natural($ParentID))
      {
         $subquery .= " AND (tblCollections_Content.ParentID = ?)";
         $subtypes[] = 'integer';
         $subvars[] = $ParentID;
      }

      if($CollectionID && is_natural($CollectionID))
      {
         $subquery .= " AND (tblCollections_Collections.ID = ?)";
         $subtypes[] = 'integer';
         $subvars[] = $CollectionID;
      }

      $userfieldquery = '(1 = 0)';
      $userfieldtypes = array();
      $userfieldvars = array();
      if($SearchFlags & SEARCH_USERFIELDS)
      {
         $userfieldquery = str_replace("tblCollections_Content.Title ", "tblCollections_UserFields.Title ", $textquery);
         $userfieldquery = str_replace("tblCollections_Content.Description ", "tblCollections_UserFields.Value ", $userfieldquery);
         $userfieldtypes = $texttypes;
         $userfieldvars = $textvars;
      }

      // Run query to find content     
      $query = "SELECT tblCollections_Content.*, tblCollections_Collections.ClassificationID as ClassificationID FROM tblCollections_Content JOIN tblCollections_Collections ON tblCollections_Collections.ID = tblCollections_Content.CollectionID JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID LEFT JOIN (SELECT ContentID FROM tblCollections_UserFields WHERE $userfieldquery) AS tblCollections_UserFields ON tblCollections_UserFields.ContentID = tblCollections_Content.ID WHERE ($textquery OR NOT (tblCollections_UserFields.ContentID IS NULL)$idquery) $subquery $enabledquery ORDER BY tblCollections_Content.SortOrder";
      call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
      $prep = $this->mdb2->prepare($query, array_merge($userfieldtypes, $texttypes, $idtypes, $subtypes, $enabledtypes), MDB2_PREPARE_RESULT);
      $result = $prep->execute(array_merge($userfieldvars, $textvars, $idvars, $subvars, $enabledvars));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows())
      {
         $arrLevelContainers = $this->getAllLevelContainers();
      }

      while($row = $result->fetchRow())
      {
         $objContent = New CollectionContent($row);
         $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];

         //$arrContent[$objContent->toString(LINK_NONE, true, true, true, true)] = $objContent;
         $arrContent[$row['ID']] = $objContent;
      }
      $result->free();
      $prep->free();

      //asort($arrContent);
      //natcaseksort($arrContent);
      // TODO: Set keys to be ID numbers here!

      return $arrContent;
   }

   /**
    * Searches the Collection database
    *
    * @todo Search User-defined fields
    *
    * @param string $SearchQuery
    * @param integer $SearchFlags[optional]
    * @param integer $SubjectID[optional]
    * @param integer $CreatorID[optional]
    * @param integer $LanguageID[optional]
    * @param integer $RepositoryID[optional]
    * @param integer $LocationID[optional]
    * @param string $RangeValue[optional]
    * @param string $Section[optional]
    * @param string $Shelf[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Collection[]
    */
   public function searchCollections($SearchQuery, $SearchFlags = SEARCH_COLLECTIONS, $SubjectID = 0, $CreatorID = 0, $LanguageID = 0, $RepositoryID = 0, $ClassificationID = 0, $LocationID = 0, $RangeValue = NULL, $Section = NULL, $Shelf = NULL, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {

      $arrPrepQueries = array();
      $arrCollections = array();

      if(!($SearchFlags & SEARCH_COLLECTIONS))
      {
         return $arrCollections;
      }

      if(!$this->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
      {
         $SearchFlags &= ~ (SEARCH_DISABLED_COLLECTIONS | SEARCH_DISABLED_COLLECTIONCONTENT);
      }


      $enabledquery = " AND (";
      if($SearchFlags & SEARCH_ENABLED_COLLECTIONS)
      {
         $enabledquery .= "tblCollections_Collections.Enabled = '1'";

         if($SearchFlags & SEARCH_DISABLED_COLLECTIONS)
         {
            $enabledquery .= " OR tblCollections_Collections.Enabled = '0'";
         }
      }
      else
      {
         $enabledquery = "tblCollections_Collections.Enabled = '0'";
      }
      $enabledquery .= ")";
      $enabledtypes = array();
      $enabledvars = array();

      $repositorytypes = array();
      $repositoryvars = array();
      if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $repositoryquery = " AND (tblCollections_Collections.RepositoryID = ?)";
         $repositorytypes = array('integer');
         $repositoryvars = array($RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $repositoryquery = " AND RepositoryID IN (";
         $repositoryquery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $repositoryquery .= ")";

         $repositorytypes = array_fill(0, count($RepositoryID), 'integer');
         $repositoryvars = $RepositoryID;
      }

      $classificationtypes = array();
      $classificationvars = array();
      if(is_natural($ClassificationID) && $ClassificationID > 0)
      {
         $classificationquery = " AND tblCollections_Collections.ClassificationID = ?";
         $classificationtypes = array('integer');
         $classificationvars = array($ClassificationID);
      }


      if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
      {
         $limitparams = array($Limit, $Offset);
      }
      elseif(is_natural($Offset) && $Offset > 0)
      {
         $limitparams = array(4294967295, $Offset);
      }
      elseif(is_natural($Limit) && $Limit > 0)
      {
         $limitparams = array($Limit);
      }
      else
      {
         $limitparams = array(4294967295);
      }

      if($SubjectID && is_natural($SubjectID))
      {
         $arrIndexSearch['Subject'] = array($SubjectID => NULL);
      }
      elseif($CreatorID && is_natural($CreatorID))
      {
         $arrIndexSearch['Creator'] = array($CreatorID => NULL);
      }
      elseif($LanguageID && is_natural($LanguageID))
      {
         $arrIndexSearch['Language'] = array($LanguageID => NULL);
      }
      elseif($LocationID && is_natural($LocationID))
      {
         $query = "SELECT tblCollections_Collections.*, tblCollections_CollectionLocationIndex.Content as Content FROM tblCollections_Collections JOIN tblCollections_CollectionLocationIndex ON tblCollections_CollectionLocationIndex.CollectionID = tblCollections_Collections.ID WHERE tblCollections_CollectionLocationIndex.LocationID = ? $repositoryquery $enabledquery";
         $types = array_merge(array('integer'), $repositorytypes);
         $vars = array_merge(array($LocationID), $repositoryvars);

         if(isset($RangeValue))
         {
            $query .= " AND RangeValue = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($RangeValue));
         }

         if(isset($Section))
         {
            $query .= " AND Section = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($Section));
         }

         if(isset($Shelf))
         {
            $query .= " AND Shelf = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($Shelf));
         }

         $prepQuery->query = $query . " ORDER BY tblCollections_Collections.SortTitle, tblCollections_Collections.CollectionIdentifier, tblCollections_CollectionLocationIndex.Content";
         $prepQuery->types = $types;
         $prepQuery->vars = $vars;
         $arrPrepQueries[] = $prepQuery;
      }
      else
      {
         $arrWords = $this->createSearchWordArray($SearchQuery);
         $textquery = '';
         $texttypes = array();
         $textvars = array();

         $subquery = '';
         $subtypes = array();
         $subvars = array();

         if(!empty($arrWords))
         {
            $i = 0;
            foreach($arrWords as $word)
            {
               $i++;
               if($word{0} == "-")
               {
                  $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
                  $textquery .= "(Title NOT LIKE ? AND Scope NOT LIKE ?)";
                  array_push($texttypes, 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%");
               }
               else
               {
                  $textquery .= "(Title LIKE ? OR Scope LIKE ?)";
                  array_push($texttypes, 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%");
               }

               if($i < count($arrWords))
               {
                  $textquery .= " AND ";
               }
            }
         }
         else
         {
            //$textquery = "Title LIKE '%%'";
            $textquery = "1=1";
         }

         // First we will try to parse the query for a Classification
         // string of the format #/#, where /# can be appended indefinitely
         // We'll try something easier than before.
         $ID = $this->getCollectionIDForNumber($SearchQuery);
         if($ID)
         {
            $subquery .= " OR ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $ID;
         }

         // If our query is just a number, try to match it
         // directly to an ID from the Collections table.
         if(is_natural($SearchQuery))
         {
            $subquery .= " OR ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $SearchQuery;
         }

         if($textquery || $subquery || $repositoryquery || $enabledquery)
         {
            $wherequery = "WHERE ($textquery $subquery) $repositoryquery $classificationquery $enabledquery";
            $wheretypes = array_merge($texttypes, $subtypes, $repositorytypes, $classificationtypes, $enabledtypes);
            $wherevars = array_merge($textvars, $subvars, $repositoryvars, $classificationvars, $enabledvars);
         }
         else
         {
            $wherequery = '';
            $wheretypes = array();
            $wherevars = array();
         }

         $prepQuery->query = "SELECT ID, Title, SortTitle, ClassificationID, InclusiveDates, CollectionIdentifier FROM tblCollections_Collections $wherequery ORDER BY SortTitle, CollectionIdentifier";
         //$prepQuery->query = "SELECT * FROM tblCollections_Collections $wherequery ORDER BY CollectionIdentifier, SortTitle";
         $prepQuery->types = $wheretypes;
         $prepQuery->vars = $wherevars;
         $arrPrepQueries[] = $prepQuery;

         if($SearchFlags & SEARCH_SUBJECTS)
         {
            $arrIndexSearch['Subject'] = $this->searchSubjects($SearchQuery);
         }

         if($SearchFlags & SEARCH_CREATORS)
         {
            $arrIndexSearch['Creator'] = $this->searchCreators($SearchQuery);
         }

         if($SearchFlags & SEARCH_LANGUAGES)
         {
            $arrIndexSearch['Language'] = $this->searchLanguages($SearchQuery);
         }
      }


      if(!empty($arrIndexSearch))
      {
         foreach($arrIndexSearch as $Type => $arrObjects)
         {
            if(!empty($arrObjects))
            {
               foreach($arrObjects as $ID => $junk)
               {
                  $selectfields = "tblCollections_Collections.ID, tblCollections_Collections.Title, tblCollections_Collections.SortTitle, tblCollections_Collections.ClassificationID, tblCollections_Collections.InclusiveDates, tblCollections_Collections.CollectionIdentifier";
                  $prepQuery->query = "SELECT {$selectfields} FROM tblCollections_Collections JOIN {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")} ON {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")}.CollectionID = tblCollections_Collections.ID WHERE {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")}.{$this->mdb2->quoteIdentifier("{$Type}ID")} = ? $repositoryquery $enabledquery ORDER BY tblCollections_Collections.SortTitle, tblCollections_Collections.CollectionIdentifier";
                  $prepQuery->types = array_merge(array('integer'), $repositorytypes, $enabledtypes);
                  $prepQuery->vars = array_merge(array($ID), $repositoryvars, $enabledvars);
                  $arrPrepQueries[] = $prepQuery;

                  //$arrQueries[] = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_{$Type}Index ON tblCollections_{$Type}Index.CollectionID = tblCollections_Collections.ID WHERE tblCollections_{$Type}Index.{$Type}ID = '$ID' $repositoryquery $enabledquery ORDER BY tblCollections_Collections.CollectionIdentifier, tblCollections_Collections.SortTitle $limitquery";
               }
            }
         }
      }


      foreach($arrPrepQueries as $prepQuery)
      {
         // Run query to list collections
         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($prepQuery->query, $prepQuery->types, MDB2_PREPARE_RESULT);
         if(PEAR::isError($prep))
         {
            echo($prepQuery->query);
            trigger_error($prep->getMessage(), E_USER_ERROR);
         }
         $result = $prep->execute($prepQuery->vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            if($row['Content'])
            {
               $objContent = New CollectionContent(0);
               $objContent->Title = $row['Content'];

               unset($row['Content']);
            }

            if(!isset($arrCollections[$row['ID']]))
            {
               $arrCollections[$row['ID']] = New Collection($row);
            }

            if($objContent)
            {
               $arrCollections[$row['ID']]->Content[] = $objContent;
            }
         }
         $result->free();
         $prep->free();
      }

      if($SearchFlags & SEARCH_COLLECTIONCONTENT)
      {
         $subquery = '';
         $subtypes = array();
         $subvars = array();

         // Note: we can convienently reuse the $textquery from above since the Fields being
         // searched are the same, we just have to prepend the table name
         $textquery = str_replace("Title ", "tblCollections_Content.Title ", $textquery);
         $textquery = str_replace("Scope ", "tblCollections_Content.Description ", $textquery);

         // If our query is just a number, try to match it
         // directly to an ID from the Collections table.
         if(is_natural($SearchQuery))
         {
            $subquery .= " OR tblCollections_Content.ID = '$SearchQuery'";
            $subquery .= " OR tblCollections_Content.ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $SearchQuery;
         }

         if(is_array($RepositoryID) || (is_natural($RepositoryID) && $RepositoryID > 0))
         {
            $subquery .= $repositoryquery;
            $subtypes = array_merge($subtypes, $repositorytypes);
            $subvars = array_merge($subvars, $repositoryvars);
         }

         $userfieldquery = '(1 = 0)';
         $userfieldtypes = array();
         $userfieldvars = array();
         if($SearchFlags & SEARCH_USERFIELDS)
         {
            $userfieldquery = str_replace("tblCollections_Content.Title ", "tblCollections_UserFields.Title ", $textquery);
            $userfieldquery = str_replace("tblCollections_Content.Description ", "tblCollections_UserFields.Value ", $userfieldquery);
            $userfieldtypes = $texttypes;
            $userfieldvars = $textvars;
         }

         // Run query to find content         
         $query = "SELECT tblCollections_Content.*, tblCollections_Collections.ClassificationID as ClassificationID FROM tblCollections_Content JOIN tblCollections_Collections ON tblCollections_Collections.ID = tblCollections_Content.CollectionID JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID LEFT JOIN (SELECT ContentID FROM tblCollections_UserFields WHERE $userfieldquery) AS tblCollections_UserFields ON tblCollections_UserFields.ContentID = tblCollections_Content.ID WHERE ($textquery OR NOT (tblCollections_UserFields.ContentID IS NULL)) $subquery $enabledquery ORDER BY tblCollections_Content.SortOrder";
         $types = array_merge($userfieldtypes, $texttypes, $subtypes, $enabledtypes);
         $vars = array_merge($userfieldvars, $textvars, $subvars, $enabledvars);

         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($query, $types, MDB2_PREPARE_RESULT);
         $result = $prep->execute($vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            $arrLevelContainers = $this->getAllLevelContainers();
         }

         while($row = $result->fetchRow())
         {
            $objContent = New CollectionContent($row);
            $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];

            $arrContent[$objContent->toString(LINK_NONE, true, true, true, true)] = $objContent;
         }
         $result->free();
         $prep->free();

         // Now we need to sort the content and add it to the final object
         if(!empty($arrContent))
         {
            natcaseksort($arrContent);

            $collectionprep = $this->mdb2->prepare('SELECT * FROM tblCollections_Collections WHERE ID = ?', 'integer', MDB2_PREPARE_RESULT);
            foreach($arrContent as &$objContent)
            {
               if(!isset($arrCollections[$objContent->CollectionID]))
               {
                  // Calling the Collection dbLoad method will end up taking more time than just running the
                  // query to get the basic information
                  $collectionresult = $collectionprep->execute($objContent->CollectionID);
                  if(PEAR::isError($collectionresult))
                  {
                     trigger_error($collectionresult->getMessage(), E_USER_ERROR);
                  }

                  $collectionrow = $collectionresult->fetchRow();
                  $collectionresult->free();

                  $arrCollections[$objContent->CollectionID] = New Collection($collectionrow);
               }

               $arrCollections[$objContent->CollectionID]->Content[$objContent->ID] = $objContent;
            }
            $collectionprep->free();
         }
      }

      reset($arrCollections);

      return $arrCollections;
   }

   public function searchCollectionsByBook($SearchQuery, $SearchFlags = SEARCH_COLLECTIONS, $BookId = 0, $SubjectID = 0, $CreatorID = 0, $LanguageID = 0, $RepositoryID = 0, $LocationID = 0, $RangeValue = NULL, $Section = NULL, $Shelf = NULL, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {

      $arrPrepQueries = array();

      if(!($SearchFlags & SEARCH_COLLECTIONS))
      {
         return $arrCollections;
      }

      if(!$this->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
      {
         $SearchFlags &= ~ (SEARCH_DISABLED_COLLECTIONS | SEARCH_DISABLED_COLLECTIONCONTENT);
      }

      $enabledquery = " AND (";
      if($SearchFlags & SEARCH_ENABLED_COLLECTIONS)
      {
         $enabledquery .= "tblCollections_Collections.Enabled = '1'";

         if($SearchFlags & SEARCH_DISABLED_COLLECTIONS)
         {
            $enabledquery .= " OR tblCollections_Collections.Enabled = '0'";
         }
      }
      else
      {
         $enabledquery = "tblCollections_Collections.Enabled = '0'";
      }
      $enabledquery .= ")";
      $enabledtypes = array();
      $enabledvars = array();

      $repositorytypes = array();
      $repositoryvars = array();
      if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $repositoryquery = " AND (tblCollections_Collections.RepositoryID = ?)";
         $repositorytypes = array('integer');
         $repositoryvars = array($RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $repositoryquery = " AND RepositoryID IN (";
         $repositoryquery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $repositoryquery .= ")";

         $repositorytypes = array_fill(0, count($RepositoryID), 'integer');
         $repositoryvars = $RepositoryID;
      }

      if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
      {
         $limitparams = array($Limit, $Offset);
      }
      elseif(is_natural($Offset) && $Offset > 0)
      {
         $limitparams = array(4294967295, $Offset);
      }
      elseif(is_natural($Limit) && $Limit > 0)
      {
         $limitparams = array($Limit);
      }
      else
      {
         $limitparams = array(4294967295);
      }

      if($SubjectID && is_natural($SubjectID))
      {
         $arrIndexSearch['Subject'] = array($SubjectID => NULL);
      }
      elseif($CreatorID && is_natural($CreatorID))
      {
         $arrIndexSearch['Creator'] = array($CreatorID => NULL);
      }
      elseif($BookID && is_natural($BookID))
      {
         $arrIndexSearch['Book'] = array($BookID => NULL);
      }
      elseif($LanguageID && is_natural($LanguageID))
      {
         $arrIndexSearch['Language'] = array($LanguageID => NULL);
      }
      elseif($LocationID && is_natural($LocationID))
      {
         $query = "SELECT tblCollections_Collections.*, tblCollections_CollectionLocationIndex.Content as Content FROM tblCollections_Collections JOIN tblCollections_CollectionLocationIndex ON tblCollections_CollectionLocationIndex.CollectionID = tblCollections_Collections.ID WHERE tblCollections_CollectionLocationIndex.LocationID = ? $repositoryquery $enabledquery";
         $types = array_merge(array('integer'), $repositoryTypes);
         $vars = array_merge(array($LocationID), $repositoryVars);

         if(isset($RangeValue))
         {
            $query .= " AND RangeValue = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($RangeValue));
         }

         if(isset($Section))
         {
            $query .= " AND Section = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($Section));
         }

         if(isset($Shelf))
         {
            $query .= " AND Shelf = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($Shelf));
         }

         $prepQuery->query = $query . " ORDER BY tblCollections_Collections.SortTitle, tblCollections_Collections.CollectionIdentifier, tblCollections_CollectionLocationIndex.Content";
         $prepQuery->types = $types;
         $prepQuery->vars = $vars;
         $arrPrepQueries[] = $prepQuery;
      }
      else
      {
         $arrWords = $this->createSearchWordArray($SearchQuery);
         $textquery = '';
         $texttypes = array();
         $textvars = array();

         $subquery = '';
         $subtypes = array();
         $subvars = array();

         if(!empty($arrWords))
         {
            $i = 0;
            foreach($arrWords as $word)
            {
               $i++;
               if($word{0} == "-")
               {
                  $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
                  $textquery .= "(Title NOT LIKE ? AND Scope NOT LIKE ?)";
                  array_push($texttypes, 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%");
               }
               else
               {
                  $textquery .= "(Title LIKE ? OR Scope LIKE ?)";
                  array_push($texttypes, 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%");
               }

               if($i < count($arrWords))
               {
                  $textquery .= " AND ";
               }
            }
         }
         else
         {
//            $textquery = "Title LIKE '%%'";
            $textquery = "1=1";
         }

         // First we will try to parse the query for a Classification
         // string of the format #/#, where /# can be appended indefinitely
         // We'll try something easier than before.
         $ID = $this->getCollectionIDForNumber($SearchQuery);
         if($ID)
         {
            $subquery .= " OR ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $ID;
         }

         // If our query is just a number, try to match it
         // directly to an ID from the Collections table.
         if(is_natural($SearchQuery))
         {
            $subquery .= " OR ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $SearchQuery;
         }

         if($textquery || $subquery || $repositoryquery || $enabledquery)
         {
            $wherequery = "WHERE ($textquery $subquery) $repositoryquery $enabledquery";
            $wheretypes = array_merge($texttypes, $subtypes, $repositorytypes, $enabledtypes);
            $wherevars = array_merge($textvars, $subvars, $repositoryvars, $enabledvars);
         }
         else
         {
            $wherequery = '';
            $wheretypes = array();
            $wherevars = array();
         }

         $prepQuery->query = "SELECT * FROM tblCollections_Collections $wherequery ORDER BY SortTitle, CollectionIdentifier";
         $prepQuery->types = $wheretypes;
         $prepQuery->vars = $wherevars;
         $arrPrepQueries[] = $prepQuery;

         if($SearchFlags & SEARCH_SUBJECTS)
         {
            $arrIndexSearch['Subject'] = $this->searchSubjects($SearchQuery);
         }

         if($SearchFlags & SEARCH_BOOKS)
         {
            $arrIndexSearch['Book'] = $this->searchBooks($SearchQuery);
         }

         if($SearchFlags & SEARCH_CREATORS)
         {
            $arrIndexSearch['Creator'] = $this->searchCreators($SearchQuery);
         }

         if($SearchFlags & SEARCH_LANGUAGES)
         {
            $arrIndexSearch['Language'] = $this->searchLanguages($SearchQuery);
         }
      }


      if(!empty($arrIndexSearch))
      {
         foreach($arrIndexSearch as $Type => $arrObjects)
         {
            if(!empty($arrObjects))
            {
               foreach($arrObjects as $ID => $junk)
               {
                  $prepQuery->query = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")} ON {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")}.CollectionID = tblCollections_Collections.ID WHERE {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")}.{$this->mdb2->quoteIdentifier("{$Type}ID")} = ? $repositoryquery $enabledquery ORDER BY tblCollections_Collections.SortTitle, tblCollections_Collections.CollectionIdentifier";
                  $prepQuery->types = array_merge(array('integer'), $repositorytypes, $enabledtypes);
                  $prepQuery->vars = array_merge(array($ID), $repositoryvars, $enabledvars);
                  $arrPrepQueries[] = $prepQuery;

                  //$arrQueries[] = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_{$Type}Index ON tblCollections_{$Type}Index.CollectionID = tblCollections_Collections.ID WHERE tblCollections_{$Type}Index.{$Type}ID = '$ID' $repositoryquery $enabledquery ORDER BY tblCollections_Collections.CollectionIdentifier, tblCollections_Collections.SortTitle $limitquery";
               }
            }
         }
      }


      foreach($arrPrepQueries as $prepQuery)
      {
         // Run query to list collections
         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($prepQuery->query, $prepQuery->types, MDB2_PREPARE_RESULT);
         $result = $prep->execute($prepQuery->vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            if($row['Content'])
            {
               $objContent = New CollectionContent(0);
               $objContent->Title = $row['Content'];

               unset($row['Content']);
            }

            if(!isset($arrCollections[$row['ID']]))
            {
               $arrCollections[$row['ID']] = New Collection($row);
            }

            if($objContent)
            {
               $arrCollections[$row['ID']]->Content[] = $objContent;
            }
         }
         $result->free();
         $prep->free();
      }

      if($SearchFlags & SEARCH_COLLECTIONCONTENT)
      {
         $subquery = '';
         $subtypes = array();
         $subvars = array();

         // Note: we can convienently reuse the $textquery from above since the Fields being
         // searched are the same, we just have to prepend the table name
         $textquery = str_replace("Title ", "tblCollections_Content.Title ", $textquery);
         $textquery = str_replace("Scope ", "tblCollections_Content.Description ", $textquery);

         // If our query is just a number, try to match it
         // directly to an ID from the Collections table.
         if(is_natural($SearchQuery))
         {
            $subquery .= " OR tblCollections_Content.ID = '$SearchQuery'";
            $subquery .= " OR tblCollections_Content.ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $SearchQuery;
         }

         if(is_array($RepositoryID) || (is_natural($RepositoryID) && $RepositoryID > 0))
         {
            $subquery .= $repositoryquery;
            $subtypes = array_merge($subtypes, $repositorytypes);
            $subvars = array_merge($subvars, $repositoryvars);
         }

         $userfieldquery = '(1 = 0)';
         $userfieldtypes = array();
         $userfieldvars = array();
         if($SearchFlags & SEARCH_USERFIELDS)
         {
            $userfieldquery = str_replace("tblCollections_Content.Title ", "tblCollections_UserFields.Title ", $textquery);
            $userfieldquery = str_replace("tblCollections_Content.Description ", "tblCollections_UserFields.Value ", $userfieldquery);
            $userfieldtypes = $texttypes;
            $userfieldvars = $textvars;
         }

         // Run query to find content         
         $query = "SELECT tblCollections_Content.*, tblCollections_Collections.ClassificationID as ClassificationID FROM tblCollections_Content JOIN tblCollections_Collections ON tblCollections_Collections.ID = tblCollections_Content.CollectionID JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID LEFT JOIN (SELECT ContentID FROM tblCollections_UserFields WHERE $userfieldquery) AS tblCollections_UserFields ON tblCollections_UserFields.ContentID = tblCollections_Content.ID WHERE ($textquery OR NOT (tblCollections_UserFields.ContentID IS NULL)) $subquery $enabledquery ORDER BY tblCollections_Content.SortOrder";
         $types = array_merge($userfieldtypes, $texttypes, $subtypes, $enabledtypes);
         $vars = array_merge($userfieldvars, $textvars, $subvars, $enabledvars);

         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($query, $types, MDB2_PREPARE_RESULT);
         $result = $prep->execute($vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            $arrLevelContainers = $this->getAllLevelContainers();
         }

         while($row = $result->fetchRow())
         {
            $objContent = New CollectionContent($row);
            $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];

            $arrContent[$objContent->toString(LINK_NONE, true, true, true, true)] = $objContent;
         }
         $result->free();
         $prep->free();

         // Now we need to sort the content and add it to the final object
         if(!empty($arrContent))
         {
            natcaseksort($arrContent);

            $collectionprep = $this->mdb2->prepare('SELECT * FROM tblCollections_Collections WHERE ID = ?', 'integer', MDB2_PREPARE_RESULT);
            foreach($arrContent as &$objContent)
            {
               if(!isset($arrCollections[$objContent->CollectionID]))
               {
                  // Calling the Collection dbLoad method will end up taking more time than just running the
                  // query to get the basic information
                  $collectionresult = $collectionprep->execute($objContent->CollectionID);
                  if(PEAR::isError($collectionresult))
                  {
                     trigger_error($collectionresult->getMessage(), E_USER_ERROR);
                  }

                  $collectionrow = $collectionresult->fetchRow();
                  $collectionresult->free();

                  $arrCollections[$objContent->CollectionID] = New Collection($collectionrow);
               }

               $arrCollections[$objContent->CollectionID]->Content[$objContent->ID] = $objContent;
            }
            $collectionprep->free();
         }
      }

      reset($arrCollections);

      return $arrCollections;
   }

   ////////////////////////////////////////////////////////////

   /**
    * Searches the CollectionContent database and returns an array of sorted Classification objects
    * that have the matching Collections in the Classifications' Collections[] member variable, and
    * if $SearchContent is true, the matching Content in the Collections' Content member variable.
    *
    * @todo Search User-defined fields
    *
    * @param string $SearchQuery
    * @param integer $SearchFlags[optional]
    * @param integer $SubjectID[optional]
    * @param integer $CreatorID[optional]
    * @param integer $LanguageID[optional]
    * @param integer $RepositoryID[optional]
    * @param integer $LocationID[optional]
    * @param string $RangeValue[optional]
    * @param string $Section[optional]
    * @param string $Shelf[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    */
   public function searchCollectionsByClassification($SearchQuery, $SearchFlags = SEARCH_COLLECTIONS, $SubjectID = 0, $CreatorID = 0, $LanguageID = 0, $RepositoryID = 0, $LocationID = 0, $RangeValue = NULL, $Section = NULL, $Shelf = NULL, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $arrPrepQueries = array();
      $arrCollections = array();

      if(!($SearchFlags & SEARCH_COLLECTIONS))
      {
         return $arrCollections;
      }

      if(!$this->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
      {
         $SearchFlags &= ~ (SEARCH_DISABLED_COLLECTIONS | SEARCH_DISABLED_COLLECTIONCONTENT);
      }


      $arrClassifications = $this->getAllClassifications();

      $enabledquery = " AND (";
      if($SearchFlags & SEARCH_ENABLED_COLLECTIONS)
      {
         $enabledquery .= "tblCollections_Collections.Enabled = '1'";

         if($SearchFlags & SEARCH_DISABLED_COLLECTIONS)
         {
            $enabledquery .= " OR tblCollections_Collections.Enabled = '0'";
         }
      }
      else
      {
         $enabledquery = "tblCollections_Collections.Enabled = '0'";
      }
      $enabledquery .= ")";
      $enabledtypes = array();
      $enabledvars = array();

      $repositorytypes = array();
      $repositoryvars = array();
      if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
      {
         $repositoryquery = " AND (tblCollections_Collections.RepositoryID = ?)";
         $repositorytypes = array('integer');
         $repositoryvars = array($RepositoryID);
      }
      elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
      {
         $repositoryquery = " AND RepositoryID IN (";
         $repositoryquery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
         $repositoryquery .= ")";

         $repositorytypes = array_fill(0, count($RepositoryID), 'integer');
         $repositoryvars = $RepositoryID;
      }


      if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
      {
         $limitparams = array($Limit, $Offset);
      }
      elseif(is_natural($Offset) && $Offset > 0)
      {
         $limitparams = array(4294967295, $Offset);
      }
      elseif(is_natural($Limit) && $Limit > 0)
      {
         $limitparams = array($Limit);
      }
      else
      {
         $limitparams = array(4294967295);
      }

      if($SubjectID && is_natural($SubjectID))
      {
         $arrIndexSearch['Subject'] = array($SubjectID => NULL);
      }
      elseif($CreatorID && is_natural($CreatorID))
      {
         $arrIndexSearch['Creator'] = array($CreatorID => NULL);
      }
      elseif($LanguageID && is_natural($LanguageID))
      {
         $arrIndexSearch['Language'] = array($LanguageID => NULL);
      }
      elseif($LocationID && is_natural($LocationID))
      {
         $query = "SELECT tblCollections_Collections.*, tblCollections_CollectionLocationIndex.Content as Content FROM tblCollections_Collections JOIN tblCollections_CollectionLocationIndex ON tblCollections_CollectionLocationIndex.CollectionID = tblCollections_Collections.ID WHERE tblCollections_CollectionLocationIndex.LocationID = ? $repositoryquery $enabledquery";
         $types = array_merge(array('integer'), $repositorytypes);
         $vars = array_merge(array($LocationID), $repositoryvars);

         if(isset($RangeValue))
         {
            $query .= " AND RangeValue = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($RangeValue));
         }

         if(isset($Section))
         {
            $query .= " AND Section = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($Section));
         }

         if(isset($Shelf))
         {
            $query .= " AND Shelf = ?";
            $types = array_merge($types, array('text'));
            $vars = array_merge($vars, array($Shelf));
         }

         $prepQuery->query = $query . " ORDER BY tblCollections_Collections.CollectionIdentifier, tblCollections_Collections.SortTitle, tblCollections_CollectionLocationIndex.Content";
         $prepQuery->types = $types;
         $prepQuery->vars = $vars;
         $arrPrepQueries[] = $prepQuery;
      }
      else
      {
         $arrWords = $this->createSearchWordArray($SearchQuery);
         $textquery = '';
         $texttypes = array();
         $textvars = array();

         $subquery = '';
         $subtypes = array();
         $subvars = array();

         if(!empty($arrWords))
         {
            $i = 0;
            foreach($arrWords as $word)
            {
               $i++;
               if($word{0} == "-")
               {
                  $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
                  $textquery .= "(Title NOT LIKE ? AND Scope NOT LIKE ?)";
                  array_push($texttypes, 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%");
               }
               else
               {
                  $textquery .= "(Title LIKE ? OR Scope LIKE ?)";
                  array_push($texttypes, 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%");
               }

               if($i < count($arrWords))
               {
                  $textquery .= " AND ";
               }
            }
         }
         else
         {
//            $textquery = "Title LIKE '%%'";
            $textquery = "1=1";
         }

         // First we will try to parse the query for a Classification
         // string of the format #/#, where /# can be appended indefinitely
         // We'll try something easier than before.
         $ID = $this->getCollectionIDForNumber($SearchQuery);
         if($ID)
         {
            $subquery .= " OR ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $ID;
         }

         // If our query is just a number, try to match it
         // directly to an ID from the Collections table.
         if(is_natural($SearchQuery))
         {
            $subquery .= " OR ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $SearchQuery;
         }

         if($textquery || $subquery || $repositoryquery || $enabledquery)
         {
            $wherequery = "WHERE ($textquery $subquery) $repositoryquery $enabledquery";
            $wheretypes = array_merge($texttypes, $subtypes, $repositorytypes, $enabledtypes);
            $wherevars = array_merge($textvars, $subvars, $repositoryvars, $enabledvars);
         }
         else
         {
            $wherequery = '';
            $wheretypes = array();
            $wherevars = array();
         }

         $prepQuery->query = "SELECT * FROM tblCollections_Collections $wherequery ORDER BY CollectionIdentifier, SortTitle";
         $prepQuery->types = $wheretypes;
         $prepQuery->vars = $wherevars;
         $arrPrepQueries[] = $prepQuery;

         if($SearchFlags & SEARCH_SUBJECTS)
         {
            $arrIndexSearch['Subject'] = $this->searchSubjects($SearchQuery);
         }

         if($SearchFlags & SEARCH_CREATORS)
         {
            $arrIndexSearch['Creator'] = $this->searchCreators($SearchQuery);
         }

         if($SearchFlags & SEARCH_LANGUAGES)
         {
            $arrIndexSearch['Language'] = $this->searchLanguages($SearchQuery);
         }
      }


      if(!empty($arrIndexSearch))
      {
         foreach($arrIndexSearch as $Type => $arrObjects)
         {
            if(!empty($arrObjects))
            {
               foreach($arrObjects as $ID => $junk)
               {
                  $prepQuery->query = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")} ON {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")}.CollectionID = tblCollections_Collections.ID WHERE {$this->mdb2->quoteIdentifier("tblCollections_Collection{$Type}Index")}.{$this->mdb2->quoteIdentifier("{$Type}ID")} = ? $repositoryquery $enabledquery ORDER BY tblCollections_Collections.CollectionIdentifier, tblCollections_Collections.SortTitle";
                  $prepQuery->types = array_merge(array('integer'), $repositorytypes, $enabledtypes);
                  $prepQuery->vars = array_merge(array($ID), $repositoryvars, $enabledvars);
                  $arrPrepQueries[] = $prepQuery;

                  //$arrQueries[] = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_{$Type}Index ON tblCollections_{$Type}Index.CollectionID = tblCollections_Collections.ID WHERE tblCollections_{$Type}Index.{$Type}ID = '$ID' $repositoryquery $enabledquery ORDER BY tblCollections_Collections.CollectionIdentifier, tblCollections_Collections.SortTitle $limitquery";
               }
            }
         }
      }


      foreach($arrPrepQueries as $prepQuery)
      {
         // Run query to list collections
         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($prepQuery->query, $prepQuery->types, MDB2_PREPARE_RESULT);
         $result = $prep->execute($prepQuery->vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            if($row['Content'])
            {
               $objContent = New CollectionContent(0);
               $objContent->Title = $row['Content'];

               unset($row['Content']);
            }

            if(!isset($arrClassifications[$row['ClassificationID']]->Collections[$row['ID']]))
            {
               $arrClassifications[$row['ClassificationID']]->Collections[$row['ID']] = New Collection($row);
            }

            if($objContent)
            {
               $arrClassifications[$row['ClassificationID']]->Collections[$row['ID']]->Content[] = $objContent;
            }

            // Set an internal flag to indicate that the classification
            // should not be removed.  We can't just check for entries in Collections[]
            // because we want to make sure we don't remove parents that have children with Collections
            // (which may not contain any Collections).
            $ID = $arrClassifications[$row['ClassificationID']]->ID;
            while($ID)
            {
               $arrClassifications[$ID]->_ContainsCollections = true;
               $ID = $arrClassifications[$ID]->ParentID;
            }
         }
         $result->free();
         $prep->free();
      }

      if($SearchFlags & SEARCH_COLLECTIONCONTENT)
      {
         $subquery = '';
         $subtypes = array();
         $subvars = array();

         // Note: we can convienently reuse the $textquery from above since the Fields being
         // searched are the same, we just have to prepend the table name
         $textquery = str_replace("Title ", "tblCollections_Content.Title ", $textquery);
         $textquery = str_replace("Scope ", "tblCollections_Content.Description ", $textquery);

         // If our query is just a number, try to match it
         // directly to an ID from the Collections table.
         if(is_natural($SearchQuery))
         {
            $subquery .= " OR tblCollections_Content.ID = '$SearchQuery'";
            $subquery .= " OR tblCollections_Content.ID = ?";
            $subtypes[] = 'integer';
            $subvars[] = $SearchQuery;
         }

         if(is_array($RepositoryID) || (is_natural($RepositoryID) && $RepositoryID > 0))
         {
            $subquery .= $repositoryquery;
            $subtypes = array_merge($subtypes, $repositorytypes);
            $subvars = array_merge($subvars, $repositoryvars);
         }

         $userfieldquery = '(1 = 0)';
         $userfieldtypes = array();
         $userfieldvars = array();
         if($SearchFlags & SEARCH_USERFIELDS)
         {
            $userfieldquery = str_replace("tblCollections_Content.Title ", "tblCollections_UserFields.Title ", $textquery);
            $userfieldquery = str_replace("tblCollections_Content.Description ", "tblCollections_UserFields.Value ", $userfieldquery);
            $userfieldtypes = $texttypes;
            $userfieldvars = $textvars;
         }

         // Run query to find content        
         $query = "SELECT tblCollections_Content.*, tblCollections_Collections.ClassificationID as ClassificationID FROM tblCollections_Content JOIN tblCollections_Collections ON tblCollections_Collections.ID = tblCollections_Content.CollectionID JOIN tblCollections_LevelContainers ON tblCollections_LevelContainers.ID = tblCollections_Content.LevelContainerID LEFT JOIN (SELECT ContentID FROM tblCollections_UserFields WHERE $userfieldquery) AS tblCollections_UserFields ON tblCollections_UserFields.ContentID = tblCollections_Content.ID WHERE ($textquery OR NOT (tblCollections_UserFields.ContentID IS NULL)) $subquery $enabledquery ORDER BY tblCollections_Content.SortOrder";
         $types = array_merge($userfieldtypes, $texttypes, $subtypes, $enabledtypes);
         $vars = array_merge($userfieldvars, $textvars, $subvars, $enabledvars);

         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($query, $types, MDB2_PREPARE_RESULT);
         $result = $prep->execute($vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            $arrLevelContainers = $this->getAllLevelContainers();
         }

         $collectionprep = $this->mdb2->prepare('SELECT * FROM tblCollections_Collections WHERE ID = ?', 'integer', MDB2_PREPARE_RESULT);
         while($row = $result->fetchRow())
         {
            if(!isset($arrClassifications[$row['ClassificationID']]->Collections[$row['CollectionID']]))
            {
               // See if we can skip grabbing information about the collections.
               if(!$arrClassifications[$row['ClassificationID']]->Collections[$row['CollectionID']])
               {
                  // Calling the Collection dbLoad method will end up taking more time than just running the
                  // query to get the basic information
                  $collectionresult = $collectionprep->execute($row['CollectionID']);
                  if(PEAR::isError($result))
                  {
                     trigger_error($result->getMessage(), E_USER_ERROR);
                  }
                  $collectionrow = $collectionresult->fetchRow();

                  $arrClassifications[$row['ClassificationID']]->Collections[$row['CollectionID']] = New Collection($collectionrow);
                  $collectionresult->free();

                  // Set an internal flag to indicate that the classification
                  // should not be removed.  We can't just check for entries in Collections[]
                  // because we want to make sure we don't remove parents that have children with Collections
                  // (which may not contain any Collections).
                  $ID = $arrClassifications[$row['ClassificationID']]->ID;
                  while($ID)
                  {
                     $arrClassifications[$ID]->_ContainsCollections = true;
                     $ID = $arrClassifications[$ID]->ParentID;
                  }
               }
            }

            $objContent = New CollectionContent($row);
            $objContent->LevelContainer = $arrLevelContainers[$objContent->LevelContainerID];
            $objContent->ClassificationID = $row['ClassificationID'];

            $arrContent[$objContent->toString(LINK_NONE, true, true, true, true)] = $objContent;

            //                $this->MemoryCache->Objects['Collection'][$objTrans->CollectionID] = $objTrans->Collection;
            //                $objTrans = $objContent;
            //                while($objTrans && !isset($this->MemoryCache->Objects['CollectionContent'][$objTrans->ID]))
            //                {
            //                    $this->MemoryCache->Objects['CollectionContent'][$objTrans->ID] = $objTrans;
            //                    $objTrans = $objTrans->Parent;
            //                }
         }
         //            unset($this->MemoryCache->Objects['CollectionContent']);
         //            unset($this->MemoryCache->Objects['Collection']);

         $collectionprep->free();
         $result->free();
         $prep->free();

         // Now we need to sort the content and add it to the final object
         if(!empty($arrContent))
         {
            natcaseksort($arrContent);

            foreach($arrContent as $objContent)
            {
               $arrClassifications[$objContent->ClassificationID]->Collections[$objContent->CollectionID]->Content[$objContent->ID] = $objContent;
            }
         }
      }

      // Remove any classifications that didn't get a hit
      foreach($arrClassifications as $objClassification)
      {
         if(!$objClassification->_ContainsCollections)
         {
            unset($arrClassifications[$objClassification->ID]);

            if($objClassification->ParentID && $arrClassifications[$objClassification->ParentID])
            {
               unset($arrClassifications[$objClassification->ParentID]->Classifications[$objClassification->ID]);
            }

            uasort($objClassification->Collections, create_function('$a,$b', 'return strnatcmp($a->CollectionIdentifier, $b->CollectionIdentifier);'));
         }
      }

      reset($arrClassifications);

      return $arrClassifications;
   }

   /**
    * Searches the ExtentUnit database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ExtentUnit[]
    */
   public function searchExtentUnits($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCollections_ExtentUnits', 'ExtentUnit', 'ExtentUnit', 'ExtentUnit', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the LevelContainer database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return LevelContainer[]
    */
   public function searchLevelContainers($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCollections_LevelContainers', 'LevelContainer', 'LevelContainer', 'LevelContainer', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the Location database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Location[]
    */
   public function searchLocations($SearchQuery, $RepositoryID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {

      if(!$RepositoryID || (!is_array($RepositoryID) && !is_natural($RepositoryID)) || empty($RepositoryID))
      {
         return $this->searchTable($SearchQuery, 'tblCollections_Locations', 'Location', 'Location', 'Location', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
      }
      else
      {

         $arrObjects = $this->searchTable($SearchQuery, 'tblCollections_Locations', 'Location', 'Location', 'Location', 'RepositoryLimit = ?', array('integer'), array('0'), NULL, array(), array(), $Limit, $Offset);

         $arrWords = $this->createSearchWordArray($SearchQuery);

         $wherequery = '';
         $wheretypes = array();
         $wherevars = array();

         if(!empty($arrWords))
         {
            foreach($arrWords as $word)
            {
               $i++;
               if($word{0} == '-')
               {
                  $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
                  $wherequery .= '(';


                  $wherequery .= "tblCollections_Locations.Location NOT LIKE ?";
                  $wheretypes[] = 'text';
                  $wherevars[] = "%$word%";


                  $wherequery .= ')';
               }
               else
               {
                  $wherequery .= '(';
                  $wherequery .= "tblCollections_Locations.Location LIKE ?";
                  $wheretypes[] = 'text';
                  $wherevars[] = "%$word%";

                  $wherequery .= ')';
               }

               if($i < count($arrWords))
               {
                  $wherequery .= " AND ";
               }
            }
         }

         if(!$wherequery)
         {
            $wherequery = '1 = 1';
         }

         // If our query is just a number, try to match it
         // directly to an ID from the table.
         if(is_natural($SearchQuery) && $SearchQuery > 0)
         {
            $wherequery .= " OR tblCollections_Locations.ID = ?";
            $wheretypes[] = 'integer';
            $wherevars[] = $SearchQuery;
         }

         if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
         {
            $limitparams = array($Limit, $Offset);
         }
         elseif(is_natural($Offset) && $Offset > 0)
         {
            $limitparams = array(4294967295, $Offset);
         }
         elseif(is_natural($Limit) && $Limit > 0)
         {
            $limitparams = array($Limit);
         }
         else
         {
            $limitparams = array(4294967295);
         }

         $wherequery = "WHERE (tblCollections_Locations.RepositoryLimit = 1) AND ({$wherequery})";

         //TODO:
         if(!is_array($RepositoryID) && is_natural($RepositoryID) && $RepositoryID > 0)
         {
            $wherequery .= " AND (RepositoryID = ?)";
            $wheretypes[] = 'integer';
            $wherevars[] = $RepositoryID;
         }
         elseif($RepositoryID && is_array($RepositoryID) && !empty($RepositoryID))
         {
            $wherequery .= " AND RepositoryID IN (";
            $wherequery .= implode(', ', array_fill(0, count($RepositoryID), '?'));
            $wherequery .= ")";

            $wheretypes = array_merge($wheretypes, array_fill(0, count($RepositoryID), 'integer'));
            $wherevars = array_merge($wherevars, $RepositoryID);
         }


         $orderquery = "ORDER BY tblCollections_Locations.Location";

         $tablestring = "tblCollections_Locations JOIN tblCollections_LocationRepositoryIndex ON (tblCollections_Locations.ID = tblCollections_LocationRepositoryIndex.LocationID)";

         $selectFields = "tblCollections_Locations.*";

         $query = "SELECT " . $selectFields . " FROM $tablestring $wherequery $orderquery";

         call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
         $prep = $this->mdb2->prepare($query, $wheretypes, MDB2_PREPARE_RESULT);
         if(PEAR::isError($prep))
         {
            echo($query);
            print_r($wheretypes);
            print_r($wherevars);
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         $result = $prep->execute($wherevars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            $arrObjects[$row['ID']] = New Location($row);
         }
         $result->free();
         $prep->free();

         reset($arrObjects);

         return $arrObjects;
      }
   }

   /**
    * Searches the MaterialType database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return MaterialType[]
    */
   public function searchMaterialTypes($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCollections_MaterialTypes', 'MaterialType', 'MaterialType', 'MaterialType', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the ResearchAppointmentField database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ResearchAppointmentField[]
    */
   public function searchResearchAppointmentFields($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCollections_ResearchAppointmentFields', 'Name', 'ResearchAppointmentField', 'DisplayOrder, Name', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the ResearchAppointmentPurpose database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ResearchAppointmentPurpose[]
    */
   public function searchResearchAppointmentPurposes($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCollections_ResearchAppointmentPurposes', 'ResearchAppointmentPurpose', 'ResearchAppointmentPurpose', 'ResearchAppointmentPurpose', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the ResearcherType database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ResearcherType[]
    */
   public function searchResearcherTypes($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblCollections_ResearcherTypes', 'ResearcherType', 'ResearcherType', 'ResearcherType', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   /**
    * Searches the Book database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Book[]
    */
   public function searchBooks($SearchQuery, $SubjectID = 0, $CreatorID = 0, $LanguageID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {

      if($SubjectID && is_natural($SubjectID))
      {
         $arrIndexSearch['Subject'] = array($SubjectID => NULL);
      }
      elseif($CreatorID && is_natural($CreatorID))
      {
         $arrIndexSearch['Creator'] = array($CreatorID => NULL);
      }
      elseif($LanguageID && is_natural($LanguageID))
      {
         $arrIndexSearch['Language'] = array($LanguageID => NULL);
      }
      else
      {
         return $this->searchTable($SearchQuery, 'tblCollections_Books', array('Title', 'Description', 'Notes'), 'Book', 'Title', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
      }

      if(!empty($arrIndexSearch))
      {
         foreach($arrIndexSearch as $Type => $arrObjects)
         {
            if(!empty($arrObjects))
            {
               foreach($arrObjects as $ID => $junk)
               {
                  $prepQuery->query = "SELECT * FROM tblCollections_Books JOIN {$this->mdb2->quoteIdentifier("tblCollections_Book{$Type}Index")} ON {$this->mdb2->quoteIdentifier("tblCollections_Book{$Type}Index")}.BookID = tblCollections_Books.ID WHERE {$this->mdb2->quoteIdentifier("tblCollections_Book{$Type}Index")}.{$this->mdb2->quoteIdentifier("{$Type}ID")} = ?  ORDER BY tblCollections_Books.Title";
                  $prepQuery->types = array('integer');
                  $prepQuery->vars = array($ID);
                  $arrPrepQueries[] = $prepQuery;
               }
            }
         }
      }

      foreach($arrPrepQueries as $prepQuery)
      {
         // Run query to list collections
         call_user_func(array($this->mdb2, 'setLimit'), $Limit);
         $prep = $this->mdb2->prepare($prepQuery->query, $prepQuery->types, MDB2_PREPARE_RESULT);
         if(PEAR::isError($prep))
         {
            echo($prepQuery->query);
            trigger_error($prep->getMessage(), E_USER_ERROR);
         }
         $result = $prep->execute($prepQuery->vars);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         while($row = $result->fetchRow())
         {
            $arrResults[$row['ID']] = New Book($row);
         }
         $result->free();
         $prep->free();
      }

      return $arrResults;
   }

   /**
    * Bulk renumbering of numeric level container identifiers
    *
    * Each identifier MUST be numeric, or else this function will not work
    *
    * @param CollectionContent[] $arrCollectionContent
    * @param integer $ShiftDirection
    * @param integer $ShiftAmount
    * @return boolean
    */
   public function shiftLevelContainerIdentifiers($arrIDs, $ShiftDirection, $ShiftAmount, $ShiftSortOrder = false)
   {
      if(!$this->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE))
      {
         $this->declareError("Could not renumber: Permission Denied.");
         return false;
      }

      if(!is_array($arrIDs) || empty($arrIDs) || $arrIDs == array('0'))
      {
         $this->declareError("Could not renumber: Invalid IDs specified.");
         return false;
      }

      if(!is_numeric($ShiftAmount))
      {
         $this->declareError("Could not renumber: ShiftAmount must be numeric.");
         return false;
      }

      if($ShiftDirection != UP && $ShiftDirection != DOWN)
      {
         $this->declareError("Could not renumber: Invalid ShiftDirection.");
         return false;
      }

      //build array of ID => LevelContainerIdentifier
      $arrLCIDs = array();
      static $getPrep = NULL;
      if(!isset($getPrep))
      {
         $query = 'SELECT LevelContainerID,LevelContainerIdentifier,ParentID,CollectionID FROM tblCollections_Content WHERE ID = ?';
         $getPrep = $this->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }

      $firstTime = true;
      $LevelContainerID = NULL;
      $ParentID = NULL;
      $CollectionID = NULL;
      foreach($arrIDs as $ID)
      {
         $this->mdb2->setLimit(1);
         $result = $getPrep->execute($ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         if(!$result->numRows())
         {
            $result->free();

            $this->declareError("Could not renumber: Invalid LevelContainerIdentifier.");
            return false;
         }
         $row = $result->fetchRow();
         $LCID = $row['LevelContainerIdentifier'];
         $levelContainerID = $row['LevelContainerID'];
         $parentID = $row['ParentID'];
         $collectionID = $row['CollectionID'];
         $result->free();

         if(!is_numeric($LCID))
         {
            $this->declareError("Could not renumber: LevelContainerIdentifiers must be numeric.");
            return false;
         }

         if(!$firstTime)
         {
            if($LevelContainerID != $levelContainerID || $ParentID != $parentID || $CollectionID != $collectionID)
            {
               $this->declareError("Could not renumber: Content must have same Parent Collection, Parent Content and Level Container.");
               return false;
            }
         }
         else
         {
            $LevelContainerID = $levelContainerID;
            $ParentID = $parentID;
            $CollectionID = $collectionID;
         }

         $firstTime = false;

         $arrLCIDs[$ID] = $LCID;
      }


      // Check for conflicts
      static $checkPrep = NULL;
      if(!isset($checkPrep))
      {
         $query = "SELECT ID FROM tblCollections_Content WHERE LevelContainerID = ? AND LevelContainerIdentifier = ? AND ParentID = ? AND CollectionID = ?";
         $checkPrep = $this->mdb2->prepare($query, array('integer', 'text', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }

      foreach($arrLCIDs as $ID => $LCID)
      {
         $arrLCIDs[$ID] = $ShiftDirection == UP ? $LCID + $ShiftAmount : $LCID - $ShiftAmount;

         if($arrLCIDs[$ID] <= 0)
         {
            $this->declareError("Could not renumber: LevelContainerIdentifiers must be positive.");
            return false;
         }

         $result = $checkPrep->execute(array($LevelContainerID, $arrLCIDs[$ID], $ParentID, $CollectionID));
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         while($row = $result->fetchRow())
         {
            // conflicts within the content we're renumbering are expected
            if(!isset($arrLCIDs[$row['ID']]))
            {
               $this->declareError("Could not renumber: A CollectionContent with the same ContainerTypeAndNumberAndParentAndCollection already exists in the database. LevelContainerIdentifier: " . $arrLCIDs[$ID]);
               return false;
            }
         }
         $result->free();
      }

      // Final uniqueness check amongst content after everything is renumbered
      if(count($arrLCIDs) != count(array_unique($arrLCIDs)))
      {
         $this->declareError("Could not renumber: Renumbering will result in duplicate LevelContainerIdentifiers.");
         return false;
      }


      // Renumber Content
      static $updatePrep = NULL;
      if(!isset($updatePrep))
      {
         $query = "UPDATE tblCollections_Content SET LevelContainerIdentifier = ? WHERE ID = ?";
         $updatePrep = $this->mdb2->prepare($query, array('text', 'integer'), MDB2_PREPARE_MANIP);
      }
      foreach($arrLCIDs as $ID => $LCID)
      {

         $affected = $updatePrep->execute(array($LCID, $ID));
         if(PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
      }


      foreach($arrLCIDs as $ID => $LCID)
      {
         if($ShiftSortOrder)
         {

            $objContent = New CollectionContent($ID);
            $objContent->dbLoad(false);

            $objContent->SortOrder = $ShiftDirection == UP ? $objContent->SortOrder + $ShiftAmount : $objContent->SortOrder - $ShiftAmount;

            if(!$objContent->dbStore())
            {
               return false;
            }
            unset($objContent);
         }
         else
         {
            //this would be logged by the dbStore otherwise
            $this->log("tblCollections_Content", $ID);
         }
      }

      $this->log("tblCollections_Collections", $CollectionID);


      return true;
   }

   /**
    * Returns an array containing Classification objects sorted from root to node
    *
    * @param integer $ID
    * @return Classification[]
    */
   public function traverseClassification($ClassificationID)
   {
      if(!$ClassificationID)
      {
         $this->declareError("Could not traverse Classification: Classification ID not defined.");
         return false;
      }

      $objClassification = New Classification($ClassificationID);
      $objClassification->dbLoad();
      $arrClassification[$objClassification->ID] = $objClassification;

      while($objClassification->ParentID)
      {
         $objClassification = New Classification($objClassification->ParentID);
         $objClassification->dbLoad();
         $arrClassification[$objClassification->ID] = $objClassification;
      }

      return array_reverse($arrClassification);
   }

   /**
    * Returns an array containing CollectionContent objects sorted from root to node
    *
    * @param integer $ID
    * @return CollectionContent[]
    */
   public function traverseCollectionContent($CollectionContentID)
   {
      if(!$CollectionContentID)
      {
         $this->declareError("Could not traverse CollectionContent: CollectionContent ID not defined.");
         return false;
      }

      $objContent = New CollectionContent($CollectionContentID);
      $objContent->dbLoad();
      $arrContent[$objContent->ID] = $objContent;

      while($objContent->ParentID)
      {
         $objContent = New CollectionContent($objContent->ParentID);
         $objContent->dbLoad();
         $arrContent[$objContent->ID] = $objContent;
      }

      return array_reverse($arrContent);
   }

   /**
    * Increments or decrements collection content sort order
    */
   public function shiftContentSortOrder($CollectionID, $ParentID, $Min, $Max, $Direction, $ExcludeID = NULL, $Amount = 1)
   {
      if(!is_natural($CollectionID))
      {
         $this->declareError("Could not shift sort order for CollectionContent: Collection ID must be numeric.");
         return false;
      }
      if(!is_natural($ParentID))
      {
         $this->declareError("Could not shift sort order for CollectionContent: Parent ID must be numeric.");
         return false;
      }
      if(($Min == NULL && $Max == NULL) || ($Min != NULL && $Max != NULL && $Min > $Max && $Min <= 0 && $Max <= 0) || ($Min != NULL && $Min <= 0) || ($Max != NULL && $Max <= 0))
      {
         $this->declareError("Could not update sort order for CollectionContent: Incorrect usage of bounds.");
         return false;
      }
      if($Direction != UP && $Direction != DOWN)
      {
         $this->declareError("Could not shift sort order for CollectionContent: Direction not valid.");
         return false;
      }
      if($Amount == 0 || !is_natural($Amount))
      {
         $this->declareError("Could not shift sort order for CollectionContent: Shift amount must be numeric.");
         return false;
      }
      if($Direction == DOWN && ($Min == NULL || $Amount >= $Min))
      {
         $this->declareError("Could not shift sort order for CollectionContent: Shift amount exceeds lower bounds.");
         return false;
      }
      if($ExcludeID != NULL && !is_natural($ExcludeID))
      {
         $this->declareError("Could not shift sort order for CollectionContent: ID must be numeric.");
         return false;
      }

      $notid = ($ExcludeID) ? " AND ID !=" . $ExcludeID : "";

      $operator = ($Direction == UP) ? "+" : "-";
      if($Min && !$Max)
      {
         $range = "SortOrder >= " . $Min;
      }
      elseif(!$Min && $Max)
      {
         $range = "SortOrder <= " . $Max;
      }
      elseif($Min == $Max)
      {
         $range = "SortOrder = " . $Min;
      }
      else
      {
         $range = "SortOrder >= " . $Min . " AND SortOrder <= " . $Max;
      }

      $query = "UPDATE tblCollections_Content SET SortOrder = SortOrder {$operator} {$Amount} WHERE CollectionID = {$CollectionID} AND ParentID = {$ParentID} AND {$range}{$notid}";
      $affected = $this->mdb2->exec($query);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      return true;
   }

   ///////////////////////////////////
   // RESEARCH STUFF//

   /**
    * Creates Researcher Cart array of CollectionIDs and CollectionContentIDs
    *
    * @param array $arrEntries
    * @return array
    */
   public function createCartFromArray($arrEntries)
   {
      $DisableStyle = $this->PublicInterface->DisableTheme;
      $this->PublicInterface->DisableTheme = true;

      $arrCart->Collections = array();
      if(!empty($arrEntries))
      {
         foreach($arrEntries as $objOrarrIDs)
         {
            if(is_array($objOrarrIDs))
            {
               $objEntry->CollectionID = $objOrarrIDs['CollectionID'];
               $objEntry->CollectionContentID = $objOrarrIDs['CollectionContentID'];
            }
            else
            {
               $objEntry = & $objOrarrIDs;
            }

            if(!is_natural($objEntry->CollectionID))
            {
               continue;
            }

            if($objEntry->CollectionContentID)
            {
               if(!is_natural($objEntry->CollectionContentID))
               {
                  continue;
               }

               $objContent = New CollectionContent($objEntry->CollectionContentID);
               $objContent->dbLoad();

               $objCollection = $objContent->Collection;
            }
            else
            {
               $objCollection = New Collection($objEntry->CollectionID);
               $objCollection->dbLoad();

               unset($objContent);
            }

            if(CONFIG_COLLECTIONS_SEARCH_BY_CLASSIFICATION && $objCollection->ClassificationID)
            {
               $objCollection->Classification = New Classification($objCollection->ClassificationID);
               $objCollection->Classification->dbLoad();

               $String = $objCollection->Classification->toString(LINK_NONE, true, false, true, false);
            }
            else
            {
               $String = '';
            }

            $String .= $objCollection->toString();

            if($objContent)
            {
               $String .= $objContent->toString(LINK_NONE, true, true, true, true);
            }

            if($objOrarrIDs instanceof ResearchAppointmentMaterials)
            {
               $objOrarrIDs->Collection = $objCollection;
               $objOrarrIDs->CollectionContent = $objContent;

               $arrSorter[$String] = $objOrarrIDs;
            }
            else
            {
               $arrSorter[$String] = $objContent ? $objContent : $objCollection;
            }
         }
      }

      if(!empty($arrSorter))
      {
         ksort($arrSorter);

         foreach($arrSorter as $obj)
         {
            if($obj instanceof ResearchAppointmentMaterials)
            {
               $arrCart->Collections[$obj->CollectionID]->Content[$obj->CollectionContentID] = $obj;

               $arrCart->RetrievalTime = isset($arrCart->RetrievalTime) ? (((!$arrCart->RetrievalTime && !$obj->RetrievalTime) || ($arrCart->RetrievalTime && $obj->RetrievalTime)) ? min($arrCart->RetrievalTime, $obj->RetrievalTime) : -1 ) : $obj->RetrievalTime;
               $arrCart->RetrievalUserID = isset($arrCart->RetrievalUserID) ? (($arrCart->RetrievalUserID == $obj->RetrievalUserID) ? $arrCart->RetrievalUserID : -1 ) : $obj->RetrievalUserID;
               $arrCart->ReturnTime = isset($arrCart->ReturnTime) ? (((!$arrCart->ReturnTime && !$obj->ReturnTime) || ($arrCart->ReturnTime && $obj->ReturnTime)) ? min($arrCart->ReturnTime, $obj->ReturnTime) : -1 ) : $obj->ReturnTime;
               $arrCart->ReturnUserID = isset($arrCart->ReturnUserID) ? (($arrCart->ReturnUserID == $obj->ReturnUserID) ? $arrCart->ReturnUserID : -1 ) : $obj->ReturnUserID;

               $arrCart->Collections[$obj->CollectionID]->RetrievalTime = isset($arrCart->Collections[$obj->CollectionID]->RetrievalTime) ? (((!$arrCart->Collections[$obj->CollectionID]->RetrievalTime && !$obj->RetrievalTime) || ($arrCart->Collections[$obj->CollectionID]->RetrievalTime && $obj->RetrievalTime)) ? min($arrCart->Collections[$obj->CollectionID]->RetrievalTime, $obj->RetrievalTime) : -1 ) : $obj->RetrievalTime;
               $arrCart->Collections[$obj->CollectionID]->RetrievalUserID = isset($arrCart->Collections[$obj->CollectionID]->RetrievalUserID) ? (($arrCart->Collections[$obj->CollectionID]->RetrievalUserID == $obj->RetrievalUserID) ? $arrCart->Collections[$obj->CollectionID]->RetrievalUserID : -1 ) : $obj->RetrievalUserID;
               $arrCart->Collections[$obj->CollectionID]->ReturnTime = isset($arrCart->Collections[$obj->CollectionID]->ReturnTime) ? (((!$arrCart->Collections[$obj->CollectionID]->ReturnTime && !$obj->ReturnTime) || ($arrCart->Collections[$obj->CollectionID]->ReturnTime && $obj->ReturnTime)) ? min($arrCart->Collections[$obj->CollectionID]->ReturnTime, $obj->ReturnTime) : -1 ) : $obj->ReturnTime;
               $arrCart->Collections[$obj->CollectionID]->ReturnUserID = isset($arrCart->Collections[$obj->CollectionID]->ReturnUserID) ? (($arrCart->Collections[$obj->CollectionID]->ReturnUserID == $obj->ReturnUserID) ? $arrCart->Collections[$obj->CollectionID]->ReturnUserID : -1 ) : $obj->ReturnUserID;
            }
            elseif($obj instanceof Collection)
            {
               $arrCart->Collections[$obj->ID]->Content['0'] = $obj;
            }
            else
            {
               $arrCart->Collections[$obj->CollectionID]->Content[$obj->ID] = $obj;
            }
         }
      }

      $this->PublicInterface->DisableTheme = $DisableStyle;

      return $arrCart;
   }

   /**
    * Retrieves all AppointmentPurposes from the database
    *
    * The returned array of AppointmentPurpose objects
    * is sorted by AppointmentPurpose and has IDs as keys.
    *
    * @return AppointmentPurpose[]
    */
   //    public function getAllResearchAppointmentPurposes()
   //    {
   //        return $this->loadTable("tblCollections_ResearchAppointmentPurposes", "ResearchAppointmentPurpose", "ResearchAppointmentPurpose");
   //    }

   /**
    * Retrieves all Appointments from the database
    *
    * The returned array of Appointment objects
    * has IDs as keys.  If PreviousAppointments is true
    * the array will have upcoming appointments first, sorted
    * by arrivaltime, followed by previous appointments, sorted
    * in reverse by arrivaltime.
    *
    * @param $PreviousAppointments[optional]
    *
    * @return Appointment[]
    */
   public function getAllResearchAppointments($PreviousAppointments = true)
   {
      if($PreviousAppointments)
      {
         return $this->loadTable("tblCollections_ResearchAppointments", "ResearchAppointment", "ArrivalTime", "ArrivalTime > " . time()) +
                 $this->loadTable("tblCollections_ResearchAppointments", "Appointment", "ArrivalTime DESC", "ArrivalTime <= " . time());
      }
      else
      {
         return $this->loadTable("tblCollections_ResearchAppointments", "ResearchAppointment", "ArrivalTime", "ArrivalTime > " . time());
      }
   }

   /**
    * Initializes Archon for use
    *
    */
   public function initialize()
   {
      if($this->Security->isAuthenticated() && !$this->Security->userHasAdministrativeAccess() && $this->Security->Session->getRemoteVariable('Cart'))
      {
         $arrCartEntries = explode(',', $this->Security->Session->getRemoteVariable('Cart'));

         if(!empty($arrCartEntries))
         {
            foreach($arrCartEntries as $Entry)
            {
               if($Entry)
               {
                  list($CollectionID, $CollectionContentID) = explode(':', $Entry);
                  //$this->Security->Session->User->dbAddToCart($CollectionID, $CollectionContentID);
               }
            }
         }

         $this->Security->Session->unsetRemoteVariable('Cart', true);
      }
   }

   /**
    * Searches the Appointments database
    *
    * @param string $SearchQuery
    * @param integer $ParentID[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return Classification[]
    */
   public function searchResearchAppointments($SearchQuery, $ArrivalTimeStartLimit = 0, $ArrivalTimeEndLimit = 0, $PreviousAppointments = true, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      if(!is_natural($ArrivalTimeStartLimit) || !is_natural($ArrivalTimeEndLimit))
      {
         return false;
      }

      $ConditionsAND = '';
      $ConditionsANDTypes = array();
      $ConditionsANDVars = array();

      if($ArrivalTimeStartLimit)
      {
         $ConditionsAND .= "ArrivalTime >= ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $ArrivalTimeStartLimit;
      }

      if($ArrivalTimeEndLimit)
      {
         $ConditionsAND .= $ConditionsAND ? " AND ArrivalTime <= ?" : "ArrivalTime >= ?";
         $ConditonsANDTypes[] = 'integer';
         $ConditionsANDVars[] = $ArrivalTimeEndLimit;
      }

      $arrFields = $SearchQuery ? array('Topic', 'ResearcherComments', 'ArchivistComments', 'tblCore_Users.FirstName', 'tblCore_Users.LastName', 'tblCore_Users.Email') : NULL;

      if($PreviousAppointments)
      {
         $ConditionsAND1 = $ConditionsAND ? $ConditionsAND . " AND ArrivalTime > ?" : "ArrivalTime > ?";
         $ConditionsAND1Types = array_merge($ConditionsANDTypes, array('integer'));
         $ConditionsAND1Vars = array_merge($ConditionsANDVars, array(time()));

         $ConditionsAND2 = $ConditionsAND ? $ConditionsAND . " AND ArrivalTime <= ?" : "ArrivalTime <= ?";
         $ConditionsAND2Types = array_merge($ConditionsANDTypes, array('integer'));
         $ConditionsAND2Vars = array_merge($ConditionsANDVars, array(time()));

         return $this->searchTable($SearchQuery, array('tblCollections_ResearchAppointments', 'tblCore_Users'), $arrFields, 'ResearchAppointment', 'ArrivalTime', $ConditionsAND1, $ConditionsAND1Types, $ConditionsAND1Vars, NULL, array(), array(), $Limit, $Offset) +
                 $this->searchTable($SearchQuery, array('tblCollections_ResearchAppointments', 'tblCore_Users'), $arrFields, 'ResearchAppointment', 'ArrivalTime DESC', $ConditionsAND2, $ConditionsAND2Types, $ConditionsAND2Vars, NULL, array(), array(), $Limit, $Offset);
      }
      else
      {
         $ConditionsAND .= $ConditionsAND ? " AND ArrivalTime > ?" : "ArrivalTime > ?";
         $ConditionsANDTypes[] = 'integer';
         $ConditionsANDVars[] = time();
         return $this->searchTable($SearchQuery, array('tblCollections_ResearchAppointments', 'tblCore_Users'), $arrFields, 'ResearchAppointment', 'ArrivalTime', $ConditionsAND, $ConditionsANDTypes, $ConditionsANDVars, NULL, array(), array(), $Limit, $Offset);
      }
   }

}

$_ARCHON->setMixinMethodParameters('Archon', 'Collections_Archon', 'initialize', NULL, MIX_AFTER);

$_ARCHON->mixClasses('Archon', 'Collections_Archon');
?>