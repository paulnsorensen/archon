<?php
abstract class Core_Script
{
    /**
    * Deletes Script from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_SCRIPTS, 'tblCore_Scripts'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads Script from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_Scripts'))
        {
            return false;
        }
        
        return true;
    }






	/**
    * Stores Script to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        
        $checkqueries = array();
        $checktypes = array();
        $checkvars = array();
        $checkqueryerrors = array();
        $problemfields = array();

        $checkqueries[] = "SELECT ID FROM tblCore_Scripts WHERE ScriptEnglishLong = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->ScriptEnglishLong, $this->ID);
        $checkqueryerrors[] = "A Script with the same English Name already exists in the database";
        $problemfields[] = array('ScriptEnglishLong');

        
        $checkqueries[] = "SELECT ID FROM tblCore_Scripts WHERE ScriptShort = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->ScriptShort, $this->ID);
        $checkqueryerrors[] = "A Script with the same ScriptShort already exists in the database";
        $problemfields[] = array('ScriptShort');

        $requiredfields = array('ScriptShort', 'ScriptEnglishLong');

        
       if(!$_ARCHON->storeObject($this, MODULE_SCRIPTS, 'tblCore_Scripts', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }

    /**
    * Outputs Script if Script is cast to string
    *
    * @magic
    * @return string
    */
    public function toString()
    {
        return $this->getString('ScriptEnglishLong');
    }



    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $ScriptEnglishLong = '';
    
    /**
     * @var string
     */
    public $ScriptShort = '';
    
    /**
     * @var string
     */
    public $ScriptFrenchLong = '';
    
    /** @var integer */
    public $ScriptCode = 0;
    
    /** @var integer */
    public $DisplayOrder = 0;
}

$_ARCHON->mixClasses('Script', 'Core_Script');
?>