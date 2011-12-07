<?php
abstract class Core_Module
{
    /**
     * Loads Module
     *
     * @return boolean
     */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_Modules'))
        {
            return false;
        }

        if($this->PackageID)
        {
            $this->Package = New Package($this->PackageID);
            $this->Package->dbLoad();
        }

        return true;
    }
    
    
    
    
    /**
     * Outputs Module as a string
     *
     * @return string
     */
    public function toString()
    {
        global $_ARCHON;
        
        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert Module to string: Module ID not defined.");
            return false;
        }
        
        $EncodingType = ($_ARCHON->PublicInterface->EscapeXML || $_ARCHON->AdministrativeInterface->EscapeXML) ? ENCODE_HTML : ENCODE_NONE;
        
        $objModulePhrase = Phrase::getPhrase("module_name", $this->PackageID, $this->ID, PHRASETYPE_ADMIN);
        $String = $objModulePhrase ? $objModulePhrase->getPhraseValue($EncodingType) : $this->Script;
        
        return $String;
    }





    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var integer
     */
    public $PackageID = 0;

    /**
     * @var string
     */
    public $Script = NULL;

    /**
     * @var Package
     */
    public $Package = NULL;
}

$_ARCHON->mixClasses('Module', 'Core_Module');
?>