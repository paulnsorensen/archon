<?php
abstract class Core_Security
{
   /**
    * ArchonSecurity Constructor
    *
    * @return ArchonSecurity
    */
   public function construct()
   {
      global $_ARCHON;

      unset($_REQUEST['archon']);
      unset($_REQUEST['archon_sessionid']);

      $prep = $_ARCHON->mdb2->prepare('DELETE FROM tblCore_Sessions WHERE Expires <= ?', array('integer'), MDB2_PREPARE_MANIP);
      $affected = $prep->execute(time());
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      $this->Session = New LiveSession();

      if($_REQUEST['f'] == 'logout')
      {
         $this->clearCredentials();

         unset($_REQUEST['f']);
      }

      if($_REQUEST['archonlogin'] && $_REQUEST['archonpassword'])
      {
         if(!$this->verifyCredentials($_REQUEST['archonlogin'], $_REQUEST['archonpassword'], $_REQUEST['rememberme']))
         {
            $_ARCHON->declareError("Authentication Failed");
         }
      }
      else
      {
         $this->Session->verify();
      }

      unset($_REQUEST['archonlogin']);
      unset($_REQUEST['archonpassword']);
   }




   /**
    * Destroys current session
    *
    */
   public function clearCredentials()
   {
      if($this->Session->getRemoteVariable('LanguageID'))
      {
         $_REQUEST['setlanguageid'] = $_REQUEST['setlanguageid'] ? $_REQUEST['setlanguageid'] : $this->Session->getRemoteVariable('LanguageID');
      }

      if($this->Session->getRemoteVariable('Theme'))
      {
         $_REQUEST['settheme'] = $_REQUEST['settheme'] ? $_REQUEST['settheme'] : $this->Session->getRemoteVariable('Theme');
      }

      if($this->Session->getRemoteVariable('AdminTheme'))
      {
         $_REQUEST['setadmintheme'] = $_REQUEST['setadmintheme'] ? $_REQUEST['setadmintheme'] : $this->Session->getRemoteVariable('AdminTheme');
      }

      if($this->Session->getRemoteVariable('RepositoryID'))
      {
         $_REQUEST['setrepositoryid'] = $_REQUEST['setrepositoryid'] ? $_REQUEST['setrepositoryid'] : $this->Session->getRemoteVariable('RepositoryID');
      }

      $this->Session->destroy();
      $this->isAuthenticated = false;

      $arrSetVars = array();
      foreach($_REQUEST as $key => $val)
      {
         if(encoding_strpos($key, 'set') === 0)
         {
            $arrSetVars[$key] = $val;
         }
      }

      $strSetVars = '';
      $seenVar = false;
      foreach($arrSetVars as $key => $val)
      {
         if($seenVar)
         {
            $strSetVars .= '&';
         }

         $strSetVars .= $key . '=' . urlencode($val);

         $seenVar = true;
      }

      header("Location: index.php?$strSetVars");
      die();
      //$this->Session = New LiveSession();
   }



   /**
    * Returns true if User has access to administrative interface.
    *
    * @return boolean
    */
   public function userHasAdministrativeAccess()
   {
      if($this->isAuthenticated())
      {
         return $this->Session->User->IsAdminUser;

//    		foreach($this->Session->User->Usergroups as $objUsergroup)
//    		{
//    			if($objUsergroup->AdministrativeAccess == 1)
//    			{
//    				return true;
//    			}
//    		}
      }

      return false;
   }





   /**
    * Checks to see if current session is authenticated
    *
    * @return boolean
    */
   public function isAuthenticated()
   {
      if(!isset($this->isAuthenticated))
      {
         $this->isAuthenticated = $this->Session->verify();
      }

      return $this->isAuthenticated;
   }





   /**
    * Verifies user credentials
    *
    * @param string $Login
    * @param string $Password
    * @param boolean $Persistent
    * @return boolean
    */
   public function verifyCredentials($Login, $Password, $Persistent = 0)
   {
      global $_ARCHON;

      if(!$Login || !$Password)
      {
         //$_ARCHON->declareError("Authentication Failed: Missing Credentials.");
         return false;
      }

      $UserID = $_ARCHON->getUserIDFromLogin($Login);

      if($this->Disabled)
      {
         $_SESSION['Archon_SessionID'] = "(Security Disabled): $UserID";
         return true;
      }

      if($UserID)
      {
         $this->Session->User = new User($UserID);
         $this->Session->User->dbLoad();
      }

      unset($this->isAuthenticated);

      if($UserID && $this->Session->User->verifyPassword($Password) && !$this->Session->User->Locked && !$this->Session->User->Pending)
      {
         $this->Session->Persistent = $Persistent ? 1 : 0;

         if($this->Session->Persistent)
         {
            $Expires = time() + COOKIE_EXPIRATION;
         }
         else
         {
            $Expires = time() + SESSION_EXPIRATION;
         }

         $prep = $_ARCHON->mdb2->prepare('DELETE FROM tblCore_Sessions WHERE Hash = ?', 'text', MDB2_PREPARE_MANIP);
         $affected = $prep->execute($this->Session->Hash);
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
         $prep->free();

         $prep = $_ARCHON->mdb2->prepare('INSERT INTO tblCore_Sessions (Hash, UserID, RemoteHost, Expires, Persistent, SecureConnection) VALUES (?, ?, ?, ?, ?, ?)', array('text', 'integer', 'text', 'integer', 'boolean', 'boolean'), MDB2_PREPARE_MANIP);
         $affected = $prep->execute(array($this->Session->Hash, $UserID, $_SERVER['REMOTE_ADDR'], $Expires, $this->Session->Persistent, $this->Session->User->RequireSecureConnection));
         if (PEAR::isError($affected))
         {
            trigger_error($affected->getMessage(), E_USER_ERROR);
         }
         $prep->free();

         $_ARCHON->mdb2->setLimit(1);
         $prep = $_ARCHON->mdb2->prepare('SELECT * FROM tblCore_Sessions WHERE UserID = ? AND RemoteHost = ? ORDER BY ID DESC', array('integer', 'text'), MDB2_PREPARE_RESULT);
         $result = $prep->execute(array($UserID, $_SERVER['REMOTE_ADDR']));
         if (PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }
         $row = $result->fetchRow();
         $result->free();
         $prep->free();

         $this->Session->ID = $row['ID'];
         $this->Session->verify();

         return true;
      }
      else
      {
         $this->Session->User = NULL;

         return false;
      }
   }





   /**
    * Verifies user permissions for module access
    *
    * @param integer $ModuleID
    * @param integer $Flag
    * @return boolean
    */
   public function verifyPermissions($ModuleID = 0, $Flag = FULL_CONTROL)
   {
      if($this->Disabled)
      {
         return true;
      }
      elseif(!$this->Session->User)
      {
         return false;
      }
      elseif(!$ModuleID)
      {
         foreach($this->Session->User->Usergroups as $objUsergroup)
         {
            if($objUsergroup->DefaultPermissions & FULL_CONTROL)
            {
               return true;
            }
         }

         return false;
      }
      elseif(!is_natural($ModuleID))
      {
         return false;
      }
      elseif(!$this->Permissions[$ModuleID][$Flag])
      {
         $this->Permissions[$ModuleID][$Flag] = $this->Session->User->verifyPermissions($ModuleID, $Flag);
      }

      return $this->Permissions[$ModuleID][$Flag];
   }





   /**
    * Verifies user permissions for repository access
    *
    * @param integer $RepositoryID
    * @return boolean
    */
   public function verifyRepositoryPermissions($RepositoryID)
   {
      if($this->Disabled)
      {
         return true;
      }

      if(!$this->Session->User)
      {
         return false;
      }

      if(!$this->Session->User->RepositoryLimit)
      {
         return true;
      }

      if(!empty($this->Session->User->Repositories))
      {
         foreach($this->Session->User->Repositories as $objRepository)
         {
            if($objRepository->ID == $RepositoryID)
            {
               return true;
            }
         }
      }

      return false;
   }



   /**
    * If this flag is set to 1, all security protections will be disabled.
    *
    * @var integer
    */
   public $Disabled = 0;

   /**
    * Contains Session object
    *
    * @var ArchonSession
    */
   public $Session = NULL;

   /**
    * Contains an array of all usergroups.
    *
    * @var Usergroup[]
    */
   //public $Usergroups = array();

   /**
    * Contains an array of all users.
    *
    * @var User[]
    */
   //public $Users = array();

   /**
    * If true, user is authenticated, if false, user is not.
    *
    * This value is not set by default.
    *
    * @var boolean
    */
   public $isAuthenticated = NULL;

   /**
    * Contains a two dimensional array where the first dimension is the module ID,
    * and the second is the flag.
    *
    * @var array
    */
   public $Permissions = array();
}

$_ARCHON->mixClasses('Security', 'Core_Security');
?>
