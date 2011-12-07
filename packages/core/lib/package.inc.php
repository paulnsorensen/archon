<?php
abstract class Core_Package
{
    /**
    * Deletes Package from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        return $_ARCHON->deleteObject($this, MODULE_PACKAGES, 'tblCore_Packages');
    }






	/**
    * Disables Package
    *
    * @return boolean
    */
    public function dbDisable()
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not disable Package: Package ID not defined.");
            return false;
        }

        if(!is_natural($this->ID))
        {
            $_ARCHON->declareError("Could not disable Package: Package ID must be numeric.");
            return false;
        }

        // Check permissions
        if(!$_ARCHON->Security->verifyPermissions(MODULE_PACKAGES, UPDATE))
        {
            $_ARCHON->declareError("Could not disable Package: Permission Denied.");
            return false;
        }

        static $selectPrep = NULL;
        if(!isset($selectPrep))
        {
        	$query = "SELECT ID, APRCode FROM tblCore_Packages WHERE ID = ?";
        	$selectPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
        }
        $result = $selectPrep->execute($this->ID);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        
        $row = $result->fetchRow();
        $result->free();

        if(!$row['ID'])
        {
            $_ARCHON->declareError("Could not disable Package: Package ID $this->ID does not exist in the database.");
            return false;
        }
        
        if($row['APRCode'] == 'core')
        {
            $_ARCHON->declareError("Could not disable Package: The Archon Core Package cannot be disabled.");
            return false;
        }
        
        if(!empty($_ARCHON->Packages[$this->ID]->DependedUponBy))
        {
            $_ARCHON->declareError("Could not disable Package: Package " . $_ARCHON->Packages[$this->ID]->toString() . " is depended upon by " . $_ARCHON->Packages[key($_ARCHON->Packages[$this->ID]->DependedUponBy)]->toString() . ".");
            return false;
        }
        
        static $updatePrep = NULL;
        if(!isset($updatePrep))
        {
        	$query = "UPDATE tblCore_Packages SET Enabled = '0' WHERE ID = ?";
        	$updatePrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
        }
        $affected = $updatePrep->execute($this->ID);
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        $_ARCHON->log("tblCore_Packages", $this->ID);
        $this->Enabled = 0;

        return true;
    }






	/**
    * Enables Package
    *
    * @return boolean
    */
    public function dbEnable()
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not enable Package: Package ID not defined.");
            return false;
        }

        if(!is_natural($this->ID))
        {
            $_ARCHON->declareError("Could not enable Package: Package ID must be numeric.");
            return false;
        }

        // Check permissions
        if(!$_ARCHON->Security->verifyPermissions(MODULE_PACKAGES, UPDATE))
        {
            $_ARCHON->declareError("Could not enable Package: Permission Denied.");
            return false;
        }

        static $selectPrep = NULL;
        if(!isset($selectPrep))
        {
            $query = "SELECT ID, APRCode FROM tblCore_Packages WHERE ID = ?";
            $selectPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
        }
        $result = $selectPrep->execute($this->ID);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        
        $row = $result->fetchRow();
        $result->free();

        if(!$row['ID'])
        {
            $_ARCHON->declareError("Could not enable Package: Package ID $this->ID does not exist in the database.");
            return false;
        }
        
        $arrAllPackages = $_ARCHON->getAllPackages(false);

        foreach($arrAllPackages as $objPackage)
        {
            $arrAPRCodeIDMap[$objPackage->APRCode] = $objPackage->ID;
        }
        
        $PackageName = $arrAllPackages[$arrAPRCodeIDMap[$row['APRCode']]] ? $arrAllPackages[$arrAPRCodeIDMap[$row['APRCode']]]->toString() : $APRCode;
        
        if(!file_exists("packages/{$row['APRCode']}/index.php"))
        {
            $_ARCHON->declareError("Could not enable Package: The $PackageName Package codebase could not be found.");
            return false;
        }
        
        $arrPackages = $_ARCHON->Packages;
        
        include("packages/{$row['APRCode']}/index.php");
        
        $arrDependsUpon = $_ARCHON->Packages[$row['APRCode']]->DependsUpon;

        $_ARCHON->Packages = $arrPackages;
        
        
        if(!empty($arrDependsUpon))
        {
            foreach($arrDependsUpon as $DependsUponAPRCode => $DependsUponVersion)
            {
                $DependsUponPackageName = $arrAllPackages[$arrAPRCodeIDMap[$DependsUponAPRCode]] ? $arrAllPackages[$arrAPRCodeIDMap[$DependsUponAPRCode]]->toString() : $DependsUponAPRCode;
                
                if(!$_ARCHON->Packages[$DependsUponAPRCode])
                {
                    if($arrAllPackages[$arrAPRCodeIDMap[$DependsUponAPRCode]])
                    {
                        $_ARCHON->declareError("Could not enable Package: Package $PackageName depends upon Package $DependsUponPackageName which is not enabled.");
                        return false;
                    }
                    else 
                    {
                        $_ARCHON->declareError("Could not enable Package: Package $PackageName depends upon Package $DependsUponPackageName which is not installed.");
                        return false;
                    }
                }
                
                if(version_compare($_ARCHON->Packages[$DependsUponAPRCode]->DBVersion, $DependsUponVersion) == -1)
                {
                    $_ARCHON->declareError("Could not enable Package: Package $DependsUponPackageName version $DependsUponVersion must be installed (installed version is {$this->Packages[$DependsUponAPRCode]->DBVersion}).");
                    return false;
                }
            }
        }

        static $updatePrep = NULL;
        if(!isset($updatePrep))
        {
            $query = "UPDATE tblCore_Packages SET Enabled = '1' WHERE ID = ?";
            $updatePrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
        }
        $affected = $updatePrep->execute($this->ID);
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        $_ARCHON->log("tblCore_Packages", $this->ID);
        $this->Enabled = 1;

        return true;
    }
    
    
    
    
    
    /**
    * Loads Package
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_Packages'))
        {
            return false;
        }

        return true;
    }






	/**
    * Stores Package to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCore_Packages WHERE APRCode = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->APRCode, $this->ID);
        $checkqueryerror = "A Package with the same APRCode already exists in the database";
        $problemfields = array('APRCode');
        $requiredfields = array('APRCode', 'DBVersion');
        
        if(!$_ARCHON->storeObject($this, MODULE_PACKAGES, 'tblCore_Packages', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    	
    }
    
    
    
    
    
    /**
     * Outputs Package as a string
     *
     * @return string
     */
    public function toString()
    {
        global $_ARCHON;
        
        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert Package to string: Package ID not defined.");
            return false;
        }
        
        $EncodingType = ($_ARCHON->PublicInterface->EscapeXML || $_ARCHON->AdministrativeInterface->EscapeXML) ? ENCODE_HTML : ENCODE_NONE;
        
        $objPackagePhrase = Phrase::getPhrase("package_name", $this->ID, 0, PHRASETYPE_ADMIN);
        $strPackage = $objPackagePhrase ? $objPackagePhrase->getPhraseValue($EncodingType) : $this->APRCode;
        
        return $strPackage;
    }





    /**
     * @var integer
     */
    public $ID = 0;
    
    /**
     * @var integer
     */
    public $Enabled = 0;

    /**
     * @var string
     */
    public $APRCode = '';

    /**
     * @var string
     */
    public $DBVersion = '';
    
    /**
     * @var boolean[]
     */
    public $DependedUponBy = array();
    
    /**
     * @var boolean[]
     */
    public $DependsUpon = array();
    
    /**
     * @var boolean[]
     */
    public $EnhancedBy = array();
    
    /**
     * @var boolean[]
     */
    public $Enhances = array();
}

$_ARCHON->mixClasses('Package', 'Core_Package');
?>