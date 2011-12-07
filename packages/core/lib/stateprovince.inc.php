<?php
abstract class Core_StateProvince
{
	/**
    * Deletes StateProvince from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_REGIONS, 'tblCore_StateProvinces'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads StateProvince from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_StateProvinces'))
        {
            return false;
        }
        
        if($this->CountryID)
        {
        	$this->Country = New Country($this->CountryID);
        	$this->Country->dbLoad();
        }

        return true;
    }






	/**
    * Stores StateProvince to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCore_StateProvinces WHERE (StateProvinceName = ? OR ISOAlpha2 = ?) AND ID != ?";
        $checktypes = array('text', 'text', 'integer');
        $checkvars = array($this->StateProvinceName, $this->ISOAlpha2, $this->ID);
        $checkqueryerror = "A StateProvince with the same StateProvinceName already exists in the database";
        $problemfields = array('StateProvinceName', 'ISOAlpha2');
        $requiredfields = array('StateProvinceName', 'ISOAlpha2');
        
        if(!$_ARCHON->storeObject($this, MODULE_REGIONS, 'tblCore_StateProvinces', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Returns a formatted string of a traversal of subject instance
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateParents[optional]
    * @param string $Delimiter[optional]
    * @return string
    */
    public function toString()
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert StateProvince to string: StateProvince ID not defined.");
            return false;
        }

        if(!$this->StateProvinceName)
        {
            $this->dbLoad();
        }
        
        $String = $this->StateProvinceName;

        return $String;
    }





    /**
     * @var integer
     **/
    public $ID = 0;

    /**
     * @var integer
     */
    public $CountryID = 0;

    /**
     * @var string
     */
    public $StateProvinceName = '';

    /**
     * @var string
     */
    public $ISOAlpha2 = '';

    /**
     * @var Country
     */
    public $Country = NULL;
}

$_ARCHON->mixClasses('StateProvince', 'Core_StateProvince');
?>