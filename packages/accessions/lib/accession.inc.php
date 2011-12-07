<?php
abstract class Accessions_Accession
{
   /**
    * Accession Constructor
    *
    *
    */
   public function construct()
   {
      global $_ARCHON;

      if($this->Identifier)
      {
         if(is_natural($this->Identifier))
         {
            $this->Identifier = str_pad($this->Identifier, CONFIG_ACCESSIONS_ACCESSION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }
      }
   }





   /**
    * Creates a new collection based upon the information stored in the accession.
    * The new collection will have fields filled in when applicable and belong to
    * the primary classification associated with the accession.
    *
    * @return mixed The created Collection on success and false on failure.
    */
   public function createCollection()
   {
      global $_ARCHON;

      if(!$this->dbLoadAll())
      {
         return false;
      }

      $objCollection = New Collection();

      $objCollection->AcquisitionDate = $this->AccessionDate;
      $objCollection->Title = ($this->Title) ? $this->Title : $this->Identifier;

      //TODO: This needs a better repository selection method
      $firstRep = reset($_ARCHON->Security->Session->User->Repositories);
      $objCollection->RepositoryID = $firstRep->ID;
      $objCollection->SortTitle = $objCollection->Title;
      $objCollection->InclusiveDates = $this->InclusiveDates;

      $objCollection->Extent = $this->ReceivedExtent;
      $objCollection->MaterialTypeID = $this->MaterialTypeID;

      $objCollection->ClassificationID = $this->PrimaryCollectionEntry ? $this->PrimaryCollectionEntry->ClassificationID : 0;

      $objCollection->AcquisitionSource = $this->Donor;
      $objCollection->Scope = $this->ScopeContent;

      if(!$objCollection->dbStoreCollection())
      {
         return false;
      }

      foreach($this->LocationEntries as $objAccessionLocationEntry)
      {
         $objLocationEntry = New LocationEntry();

         foreach($objAccessionLocationEntry as $key => $value)
         {
            $objLocationEntry->$key = $value;
         }

         $objLocationEntry->ID = 0;
         $objLocationEntry->CollectionID = $objCollection->ID;

         $objLocationEntry->dbStore();
      }

      if($this->Creators)
      {
         $arrPrimaryCreatorID = ($this->PrimaryCreator) ? array($this->PrimaryCreator->ID) : array();
         $objCollection->dbUpdateRelatedCreators(array_keys($this->Creators), $arrPrimaryCreatorID);
      }

      return $objCollection;
   }





   /**
    * Deletes a Accession from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_ACCESSIONS, 'tblAccessions_Accessions'))
      {
         return false;
      }

      // Delete Creator Index entries
      static $creatorsPrep = NULL;
      if(!isset($creatorsPrep))
      {
         $query = "DELETE FROM tblAccessions_AccessionCreatorIndex WHERE AccessionID = ?";
         $creatorsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $creatorsPrep->execute($ID);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      // Delete Subject Index entries
      static $subjectsPrep = NULL;
      if(!isset($subjectsPrep))
      {
         $query = "DELETE FROM tblAccessions_AccessionSubjectIndex WHERE AccessionID = ?";
         $subjectsPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $subjectsPrep->execute($ID);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      // Delete Collection Index entries
      static $collectionPrep = NULL;
      if(!isset($collectionPrep))
      {
         $query = "DELETE FROM tblAccessions_AccessionCollectionIndex WHERE AccessionID = ?";
         $collectionPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $collectionPrep->execute($ID);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if(!$_ARCHON->deleteRelationship('tblAccessions_AccessionLocationIndex', 'AccessionID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      return true;
   }





   /**
    * Loads Accession from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblAccessions_Accessions'))
      {
         return false;
      }

      $this->AccessionDateMonth = encoding_substr($this->AccessionDate, 4, 2);
      $this->AccessionDateDay = encoding_substr($this->AccessionDate, 6, 2);
      $this->AccessionDateYear = encoding_substr($this->AccessionDate, 0, 4);

      if($this->Identifier)
      {
         if(is_natural($this->Identifier))
         {
            $this->Identifier = str_pad($this->Identifier, CONFIG_ACCESSIONS_ACCESSION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }
      }

      if($this->ExpectedCompletionDate)
      {
         $this->ExpectedCompletionDateMonth = encoding_substr($this->ExpectedCompletionDate, 4, 2);
         $this->ExpectedCompletionDateDay = encoding_substr($this->ExpectedCompletionDate, 6, 2);
         $this->ExpectedCompletionDateYear = encoding_substr($this->ExpectedCompletionDate, 0, 4);
      }

      if($this->UnprocessedExtentUnitID)
      {
         $this->UnprocessedExtentUnit = New ExtentUnit($this->UnprocessedExtentUnitID);
         $this->UnprocessedExtentUnit->dbLoad();
      }

      if($this->ReceivedExtentUnitID)
      {
         $this->ReceivedExtentUnit = New ExtentUnit($this->ReceivedExtentUnitID);
         $this->ReceivedExtentUnit->dbLoad();
      }

      if($this->MaterialTypeID)
      {
         $this->MaterialType = New MaterialType($this->MaterialTypeID);
         $this->MaterialType->dbLoad();
      }

      if($this->ProcessingPriorityID)
      {
         $this->ProcessingPriority = New ProcessingPriority($this->ProcessingPriorityID);
         $this->ProcessingPriority->dbLoad();
      }


      return true;
   }




   /**
    * Loads Accession and all related data and objects
    *
    * @return boolean
    */
   public function dbLoadAll()
   {
      // If something is already wrong, abort.
      if($_ARCHON->Error)
      {
         $_ARCHON->declareError("Could not load Accession: There was already an error.");
         return false;
      }


      // Check for an error every step of the way, so we don't waste
      // time doing more work when something has already gone wrong.
      // Furthermore, order loading so that shorter tasks go first,
      // so longer tasks won't run if the shorter ones fail.
      if(!$this->dbLoad())
      {
         return false;
      }

      if(!$this->dbLoadCreators())
      {
         return false;
      }

      if(!$this->dbLoadSubjects())
      {
         return false;
      }

      if(!$this->dbLoadCollectionEntries())
      {
         return false;
      }

      if(!$this->dbLoadLocationEntries())
      {
         return false;
      }

      return true;
   }




   /**
    * Loads Creators for Accession instance
    *
    * @return boolean
    */
   public function dbLoadCreators()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Creators: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Creators: Accession ID must be numeric.");
         return false;
      }

      $this->Creators = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCreators_Creators.*, tblAccessions_AccessionCreatorIndex.PrimaryCreator FROM tblCreators_Creators JOIN tblAccessions_AccessionCreatorIndex ON tblCreators_Creators.ID = tblAccessions_AccessionCreatorIndex.CreatorID WHERE tblAccessions_AccessionCreatorIndex.AccessionID = ? ORDER BY tblAccessions_AccessionCreatorIndex.PrimaryCreator DESC, tblCreators_Creators.Name";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No creators found, return.
         return true;
      }

      $arrCreatorTypes = $_ARCHON->getAllCreatorTypes();

      while($row = $result->fetchRow())
      {
         $objCreator = New Creator($row);
         $objCreator->CreatorType = $arrCreatorTypes[$objCreator->CreatorTypeID];

         $this->Creators[$row['ID']] = $objCreator;

         if($row['PrimaryCreator'])
         {
            //if(empty($this->PrimaryCreators))
            //{
            $this->PrimaryCreator = $this->Creators[$objCreator->ID];
            //}
            //$this->PrimaryCreators[$objCreator->ID] = $this->Creators[$objCreator->ID];
         }
      }
      $result->free();

      return true;
   }




   /**
    * Loads Collection Entries for Accession instance
    *
    * @return boolean
    */
   public function dbLoadCollectionEntries()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load CollectionEntries: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load CollectionEntries: Accession ID must be numeric.");
         return false;
      }

      $this->CollectionEntries = array();

      $query = "SELECT ID FROM tblAccessions_AccessionCollectionIndex WHERE AccessionID = ? ORDER BY PrimaryCollection DESC";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No collection entries found, return.
         $result->free();
         $prep->free();
         return true;
      }

      while($row = $result->fetchRow())
      {
         $objAccessionCollectionEntry = New AccessionCollectionEntry($row['ID']);
         $objAccessionCollectionEntry->dbLoad();

         if($objAccessionCollectionEntry->ClassificationID)
         {
            $objAccessionCollectionEntry->Classification->dbLoad();
         }

         if($objAccessionCollectionEntry->CollectionID)
         {
            $objAccessionCollectionEntry->Collection->dbLoad();
         }

         $this->CollectionEntries[$row['ID']] = $objAccessionCollectionEntry;

         if($objAccessionCollectionEntry->PrimaryCollection)
         {
            $this->PrimaryCollectionEntry = $objAccessionCollectionEntry;
         }
      }
      $result->free();
      $prep->free();

      return true;
   }




   /**
    * Loads Location Entries for Accession instance
    *
    * @return boolean
    */
   public function dbLoadLocationEntries()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load LocationEntries: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load LocationEntries: Accession ID must be numeric.");
         return false;
      }

      $this->LocationEntries = array();

      $query = "SELECT tblAccessions_AccessionLocationIndex.* FROM tblAccessions_AccessionLocationIndex JOIN tblCollections_Locations ON tblCollections_Locations.ID = tblAccessions_AccessionLocationIndex.LocationID WHERE tblAccessions_AccessionLocationIndex.AccessionID = ? ORDER BY tblAccessions_AccessionLocationIndex.Content, tblCollections_Locations.Location, tblAccessions_AccessionLocationIndex.RangeValue, tblAccessions_AccessionLocationIndex.Section, tblAccessions_AccessionLocationIndex.Shelf";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No location entries found, return.
         $result->free();
         $prep->free();
         return true;
      }

      $arrLocations = $_ARCHON->getAllLocations();
      $arrExtentUnits = $_ARCHON->getAllExtentUnits();

      while($row = $result->fetchRow())
      {
         $objAccessionLocationEntry = New AccessionLocationEntry($row);
         $objAccessionLocationEntry->Location = $arrLocations[$objAccessionLocationEntry->LocationID];
         $objAccessionLocationEntry->ExtentUnit = $arrExtentUnits[$objAccessionLocationEntry->ExtentUnitID];

         $this->LocationEntries[$row['ID']] = $objAccessionLocationEntry;
      }
      $result->free();
      $prep->free();

      return true;
   }




   /**
    * Loads Subjects for Accession instance
    *
    * @return boolean
    */
   public function dbLoadSubjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Subjects: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Subjects: Accession ID must be numeric.");
         return false;
      }

      $this->Subjects = array();

      $query = "SELECT tblSubjects_Subjects.* FROM tblSubjects_Subjects JOIN tblAccessions_AccessionSubjectIndex ON tblSubjects_Subjects.ID = tblAccessions_AccessionSubjectIndex.SubjectID WHERE tblAccessions_AccessionSubjectIndex.AccessionID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No subjects found, return.
         $result->free();
         $prep->free();
         return true;
      }

      $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();

      while($row = $result->fetchRow())
      {
         // We can't add the subjects to the final array just yet
         // because the subjects need to be sorted based upon how
         // they will end up displaying (parent subjects will
         // be concatenated before child subjects).
         $objSubject = New Subject($row);
         $objSubject->SubjectType = $arrSubjectTypes[$objSubject->SubjectTypeID];

         $arrSorter[$objSubject->toString(LINK_NONE, true)] = $objSubject;

         // this should now be taken care of by calling dbLoad() within the subject class which is invoked by toString()
//         // In case parents are used multiple times
//         $objTransSubject = $objSubject;
//         while($objTransSubject)
//         {
//            $_ARCHON->MemoryCache['Objects']['Subject'][$objTransSubject->ID] = $objTransSubject;
//            $objTransSubject = $objTransSubject->Parent;
//         }
      }
      $result->free();
      $prep->free();

      natcaseksort($arrSorter);

      if(!empty($arrSorter))
      {
         foreach($arrSorter as $objSubject)
         {
            $this->Subjects[$objSubject->ID] = $objSubject;
         }
      }

      return true;
   }






   public function dbUpdateRelatedCreators($arrRelatedIDs, $arrPrimaryCreatorIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateCreatorRelations($this, MODULE_ACCESSIONS, 'tblAccessions_AccessionCreatorIndex', $arrRelatedIDs, $arrPrimaryCreatorIDs))
      {
         return false;
      }

      return true;
   }




   /**
    * Relate Creator to Accession
    *
    * @param integer $CreatorID
    * @return boolean
    */
   public function dbRelateCreator($CreatorID)
   {
      global $_ARCHON;

//      if(!$_ARCHON->updateCreatorRelations($this, MODULE_ACCESSIONS, 'tblAccessions_AccessionCreatorIndex', array($CreatorID), $arrPrimaryCreatorIDs, ADD))
//      {
//         return false;
//      }


      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not relate Creator: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not relate Creator: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not relate Creator: Accession ID must be numeric.");
         return false;
      }

      // Make sure user isn't dealing with a accession from another repository if they're limited
      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not relate Creator: Accessions may only be altered for the primary repository.");
         return false;
      }

      if(!is_natural($CreatorID) || !$CreatorID)
      {
         $_ARCHON->declareError("Could not relate Creator: Creator ID must be numeric.");
         return false;
      }

      static $existPrep = NULL;
      if(!isset($existPrep))
      {
         $query = "SELECT ID FROM tblCreators_Creators WHERE ID = ?";
         $existPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $existPrep->execute($CreatorID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $creatorrow = $result->fetchRow();
      $result->free();

      if(!$creatorrow['ID'])
      {
         $_ARCHON->declareError("Could not relate Creator: Creator ID $CreatorID not found in database.");
         return false;
      }

      static $checkPrep = NULL;
      if(!isset($checkPrep))
      {
         $checkquery = "SELECT ID FROM tblAccessions_AccessionCreatorIndex WHERE AccessionID = ? AND CreatorID = ?";
         $checkPrep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkPrep->execute(array($this->ID, $CreatorID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not relate Creator: Creator ID $CreatorID already related to Accession ID $this->ID.");
         return false;
      }

      // Assume this creator is the primary creator.
      $PrimaryCreator = 1;

      // If a creator is already assigned as the primary creator, don't assign this creator as primary.
      static $primaryPrep = NULL;
      if(!isset($primaryPrep))
      {
         $query = "SELECT PrimaryCreator FROM tblAccessions_AccessionCreatorIndex WHERE AccessionID = ?";
         $primaryPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $primaryPrep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         if($row['PrimaryCreator'])
         {
            $PrimaryCreator = 0;
         }
      }
      $result->free();

      static $insertPrep = NULL;
      if(!isset($insertPrep))
      {
         $query = "INSERT INTO tblAccessions_AccessionCreatorIndex (AccessionID, CreatorID, PrimaryCreator) VALUES (?, ?, ?)";
         $insertPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $insertPrep->execute(array($this->ID, $CreatorID, $PrimaryCreator));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $result = $checkPrep->execute(array($this->ID, $CreatorID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not relate Creator: Unable to update the database table.");
         return false;
      }

      // Add the creator to the Accessions's Creators[] array
      $objCreator = New Creator($creatorrow);

      if($PrimaryCreator)
      {
         $this->PrimaryCreator = $objCreator;
      }

      $this->Creators[$CreatorID] = $objCreator;

      $_ARCHON->log("tblAccessions_AccessionCreatorIndex", $row['ID']);
      $_ARCHON->log("tblAccessions_Accessions", $this->ID);

      return true;
   }




   /**
    * Relate Subject to Accession
    *
    * @param integer $SubjectID
    * @return boolean
    */
   public function dbRelateSubject($SubjectID)
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not relate Subject: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not relate Subject: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not relate Subject: Accession ID must be numeric.");
         return false;
      }

      // Make sure user isn't dealing with a accession from another repository if they're limited
      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not relate Subject: Accessions may only be altered for the primary repository.");
         return false;
      }

      if(!is_natural($SubjectID) || !$SubjectID)
      {
         $_ARCHON->declareError("Could not relate Subject: Subject ID must be numeric.");
         return false;
      }

      static $existPrep = NULL;
      if(!isset($existPrep))
      {
         $query = "SELECT ID FROM tblSubjects_Subjects WHERE ID = ?";
         $existPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $existPrep->execute($SubjectID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $languagerow = $result->fetchRow();
      $result->free();

      if(!$languagerow['ID'])
      {
         $_ARCHON->declareError("Could not relate Subject: Subject ID $SubjectID not found in database.");
         return false;
      }

      static $checkPrep = NULL;
      if(!isset($checkPrep))
      {
         $checkquery = "SELECT ID FROM tblAccessions_AccessionSubjectIndex WHERE AccessionID = ? AND SubjectID = ?";
         $checkPrep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkPrep->execute(array($this->ID, $SubjectID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not relate Subject: Subject ID $SubjectID already related to Accession ID $this->ID.");
         return false;
      }

      static $insertPrep = NULL;
      if(!isset($insertPrep))
      {
         $query = "INSERT INTO tblAccessions_AccessionSubjectIndex (AccessionID, SubjectID) VALUES (?, ?)";
         $insertPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $insertPrep->execute(array($this->ID, $SubjectID));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $result = $checkPrep->execute(array($this->ID, $SubjectID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not relate Subject: Unable to update the database table.");
         return false;
      }

      // Add the language to the Accessions's Subjects[] array
      $objSubject = New Subject($languagerow);

      $this->Subjects[$SubjectID] = $objSubject;

      $_ARCHON->log("tblAccessions_AccessionSubjectIndex", $row['ID']);
      $_ARCHON->log("tblAccessions_Accessions", $this->ID);

      return true;
   }





   /**
    * Stores Accession to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if(!$this->AccessionDateYear || !$this->AccessionDateMonth || !$this->AccessionDateDay)
      {
         $_ARCHON->declareError("Could not store Accession: AccessionDate not defined.");
         return false;
      }

      if($this->ReceivedExtent && !is_numeric($this->ReceivedExtent))
      {
         $_ARCHON->declareError("Could not store Accession: ReceivedExtent must be numeric.");
         return false;
      }

      if($this->AccessionDateMonth && (!is_natural($this->AccessionDateMonth) || $this->AccessionDateMonth < 1 || $this->AccessionDateMonth > 12))
      {
         $_ARCHON->declareError("Could not store Accession: AccessionDate not formatted correctly.");
         return false;
      }

      if($this->AccessionDateDay && (!is_natural($this->AccessionDateDay) || $this->AccessionDateDay < 1 || $this->AccessionDateDay > 31))
      {
         $_ARCHON->declareError("Could not store Accession: AccessionDate not formatted correctly.");
         return false;
      }

      if($this->AccessionDateYear && !is_natural($this->AccessionDateYear))
      {
         $_ARCHON->declareError("Could not store Accession: AccessionDate not formatted correctly.");
         return false;
      }

      if($this->ExpectedCompletionDateMonth && (!is_natural($this->ExpectedCompletionDateMonth) || $this->ExpectedCompletionDateMonth < 1 || $this->ExpectedCompletionDateMonth > 12))
      {
         $_ARCHON->declareError("Could not store Accession: ExpectedCompletionDate not formatted correctly.");
         return false;
      }

      if($this->ExpectedCompletionDateDay && (!is_natural($this->ExpectedCompletionDateDay) || $this->ExpectedCompletionDateDay < 1 || $this->ExpectedCompletionDateDay > 31))
      {
         $_ARCHON->declareError("Could not store Accession: ExpectedCompletionDate not formatted correctly.");
         return false;
      }

      if($this->ExpectedCompletionDateYear && !is_natural($this->ExpectedCompletionDateYear))
      {
         $_ARCHON->declareError("Could not store Accession: ExpectedCompletionDate not formatted correctly.");
         return false;
      }

      // Transform non-table variables into table variables if set.
      if($this->AccessionDateYear || $this->AccessionDateMonth || $this->AccessionDateDay)
      {
         $this->AccessionDate = str_pad($this->AccessionDateYear, 4, "0", STR_PAD_LEFT);
         $this->AccessionDate .= str_pad($this->AccessionDateMonth, 2, "0", STR_PAD_LEFT);
         $this->AccessionDate .= str_pad($this->AccessionDateDay, 2, "0", STR_PAD_LEFT);
      }


      if($this->ExpectedCompletionDateYear || $this->ExpectedCompletionDateMonth || $this->ExpectedCompletionDateDay)
      {
         $this->ExpectedCompletionDate = str_pad($this->ExpectedCompletionDateYear, 4, "0", STR_PAD_LEFT);
         $this->ExpectedCompletionDate .= str_pad($this->ExpectedCompletionDateMonth, 2, "0", STR_PAD_LEFT);
         $this->ExpectedCompletionDate .= str_pad($this->ExpectedCompletionDateDay, 2, "0", STR_PAD_LEFT);
      }

      $checkqueries = array();
      $checktypes = array();
      $checkvars = array();
      $checkqueryerrors = array();
      $problemfields = array();

//        $checkqueries[] = "SELECT ID FROM tblAccessions_Accessions WHERE Title = ? AND ID != ?";
//        $checktypes[] = array('text', 'integer');
//        $checkvars[] = array($this->Title, $this->ID);
//        $checkqueryerrors[] = "A Accession with the same Title already exists in the database";
//        $problemfields[] = array('Title');

      if($this->Identifier)
      {
         if(is_natural($this->Identifier))
         {
            $this->Identifier = str_pad($this->Identifier, CONFIG_ACCESSIONS_ACCESSION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }
      }

      $checkqueries[] = "SELECT ID FROM tblAccessions_Accessions WHERE Identifier = ? AND ID != ?";
      $checktypes[] = array('text', 'text', 'integer');
      $checkvars[] = array($this->Identifier, $this->ID);
      $checkqueryerrors[] = "A Accession with the same Identifier already exists in the database";
      $problemfields[] = array('Identifier');


      $requiredfields = array('Identifier');

      if(!$_ARCHON->storeObject($this, MODULE_ACCESSIONS, 'tblAccessions_Accessions', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }




   /**
    * Unrelates all creators for accession
    *
    * @return boolean
    */
   public function dbUnrelateAllCreators()
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Creators: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Creators: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Creators: Accession ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Creators: Accessions may only be altered for the primary repository.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblAccessions_AccessionCreatorIndex WHERE AccessionID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute($this->ID);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblAccessions_AccessionCreatorIndex WHERE AccessionID = ?";
         $_ARCHON->setLimit(1);
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Creators: Unable to update the database table.");
         return false;
      }
      else
      {
         $this->Creators = array();
         $this->PrimaryCreator = NULL;

         $_ARCHON->log("tblAccessions_AccessionCreatorIndex", "-1");
         $_ARCHON->log("tblAccessions_Accessions", $this->ID);

         return true;
      }
   }




   /**
    * Unrelates all subjects for accession
    *
    * @return boolean
    */
   public function dbUnrelateAllSubjects()
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Accession ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Accessions may only be altered for the primary repository.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblAccessions_AccessionSubjectIndex WHERE AccessionID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute($this->ID);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblAccessions_AccessionSubjectIndex WHERE AccessionID = ?";
         $_ARCHON->setLimit(1);
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Subjects: Unable to update the database table.");
         return false;
      }
      else
      {
         $this->Subjects = array();
         $this->PrimarySubject = NULL;

         $_ARCHON->log("tblAccessions_AccessionSubjectIndex", "-1");
         $_ARCHON->log("tblAccessions_Accessions", $this->ID);

         return true;
      }
   }




   /**
    * Unrelate Creator from Accession
    *
    * @param integer $CreatorID
    * @return boolean
    */
   public function dbUnrelateCreator($CreatorID)
   {
      global $_ARCHON;

//      if(!$_ARCHON->updateCreatorRelations($this, MODULE_ACCESSIONS, 'tblAccessions_AccessionCreatorIndex', array($CreatorID), $arrPrimaryCreatorIDs, DELETE))
//      {
//         return false;
//      }


      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Creator: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Creator: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Creator: Accession ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Creator: Accessions may only be altered for the primary repository.");
         return false;
      }

      if(!is_natural($CreatorID) || !$CreatorID)
      {
         $_ARCHON->declareError("Could not unrelate Creator: Creator ID must be numeric.");
         return false;
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblAccessions_AccessionCreatorIndex WHERE AccessionID = ? AND CreatorID = ?";
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute(array($this->ID, $CreatorID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      $RowID = $row['ID'];

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Creator: Creator ID $CreatorID is not related to Accession ID $this->ID.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblAccessions_AccessionCreatorIndex WHERE AccessionID = ? AND CreatorID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($this->ID, $CreatorID));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $result = $checkprep->execute(array($this->ID, $CreatorID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Creator: Unable to update the database table.");
         return false;
      }
      else
      {
         unset($this->Creators[$CreatorID]);

         $_ARCHON->log("tblAccessions_AccessionCreatorIndex", $RowID);
         $_ARCHON->log("tblAccessions_Accessions", $this->ID);

         return true;
      }
   }




   /**
    * Unrelate Subject from Accession
    *
    * @param integer $SubjectID
    * @return boolean
    */
   public function dbUnrelateSubject($SubjectID)
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
      {
         $_ARCHON->declareError("Could not unrelate Subject: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unrelate Subject: Accession ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unrelate Subject: Accession ID must be numeric.");
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not unrelate Subject: Accessions may only be altered for the primary repository.");
         return false;
      }

      if(!is_natural($SubjectID) || !$SubjectID)
      {
         $_ARCHON->declareError("Could not unrelate Subject: Subject ID must be numeric.");
         return false;
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblAccessions_AccessionSubjectIndex WHERE AccessionID = ? AND SubjectID = ?";
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute(array($this->ID, $SubjectID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      $RowID = $row['ID'];

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Subject: Subject ID $SubjectID is not related to Accession ID $this->ID.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblAccessions_AccessionSubjectIndex WHERE AccessionID = ? AND SubjectID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($this->ID, $SubjectID));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $result = $checkprep->execute(array($this->ID, $SubjectID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not unrelate Subject: Unable to update the database table.");
         return false;
      }
      else
      {
         unset($this->Subjects[$SubjectID]);

         $_ARCHON->log("tblAccessions_AccessionSubjectIndex", $RowID);
         $_ARCHON->log("tblAccessions_Accessions", $this->ID);

         return true;
      }
   }



   //    public function dbUpdateRelatedCreators($arrRelatedIDs)
   //    {
   //    	global $_ARCHON;
   //
   //        if(!$_ARCHON->updateObjectRelations($this, MODULE_ACCESSIONS, 'Creator', 'tblAccessions_AccessionCreatorIndex', 'tblCreators_Creators', $arrRelatedIDs))
   //        {
   //           return false;
   //        }
   //
   //    	return true;
   //    }


   public function dbUpdateRelatedSubjects($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_ACCESSIONS, 'Subject', 'tblAccessions_AccessionSubjectIndex', 'tblSubjects_Subjects', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   
   
   

   public function enabled()
   {
      global $_ARCHON;

      $readPermissions = true;

      if(!$this->Enabled)
      {
         $readPermissions = false;

         if($_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, READ))
         {
            $readPermissions = true;
         }
      }
      return $readPermissions;
   }




   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, DELETE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete Accession: Accessions may only be altered for the primary repository.");
         return false;
      }

      return true;
   }



   /**
    * Returns true only if a user with correct repository editing permissions is currently logged in.
    * This will return false if the Accession is associated with one or more collections outside
    * the users repository permissions and no collections within his or her permissions.
    *
    * @return boolean
    */
   public function verifyRepositoryPermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->Session->User->RepositoryLimit)
      {
         return true;
      }

      if(!$this->ID)
      {
         return true;
      }

      if(!is_natural($this->ID))
      {
         return false;
      }

      $seenOtherRepository = false;
      $seenOwnRepository = false;

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Collections.RepositoryID FROM tblAccessions_AccessionCollectionIndex INNER JOIN tblCollections_Collections ON tblAccessions_AccessionCollectionIndex.CollectionID = tblCollections_Collections.ID WHERE tblAccessions_AccessionCollectionIndex.AccessionID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         if($row['RepositoryID'])
         {
//            if($row['RepositoryID'] == $_ARCHON->Security->Session->User->RepositoryID)
            if(array_key_exists($row['RepositoryID'], $_ARCHON->Security->Session->User->Repositories) == true)
            {
               $seenOwnRepository = true;
            }
            else
            {
               $seenOtherRepository = true;
            }
         }
      }
      $result->free();

      return $seenOwnRepository || !$seenOtherRepository;
   }




   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE)))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not store Accession: Accessions may only be altered for the primary repository.");
         return false;
      }

      return true;
   }




   /**
    * Generates a formatted string of the Accession object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Accession to string: Accession ID not defined.");
         return false;
      }

      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         if($_ARCHON->QueryStringURL)
         {
            $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         }

         $String .= " <a href='?p=accessions/accession&amp;id={$this->ID}{$q}'> ";
      }

      if($this->Title)
      {
         $String .= $this->getString('Title');
      }
      else
      {
         $String .= $this->getString('Identifier');
      }


      if($this->InclusiveDates)
      {
         $String .= ', ' . $this->getString('InclusiveDates');
      }

      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         $String .= '</a>';
      }



      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $this->ID && $_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
      {
         

         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='?p=admin/accessions/accessions&amp;id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }

      return $String;
   }




   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var integer
    */
   public $Enabled = 1;

   /**
    * @var string
    */
   public $AccessionDate = '';

   /**
    * @var string
    */
   public $Title = '';

   /**
    * @var string
    */
   public $Identifier = '';


   /**
    * @var string
    */
   public $InclusiveDates = '';

   /**
    * @var float
    */
   public $ReceivedExtent = 0.00;

   /** @var ExtentUnit */
   public $ReceivedExtentUnit = NULL;

   /**
    * @var integer
    */
   public $ReceivedExtentUnitID = 0;

   /**
    * @var float
    */
   public $UnprocessedExtent = 0.00;


   /** @var ExtentUnit */
   public $UnprocessedExtentUnit = NULL;

   /**
    * @var integer
    */
   public $UnprocessedExtentUnitID = 0;


   /** @var MaterialType */
   public $MaterialType = NULL;

   /**
    * @var integer
    */
   public $MaterialTypeID = 0;

   /**
    * @var integer
    */
   public $ProcessingPriorityID = 0;

   /**
    * @var ProcessingPriority
    */
   public $ProcessingPriority = NULL;

   /**
    * @var string
    */
   public $ExpectedCompletionDate = '';


   /**
    * @var string
    */
   public $Donor = '';

   /**
    * @var string
    */
   public $DonorContactInformation = '';

   /**
    * @var string
    */
   public $DonorNotes = '';

   /**
    * @var string
    */
   public $PhysicalDescription = '';

   /**
    * @var string
    */
   public $ScopeContent = '';

   /**
    * Array containing Subjects for Accession
    *
    * @var Subject[]
    */
   public $Subjects = array();

   /**
    * @var string
    */
   public $Comments = '';

   /** @var string */
   public $AccessionDateMonth;

   /** @var string */
   public $AccessionDateDay;

   /** @var string */
   public $AccessionDateYear;

   /** @var string */
   public $ExpectedCompletionDateMonth;

   /** @var string */
   public $ExpectedCompletionDateDay;

   /** @var string */
   public $ExpectedCompletionDateYear;

   /**
    * @var AccessionCollectionEntry[]
    */
   public $CollectionEntries = array();

   /**
    * @var AccessionLocationEntry[]
    */
   public $LocationEntries = array();

   /**
    * @var Creator[]
    */
   public $Creators = array();

   /**
    * @var AccessionCollectionEntry
    */
   public $PrimaryCollectionEntry = NULL;

   /**
    * @var Creator
    */
   public $PrimaryCreator = NULL;

   public $ToStringFields = array('ID', 'Title', 'InclusiveDates');
}

$_ARCHON->mixClasses('Accession', 'Accessions_Accession');
?>