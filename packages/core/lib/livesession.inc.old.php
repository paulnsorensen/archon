<?php
abstract class Core_LiveSession
{
   /**
    * ArchonLiveSession Constructor
    *
    * @return ArchonLiveSession
    */
   public function construct()
   {
      global $_ARCHON;

//      ini_set('url_rewriter.tags', '');            // Tell URL rewriter not to change anything
//      ini_set('session.use_trans_sid', '0');        // Disables transparent SID support
//      ini_set('session.use_only_cookies', '1');    // Don't attach session id to url
      

//      if($this->getRemoteVariable('SessionID'))
//      {
//         session_id($this->getRemoteVariable('SessionID'));
//      }

      if(ini_get(session.gc_maxlifetime) < SESSION_EXPIRATION)
      {
         ini_set('session.gc_maxlifetime', SESSION_EXPIRATION);
      }

      if(!$_ARCHON->Security->SessionCreated && session_save_path())
      {
         strstr(strtoupper(substr($_SERVER["OS"], 0, 3)), "WIN") ? $sep = "\\" : $sep = "/";
         $sessdir = session_save_path().$sep."archon_sessions";

         if (!@is_dir($sessdir))
         {
            @mkdir($sessdir, 0777);
         }
         if(@is_dir($sessdir))
         {
            session_save_path($sessdir);
         }
      }

      $Path = preg_replace('/[\w.]+php/u', '', $_SERVER['SCRIPT_NAME']);
      session_set_cookie_params(0, $Path);

      session_name('archon');
      session_start();

      $this->Hash = session_id();

      $_ARCHON->Security->SessionCreated = true;

      return $this;
   }





   /**
    * Destorys live session
    *
    * @return boolean
    */
   public function destroy()
   {
      global $_ARCHON;

      $prep = $_ARCHON->mdb2->prepare('DELETE FROM tblCore_Sessions WHERE Hash = ?', 'text', MDB2_PREPARE_MANIP);
      $affected = $prep->execute(session_id());
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      $this->unsetRemoteVariable('SessionID', true);
      $this->unsetRemoteVariable('LanguageID', true);
      $this->unsetRemoteVariable('Theme', true);
      $this->unsetRemoteVariable('AdminTheme', true);
      $this->unsetRemoteVariable('RepositoryID', true);

      $this->ID = 0;

      session_destroy();
      setcookie('archon', '');

      return true;
   }





   /**
    * Return time of expiration of live session
    *
    * @return timestamp
    */
   public function getExpiration()
   {
      return $this->Expires;
   }



   /**
    * Returns language id according to preference of current Archon user and
    * the default language in the configuration.
    *
    */
   public function getLanguageID()
   {
      if($this->User->LanguageID)
      {
         $LanguageID = $this->User->LanguageID;
      }
      else if($this->getRemoteVariable('LanguageID'))
      {
         $LanguageID = $this->getRemoteVariable('LanguageID');
      }
      else
      {
         $LanguageID = CONFIG_CORE_DEFAULT_LANGUAGE;
      }

      return $LanguageID;
   }





   /**
    * Returns a remote variable according to Name
    *
    * @param string $Name
    * @return mixed
    */
   public function getRemoteVariable($Name)
   {
      return isset($_SESSION["Archon_$Name"]) ? $_SESSION["Archon_$Name"] : $_COOKIE["Archon_$Name"];
   }


   /**
    * Returns a $_SESSION variable according to Name
    *
    * This is for session data that should be strictly private
    * instead of using the RemoteVariable methods
    *
    * Also, headers_sent() isn't checked so this will throw
    * an error if used incorrectly
    *
    * @param string $Name
    * @return mixed
    */
   public function getSessionVariable($Name)
   {
      return $_SESSION["Archon_{$Name}"];
   }


   /**
    * Sets a $_SESSION variable to Value
    *
    * This is for session data that should be strictly private
    * instead of using the RemoteVariable methods
    *
    * @param string $Name
    * @param string $Value
    */
   public function setSessionVariable($Name, $Value)
   {
      $_SESSION["Archon_{$Name}"] = $Value;
   }


   /**
    * Returns Remote Host location
    *
    * @return string
    */
   public function getRemoteHost()
   {
      return $this->RemoteHost;
   }




   /**
    * Returns User ID
    *
    * @return integer
    */
   public function getUserID()
   {
      return $this->UserID;
   }





   /**
    * Checks to see if current session is over a secure connection
    *
    * @return boolean
    */
   public function isSecureConnection()
   {
      if($_SERVER['HTTPS'] == 1 || $_SERVER['HTTPS'] == 'on' || $_SERVER['SERVER_PORT'] == 443)
      {
         return true;
      }
      else
      {
         return false;
      }
   }





   /**
    * Checks to see if current session is over a secure connection
    *
    * @return boolean
    */
   public function requireSecureConnection()
   {
      global $_ARCHON;

      if($this->isSecureConnection())
      {
         return true;
      }
      elseif(!$this->Hash)
      {
         return false;
      }

      $prep = $_ARCHON->mdb2->prepare('UPDATE tblCore_Sessions SET SecureConnection = 1 WHERE Hash = ?', 'text', MDB2_PREPARE_MANIP);
      $affected = $prep->execute($this->Hash);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      return $this->verify();
   }





   /**
    * Sets a remote variable to Value
    *
    * @param string $Name
    * @param string $Value
    * @return boolean
    */
   public function setRemoteVariable($Name, $Value, $SetCookie = false)
   {
      if(headers_sent())
      {
         return false;
      }

      if($this->Persistent || $SetCookie)
      {
         $Path = preg_replace('/[\w.]+php/u', '', $_SERVER['SCRIPT_NAME']);
         setcookie("Archon_$Name", $Value, $this->Expires, $Path, $_SERVER['HTTP_HOST'], 0);
         $_COOKIE["Archon_$Name"] = $Value;
      }

      $_SESSION["Archon_$Name"] = $Value;

      return true;
   }





   /**
    * Unsets a remote variable
    *
    * @param string $Name
    * @return boolean
    */
   public function unsetRemoteVariable($Name, $UnsetCookie = false)
   {
      if(headers_sent())
      {
         return false;
      }

      if($this->Persistent || $UnsetCookie)
      {
         $Path = preg_replace('/[\w.]+php/u', '', $_SERVER['SCRIPT_NAME']);
         setcookie("Archon_$Name", '', time() - 24 * 60 * 60, $Path, $_SERVER['HTTP_HOST'], 0);
         unset($_COOKIE["Archon_$Name"]);
      }

      unset($_SESSION["Archon_$Name"]);

      return true;
   }




   /**
    * Unsets all remote variables used to customize user interface
    *
    * @param string $Name
    * @return boolean
    */
   public function unsetAllRemoteVariables($UnsetCookie = false)
   {
      $returnVal = true;

      $returnVal = $returnVal && $this->unsetRemoteVariable('LanguageID', $UnsetCookie);
      $returnVal = $returnVal && $this->unsetRemoteVariable('Theme', $UnsetCookie);
      $returnVal = $returnVal && $this->unsetRemoteVariable('AdminTheme', $UnsetCookie);
      $returnVal = $returnVal && $this->unsetRemoteVariable('RepositoryID', $UnsetCookie);

      return $returnVal;
   }





   /**
    * Verifies live session
    *
    * @return boolean
    */
   public function verify()
   {
      global $_ARCHON;

      $prep = $_ARCHON->mdb2->prepare('SELECT * FROM tblCore_Sessions WHERE Hash = ?', array('text'), MDB2_PREPARE_RESULT);
      $result = $prep->execute(session_id());
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $result->free();
      $prep->free();

      if(!$row['ID'])
      {
         return false;
      }

      $row = array_change_key_case($row);
      $arrVariables = get_object_vars($this);
      foreach($arrVariables as $name => $defaultvalue)
      {
         if(isset($row[strtolower($name)]))
         {
            $this->$name = $row[strtolower($name)];
         }
      }

      if($this->Expires <= time())
      {
         return false;
      }

      if($this->RemoteHost != $_SERVER['REMOTE_ADDR'])
      {
         return false;
      }

      if($this->Persistent)
      {
         $this->Expires = time() + COOKIE_EXPIRATION;
      }
      else
      {
         $this->Expires = time() + SESSION_EXPIRATION;
      }

      if($this->SecureConnection && !$this->isSecureConnection())
      {
         die('<html><body onLoad="location.href=\'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .'\';"></body></html>');
         //header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
      }

      if(!$this->User && $this->UserID)
      {
         $this->User = New User($this->UserID);
         $this->User->dbLoad();
      }

      if($this->User->RequireSecureConnection && !$this->isSecureConnection())
      {
         $this->requireSecureConnection();
      }

      //The query to set the integer to the timestamp would often take 2 seconds for some reason
      //so I'm going to try running the straight query once to see if this resolves the issue

      //$prep = $_ARCHON->mdb2->prepare('UPDATE tblCore_Sessions SET Expires = ? WHERE ID = ?', array('integer', 'integer'), MDB2_PREPARE_MANIP);
      //$affected = $prep->execute(array($this->Expires, $this->ID));


      // do we want to update every time?

      $affected = $_ARCHON->mdb2->exec("UPDATE tblCore_Sessions SET Expires = {$this->Expires} WHERE ID = {$this->ID}");

      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      //$prep->free();

      $this->setRemoteVariable('SessionID', session_id());

      return true;
   }





   /**
    * Session ID
    *
    * @var integer
    */
   public $ID = 0;

   /**
    * Hash
    *
    * @var string
    */
   public $Hash = NULL;

   /**
    * User ID
    *
    * @var integer
    */
   public $UserID = 0;

   /**
    * Remote Host for session
    *
    * @var string
    */
   public $RemoteHost = NULL;

   /**
    * Expiration time (timestamp)
    *
    * @var integer
    */
   public $Expires = 0;

   /**
    * Persistent Session
    *
    * @var integer
    */
   public $Persistent = 0;

   /**
    * Secure Connection
    *
    * @var integer
    */
   public $SecureConnection = 0;

   /**
    * @var User
    */
   public $User = NULL;
}

$_ARCHON->mixClasses('LiveSession', 'Core_LiveSession');
?>