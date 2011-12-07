<?php
abstract class Core_Repository
{
   /**
    * Deletes Repository from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;
      
      
      if(CONFIG_CORE_DEFAULT_REPOSITORY == $this->ID)
      {
         $_ARCHON->declareError('Could not delete Repository: Repository is default. Please set new default before deleting.');
         return false;
      }
      

      if(!$_ARCHON->deleteObject($this, MODULE_REPOSITORIES, 'tblCore_Repositories'))
      {
         return false;
      }

      return true;
   }


   /**
    * Loads Repository from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCore_Repositories'))
      {
         return false;
      }

      if($this->CountryID)
      {
         $this->Country = new Country($this->CountryID);
         $this->Country->dbLoad();
      }
      else
      {
         $this->CountryID = 226; //US
         $this->Country = new Country($this->CountryID);
         $this->Country->dbLoad();
      }

      return true;
   }





   /**
    * Loads Users for Repository from the database
    *
    * @return boolean
    */
   public function dbLoadUsers()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Users: Repository ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Users: Repository ID must be numeric.");
         return false;
      }

      $this->Users = array();

      $query = "SELECT tblCore_Users.* FROM tblCore_Users JOIN tblCore_UserRepositoryIndex ON tblCore_Users.ID = tblCore_UserRepositoryIndex.UserID WHERE tblCore_UserRepositoryIndex.RepositoryID = ? ORDER BY tblCore_Users.LastName, tblCore_Users.FirstName";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);

      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         return true;
      }

      while($row = $result->fetchRow())
      {
         $this->Users[$row['ID']] = New User($row);
      }

      $result->free();
      $prep->free();

      return true;
   }



   public function dbUpdateRelatedUsers($arrRelatedIDs)
   {
      global $_ARCHON;

      if(!$_ARCHON->updateObjectRelations($this, MODULE_REPOSITORIES, 'User', 'tblCore_UserRepositoryIndex', 'tblCore_Users', $arrRelatedIDs))
      {
         return false;
      }

      return true;
   }




   /**
    * Stores Repository to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      if(($this->ZIPCode && !is_natural($this->ZIPCode)) || ($this->ZIPPlusFour && !is_natural($this->ZIPPlusFour)))
      {
         $_ARCHON->declareError("Could not store Repository: Repository ZIPCode must be numeric.");
         $_ARCHON->ProblemFields[] = 'ZIPCode';
         return false;
      }

      if($this->URL && !preg_match('/[\w\d]+:\/\//u', $this->URL))
      {
         $this->URL = 'http://' . $this->URL;
      }

      $checkquery = "SELECT ID FROM tblCore_Repositories WHERE Name = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->Name, $this->ID);
      $checkqueryerror = "A Repository with the same Name already exists in the database";
      $problemfields = array('Name');
      $requiredfields = array('Name', 'CountryID');

      if(!$_ARCHON->storeObject($this, MODULE_REPOSITORIES, 'tblCore_Repositories', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }








   /**
    * Generates a formatted string of the Repository object
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
         $_ARCHON->declareError("Could not convert Repository to string: Repository ID not defined.");
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

      if($this->Dates)
      {
         $String .= ' (' . $this->getString('Dates') . ')';
      }

      if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
      {
         if($_ARCHON->QueryStringURL)
         {
            $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         }

         $String = "<a href='?p=core/search&amp;repositoryid={$this->ID}'>{$String}</a>";
      }

      if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $_ARCHON->Security->verifyPermissions(MODULE_REPOSITORIES, UPDATE))
      {
         

         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='?p=admin/core/repositories&id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }

      return $String;
   }




   /**
    * @var int
    */
   public $ID = 0;

   /**
    * @var string
    */
   public $Name = '';

   /**
    * @var string
    */
   public $Administrator = '';

   /**
    * @var string
    */
   public $Code = '';

   /**
    * @var string
    */
   public $Address = '';

   /**
    * @var string
    */
   public $Address2 = '';

   /**
    * @var string
    */
   public $City = '';

   /**
    * @var string
    */
   public $State = '';

   /**
    * @var integer
    */

   public $CountryID = 0;

   /**
    * @var string
    */

   public $ZIPCode = '';

   /**
    * @var string
    */
   public $ZIPPlusFour = '';

   /**
    * @var string
    */
   public $Phone = '';

   /**
    * @var string
    */
   public $PhoneExtension = '';

   /**
    * @var string
    */
   public $Fax = '';

   /**
    * @var string
    */
   public $Email = '';

   /**
    * @var string
    */
   public $URL = '';

   /**
    * @var string
    */
   public $EmailSignature = '';

   /**
    * @var Object
    */
   public $Country = NULL;


   public $TemplateSet = '';

   public $ResearchFunctionality = RESEARCH_ALL;
}

$_ARCHON->mixClasses('Repository', 'Core_Repository');
?>