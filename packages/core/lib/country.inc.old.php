<?php
abstract class Core_Country
{
	/**
    * Deletes Country from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_COUNTRIES, 'tblCore_Countries'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads Country from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_Countries'))
        {
            return false;
        }

        return true;
    }






	/**
    * Stores Country to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCore_Countries WHERE (CountryName = ? OR ISOAlpha2 = ? OR ISOAlpha3 = ? OR ISONumeric3 = ?) AND ID != ?";
        $checktypes = array('text', 'text', 'text', 'text', 'integer');
        $checkvars = array($this->CountryName, $this->ISOAlpha2, $this->ISOAlpha3, $this->ISONumeric3, $this->ID);
        $checkqueryerror = "A Country with the same CountryName, ISOAlpha2, ISOAlpha3, or ISONumeric3 already exists in the database";
        $problemfields = array('CountryName', 'ISOAlpha2');
        $requiredfields = array('CountryName', 'ISOAlpha2');
        
        if(!$_ARCHON->storeObject($this, MODULE_COUNTRIES, 'tblCore_Countries', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Returns a formatted string of a traversal of subject instance
    *
    * @param string $Delimiter[optional]
    * @return string
    */
    public function toString()
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert Country to string: Country ID not defined.");
            return false;
        }

        if(!$this->CountryName)
        {
            $this->dbLoad();
        }
        
        $String = $this->CountryName;

        return $String;
    }





    /**
     * @var integer
     **/
    public $ID = 0;

    /**
     * @var string
     */
    public $CountryName = '';

    /**
     * @var string
     */
    public $ISOAlpha2 = '';

    /**
     * @var string
     */
    public $ISOAlpha3 = '';

    /**
     * @var string
     */
    public $ISONumeric3 = '';
}

$_ARCHON->mixClasses('Country', 'Core_Country');
?>