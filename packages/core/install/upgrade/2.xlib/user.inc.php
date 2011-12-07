<?php
abstract class Core_User2x
{
   /**
    * Activates a pending User's account
    *
    * @param string $PendingCode
    * @return boolean
    */
   public function dbActivate($PendingCode)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not verify PendingCode: User ID not defined.");
         return false;
      }

      // If the PendingHash is not set, load the user
      if(!$this->PendingHash)
      {
         if(!$this->dbLoad())
         {
            $_ARCHON->declareError("Could not verify PendingCode: There was already an error.");
            return false;
         }
      }

      if(!$this->Pending)
      {
         $_ARCHON->declareError("Could not verify PendingCode: Account has already been verified.");
         return false;
      }

      if(!CONFIG_CORE_VERIFY_PUBLIC_ACCOUNTS || ($this->PendingHash == md5($PendingCode)))
      {

         $query = "UPDATE tblCore_Users SET Pending = 0, PendingHash = NULL WHERE ID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer'), MDB2_PREPARE_MANIP);
         $affected = $prep->execute(array($this->ID));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
         $prep->free();

         $this->Pending = 0;
         $this->PendingHash = NULL;

         return true;
      }
      else
      {
         return false;
      }
   }





   /**
    * Deletes User from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(!$_ARCHON->deleteObject($this, MODULE_USERS, 'tblCore_Users'))
      {
         return false;
      }

      $prep = $this->mdb2->prepare("DELETE FROM tblCore_Sessions WHERE UserID = ?", 'integer', MDB2_PREPARE_MANIP);
      $prep->execute($ID);

      $prep = $this->mdb2->prepare("DELETE FROM tblCore_UserHomeWidgetsIndex WHERE UserID = ?", 'integer', MDB2_PREPARE_MANIP);
      $prep->execute($ID);

      $prep = $this->mdb2->prepare("DELETE FROM tblCore_UserPermissions WHERE UserID = ?", 'integer', MDB2_PREPARE_MANIP);
      $prep->execute($ID);

      $prep = $this->mdb2->prepare("DELETE FROM tblCore_UserUserProfileFieldIndex WHERE UserID = ?", 'integer', MDB2_PREPARE_MANIP);
      $prep->execute($ID);

      return true;
   }






   /**
    * Loads User from the database
    *
    * @return boolean
    */
   public function dbLoad($ModuleID = NULL)
   {
      global $_ARCHON;

      // sa
      if($this->ID == -1)
      {
         $query = "SELECT Value FROM tblCore_Configuration WHERE Directive = 'SA Password'";
         $result = $_ARCHON->mdb2->query($query);
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         $row = $result->fetchRow();

         $this->Login = 'sa';
         $this->PasswordHash = $row['Value'];
         $this->DisplayName = 'Super Administrator';
         $this->Usergroup->DefaultPermissions = FULL_CONTROL | DELETE | UPDATE | ADD | READ;

         return true;
      }

      if(!$_ARCHON->loadObject($this, 'tblCore_Users'))
      {
         return false;
      }

      $this->dbLoadPermissions($ModuleID);
      //$this->dbLoadHomeWidgets();
      // $this->dbLoadUserProfileFields();

      $this->Usergroup = New Usergroup2x($this->UsergroupID);
      $this->Usergroup->dbLoad();

      return true;
   }





   /**
    * Loads User home Widgets from the database
    *
    * @return boolean
    */
   public function dbLoadHomeWidgets()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load UserPermissions: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load UserPermissions: User ID must be numeric.");
         return false;
      }

      $this->HomeWidgets = array();

      $prep = $_ARCHON->mdb2->prepare("SELECT * FROM tblCore_UserHomeWidgetsIndex WHERE UserID = ? ORDER BY DisplayOrder", 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->HomeWidgets[$row['ID']]->PackageID = $row['PackageID'];
         $this->HomeWidgets[$row['ID']]->WidgetCode = $row['WidgetCode'];
         $this->HomeWidgets[$row['ID']]->DisplayOrder = $row['DisplayOrder'];
      }
      $result->free();
      $prep->free();
   }





   /**
    * Loads User Permissions from the database
    *
    * @return boolean
    */
   public function dbLoadPermissions($ModuleID = NULL)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load UserPermissions: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load UserPermissions: User ID must be numeric.");
         return false;
      }

      if($ModuleID)
      {
         $prep = $_ARCHON->mdb2->prepare("SELECT * FROM tblCore_UserPermissions WHERE UserID = ? AND ModuleID = ?", array('integer', 'integer'), MDB2_PREPARE_RESULT);
         $result = $prep->execute(array($this->ID, $moduleID));
      }
      else
      {
         $prep = $_ARCHON->mdb2->prepare("SELECT * FROM tblCore_UserPermissions WHERE UserID = ?", 'integer', MDB2_PREPARE_RESULT);
         $result = $prep->execute($this->ID);
      }
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $this->Permissions[$row['ModuleID']] = intval($row['Permissions']);
      }
      $result->free();
      $prep->free();
   }





   /**
    * Loads UserProfileFields for User
    *
    * @return boolean
    */
   public function dbLoadUserProfileFields()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load UserProfileFields: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load UserProfileFields: User ID must be numeric.");
         return false;
      }

      $this->UserProfileFields = $_ARCHON->getAllUserProfileFields();

      $query = "SELECT tblCore_UserProfileFields.*, tblCore_UserUserProfileFieldIndex.Value FROM tblCore_UserProfileFields JOIN tblCore_UserUserProfileFieldIndex ON tblCore_UserProfileFields.ID = tblCore_UserUserProfileFieldIndex.UserProfileFieldID WHERE tblCore_UserUserProfileFieldIndex.UserID = ? ORDER BY tblCore_UserProfileFields.PackageID, tblCore_UserProfileFields.UserProfileField;";
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
         $this->UserProfileFields[$row['ID']] = clone $this->UserProfileFields[$row['ID']];
         $this->UserProfileFields[$row['ID']]->Value = $row['Value'];
      }

      $result->free();
      $prep->free();

      return true;
   }








   /**
    * Sets User LanguageID in the database
    *
    * @param integer LanguageID
    * @return boolean
    */
   public function dbSetLanguageID($LanguageID)
   {
      global $_ARCHON;

      // Check permissions
      if(!($_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE) || ($_ARCHON->Security->verifyPermissions(MODULE_MYPREFERENCES, UPDATE) && ($_ARCHON->Security->Session->getUserID() == $this->ID))))
      {
         $_ARCHON->declareError("Could not set Language: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not set Language: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not set Language: User ID must be numeric.");
         return false;
      }

      if(!is_natural($LanguageID))
      {
         $_ARCHON->declareError("Could not set Language: Language ID must be numeric.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "UPDATE tblCore_Users SET LanguageID = ? WHERE ID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($LanguageID, $this->ID));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $_ARCHON->log("tblCore_Users", $this->ID);
      $this->LanguageID = $LanguageID;

      return true;
   }





   /**
    * Sets User Password in the database
    *
    * @param string $Password
    * @return boolean
    */
   public function dbSetPassword($Password)
   {
      global $_ARCHON;

      // Check permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE) && $_ARCHON->Security->Session->User->ID != $this->ID)
      {
         $_ARCHON->declareError("Could not set Password: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not set Password: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not set Password: User ID must be numeric.");
         return false;
      }

      if(!$Password)
      {
         $_ARCHON->declareError("Could not set Password: User Password not defined.");
         return false;
      }

      $pwhash = crypt($Password, crypt($Password));

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "UPDATE tblCore_Users SET PasswordHash = ? WHERE ID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('text', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($pwhash, $this->ID));

      $_ARCHON->log("tblCore_Users", $this->ID);
      $this->PasswordHash = $pwhash;

      return true;
   }

   /**
    * Sets User Scratchpad in the database
    *
    * @param integer LanguageID
    * @return boolean
    */
   public function dbSetScratchpad($Note)
   {
      global $_ARCHON;

      // Check permissions
      if(!($_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE) || ($_ARCHON->Security->verifyPermissions(MODULE_MYPREFERENCES, UPDATE) && ($_ARCHON->Security->Session->getUserID() == $this->ID))))
      {
         $_ARCHON->declareError("Could not set Scratchpad: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not set Scratchpad: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not set Scratchpad: User ID must be numeric.");
         return false;
      }


      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "UPDATE tblCore_Users SET Scratchpad = ? WHERE ID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('text', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($Note, $this->ID));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $_ARCHON->log("tblCore_Users", $this->ID);
      $this->Scratchpad = $Scratchpad;



      return true;
   }




   /**
    * Sets User Permissions in database for a particular module
    *
    * @param integer $ModuleID
    * @param integer $Permissions
    * @return boolean
    */
   public function dbSetPermissions($ModuleID, $Permissions)
   {
      global $_ARCHON;

      // Check permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE))
      {
         $_ARCHON->declareError("Could not set Permissions: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not set Permissions: User ID not defined.");
         return false;
      }

      if(!$ModuleID)
      {
         $_ARCHON->declareError("Could not set Permissions: Module ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not set Permissions: User ID must be numeric.");
         return false;
      }

      if(!is_natural($ModuleID))
      {
         $_ARCHON->declareError("Could not set Permissions: Module ID must be numeric.");
         return false;
      }

      if(!isset($Permissions))
      {
         $_ARCHON->declareError("Could not set Permissions: User Permissions not defined.");
         return false;
      }

      $this->dbUnsetPermissions($ModuleID);

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "INSERT INTO tblCore_UserPermissions (
	            UserID,
	            ModuleID,
	            Permissions
	         ) VALUES (
	            ?,
	            ?,
	            ?
	         )";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($this->ID, $ModuleID, $Permissions));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $_ARCHON->log("tblCore_UserPermissions", $this->ID);
      $this->dbLoadPermissions();

      return true;
   }




   /**
    * Set UserProfileFieldValue for User
    *
    * @param integer $UserProfileFieldID
    * @param string $Value
    * @return boolean
    */
   public function dbSetUserProfileField($UserProfileFieldID, $Value)
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE) && $_ARCHON->Security->Session->User->ID != $this->ID && $this->NewID != $this->ID)
      {
         $_ARCHON->declareError("Could not relate UserProfileField: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not relate UserProfileField: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not relate UserProfileField: User ID must be numeric.");
         return false;
      }

      if(!is_natural($UserProfileFieldID) || !$UserProfileFieldID)
      {
         $_ARCHON->declareError("Could not relate UserProfileField: UserProfileField ID must be numeric.");
         return false;
      }

      $objUserProfileField = New UserProfileField($UserProfileFieldID);

      if(!$objUserProfileField->dbLoad())
      {
         $_ARCHON->declareError("Could not relate UserProfileField: There was already an error.");
         return false;
      }

      if($Value && $objUserProfileField->Pattern && !$objUserProfileField->Pattern->match($Value))
      {
         $_ARCHON->declareError("Could not relate UserProfileField: $Value is not a valid {$objUserProfileField->Pattern->Name}.");
         return false;
      }

      $prep = $_ARCHON->mdb2->prepare("DELETE FROM tblCore_UserUserProfileFieldIndex WHERE UserID = ? AND UserProfileFieldID = ?", array('integer', 'integer'), MDB2_PREPARE_MANIP);
      $affected = $prep->execute(array($this->ID, $UserProfileFieldID));

      unset($this->UserProfileFields[$UserProfileFieldID]);

      if($Value)
      {
         static $insertPrep = NULL;
         if(!isset($insertPrep))
         {
            $query = "INSERT INTO tblCore_UserUserProfileFieldIndex (UserID, UserProfileFieldID, Value) VALUES (?, ?, ?)";
            $insertPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'text'), MDB2_PREPARE_MANIP);
         }
         $affected = $insertPrep->execute(array($this->ID, $UserProfileFieldID, $Value));

         if(PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);

            $_ARCHON->declareError("Could not relate UserProfileField: Unable to update the database table.");
            return false;
         }

         // Add the userprofilefield to the Users UserProfileFields[] array
         $objUserProfileField = New UserProfileField($userprofilefieldrow);
         $this->UserProfileFields[$UserProfileFieldID] = $objUserProfileField;
         $this->UserProfileFields[$UserProfileFieldID]->Value = $Value;
      }

      $_ARCHON->log("tblCore_Users", $this->ID);

      return true;
   }




   /**
    * Stores User to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $ID = $this->ID;

      if(($this->ID == 0 && !$this->Password))
      {
         $_ARCHON->declareError("Could not store User: Password not defined.");
         $_ARCHON->ProblemFields[] = 'Password';
         return false;
      }

      if($this->Login == 'sa')
      {
         $_ARCHON->declareError("Could not store User: The sa account can only be altered from the Archon Configuration module.");
         return false;
      }

      $ignoredfields = array('Password');

      if($this->ID == 0)
      {
         $this->PasswordHash = crypt($this->Password, crypt($this->Password));
         $this->RegisterTime = time();

         $newaccount = true; // needed to determine if account was just added after success
      }
      else
      {
         $ignoredfields[] = 'PasswordHash';
      }

      $checkquery = "SELECT ID FROM tblCore_Users WHERE Login = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->Login, $this->ID);
      $checkqueryerror = "A User with the same Login already exists in the database";

      $checkquery = "SELECT ID FROM tblCore_Users WHERE Email = ? AND ID != ?";
      $checktypes = array('text', 'integer');
      $checkvars = array($this->Email, $this->ID);
      $checkqueryerror = "A User with the same Email already exists in the database";

      $problemfields = array('Login', 'Email', 'UsergroupID', 'FirstName', 'LastName', 'DisplayName');
      $requiredfields = array('Login', 'Email', 'UsergroupID', 'FirstName', 'LastName', 'DisplayName');

      if(!$_ARCHON->storeObject($this, MODULE_USERS, 'tblCore_Users', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields, $ignoredfields))
      {
         return false;
      }

      if($ID > 0 && $this->Password)
      {
         if(!$this->dbSetPassword($this->Password))
         {
            $_ARCHON->declareError("Could not update User: Unable to set Password.");
            return false;
         }
      }

      if($newaccount && $this->Pending)
      {
         $this->NewID = $this->ID;

         $this->sendActivationEmail($this->PendingCode);
         unset($this->PendingCode);
      }

      return true;
   }
   



   /**
    * Unsets User Permissions for a particular module
    *
    * @param integer $ModuleID
    * @return boolean
    */
   public function dbUnsetPermissions($ModuleID)
   {
      global $_ARCHON;

      // Check permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE))
      {
         $_ARCHON->declareError("Could not unset Permissions: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not unset Permissions: User ID not defined.");
         return false;
      }

      if(!$ModuleID)
      {
         $_ARCHON->declareError("Could not unset Permissions: Module ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not unset Permissions: User ID must be numeric.");
         return false;
      }

      if(!is_natural($ModuleID))
      {
         $_ARCHON->declareError("Could not unset Permissions: Module ID must be numeric.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "DELETE FROM tblCore_UserPermissions WHERE UserID = ? AND ModuleID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $prep->execute(array($this->ID, $ModuleID));
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if($affected > 0)
      {
         $_ARCHON->log("tblCore_UserPermissions", $this->ID);
      }

      $this->dbLoadPermissions();

      return true;
   }





   /**
    * Sends verification e-mail to user
    *
    * @return boolean
    */
   public function sendActivationEmail($PendingCode)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not send VerificationEmail: User ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not send VerificationEmail: User ID must be numeric.");
         return false;
      }

      if(!$this->Email)
      {
         if(!$this->dbLoad())
         {
            $_ARCHON->declareError("Could not send VerificationEmail: There was already an error.");
            return false;
         }
      }

      $HttpHost = preg_match('/^[\d]+\.[\d]+\.[\d]+\.[\d]+$/u', $_SERVER['HTTP_HOST']) ? gethostbyaddr($_SERVER['HTTP_HOST']) : $_SERVER['HTTP_HOST'];
      $MailFrom = $_ARCHON->Repository->Email ? $_ARCHON->Repository->Email : "noreply@" . $HttpHost;

      $Message = "$this->FirstName $this->LastName,\n\nThank you for registering an account.  In order to activate your account, please click on the following link or copy and paste the URL into your web browser.\n\nhttp://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?p=core/register&f=activate&id=$this->ID&v=$PendingCode\n\n{$_ARCHON->Repository->Name}";

      if(mail(encoding_convert_encoding($this->Email, 'ISO-8859-1'), encoding_convert_encoding($_ARCHON->Repository->Name, 'ISO-8859-1') . ": Account Activation", encoding_convert_encoding($Message, 'ISO-8859-1'), "From: $MailFrom\r\n"))
      {
         return true;
      }
      else
      {
         $_ARCHON->declareError("Could not send VerificationEmail: MailFunction reported an error.");
         return false;
      }
   }




   /**
    * Generates a formatted string of the User object
    *
    * @todo Custom Formatting
    *
    * @return string
    */
   public function toString()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert User to string: User ID not defined.");
         return false;
      }

      if(!$this->Login)
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }

      $String = $this->getString('DisplayName');

      if($_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE) && !$_ARCHON->PublicInterface->DisableTheme && !$_ARCHON->AdministrativeInterface)
      {
         

         $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
         $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

         $String .= "<a href='?p=admin&amp;go=" . urlencode("?p=admin/core/users&id={$this->ID}") . "' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
      }

      return $String;
   }




   /**
    * Verifies a User's Password
    *
    * @param string $Password
    * @return boolean
    */
   public function verifyPassword($Password)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not verify Password: User ID not defined.");
         return false;
      }

      // If the PasswordHash is not set, load the user
      if(!$this->PasswordHash)
      {
         if(!$this->dbLoad())
         {
            $_ARCHON->declareError("Could not verify Password: There was already an error.");
            return false;
         }
      }

      return ($this->PasswordHash && ($this->PasswordHash == crypt($Password, $this->PasswordHash)));
   }




   /**
    * Verifies Permissions of User for a particular module
    *
    * @param integer $ModuleID
    * @param integer $AccessFlag
    * @return boolean
    */
   public function verifyPermissions($ModuleID, $AccessFlag)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not verify Permissions: User ID not defined.");
         return false;
      }

      // If the PasswordHash is not set, load the user
      if(!$this->PasswordHash)
      {
         if(!$this->dbLoad($ModuleID))
         {
            $_ARCHON->declareError("Could not verify Permissions: There was already an error.");
            return false;
         }
      }

      // Custom permissions for the user have the highest priority, then custom Usergroup permissions
      // if neither are set, use the Usergroup's default permissions.  Also, permissions can be 0, so
      // we must use the identical comparison operator.
      if($this->Permissions[$ModuleID] !== NULL)
      {
         $Permissions = $this->Permissions[$ModuleID];
      }
      else if($this->Usergroup)
      {
         if($this->Usergroup->Permissions[$ModuleID] !== NULL)
         {
            $Permissions = $this->Usergroup->Permissions[$ModuleID];
         }
         else
         {
            $Permissions = $this->Usergroup->DefaultPermissions;
         }
      }
      else
      {
         // No permissions are set at all, so deny any request.
         $Permissions = 0;
      }

      return (($Permissions & $AccessFlag) == $AccessFlag);
   }




   /**
    * Verifies Store Permissions of User
    *
    * @return boolean
    */
   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if($this->ID == 0)
      {
         if($_ARCHON->Security->verifyPermissions(MODULE_USERS, ADD))
         {
            return true;
         }
         elseif(CONFIG_CORE_PUBLIC_REGISTRATION_ENABLED)
         {
            if(!CONFIG_CORE_PUBLIC_REGISTRATION_USERGROUP)
            {
               $_ARCHON->declareError("Could not store User: Usergroup for public registration not defined.");
               return false;
            }
            elseif($this->UsergroupID != CONFIG_CORE_PUBLIC_REGISTRATION_USERGROUP)
            {
               $this->declareError("Could not store User: Permission Denied.");
               return false;
            }
            else
            {
               if(CONFIG_CORE_VERIFY_PUBLIC_ACCOUNTS)
               {
                  $this->Pending = 1;
                  $this->PendingCode = md5(mt_rand(0, mt_getrandmax()));
                  $this->PendingHash = md5($this->PendingCode);
               }

               return true;
            }
         }
         else
         {
            $this->declareError("Could not store User: Public registration is disabled.");
            return false;
         }
      }
      else
      {
         if($_ARCHON->Security->verifyPermissions(MODULE_USERS, UPDATE))
         {
            return true;
         }
         elseif($_ARCHON->Security->Session->User->ID == $this->ID)
         {
            $objTempUser = New User2x($this->ID);
            $objTempUser->dbLoad();

            $this->Login = $objTempUser->Login;
            $this->UsergroupID = $objTempUser->UsergroupID;
            $this->RepositoryID = $objTempUser->RepositoryID;
            $this->RepositoryLimit = $objTempUser->RepositoryLimit;
            $this->Locked = $objTempUser->Locked;
            $this->Pending = $objTempUser->Pending;
            $this->PendingHash = $objTempUser->PendingHash;

            unset($objTempUser);

            return true;
         }
         else
         {
            $this->declareError("Could not store User: Permission Denied.");
            return false;
         }
      }
   }

   // These variables correspond directly to the fields in the tblCore_Users table
   /**
    * @var integer
    **/
   public $ID = 0;

   /**
    * @var string
    **/
   public $Login = '';

   /**
    * @var string
    **/
   public $Email = '';

   /**
    * @var string
    **/
   public $FirstName = '';

   /**
    * @var string
    **/
   public $LastName = '';

   /**
    * @var string
    **/
   public $DisplayName = '';

   /**
    * @var integer
    */
   public $RegisterTime = 0;

   /**
    * @var integer
    */
   public $Pending = 0;

   /**
    * @var string
    */
   public $PendingHash = '';

   /**
    * @var integer
    **/
   public $UsergroupID = 0;

   /**
    *
    * @var integer
    */
   public $LanguageID = 0;

   /**
    * @var integer
    **/
   public $Locked = 0;

   /**
    * @var string
    */
   public $Scratchpad = '';

   /**
    * @var string
    **/
   public $PasswordHash = '';

   // These variables are loaded from other tables, but relate to the user
   /**
    * @var string
    **/
   public $Password = '';

   /**
    * @var Usergroup
    **/
   public $Usergroup = NULL;

   /**
    * @var integer[]
    **/
   public $Permissions = array();

   /**
    * @var object[][]
    */
   public $HomeWidgets = array();

   /**
    * @var integer
    **/
   private $NewID = 0;
}

$_ARCHON->mixClasses('User2x', 'Core_User2x');
?>
