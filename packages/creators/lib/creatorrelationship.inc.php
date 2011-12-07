<?php

abstract class Creators_CreatorRelationship
{

   public function setCreatorRelationshipType()
   {
      $this->CreatorRelationshipType = new CreatorRelationshipType($this->CreatorRelationshipTypeID);
      $this->CreatorRelationshipType->dbLoad();
   }

   /**
    * Deletes Creator from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {

      global $_ARCHON;

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT CreatorID FROM tblCreators_CreatorCreatorIndex WHERE ID = ?";
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $CreatorID = $row['CreatorID'] ? $row['CreatorID'] : 0;

      if(!$_ARCHON->deleteObject($this, MODULE_CREATORS, 'tblCreators_CreatorCreatorIndex'))
      {
         return false;
      }

      $reciprocal = $this->getReciprocalRelationship();
      if($reciprocal)
      {
         $reciprocal->dbDelete();
      }

      $_ARCHON->log("tblCreators_Creators", $CreatorID);

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

      if(!$_ARCHON->loadObject($this, 'tblCreators_CreatorCreatorIndex'))
      {
         return false;
      }

      $this->dbLoadCreator($ForToString);

      $this->setCreatorRelationshipType();

      return true;
   }

   /**
    * Loads Creator object from database
    *
    * @return boolean
    */
   public function dbLoadCreator($ForToString = false)
   {
      if(!$this->RelatedCreatorID)
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }

      $this->RelatedCreator = new Creator($this->RelatedCreatorID);
      $this->RelatedCreator->dbLoad($ForToString);
   }

   /**
    * Stores Creator to the database
    *
    * @return boolean
    */
   public function dbStore()
   {

      global $_ARCHON;

      if($this->CreatorID == $this->RelatedCreatorID)
      {
         $_ARCHON->declareError("Could not store Creator Relationship: Cannot relate a Creator to itself.");
         return false;
      }

      $checkquery = "SELECT ID FROM tblCreators_CreatorCreatorIndex WHERE CreatorID = ? AND RelatedCreatorID = ? AND ID != ?";
      $checktypes = array('integer', 'integer', 'integer');
      $checkvars = array($this->CreatorID, $this->RelatedCreatorID, $this->ID);
      $checkqueryerror = "A CreatorRelationship already exists in the database";
      $problemfields = array('CreatorID', 'RelatedCreatorID', 'CreatorRelationshipTypeID');
      $requiredfields = array('CreatorID', 'RelatedCreatorID', 'CreatorRelationshipTypeID');
      if(!$_ARCHON->storeObject($this, MODULE_CREATORS, 'tblCreators_CreatorCreatorIndex', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      if($this->ID == 0)
      {
         switch($this->CreatorRelationshipTypeID)
         {
            case(2):
               $reciprocal = New CreatorRelationship();
               $reciprocal->CreatorRelationshipTypeID = 3;
               $reciprocal->CreatorID = $this->RelatedCreatorID;
               $reciprocal->RelatedCreatorID = $this->CreatorID;
               $reciprocal->dbStore();
               break;
            case(3):
               $reciprocal = New CreatorRelationship();
               $reciprocal->CreatorRelationshipTypeID = 2;
               $reciprocal->CreatorID = $this->RelatedCreatorID;
               $reciprocal->RelatedCreatorID = $this->CreatorID;
               $reciprocal->dbStore();               
               break;
            case(4):
               $reciprocal = New CreatorRelationship();
               $reciprocal->CreatorRelationshipTypeID = 5;
               $reciprocal->CreatorID = $this->RelatedCreatorID;
               $reciprocal->RelatedCreatorID = $this->CreatorID;
               $reciprocal->dbStore();               
               break;
            case(5):
               $reciprocal = New CreatorRelationship();
               $reciprocal->CreatorRelationshipTypeID = 4;
               $reciprocal->CreatorID = $this->RelatedCreatorID;
               $reciprocal->RelatedCreatorID = $this->CreatorID;
               $reciprocal->dbStore();             
               break;
         }
      }
      $_ARCHON->log("tblCreators_Creators", $this->CreatorID);

      return true;
   }

   public function getReciprocalRelationship()
   {
      global $_ARCHON;
      $query = "SELECT * FROM tblCreators_CreatorCreatorIndex WHERE CreatorID = ? AND RelatedCreatorID = ? AND CreatorRelationshipTypeID = ?";
      $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);


      switch($this->CreatorRelationshipTypeID)
      {
         case(2):
            $result = $prep->execute(array($this->RelatedCreatorID, $this->CreatorID, 3));
            break;
         case(3):
            $result = $prep->execute(array($this->RelatedCreatorID, $this->CreatorID, 2));
            break;
         case(4):
            $result = $prep->execute(array($this->RelatedCreatorID, $this->CreatorID, 5));
            break;
         case(5):
            $result = $prep->execute(array($this->RelatedCreatorID, $this->CreatorID, 4));
            break;
         default:
            return NULL;
      }
      if(!$result->numRows())
      {
         return NULL;
      }

      $row = $result->fetchRow();
      return New CreatorRelationship($row);
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
   public function toString($MakeIntoLink = LINK_NONE)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Creator to string: Creator ID not defined.");
         return false;
      }

      if(!$this->RelatedCreatorID)
      {
         if(!$this->dbLoad(true))
         {
            return false;
         }
      }

      $strRelatedCreator = $this->RelatedCreator->getString('Name');


      //@TODO: make relationships phrases, maybe define the types since static functions don't play nice
      switch($this->CreatorRelationshipTypeID)
      {
         case(1):
            $strRelationship = "Alternate Identity";
            break;
         case(2):
            if($this->RelatedCreator->CreatorType == "Corporate Name")
            {
               $strRelationship = "Parent Body";
            }
            else
            {
               $strRelationship = "Parent";
            }
            break;
         case(3):
            if($this->RelatedCreator->CreatorType == "Corporate Name")
            {
               $strRelationship = "Subordinate Body";
            }
            else
            {
               $strRelationship = "Child";
            }
            break;
         case(4):
            if($this->RelatedCreator->CreatorType == "Corporate Name")
            {
               $strRelationship = "Predecessor Body";
            }
            else
            {
               $strRelationship = "Predecessor";
            }
            break;
         case(5):
            if($this->RelatedCreator->CreatorType == "Corporate Name")
            {
               $strRelationship = "Successor Body";
            }
            else
            {
               $strRelationship = "Successor";
            }
            break;
         case(6):
            $strRelationship = "Related Family";
            break;
         case(7):
            $strRelationship = "Associate";
            break;
      }

      $String = $strRelatedCreator . " (" . $strRelationship . ")";


      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         $String = "<a href='?p=creators/creator&amp;id={$this->RelatedCreatorID}'>{$String}</a>";
      }

      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $_ARCHON->Security->verifyPermissions(MODULE_CREATORS, UPDATE))
      {


         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='?p=admin/creators/creators&amp;id={$this->RelatedCreatorID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }

      return $String;
   }

   // These public  variables correspond directly to the fields in the tblCreators_Creators table
   /**
    * @var integer
    */
   public $ID = 0;

   /**
    * @var string
    */
   public $CreatorID = '';

   /**
    * @var string
    */
   public $RelatedCreatorID = '';

   /**
    * @var string
    */
   // public $PrimaryCreator = '';
   /**
    * @var integer
    */
   public $CreatorRelationshipTypeID = 0;

   /**
    * @var object
    */
   public $CreatorRelationshipType = NULL;

   /**
    * @var string
    */
   public $Description = '';

   /**
    * @var string
    */
   public $RelatedCreator = NULL;
   public $ToStringFields = array('ID', 'RelatedCreatorID', 'CreatorRelationshipTypeID');

}

$_ARCHON->mixClasses('CreatorRelationship', 'Creators_CreatorRelationship');
?>
