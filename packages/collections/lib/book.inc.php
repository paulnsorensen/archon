<?php
abstract class Books_Books
{
   /**
    * Deletes Book from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_BOOKS, 'tblCollections_Books'))
      {
         return false;
      }


      if(!$_ARCHON->deleteRelationship('tblCollections_CollectionBookIndex', 'BookID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCollections_BookCreatorIndex', 'BookID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCollections_BookLanguageIndex', 'BookID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCollections_BookSubjectIndex', 'BookID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      return true;
   }





   /**
    * Loads Book from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_Books'))
      {
         return false;
      }
      if(!$this->dbLoadRelatedObjects())
      {
         return false;
      }


      return true;
   }





   /**
    * Stores Book to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;


      $checkquery = "SELECT ID FROM tblCollections_Books WHERE Title = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->Title, $this->ID);
      $checkqueryerror = "A Book with the same Title already exists in the database";
      $problemfields = array('Title');
      $requiredfields = array('Title');


      if(!$_ARCHON->storeObject($this, MODULE_BOOKS, 'tblCollections_Books', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }




   /**
    * Generates a formatted string of the Book object
    *
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Book to string: Book not defined.");
         return false;
      }

      if(!$this->Title)
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }


      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         if($_ARCHON->QueryStringURL)
         {
            $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         }

         $String .= " <a href='?p=collections/bookcard&amp;id={$this->ID}{$q}'>";
      }
      if($this->Title)
      {
         $String .= $this->getString('Title');
      }
      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         $String .= '</a>';
      }

      if(!$_ARCHON->PublicInterface->DisableTheme && !$_ARCHON->AdministrativeInterface && $this->ID && $_ARCHON->Security->verifyPermissions(MODULE_BOOKS, UPDATE))
      {
         

         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='index.php?p=admin/collections/books&amp;id={$this->ID}" . "' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }


      return $String;
   }

   /**
    * Loads Creators for Book instance
    *
    * @return boolean
    */
   public function dbLoadCreators()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Creators: Book ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Creators: Book ID must be numeric.");
         return false;
      }

      $this->Creators = array();

      $query = "SELECT tblCreators_Creators.*, tblCollections_BookCreatorIndex.PrimaryCreator FROM tblCreators_Creators JOIN tblCollections_BookCreatorIndex ON tblCreators_Creators.ID = tblCollections_BookCreatorIndex.CreatorID WHERE tblCollections_BookCreatorIndex.BookID = ? ORDER BY tblCollections_BookCreatorIndex.PrimaryCreator DESC, tblCreators_Creators.Name";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
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
    * Loads Subjects for Book instance
    *
    * @return boolean
    */
   public function dbLoadSubjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Subjects: Book ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Subjects: Book ID must be numeric.");
         return false;
      }

      $this->Subjects = array();

      $query = "SELECT tblSubjects_Subjects.* FROM tblSubjects_Subjects JOIN tblCollections_BookSubjectIndex ON tblSubjects_Subjects.ID = tblCollections_BookSubjectIndex.SubjectID WHERE tblCollections_BookSubjectIndex.BookID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
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




   /**
    * Loads Languages for Book instance
    *
    * @return boolean
    */
   public function dbLoadLanguages()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Languages: Book ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Languages: Book ID must be numeric.");
         return false;
      }

      $this->Languages = array();

      $query = "SELECT LanguageID FROM tblCollections_BookLanguageIndex WHERE BookID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
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





   public function dbUpdateRelatedCreators($arrRelatedIDs, $arrPrimaryCreatorIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateCreatorRelations($this, MODULE_BOOKS, 'tblCollections_BookCreatorIndex', $arrRelatedIDs, $arrPrimaryCreatorIDs))
      {
         return false;
      }

      return true;
   }







   public function dbUpdateRelatedSubjects($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_BOOKS, 'Subject', 'tblCollections_BookSubjectIndex', 'tblSubjects_Subjects', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }





   public function dbUpdateRelatedLanguages($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_BOOKS, 'Language', 'tblCollections_BookLanguageIndex', NULL, $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }





   public function dbUpdateRelatedCollections($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_BOOKS, 'Collection', 'tblCollections_CollectionBookIndex', 'tblCollections_Collections', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }







   /**
    * Loads All Related Objects for Book instance
    *
    * @return boolean
    */
   public function dbLoadRelatedObjects()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load RelatedObjects: Book ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load RelatedObjects: Book ID must be numeric.");
         return false;
      }

      // Creators, Subjects, and Language Entries do not need instances created here,
      // since they have their own load function within the Book object.

      $this->dbLoadCreators();
      $this->dbLoadSubjects();
      $this->dbLoadLanguages();



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
    * Loads Collections from the database
    *
    * This function loads collections that fall under this book
    *
    * @return boolean
    */
   public function dbLoadCollections()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Collections: Book ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Collections: Book ID must be numeric.");
         return false;
      }

      $this->Collections = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_CollectionBookIndex ON tblCollections_Collections.ID = tblCollections_CollectionBookIndex.CollectionID WHERE tblCollections_CollectionBookIndex.BookID = ? ORDER BY tblCollections_Collections.SortTitle";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Collections[$row['ID']] = New Collection($row);
      }
      $result->free();

      return true;
   }

   /**
    * Loads Collections from the database
    *
    * This function loads collections that fall under this book
    *
    * @return boolean
    */
   public function dbLoadCollectionsByBook()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Collections: Book ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Collections: Book ID must be numeric.");
         return false;
      }

      $this->Collections = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblCollections_Collections.* FROM tblCollections_Collections JOIN tblCollections_CollectionBookIndex ON tblCollections_Collections.ID = tblCollections_CollectionBookIndex.CollectionID WHERE tblCollections_CollectionBookIndex.BookID = ? ORDER BY tblCollections_Collections.SortTitle";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Collections[$row['ID']] = New Collection($row);
      }
      $result->free();

      return true;
   }



   // These publiciables correspond directly to the fields in the tblCollections_Books table
   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var string
    */
   public $Title = '';


   /**
    * @var string
    */

   public $Edition = '';

   /**
    * @var int
    */

   public $CopyNumber = '';

   /**
    * @var string
    */

   public $PublicationDate = '';

   /**
    * @var string
    */

   public $Publisher = '';

   /**
    * @var string
    */

   public $PlaceOfPublication = '';

   /**
    * @var string
    */

   public $Series = '';

   /**
    * @var text
    */

   public $Description = '';

   /**
    * @var text
    */

   public $Notes = '';

   /**
    * @var int
    */

   public $NumberOfPages = '';

   public $Identifier = '';
   
   /**
    * Array containing Creators for Collection
    *
    * @var Creator[]
    */
   public $Creators = array();


   /**
    * Array containing Subjects for Collection
    *
    * @var Subject[]
    */
   public $Subjects = array();


   /**
    * Array containing Languages for Collection
    *
    * @var Language[]
    */
   public $Languages = array();


   /**
    * Creator Object of the Primary Creator for Collection
    *
    * @var Creator
    */
   public $PrimaryCreator = NULL;

   /**
    * @var Collection[]
    */
   public $Collections = array();


   //public $PrimaryCreators = array();

   public $ToStringFields = array('ID', 'Title');
}

$_ARCHON->mixClasses('Book', 'Books_Books');

?>