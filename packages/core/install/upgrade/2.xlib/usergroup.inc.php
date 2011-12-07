<?php
abstract class Core_Usergroup2x
{
    public function _construct($row)
    {
        $this->DefaultPermissions = intval($this->DefaultPermissionsRead * READ)
            | intval($this->DefaultPermissionsAdd * ADD)
            | intval($this->DefaultPermissionsUpdate * UPDATE)
            | intval($this->DefaultPermissionsDelete * DELETE)
            | intval($this->DefaultPermissionsFullControl * FULL_CONTROL);
    }

    /**
    * Deletes Usergroup from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        $ID = $this->ID;
        
        if(!$_ARCHON->deleteObject($this, MODULE_USERGROUPS, 'tblCore_Usergroups'))
        {
            return false;
        }
        
        $prep = $this->mdb2->prepare("DELETE FROM tblCore_UsergroupPermissions WHERE UsergroupID = ?", 'integer', MDB2_PREPARE_MANIP);
        $prep->execute($ID);
        
        return true;
    }






    /**
    * Loads Usergroup from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;
        
        if(!$_ARCHON->loadObject($this, 'tblCore_Usergroups'))
        {
            return false;
        }
        
        $this->DefaultPermissions = intval($this->DefaultPermissions);

        $this->DefaultPermissionsRead = (($this->DefaultPermissions & READ) == READ);
        $this->DefaultPermissionsAdd = (($this->DefaultPermissions & ADD) == ADD);
        $this->DefaultPermissionsUpdate = (($this->DefaultPermissions & UPDATE) == UPDATE);
        $this->DefaultPermissionsDelete = (($this->DefaultPermissions & DELETE) == DELETE);
        $this->DefaultPermissionsFullControl = (($this->DefaultPermissions & FULL_CONTROL) == FULL_CONTROL);

        $this->dbLoadPermissions();

        return true;
    }





    /**
    * Loads Usergroup Permissions from the database
    *
    * @return boolean
    */
    public function dbLoadPermissions()
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not load UsergroupPermissions: Usergroup ID not defined.");
            return false;
        }

        if(!is_natural($this->ID))
        {
            $_ARCHON->declareError("Could not load UsergroupPermissions: Usergroup ID must be numeric.");
            return false;
        }
        
        $this->Permissions = array();

        $prep = $_ARCHON->mdb2->prepare("SELECT * FROM tblCore_UsergroupPermissions WHERE UsergroupID = ?", 'integer', MDB2_PREPARE_RESULT);
        $result = $prep->execute($this->ID);
        if (PEAR::isError($result)) {
           trigger_error($result->getMessage(), E_USER_ERROR);
        }
        
        while($row = $result->fetchRow())
        {
            $this->Permissions[$row['ModuleID']] = intval($row['Permissions']);
        }
        $result->free();
        $prep->free();

        return true;
    }





    /**
	 * Sets Usergroup Permissions in database for a particular module
	 *
	 * @param integer $ModuleID
	 * @param integer $Permissions
	 * @return boolean
	 */
    public function dbSetPermissions($ModuleID, $Permissions)
    {
        global $_ARCHON;

        // Check permissions
        if(!$_ARCHON->Security->verifyPermissions(MODULE_USERGROUPS, UPDATE))
        {
            $_ARCHON->declareError("Could not set Permissions: Permission Denied.");
            return false;
        }

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not set Permissions: Usergroup ID not defined.");
            return false;
        }

        if(!$ModuleID)
        {
            $_ARCHON->declareError("Could not set Permissions: Module ID not defined.");
            return false;
        }

        if(!is_natural($this->ID))
        {
            $_ARCHON->declareError("Could not set Permissions: Usergroup ID must be numeric.");
            return false;
        }

        if(!is_natural($ModuleID))
        {
            $_ARCHON->declareError("Could not set Permissions: Module ID must be numeric.");
            return false;
        }

        if(!isset($Permissions))
        {
            $_ARCHON->declareError("Could not set Permissions: Permissions not defined.");
            return false;
        }

        $this->dbUnsetPermissions($ModuleID);

        static $prep = NULL;
        if(!isset($prep))
        {
	        $query = "INSERT INTO tblCore_UsergroupPermissions (
	            UsergroupID,
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
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        $_ARCHON->log("tblCore_UsergroupPermissions", $this->ID);
        $this->dbLoadPermissions();

        return true;
    }






    /**
    * Stores Usergroup to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $this->DefaultPermissions = intval($this->DefaultPermissionsRead * READ)
            | intval($this->DefaultPermissionsAdd * ADD)
            | intval($this->DefaultPermissionsUpdate * UPDATE)
            | intval($this->DefaultPermissionsDelete * DELETE)
            | intval($this->DefaultPermissionsFullControl * FULL_CONTROL);

        $checkquery = "SELECT ID FROM tblCore_Usergroups WHERE Usergroup = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->Usergroup, $this->ID);
        $checkqueryerror = "A Usergroup with the same Name already exists in the database";
        $problemfields = array('Usergroup');
        $requiredfields = array('Usergroup');
        $ignoredfields = array('DefaultPermissionsRead', 'DefaultPermissionsFullControl', 'DefaultPermissionsAdd', 'DefaultPermissionsUpdate', 'DefaultPermissionsDelete');
        
        if(!$_ARCHON->storeObject($this, MODULE_USERGROUPS, 'tblCore_Usergroups', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields, $ignoredfields))
        {
            return false;
        }
        
        return true;
    }





    /**
     * Unsets Usergroup Permissions for a particular module
     *
     * @param integer $ModuleID
     * @return boolean
     */
    public function dbUnsetPermissions($ModuleID)
    {
        global $_ARCHON;

        // Check permissions
        if(!$_ARCHON->Security->verifyPermissions(MODULE_USERGROUPS, UPDATE))
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
            $_ARCHON->declareError("Could not unset Permissions: Usergroup ID must be numeric.");
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
            $query = "DELETE FROM tblCore_UsergroupPermissions WHERE UsergroupID = ? AND ModuleID = ?";
            $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer'), MDB2_PREPARE_MANIP);
        }
        $affected = $prep->execute(array($this-ID, $ModuleID));

        $_ARCHON->log("tblCore_UsergroupPermissions", $this->ID);
        $this->dbLoadPermissions();

        return true;
    }
    
    
    
    /**
     * Outputs Usergroup as a string
     *
     * @return unknown
     */
    public function toString()
    {
        return $this->getString('Usergroup');
    }


    // These variables correspond directly to the fields in the tblCore_Usergroups table
    /**
     * @var integer
     **/
    public $ID = 0;

    /**
     * @var string
     **/
    public $Usergroup = '';

    /**
     * @var integer
     **/
    public $DefaultPermissions = 0;

    /**
     * @var integer
     **/
    public $AdministrativeAccess = 0;

    public $DefaultPermissionsRead = false;

    public $DefaultPermissionsAdd = false;

    public $DefaultPermissionsUpdate = false;

    public $DefaultPermissionsDelete = false;

    public $DefaultPermissionsFullControl = false;

    // These variables are loaded from other tables, but relate to the usergroup

    /**
     * @var integer[]
     **/
    public $Permissions = array(); // array
}

$_ARCHON->mixClasses('Usergroup2x', 'Core_Usergroup2x');
?>