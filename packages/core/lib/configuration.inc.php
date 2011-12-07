<?php
abstract class Core_Configuration
{
    /**
    * Loads Configuration from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_Configuration', true))
        {
            return false;
        }
        
        if($this->Encrypted)
        {
        	$objCryptor = New Cryptor();
        	$this->Value = $objCryptor->decrypt(base64_decode($this->Value));
        }
        
        if($this->PackageID)
        {
            $this->Package = New Package($this->PackageID);
            $this->Package->dbLoad();
        }
        
        if($this->ModuleID)
        {
            $this->Module = New Module($this->ModuleID);
            $this->Module->dbLoad();
        }
  		
  		if($this->PatternID)
  		{
  		    $this->Pattern = New Pattern($this->PatternID);
  		    $this->Pattern->dbLoad();
  		}

        return true;
    }


    
    
    
    /**
    * Returns Configuration object as a formatted string
    *
    * @return string
    */
    public function toString($MakeIntoLink = LINK_NONE)
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert Configuration to string: Configuration ID not defined.");
            return false;
        }

        if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
        {
            if($_ARCHON->QueryStringURL)
            {
                $q = '&amp;q=' . $_ARCHON->QueryStringURL;
            }

            $String .= " <a href='?p=admin/core/configuration&amp;id={$this->ID}{$q}'> ";
        }

        $String .= $this->getString('Directive');

        if($MakeIntoLink == LINK_EACH || $MakeIntoLink == LINK_TOTAL)
        {
            $String .= '</a>';
        }
        
        return $String;
    }
    
    

    /**
    * Stores Configuration to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
        global $_ARCHON;

        // Check permissions
        if(!$_ARCHON->Security->verifyPermissions(MODULE_CONFIGURATION, UPDATE))
        {
            $_ARCHON->declareError("Could not store Configuration: Permission Denied.");
            return false;
        }
        elseif($this->ID && !is_natural($this->ID))
        {
            $_ARCHON->declareError("Could not store Configuration: Configuration ID must be numeric.");
            return false;
        }
        elseif(!isset($this->Value))
        {
            $_ARCHON->declareError("Could not store Configuration: Configuration Value not defined.");
            return false;
        }

        static $checkPrep = NULL;
        if(!isset($checkPrep))
        {
	        $query = "SELECT * FROM tblCore_Configuration WHERE ID = ?";
	        $checkPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
        }
        $result = $checkPrep->execute($this->ID);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        
        $row = $result->fetchRow();

        if(!$row['ID'])
        {
            $_ARCHON->declareError("Could not update Configuration: Configuration ID $this->ID does not exist in the database.");
            return false;
        }
        elseif($row['ReadOnly'])
        {
            $_ARCHON->declareError("Could not update Configuration: {$row['Directive']} is read-only.");
            return false;
        }
        
        if($row['InputType'] == 'timestamp' && !is_natural($this->Value))
        {
        	if(($timeValue = strtotime($this->Value)) === false)
        	{
                $_ARCHON->declareError("Could not update Configuration: strtotime() unable to parse value '$this->Value'.");
                return false;
        	}
        	
        	$this->Value = $timeValue;
        }
        
        if($row['PatternID'])
        {
        	if(!$this->Pattern)
        	{
        		$this->Pattern = New Pattern($row['PatternID']);
        	}
        	
        	if($this->Value && !$this->Pattern->match($this->Value))
            {
                $_ARCHON->declareError("Could not update Configuration: $this->Value is not a valid {$this->Pattern->Name}.");
                return false;
            }
        }

        if($row['InputType'] == 'password')
        {
            if(!$_ARCHON->Security->verifyPermissions(MODULE_CONFIGURATION, FULL_CONTROL))
            {
                $_ARCHON->declareError("Could not store Configuration: Permission Denied.");
                return false;
            }

            echo($this->Value."<br/>");

            $this->Value = crypt($this->Value, crypt($this->Value));

                        echo($this->Value);

        }

        if($this->Encrypted)
        {
        	$objCryptor = New Cryptor();
        	$cryptValue = $objCryptor->encrypt($this->Value);
        	
        	if($cryptValue && $row['InputType'] != 'password')
        	{
        		$dbValue = base64_encode($cryptValue);
        	}
        	else
        	{
        		$dbValue = $this->Value;
        		$this->Encrypted = 0;
        	}
        }
        else
        {
        	$dbValue = $this->Value;
        }

        static $prep = NULL;
        if(!isset($prep))
        {
	        $query = "UPDATE tblCore_Configuration
	            SET
	               Value = ?,
	               Encrypted = ?
	            WHERE
	               ID = ?";
	        $prep = $_ARCHON->mdb2->prepare($query, array('text', 'integer', 'integer'), MDB2_PREPARE_MANIP);
        }
        
        $affected = $prep->execute(array($dbValue, $this->Encrypted, $this->ID));
        
        if(PEAR::isError($affected))
        {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        $_ARCHON->log("tblCore_Configuration", $this->ID);

        return true;
    }




    /**
     * Verifies Load Permissions of Configuration
     *
     * @return boolean
     */
    public function verifyLoadPermissions()
    {
        global $_ARCHON;
        
        if(!$this->ID)
        {
            return false;
        }
        elseif(!is_natural($this->ID))
        {
            return false;
        }
        
        if($_ARCHON->Security->verifyPermissions(MODULE_CONFIGURATION, FULL_CONTROL))
        {
            return true;
        }

        $prep = $_ARCHON->mdb2->prepare("SELECT ModuleID FROM tblCore_Configuration WHERE ID = ?", 'integer', MDB2_PREPARE_RESULT);
        $result = $prep->execute($this->ID);

        $row = $result->fetchRow();
        
        $result->free();
        $prep->free();
        
        if(($row['ModuleID'] && !$_ARCHON->Security->verifyPermissions($row['ModuleID'], FULL_CONTROL)) || (!$row['ModuleID'] && !$_ARCHON->Security->verifyPermissions(MODULE_CONFIGURATION, UPDATE)))
        {
            return false;
        }
        
        return true;
    }




    /**
     * Verifies Store Permissions of Configuration
     *
     * @return boolean
     */
    public function verifyStorePermissions()
    {
    	return $this->verifyLoadPermissions();
    }
    
    
    
    

    /**
     * @var integer
     **/
    public $ID = 0;
    
    /**
     * @var integer
     */
    public $PackageID = 0;
    
    /**
     * @var integer
     */
    public $ModuleID = 0;

    /**
     * @var string
     **/
    public $Directive = NULL;

    /**
     * @var string
     **/
    public $Value = NULL;

    /**
     * @var string
     **/
    public $InputType = NULL;

    /**
     * @var integer
     */
    public $PatternID = 0;

    /**
     * @var integer
     **/
    public $ReadOnly = 0;

    /**
     * @var integer
     **/
    public $Encrypted = 0;
    
    /**
     * @var string
     **/
    public $PhraseName = NULL;

    /**
     * @var string
     **/
    public $ListDataSource = NULL;
    
    /**
     * @var string
     **/
    public $Package = NULL;
    
    /**
     * @var string
     **/
    public $Module = NULL;

    /**
     * @var Pattern
     */
    public $Pattern = NULL;

    
}

$_ARCHON->mixClasses('Configuration', 'Core_Configuration');
?>