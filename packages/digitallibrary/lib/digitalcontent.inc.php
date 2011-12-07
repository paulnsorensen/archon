<?php

abstract class DigitalLibrary_DigitalContent
{

   /**
    * Digital Content Constructor
    *
    *
    */
   public function construct()
   {
      if($this->Identifier)
      {
         if(is_natural($this->Identifier))
         {
            $this->Identifier = str_pad($this->Identifier, CONFIG_DIGITALLIBRARY_DIGITAL_CONTENT_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }
      }
   }

   /**
    * Deletes DigitalContent from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      static $checkPrep = NULL;
      if(!isset($checkPrep))
      {
         $checkquery = "SELECT ID, CollectionID, CollectionContentID FROM tblDigitalLibrary_DigitalContent WHERE ID = ?";
         $checkPrep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      $this->dbLoadFiles();

      if(!$_ARCHON->deleteObject($this, MODULE_DIGITALLIBRARY, 'tblDigitalLibrary_DigitalContent'))
      {
         return false;
      }

      static $filePrep = NULL;
      if(!isset($filePrep))
      {
         $query = 'DELETE FROM tblDigitalLibrary_Files WHERE DigitalContentID = ?';
         $filePrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $filePrep->execute($ID);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if(defined('PACKAGE_CREATORS'))
      {
         static $creatorPrep = NULL;
         if(!isset($creatorPrep))
         {
            $query = 'DELETE FROM tblDigitalLibrary_DigitalContentCreatorIndex WHERE DigitalContentID = ?';
            $creatorPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
         }
         $affected = $creatorPrep->execute($ID);
         if(PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
      }

      static $languagePrep = NULL;
      if(!isset($languagePrep))
      {
         $query = 'DELETE FROM tblDigitalLibrary_DigitalContentLanguageIndex WHERE DigitalContentID = ?';
         $languagePrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $languagePrep->execute($ID);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if(defined('PACKAGE_SUBJECTS'))
      {
         static $subjectPrep = NULL;
         if(!isset($subjectPrep))
         {
            $query = 'DELETE FROM tblDigitalLibrary_DigitalContentSubjectIndex WHERE DigitalContentID = ?';
            $subjectPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
         }
         $affected = $subjectPrep->execute($ID);
         if(PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
      }

      if(defined('PACKAGE_COLLECTIONS') && $row['CollectionID'])
      {
         $_ARCHON->log("tblCollections_Collections", $this->CollectionID);
         FindingAidCache::setDirty($this->CollectionID);
      }

      if(defined('PACKAGE_COLLECTIONS') && $row['CollectionContentID'])
      {
         $_ARCHON->log("tblCollections_Content", $this->CollectionContentID);
      }

      return true;
   }

   /**
    * Loads DigitalContent from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblDigitalLibrary_DigitalContent'))
      {
         return false;
      }

      if($this->Identifier)
      {
         if(is_natural($this->Identifier))
         {
            $this->Identifier = str_pad($this->Identifier, CONFIG_DIGITALLIBRARY_DIGITAL_CONTENT_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }
      }

      return true;
   }

   /**
    * Loads Creators for Digital Library instance
    *
    * @return boolean
    */
   public function dbLoadCreators()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Creators: DigitalContent ID not defined.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Creators: DigitalContent ID must be numeric.");
         return false;
      }
      elseif(!$this->verifyLoadPermissions())
      {
         $_ARCHON->declareError("Could not load Creators: Permission Denied.");
         return false;
      }

      $this->Creators = array();

      $query = "SELECT tblCreators_Creators.*, tblDigitalLibrary_DigitalContentCreatorIndex.PrimaryCreator FROM tblCreators_Creators JOIN tblDigitalLibrary_DigitalContentCreatorIndex ON tblCreators_Creators.ID = tblDigitalLibrary_DigitalContentCreatorIndex.CreatorID WHERE tblDigitalLibrary_DigitalContentCreatorIndex.DigitalContentID = ? ORDER BY tblDigitalLibrary_DigitalContentCreatorIndex.PrimaryCreator DESC, tblCreators_Creators.Name";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No creators found, return.
         $result->free();
         $prep->free();
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
      $prep->free();

      return true;
   }

   /**
    * Loads Files for Digital Library instance
    *
    * Note: Does NOT load FileContents for each file.
    *
    * @return boolean
    */
   public function dbLoadFiles()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Files: DigitalLibrary ID not defined.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Files: DigitalLibrary ID must be numeric.");
         return false;
      }
      elseif(!$this->verifyLoadPermissions())
      {
         $_ARCHON->declareError("Could not load Files: Permission Denied.");
         return false;
      }

      $this->Files = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT ID, DefaultAccessLevel, DigitalContentID, Title, Filename, FileTypeID, Size, DisplayOrder FROM tblDigitalLibrary_Files WHERE DigitalContentID = ? ORDER BY DisplayOrder, Title";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No files found, return.
         $result->free();
         return true;
      }

      $arrFileTypes = $_ARCHON->getAllFileTypes();
      $arrMediaTypes = $_ARCHON->getAllMediaTypes();

      while($row = $result->fetchRow())
      {
         $objFile = New File($row);

         if($objFile->FileTypeID)
         {
            $objFile->FileType = $arrFileTypes[$objFile->FileTypeID];
            $objFile->FileType->MediaType = $arrMediaTypes[$objFile->FileType->MediaTypeID];
         }

         $this->Files[$row['ID']] = $objFile;
      }
      $result->free();

      return true;
   }

   /**
    * Loads Languages for Digital Library instance
    *
    * @return boolean
    */
   public function dbLoadLanguages()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Languages: DigitalContent ID not defined.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Languages: DigitalContent ID must be numeric.");
         return false;
      }
      elseif(!$this->verifyLoadPermissions())
      {
         $_ARCHON->declareError("Could not load Languages: Permission Denied.");
         return false;
      }

      $this->Languages = array();

      $query = "SELECT LanguageID FROM tblDigitalLibrary_DigitalContentLanguageIndex WHERE DigitalContentID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No languages found, return.
         $result->free();
         $prep->free();
         return true;
      }

      while($row = $result->fetchRow())
      {
         $this->Languages[$row['LanguageID']] = New Language($row['LanguageID']);
         $this->Languages[$row['LanguageID']]->dbLoad();
      }
      $result->free();
      $prep->free();

      return true;
   }

   /**
    * Loads All Related Objects for DigitalContent instance
    *
    * @return boolean
    */
   public function dbLoadRelatedObjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load RelatedObjects: DigitalContent ID not defined.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load RelatedObjects: DigitalContent ID must be numeric.");
         return false;
      }
      elseif(!$this->verifyLoadPermissions())
      {
         $_ARCHON->declareError("Could not load RelatedObjects: Permission Denied.");
         return false;
      }

      if(defined('PACKAGE_COLLECTIONS'))
      {
         if($this->CollectionID)
         {
            $this->Collection = New Collection($this->CollectionID);
            $this->Collection->dbLoad();
         }

         if($this->CollectionContentID)
         {
            $this->CollectionContent = New CollectionContent($this->CollectionContentID);
            $this->CollectionContent->dbLoad();
         }
      }

      // The following do not need instances created here,
      // since they have their own load function within the DigitalContent object.

      if(defined('PACKAGE_CREATORS'))
      {
         $this->dbLoadCreators();
      }


      $this->dbLoadFiles();
      $this->dbLoadLanguages();

      if(defined('PACKAGE_SUBJECTS'))
      {
         $this->dbLoadSubjects();
      }

      if($_ARCHON->Error)
      {
         return false;
      }
      else
      {
         return true;
      }
   }

   /**
    * Loads Subjects for Digital Library instance
    *
    * @return boolean
    */
   public function dbLoadSubjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Subjects: DigitalContent ID not defined.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Subjects: DigitalContent ID must be numeric.");
         return false;
      }
      elseif(!$this->verifyLoadPermissions())
      {
         $_ARCHON->declareError("Could not load Subjects: Permission Denied.");
         return false;
      }

      $this->Subjects = array();

      $query = "SELECT tblSubjects_Subjects.* FROM tblSubjects_Subjects JOIN tblDigitalLibrary_DigitalContentSubjectIndex ON tblSubjects_Subjects.ID = tblDigitalLibrary_DigitalContentSubjectIndex.SubjectID WHERE tblDigitalLibrary_DigitalContentSubjectIndex.DigitalContentID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No subjects found, return.
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

      if(!$_ARCHON->updateCreatorRelations($this, MODULE_DIGITALLIBRARY, 'tblDigitalLibrary_DigitalContentCreatorIndex', $arrRelatedIDs, $arrPrimaryCreatorIDs))
      {
         return false;
      }

      return true;
   }

   public function dbUpdateRelatedLanguages($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_DIGITALLIBRARY, 'Language', 'tblDigitalLibrary_DigitalContentLanguageIndex', NULL, $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   public function dbUpdateRelatedSubjects($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_DIGITALLIBRARY, 'Subject', 'tblDigitalLibrary_DigitalContentSubjectIndex', 'tblSubjects_Subjects', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }

   /**
    * Stores DigitalContent to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if(!defined('PACKAGE_COLLECTIONS'))
      {
         $this->CollectionID = 0;
         $this->CollectionContentID = 0;
      }

      if($this->ContentURL && $this->HyperlinkURL && !preg_match('/[\w\d]+:\/\//u', $this->ContentURL))
      {
         $this->ContentURL = 'http://' . $this->ContentURL;
      }

      $checkqueries = array();
      $checktypes = array();
      $checkvars = array();
      $checkqueryerrors = array();
      $problemfields = array();

      $requiredfields = array('Title');

      if($this->Identifier)
      {
         if(is_natural($this->Identifier))
         {
            $this->Identifier = str_pad($this->Identifier, CONFIG_DIGITALLIBRARY_DIGITAL_CONTENT_IDENTIFIER_MINIMUM_LENGTH, '0', STR_PAD_LEFT);
         }

         $checkqueries[] = "SELECT ID FROM tblDigitalLibrary_DigitalContent WHERE Identifier = ? AND ID != ? ORDER BY ID DESC";
         $checktypes[] = array('text', 'integer');
         $checkvars[] = array($this->Identifier, $this->ID);
         $checkqueryerrors[] = "A DigitalContent with the same Identifier already exists in the database";
         $problemfields[] = array('Identifier');
      }
      else
      {
         if($this->CollectionID)
         {
            $checkqueries[] = "SELECT ID FROM tblDigitalLibrary_DigitalContent WHERE Title = ? AND CollectionID = ? AND CollectionContentID = ? AND ID != ? ORDER BY ID DESC";
            $checktypes[] = array('text', 'integer', 'integer', 'integer');
            $checkvars[] = array($this->Title, $this->CollectionID, $this->CollectionContentID, $this->ID);
            $checkqueryerrors[] = "A DigitalContent with the same TitleAndCollectionIDAndCollectionContentID already exists in the database";
            $problemfields[] = array('Title');
         }
         else
         {
            $checkqueries[] = "SELECT ID FROM tblDigitalLibrary_DigitalContent WHERE Title = ? AND ID != ? ORDER BY ID DESC";
            $checktypes[] = array('text', 'integer');
            $checkvars[] = array($this->Title, $this->ID);
            $checkqueryerrors[] = "A Collection with the same Title already exists in the database";
            $problemfields[] = array('Title');
         }
      }

      if(!$_ARCHON->storeObject($this, MODULE_DIGITALLIBRARY, 'tblDigitalLibrary_DigitalContent', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
      {
         return false;
      }

      if($this->CollectionID)
      {
         $_ARCHON->log("tblCollections_Collections", $this->CollectionID);
         FindingAidCache::setDirty($this->CollectionID);
      }

      if($this->CollectionContentID)
      {
         $_ARCHON->log("tblCollections_Content", $this->CollectionContentID);
      }

      return true;
   }

   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, DELETE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete DigitalContent: DigitalContent may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   /**
    * Verifies Load Permissions of DigitalContent
    *
    * @return boolean
    */
   public function verifyLoadPermissions()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         return false;
      }

      if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
      {
         return true;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $prep = $_ARCHON->mdb2->prepare("SELECT Browsable FROM tblDigitalLibrary_DigitalContent WHERE ID = ?", 'integer', MDB2_PREPARE_RESULT);
         if(PEAR::isError($prep))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
      }
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows() != 1)
      {
         $result->free();
         return false;
      }

      $row = $result->fetchRow();
      $result->free();


      if($row['Browsable'])
      {
         return true;
      }

      return false;
   }


   public function getRepository()
   {
      global $_ARCHON;

       //check the current collection
      if($this->CollectionID)
      {
         if(!$this->Collection || $this->Collection->ID != $this->CollectionID)
         {
            $this->Collection = New Collection($this->CollectionID);
            $this->Collection->dbLoad();
         }

         if(!$this->Collection->Repository && $this->Collection->RepositoryID)
         {
            $this->Collection->Repository = New Repository($this->Collection->RepositoryID);
            $this->Collection->Repository->dbLoad();
         }

         return $this->Collection->Repository;
      }
      else
      {
         return $_ARCHON->Repository;
      }

   }


   public function verifyRepositoryPermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->Session->User->RepositoryLimit)
      {
         return true;
      }

      //check the current collection
      if($this->CollectionID)
      {
         if(!$this->Collection || $this->Collection->ID != $this->CollectionID)
         {
            $this->Collection = New Collection($this->CollectionID);
            $this->Collection->dbLoad();
         }

         if(!$_ARCHON->Security->verifyRepositoryPermissions($this->Collection->RepositoryID))
         {
            return false;
         }
      }

      //check the stored collection (if it changed)
      if($this->ID && is_natural($this->ID))
      {
         $tmpObj = New DigitalContent($this->ID);
         $tmpObj->dbLoad();
         if($tmpObj->CollectionID)
         {
            if(!$tmpObj->Collection)
            {
               $tmpObj->Collection = New Collection($tmpObj->CollectionID);
               $tmpObj->Collection->dbLoad();
            }
            if(!$_ARCHON->Security->verifyRepositoryPermissions($tmpObj->Collection->RepositoryID))
            {
               return false;
            }
         }
         unset($tmpObj);
      }

      return true;
   }

   public function verifyStorePermissions()
   {
      global $_ARCHON;


      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, UPDATE)))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not store DigitalContent: DigitalContent may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   /**
    * Generates a formatted string of the DigitalContent object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $DirectlyLinkToContentURL[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE, $DirectlyLinkToContentURL = false)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert DigitalLibraryContent to string: DigitalLibraryContent ID not defined.");
         return false;
      }

      if(!$this->Title)
      {
         $this->dbLoad();
      }

      if($DirectlyLinkToContentURL && empty($this->Files))
      {
         $this->dbLoadFiles();
      }

      if($MakeIntoLink == LINK_NONE)
      {
         $String .= $this->getString('Title');
      }
      elseif($DirectlyLinkToContentURL && $this->ContentURL && $this->HyperlinkURL && !count($this->Files))
      {
         $String .= "<a href='$this->ContentURL'>{$this->getString('Title')}</a>";
      }
      else
      {
         if($_ARCHON->QueryStringURL)
         {
            $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         }

         $String .= "<a href='?p=digitallibrary/digitalcontent&amp;id={$this->ID}{$q}'>{$this->getString('Title')}</a>";
      }

      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, UPDATE))
      {
         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $strHref = "?p=admin/digitallibrary/digitallibrary&amp;id={$this->ID}";
         $String .= "<a href='$strHref' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
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
//   public $DefaultAccessLevel = DIGITALLIBRARY_ACCESSLEVEL_FULL;
   /**
    * @var bit
    */
   public $Browsable = 1;
   /**
    * @var string
    */
   public $Title = '';
   /**
    * @var integer
    */
   public $CollectionID = 0;
   /**
    * @var integer
    */
   public $CollectionContentID = 0;
   /**
    * @var string
    */
   public $Identifier = '';
   /**
    * @var string
    */
   public $Scope = '';
   /**
    * @var string
    */
   public $PhysicalDescription = '';
   /**
    * @var string
    */
   public $Date = '';
   /**
    * @var string
    */
   public $Publisher = '';
   /**
    * @var string
    */
   public $Contributor = '';
   /**
    * @var string
    */
   public $RightsStatement = '';
   /**
    * This URL must be an absolute URL.
    *
    * @var string
    */
   public $ContentURL = '';
   public $HyperlinkURL = 1;
   /**
    * @var Collection
    */
   public $Collection = NULL;
   /**
    * @var CollectionContent
    */
   public $CollectionContent = NULL;
   /**
    * @var File[]
    */
   public $Files = array();
   /**
    * @var Creator[]
    */
   public $Creators = array();
   /**
    * @var Language[]
    */
   public $Languages = array();
   /**
    * @var Subject[]
    */
   public $Subjects = array();
   /**
    * @var Creator
    */
   public $PrimaryCreator = NULL;
   //public $PrimaryCreators = array();
   public $ToStringFields = array('ID', 'Title', 'ContentURL');
}

$_ARCHON->mixClasses('DigitalContent', 'DigitalLibrary_DigitalContent');
?>