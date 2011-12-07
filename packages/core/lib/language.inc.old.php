<?php
abstract class Core_Language
{
	/**
    * Deletes Language from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_LANGUAGES, 'tblCore_Languages'))
        {
            return false;
        }

        return true;
    }





    /**
    * Loads Language from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_Languages'))
        {
            return false;
        }

        return true;
    }






	/**
    * Stores Language to the database
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

        $checkqueries[] = "SELECT ID FROM tblCore_Languages WHERE LanguageLong = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->LanguageLong, $this->ID);
        $checkqueryerrors[] = "A Language with the same Name already exists in the database";
        $problemfields[] = array('LanguageLong');

        $checkqueries[] = "SELECT ID FROM tblCore_Languages WHERE LanguageShort = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->LanguageShort, $this->ID);
        $checkqueryerrors[] = "A Language with the same LanguageShort already exists in the database";
        $problemfields[] = array('LanguageShort');

        $requiredfields = array('LanguageShort', 'LanguageLong');

        if(!$_ARCHON->storeObject($this, MODULE_LANGUAGES, 'tblCore_Languages', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
        {
            return false;
        }

        return true;
    }





    /**
    * Generates a formatted string of the Language object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @return string
    */
    public function toString($MakeIntoLink = LINK_NONE)
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert Language to string: Language ID not defined.");
            return false;
        }

        if(!$this->LanguageLong)
        {
            $this->dbLoad();
        }

        if($MakeIntoLink == LINK_NONE)
        {
            $String .= $this->getString('LanguageLong');
        }
        else
        {
            if($_ARCHON->QueryStringURL)
            {
                $q = '&amp;q=' . $_ARCHON->QueryStringURL;
            }

            $String .= "<a href='?p=core/search&amp;languageid={$this->ID}$q'>{$this->getString('LanguageLong')}</a>";
        }

        if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $_ARCHON->Security->verifyPermissions(MODULE_LANGUAGES, UPDATE))
        {
            

            $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
            $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

            $String .= "<a href='?p=admin/core/languages&id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
        }

        return $String;
    }

    /** @var integer */
    public $ID = 0;

    /** @var string */
    public $LanguageShort = '';

    /** @var string */
    public $LanguageLong = '';

    /** @var integer */
    public $DisplayOrder = 0;
}

$_ARCHON->mixClasses('Language', 'Core_Language');
?>