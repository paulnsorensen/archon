<?php

abstract class Collections_Classification
{

   /**
    * Classification Constructor
    *
    * @param unknown_type $row
    */
   public function construct($row)
   {
      if(isset($this->ClassificationIdentifier) && is_natural($this->ClassificationIdentifier))
      {
         $this->ClassificationIdentifier = str_pad($this->ClassificationIdentifier, CONFIG_COLLECTIONS_CLASSIFICATION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
      }
   }

   /**
    * Deletes Classification and children of Classification from the database
    *
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_Collections WHERE ClassificationID = ?', 'integer', MDB2_PREPARE_RESULT);

      $result = $collIDPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows())
      {
         while($row = $result->fetchRow())
         {
            $collIDs[] = $row['ID'];
         }
      }


      $arrClassifications = $_ARCHON->getChildClassifications($this->ID);
      foreach($arrClassifications as $classification)
      {
         $result = $collIDPrep->execute($classification->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            while($row = $result->fetchRow())
            {
               $collIDs[] = $row['ID'];
            }
         }
      }


      if(isset($collIDs))
      {
         FindingAidCache::setDirty($collIDs);
      }


      if(!$_ARCHON->deleteObject($this, MODULE_CLASSIFICATION, 'tblCollections_Classifications'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads Classification from the database
    *
    *
    * @return boolean
    */
   public function dbLoad($ForToString = false)
   {
      global $_ARCHON;

      $Fields = ($ForToString) ? $this->ToStringFields : array();

      if(!$_ARCHON->loadObject($this, 'tblCollections_Classifications', false, $Fields))
      {
         return false;
      }

      if($this->ParentID)
      {
         $this->Parent = New Classification($this->ParentID);
         $this->Parent->dbLoad($ForToString);
      }

      if($this->CreatorID)
      {
         $this->Creator = New Creator($this->CreatorID);
         $this->Creator->dbLoad();
      }

      if(isset($this->ClassificationIdentifier) && is_natural($this->ClassificationIdentifier))
      {
         $this->ClassificationIdentifier = str_pad($this->ClassificationIdentifier, CONFIG_COLLECTIONS_CLASSIFICATION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
      }

      return true;
   }

   /**
    * Loads Collections from the database
    *
    * This function loads collections that fall under this classification
    *
    * @return boolean
    */
   public function dbLoadCollections()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load collections: Classification ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load collections: Classification ID must be numeric.");
         return false;
      }

      $this->Collections = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT * FROM tblCollections_Collections WHERE ClassificationID = ? ORDER BY CollectionIdentifier, SortTitle";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Collections[$row['ID']] = New Collection($row);
      }
      $result->free();

      uasort($this->Collections, create_function('$a,$b', 'return strnatcmp($a->CollectionIdentifier, $b->CollectionIdentifier);'));

      return true;
   }

   /**
    * Stores Classification to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      //TODO: Fix the parent stuff in the $_ARCHON function.

      global $_ARCHON;

      if(is_natural($this->ClassificationIdentifier))
      {
         $this->ClassificationIdentifier = str_pad($this->ClassificationIdentifier, CONFIG_COLLECTIONS_CLASSIFICATION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
      }

      $checkquery = "SELECT ID FROM tblCollections_Classifications WHERE (ClassificationIdentifier = ? OR Title = ?) AND ParentID = ? AND ID != ?";
      $checktypes = array('text', 'text', 'integer', 'integer');
      $checkvars = array($this->ClassificationIdentifier, $this->Title, $this->ParentID, $this->ID);
      $checkqueryerror = "A Classification with the same ParentAndIdentifierOrTitle already exists in the database";
      $problemfields = array('ClassificationIdentifier', 'Title', 'ParentID');
      $requiredfields = array('Title');

      if(!$_ARCHON->storeObject($this, MODULE_CLASSIFICATION, 'tblCollections_Classifications', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      static $collIDPrep = NULL;
      $collIDPrep = $collIDPrep ? $collIDPrep : $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_Collections WHERE ClassificationID = ?', 'integer', MDB2_PREPARE_RESULT);

      $result = $collIDPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if($result->numRows())
      {
         while($row = $result->fetchRow())
         {
            $collIDs[] = $row['ID'];
         }
      }


      $arrClassifications = $_ARCHON->getChildClassifications($this->ID);
      foreach($arrClassifications as $classification)
      {
         $result = $collIDPrep->execute($classification->ID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         if($result->numRows())
         {
            while($row = $result->fetchRow())
            {
               $collIDs[] = $row['ID'];
            }
         }
      }


      if(isset($collIDs))
      {
         FindingAidCache::setDirty($collIDs);
      }


      return true;
   }

   public function hasChildren()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT ID FROM tblCollections_Classifications WHERE ParentID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $_ARCHON->mdb2->setLimit(1);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $hasChildren = false;

      if($result->numRows())
      {
         $hasChildren = true;
      }

      $result->free();

      return $hasChildren;
   }

   /**
    * Returns a formatted string of a traversal of subject instance
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateIdentifier[optional]
    * @param boolean $ConcatinateTitle[optional]
    * @param boolean $ConcatinateParentIdentifier[optional]
    * @param boolean $ConcatinateParentTitle[optional]
    * @param string $Delimiter[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE, $ConcatinateIdentifier = true, $ConcatinateTitle = true, $ConcatinateParentIdentifier = false, $ConcatinateParentTitle = false, $Delimiter = "/")
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Classification to string: Classification ID not defined.");
         return false;
      }

      // If data is not set, load classification.
      if(!$this->Title || (!$this->ClassificationIdentifier && $ConcatenateIdentifier))
      {
         $this->dbLoad(true);
      }

      if($this->ParentID && ($ConcatinateParentIdentifier || $ConcatinateParentTitle) && !$this->Parent)
      {
         $this->Parent = New Classification($this->ParentID);
         $this->Parent->dbLoad(true);
      }

      if($_ARCHON->QueryStringURL)
      {
         $q = '&amp;q=' . $_ARCHON->QueryStringURL;
      }

      $objTmp = $this;

      $String = '';

      while($objTmp)
      {
         if(is_natural($objTmp->ClassificationIdentifier))
         {
            $objTmp->ClassificationIdentifier = str_pad($objTmp->ClassificationIdentifier, CONFIG_COLLECTIONS_CLASSIFICATION_IDENTIFIER_MINIMUM_LENGTH, "0", STR_PAD_LEFT);
         }

         if((($objTmp->ID == $this->ID) && $ConcatinateIdentifier)
                 || (($objTmp->ID != $this->ID) && $ConcatinateParentIdentifier))
         {
            $encoding_substring = $objTmp->getString('ClassificationIdentifier');

            if((($objTmp->ID == $this->ID) && $ConcatinateTitle)
                    || (($objTmp->ID != $this->ID) && $ConcatinateParentTitle))
            {
               $encoding_substring .= ' ' . $objTmp->getString('Title');
            }
         }
         else if((($objTmp->ID == $this->ID) && $ConcatinateTitle)
                 || (($objTmp->ID != $this->ID) && $ConcatinateParentTitle))
         {
            $encoding_substring = $objTmp->getString('Title');
         }

         $String = ($MakeIntoLink == LINK_EACH) ? "<a href='?p=collections/classifications&amp;id={$objTmp->ID}{$q}'>$encoding_substring</a>" . $String : $encoding_substring . $String;

         if($objTmp->ParentID && ($ConcatinateParentIdentifier || $ConcatinateParentTitle))
         {
            $String = $Delimiter . $String;

            $objTmp = $objTmp->Parent;
         }
         else
         {
            $objTmp = NULL;
         }
      }

      if($MakeIntoLink == LINK_TOTAL)
      {
         $String = "<a href='?p=collections/classifications&amp;id={$this->ID}{$q}'>$String</a>";
      }

      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $ConcatinateTitle && $_ARCHON->Security->verifyPermissions(MODULE_CLASSIFICATION, UPDATE))
      {


         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='?p=admin/collections/classification&amp;id={$this->ID}&amp;parentid={$this->ParentID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }

      return $String;
   }

   /*    * *********************************************************** */




   /**
    * These variables correspond directly to the fields in the tblCollections_Classifications table.
    */

   /** @var int */
   public $ID = 0;
   /** @var int */
   public $ClassificationIdentifier = '';
   /** @var string */
   public $Title = '';
   /** @var string */
   public $Description = '';
   /** @var int */
   public $ParentID = 0;
   /** @var int */
   public $CreatorID = 0;

   /**
    * These variables store related objects.
    */
   /** @var Classification */
   public $Parent = NULL;
   /** @var Creator */
   public $Creator = NULL;
   /** @var Collections[] */
   public $Collections = array();
   /**
    * Contains child Classifications
    *
    * @var Classification[]
    */
   public $Classifications = array();
   public $ToStringFields = array('ID', 'ClassificationIdentifier', 'Title', 'ParentID');
}

$_ARCHON->mixClasses('Classification', 'Collections_Classification');
?>