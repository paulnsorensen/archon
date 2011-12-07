<?php
abstract class DigitalLibrary_FileType
{
    /**
     * Deletes a FileType from the database
     *
     * @return boolean
     */
    public function dbDelete()
    {
        global $_ARCHON;
        
        $ID = $this->ID;
        
        if(!$_ARCHON->deleteObject($this, MODULE_FILETYPES, 'tblDigitalLibrary_FileTypes'))
        {
            return false;
        }
        
        static $filePrep = NULL;
        if(!isset($filePrep))
        {
        	// Delete any references to the filetype
            $query = "UPDATE tblDigitalLibrary_Files SET FileTypeID = '0' WHERE FileTypeID = ?";
            $filePrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
        }
        $affected = $filePrep->execute($ID);
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        return true;
    }





    /**
    * Loads FileType from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblDigitalLibrary_FileTypes'))
        {
            return false;
        }

        if($this->MediaTypeID)
        {
            $this->MediaType = New MediaType($this->MediaTypeID);
            $this->MediaType->dbLoad();
        }

        return true;
    }





    /**
     * Stores FileType to the database
     *
     * @return boolean
     */
    public function dbStore()
    {
        global $_ARCHON;
        
        $this->FileExtensions = str_replace(' ', '', $this->FileExtensions);
        
        $checkquery = "SELECT ID FROM tblDigitalLibrary_FileTypes WHERE FileType = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->FileType, $this->ID);
        $checkqueryerror = "A FileType with the same Name already exists in the database";
        $problemfields = array('FileType');
        $requiredfields = array('FileType', 'FileExtensions', 'ContentType');
        
        if(!$_ARCHON->storeObject($this, MODULE_FILETYPES, 'tblDigitalLibrary_FileTypes', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }

        return true;
    }



    /**
    * Generates a formatted string of the FileType object
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
            $_ARCHON->declareError("Could not convert FileType to string: FileType ID not defined.");
            return false;
        }

        if(!$this->FileType)
        {
            $this->dbLoad();
        }

        if($MakeIntoLink == LINK_NONE)
        {
            $String .= $this->getString('FileType');
        }
        else
        {
            if($_ARCHON->QueryStringURL)
            {
                $q = '&amp;q=' . $_ARCHON->QueryStringURL;
            }

            $String .= "<a href='?p=core/search&amp;filetypeid={$this->ID}$q'>{$this->getString('FileType')}</a>";
        }

        if(!$_ARCHON->AdministrativeInterface && !$_ARCHON->PublicInterface->DisableTheme && $_ARCHON->Security->verifyPermissions(MODULE_FILETYPES, UPDATE))
        {
            

            $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
            $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';
            
            $String .= "<a href='?p=admin/digitallibrary/filetypes&id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
        }

        return $String;
    }

    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $FileType = '';

    /**
     * @var string
     */
    public $FileExtensions = '';

    /**
     * @var string
     */
    public $ContentType = '';

    /**
     * @var integer
     */
    public $MediaTypeID = 0;



    /**
     * @var MediaType
     */
    public $MediaType = NULL;

     public $ToStringFields = array('ID', 'FileType');
}

$_ARCHON->mixClasses('FileType', 'DigitalLibrary_FileType');
?>