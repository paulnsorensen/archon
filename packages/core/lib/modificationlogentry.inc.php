<?php
abstract class Core_ModificationLogEntry
{
    /**
    * Loads ModificationLogEntry
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_ModificationLog'))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    /**
     * Outputs Entry as a string
     *
     * @return string
     */
    public function toString()
    {
        return $this->ID;
    }

    /** @var integer */
    public $ID = 0;

    /** @var string */
    public $TableName = NULL;
    
    /** @var int */
    public $RowID = 0;
    
    /** @var int */
    public $Timestamp = 0;
    
    /** @var int */
    public $UserID = 0;
    
    /** @var string */
    public $Login = NULL;
    
    /** @var string */
    public $RemoteHost = NULL;
    
    /** @var int */
    public $ModuleID = 0;
    
    /** @var string */
    public $ArchonFunction = NULL;
    
    /** @var string */
    public $RequestData = NULL;
}

$_ARCHON->mixClasses('ModificationLogEntry', 'Core_ModificationLogEntry');
?>