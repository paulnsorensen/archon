<?php
abstract class Core_Security2x
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
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }
        $prep->free();

        $this->Session = New LiveSession2x();

        //$this->Usergroups = $_ARCHON->getAllUsergroups();
        //$this->Users = $_ARCHON->getAllUsers();  // FIXME Find all references to this and see if we can mitigate loading all users
        //$this->User = new User();

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
    	if($this->isAuthenticated() && $this->Session->User->Usergroup->AdministrativeAccess == 1)
    	{
    	    return true;
    	}
    	else
    	{
    		return false;
    	}
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

        if($this->Disabled)
        {
            $_SESSION['Archon_SessionID'] = "(Security Disabled): $UserID";
            return true;
        }

        if(!$Login || !$Password)
        {
            echo("Authentication Failed: Missing Credentials.");
            return false;
        }

        $UserID = $_ARCHON->getUserIDFromLogin($Login);

//        echo($UserID);
//        echo($Login);

        if($UserID)
        {
            $this->Session->User = new User2x($UserID);
            $this->Session->User->dbLoad();
            $this->Session->User->dbLoadPermissions();

//             echo($this->Session->User->verifyPassword($Password));
//        echo($this->Session->User->Locked);
//        echo($this->Session->User->Pending);
        }

       

        unset($this->isAuthenticated);

        //if($UserID && $this->Users[$UserID]->verifyPassword($Password) && !$this->Users[$UserID]->Locked)
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
            if (PEAR::isError($affected)) {
                trigger_error($affected->getMessage(), E_USER_ERROR);
            }
            $prep->free();
            
            $prep = $_ARCHON->mdb2->prepare('INSERT INTO tblCore_Sessions (Hash, UserID, RemoteHost, Expires, Persistent) VALUES (?, ?, ?, ?, ?)', array('text', 'integer', 'text', 'integer', 'boolean'), MDB2_PREPARE_MANIP);
            $affected = $prep->execute(array($this->Session->Hash, $UserID, $_SERVER['REMOTE_ADDR'], $Expires, $this->Session->Persistent));
            if (PEAR::isError($affected)) {
                trigger_error($affected->getMessage(), E_USER_ERROR);
            }
            $prep->free();
            
            $_ARCHON->mdb2->setLimit(1);
            $prep = $_ARCHON->mdb2->prepare('SELECT * FROM tblCore_Sessions WHERE UserID = ? AND RemoteHost = ? ORDER BY ID DESC', array('integer', 'text'), MDB2_PREPARE_RESULT);
            $result = $prep->execute(array($UserID, $_SERVER['REMOTE_ADDR']));
            if (PEAR::isError($result)) {
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
    public function verifyPermissions($ModuleID = 0, $Flag)
    {
        if($this->Disabled)
        {
            return true;
        }
        if(!$this->Session->User)
        {
            return false;
        }

        
        if(!$ModuleID && !($this->Session->User->Usergroup->DefaultPermissions & FULL_CONTROL))
        {
            return false;
        }

        if(!is_natural($ModuleID))
        {
            return false;
        }



        if(!$this->Permissions[$ModuleID][$Flag])
        {
            $this->Permissions[$ModuleID][$Flag] = $this->Session->User->verifyPermissions($ModuleID, $Flag);
        }

        return $this->Permissions[$ModuleID][$Flag];
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
    public $Usergroups = array();

    /**
     * Contains an array of all users.
     *
     * @var User[]
     */
    public $Users = array();

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


    public $User = NULL;
}

$_ARCHON->mixClasses('Security2x', 'Core_Security2x');
?>
