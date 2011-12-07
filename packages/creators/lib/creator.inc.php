<?php
abstract class Creators_Creator
{
   /**
    * Deletes Creator from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_CREATORS, 'tblCreators_Creators'))
      {
         return false;
      }


      if(!$_ARCHON->deleteRelationship('tblCreators_CreatorCreatorIndex', 'CreatorID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      if(!$_ARCHON->deleteRelationship('tblCreators_CreatorCreatorIndex', 'RelatedCreatorID', $ID, MANY_TO_MANY))
      {
         return false;
      }

      return true;
   }





   /**
    * Loads Creator from the database
    *
    * @return boolean
    */
   public function dbLoad($ForToString = false)
   {
      global $_ARCHON;

      $Fields = ($ForToString) ? $this->ToStringFields : array();

      if(!$_ARCHON->loadObject($this, 'tblCreators_Creators', false, $Fields))
      {
         return false;
      }

      $this->dbLoadRelatedObjects();

      return true;
   }


   public function dbLoadRelatedObjects()
   {
      if($this->CreatorTypeID)
      {
         $this->CreatorType = New CreatorType($this->CreatorTypeID);
         $this->CreatorType->dbLoad();
      }

      if($this->CreatorSourceID)
      {
         $this->CreatorSource = New CreatorSource($this->CreatorSourceID);
         $this->CreatorSource->dbLoad();
      }

      if($this->RepositoryID)
      {
         $this->Repository = New Repository($this->RepositoryID);
         $this->Repository->dbLoad();
      }

      if($this->LanguageID)
      {
         $this->Language = New Language($this->LanguageID);
         $this->Language->dbLoad();
      }

      if($this->ScriptID)
      {
         $this->Script = New Script($this->ScriptID);
         $this->Script->dbLoad();
      }
   }


   /**
    * Stores Creator to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkqueries = array();
      $checktypes = array();
      $checkvars = array();
      $checkqueryerrors = array();
      $problemfields = array();


      if($this->Identifier)
      {
         $checkqueries[] = "SELECT ID FROM tblCreators_Creators WHERE Identifier = ? AND RepositoryID = ? AND ID != ?";
         $checktypes[] = array('text', 'integer', 'integer');
         $checkvars[] = array($this->Identifier, $this->RepositoryID, $this->ID);
         $problemfields[] = array('Identifier');
         $checkqueryerrors[] = "A Creator with the same Identifier and RepositoryID already exists in the database";
      }
      
      if($this->Dates)
      {
         $datesquery = "AND Dates = ?";
         $datestypes = array('text');
         $datesvars = array($this->Dates);
      }
      else
      {
         $datesquery = "AND Dates IS NULL";
         $datestypes = array();
         $datesvars = array();
      }


      $checkqueries[] = "SELECT ID FROM tblCreators_Creators WHERE Name = ? AND CreatorTypeID = ? AND ID != ? $datesquery";
      $checktypes[] = array_merge(array('text', 'integer', 'integer'), $datestypes);
      $checkvars[] = array_merge(array($this->Name, $this->CreatorTypeID, $this->ID), $datesvars);



      $checkqueryerrors[] = "A Creator with the same Name, CreatorType and Dates already exists in the database";



      $problemfields[] = array('Name', 'Dates', 'CreatorTypeID');
      $requiredfields = array('Name', 'CreatorTypeID', 'RepositoryID', 'CreatorSourceID');

      if(!$_ARCHON->storeObject($this, MODULE_CREATORS, 'tblCreators_Creators', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }




   /**
    * Generates a formatted string of the Creator object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateParentBody[optional]
    * @return string
    */
   //public function toString($MakeIntoLink = LINK_NONE, $ConcatinateParentBody = false)
   public function toString($MakeIntoLink = LINK_NONE)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Creator to string: Creator ID not defined.");
         return false;
      }

      if(!$this->Name)
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }

      $String = $this->getString('Name');

      //if($ConcatinateParentBody && $this->ParentBody)
      //{
      //    $String = $this->getString('ParentBody') . '. ' . $String;
      //}

      if($this->Dates)
      {
         $String .= ' (' . $this->getString('Dates') . ')';
      }

      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         //            if($_ARCHON->QueryStringURL)
         //            {
         //                $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         //            }
         $String = "<a href='?p=creators/creator&amp;id={$this->ID}'>{$String}</a>";
      }

      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $_ARCHON->Security->verifyPermissions(MODULE_CREATORS, UPDATE))
      {
         

         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='?p=admin/creators/creators&amp;id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }

      return $String;
   }




   public function dbLoadRelatedCreatorsForToString()
   {
      return $this->dbLoadRelatedCreators(true);
   }


   /**
    * Loads Creators for Creator instance
    *
    * @return boolean
    */
   public function dbLoadRelatedCreators($ForToString = false)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Creators: Creator ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Creators: Creator ID must be numeric.");
         return false;
      }

      $this->CreatorRelationships = array();

      //    $query = "SELECT tblCreators_CreatorCreatorIndex.*, tblCreators_Creators.* FROM tblCreators_CreatorCreatorIndex JOIN tblCreators_Creators ON tblCreators_Creators.ID = tblCreators_CreatorCreatorIndex.RelatedCreatorID WHERE tblCreators_CreatorCreatorIndex.CreatorID = ? ORDER BY tblCreators_Creators.Name";
      $query = "SELECT tblCreators_CreatorCreatorIndex.* FROM tblCreators_CreatorCreatorIndex JOIN tblCreators_Creators ON tblCreators_Creators.ID = tblCreators_CreatorCreatorIndex.RelatedCreatorID WHERE tblCreators_CreatorCreatorIndex.CreatorID = ? ORDER BY tblCreators_Creators.Name";
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


      while($row = $result->fetchRow())
      {
         $objCreatorRelationship = New CreatorRelationship($row);

         $this->CreatorRelationships[$row['ID']] = $objCreatorRelationship;
         $this->CreatorRelationships[$row['ID']]->dbLoadCreator($ForToString);
         $this->CreatorRelationships[$row['ID']]->setCreatorRelationshipType();
      }
      $result->free();
      $prep->free();


      return true;
   }


//   public function verifyRepositoryPermissions()
//   {
//      global $_ARCHON;
//
//      if(!$_ARCHON->Security->Session->User->RepositoryLimit)
//      {
//         return true;
//      }
//
//      if($this->ID) // Old repository may be disallowed.
//
//      {
//         static $prep = NULL;
//         if(!isset($prep))
//         {
//            $query = "SELECT RepositoryID FROM tblCreators_Creators WHERE ID = ?";
//            $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
//         }
//         $result = $prep->execute($this->ID);
//         if (PEAR::isError($result))
//         {
//            trigger_error($result->getMessage(), E_USER_ERROR);
//         }
//
//         if($row = $result->fetchRow())
//         {
//            $prevRepositoryID = $row['RepositoryID'];
//         }
//         $result->free();
//
//         if(!$prevRepositoryID || array_key_exists($prevRepositoryID, $_ARCHON->Security->Session->User->Repositories) == false)
//         {
//            return false;
//         }
//      }
//
//      if(!$this->RepositoryID || array_key_exists($this->RepositoryID, $_ARCHON->Security->Session->User->Repositories) == false)
//      {
//         return false;
//      }
//
//      return true;
//   }




   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_CREATORS, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_CREATORS, UPDATE)))
      {
         return false;
      }

//      if(!$this->verifyRepositoryPermissions())
//      {
//         $_ARCHON->declareError("Could not store Creator: Creators may only be altered for the primary repository.");
//         return false;
//      }

      return true;
   }




   // These publiciables correspond directly to the fields in the tblCreators_Creators table
   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var string
    */
   public $Name = '';

   /**
    * @var string
    */
   public $NameFullerForm = '';

   /**
    * @var string
    */
   public $NameVariants = '';

   /**
    * @var integer
    */
   public $CreatorSourceID = 0;

   /**
    * @var integer
    */
   public $CreatorTypeID = 0;

   /**
    * @var String
    */
   public $Identifier = '';

   /**
    * @var string
    */
   public $Dates = '';

   /**
    * @var string
    */
   public $BiogHistAuthor = '';

   /**
    * @var string
    */
   public $BiogHist = '';

   /**
    * @var string
    */
   public $Sources = '';

   /**
    * @var CreatorRelationships[]
    */
   public $CreatorRelationships = array();


   /** @var integer */
   public $RepositoryID = 0;

   /** @var integer */
   public $LanguageID = 0;

   /** @var integer */
   public $ScriptID = 0;

   // These public variables are loaded from other tables, but relate to the creator

   /**
    * @var CreatorType
    */
   public $CreatorType = NULL;

   /**    
    * @var CreatorSource
    */
   public $CreatorSource = NULL;

   /**
    * @var Repository
    */
   public $Repository = NULL;
   /**
    * @var Language
    */
   public $Language = NULL;
   /**
    * @var CreatorType
    */
   public $Script = NULL;

   public $Creators = array();

   public $ToStringFields = array('ID', 'Name', 'Dates', 'CreatorTypeID');
}

$_ARCHON->mixClasses('Creator', 'Creators_Creator');

?>
