<?php
abstract class Core_LiveSession2x
{
    /**
     * ArchonLiveSession Constructor
     *
     * @return ArchonLiveSession
     */
    public function construct()
    {
        if($this->getRemoteVariable('SessionID'))
        {
            session_id($this->getRemoteVariable('SessionID'));
        }

        if(ini_get(session.gc_maxlifetime) < SESSION_EXPIRATION)
        {
            ini_set('session.gc_maxlifetime', SESSION_EXPIRATION);
        }

        if(!$_ARCHON->Security->SessionCreated && session_save_path())
        {
            strstr(strtoupper(substr($_SERVER["OS"], 0, 3)), "WIN") ? $sep = "\\" : $sep = "/";
            $sessdir = session_save_path().$sep."archon_sessions";

            if (!@is_dir($sessdir)) { @mkdir($sessdir, 0777); }
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
     * Destorys live session
     *
     * @return boolean
     */
    public function destroy()
    {
        global $_ARCHON;

        $prep = $_ARCHON->mdb2->prepare('DELETE FROM tblCore_Sessions WHERE Hash = ?', 'text', MDB2_PREPARE_MANIP);
        $affected = $prep->execute(session_id());
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }
        $prep->free();

        $this->unsetRemoteVariable('SessionID', true);
        $this->unsetRemoteVariable('LanguageID', true);
        $this->unsetRemoteVariable('Theme', true);
        $this->unsetRemoteVariable('AdminTheme', true);

        $this->ID = 0;

        session_destroy();
        setcookie('archon', '');

        return true;
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
        if (PEAR::isError($result)) {
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

        if(!$this->User && $this->UserID)
        {
            $this->User = New User2x($this->UserID);
            $this->User->dbLoad();
        }

        $prep = $_ARCHON->mdb2->prepare('UPDATE tblCore_Sessions SET Expires = ? WHERE ID = ?', array('integer', 'integer'), MDB2_PREPARE_MANIP);
        $affected = $prep->execute(array($this->Expires, $this->ID));
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }
        $prep->free();

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
     * @var User
     */
    public $User = NULL;
}

$_ARCHON->mixClasses('LiveSession2x', 'Core_LiveSession2x');
?>