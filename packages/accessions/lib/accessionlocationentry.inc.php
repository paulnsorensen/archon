<?php
abstract class Accessions_AccessionLocationEntry
{
	/**
    * Deletes AccessionLocationEntry from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_ACCESSIONS, 'tblAccessions_AccessionLocationIndex'))
        {
            return false;
        }
    	
    	$_ARCHON->log("tblAccessions_Accessions", $this->AccessionID);

        return true;
    }




	/**
    * Loads AccessionLocationEntry from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblAccessions_AccessionLocationIndex'))
        {
            return false;
        }

        $this->Location = New Location($this->LocationID);
        $this->Accession = New Accession($this->AccessionID);

        return true;
    }






	/**
    * Stores AccessionLocationEntry to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;
    	
    	$this->Extent = str_replace(',', '.', $this->Extent);

        $checkquery = "SELECT ID FROM tblAccessions_AccessionLocationIndex WHERE AccessionID = ? AND LocationID = ? AND Content = ? AND ID != ?";
        $checktypes = array('integer', 'integer', 'text', 'integer');
        $checkvars = array($this->AccessionID, $this->LocationID, $this->Content, $this->ID);
        $checkqueryerror = "A LocationEntry with the same CollectionContent already exists in the database";
        $problemfields = array('AccessionID', 'LocationID', 'Content');
        $requiredfields = array('AccessionID', 'LocationID', 'Content');
        
        if(!$_ARCHON->storeObject($this, MODULE_ACCESSIONS, 'tblAccessions_AccessionLocationIndex', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        $_ARCHON->log("tblAccessions_Accessions", $this->AccessionID);
        
        return true;
    }





    /**
    * Generates a formatted string of the AccessionLocationEntry object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateFieldNames[optional]
    * @param string $Delimiter[optional]
    * @param boolean $AlwaysDelimit
    * @return string
    */
    public function toString($MakeIntoLink = LINK_NONE, $ConcatinateFieldNames = true, $Delimiter = ', ', $AlwaysDelimit = false)
    {
        global $_ARCHON;
        
        $objLocationPhrase = Phrase::getPhrase('locationentries_location', PACKAGE_ACCESSIONS, 0, PHRASETYPE_ADMIN);
        $strLocation =  $objLocationPhrase ? $objLocationPhrase->getPhraseValue(ENCODE_HTML) : 'Location';
        $objRangeValuePhrase = Phrase::getPhrase('locationentries_rangevalue', PACKAGE_ACCESSIONS, 0, PHRASETYPE_ADMIN);
        $strRangeValue =  $objRangeValuePhrase ? $objRangeValuePhrase->getPhraseValue(ENCODE_HTML) : 'Range';
        $objSectionPhrase = Phrase::getPhrase('locationentries_section', PACKAGE_ACCESSIONS, 0, PHRASETYPE_ADMIN);
        $strSection =  $objSectionPhrase ? $objSectionPhrase->getPhraseValue(ENCODE_HTML) : 'Section';
        $objShelfPhrase = Phrase::getPhrase('locationentries_shelf', PACKAGE_ACCESSIONS, 0, PHRASETYPE_ADMIN);
        $strShelf =  $objShelfPhrase ? $objShelfPhrase->getPhraseValue(ENCODE_HTML) : 'Shelf';
        
        if($this->LocationID && !$this->Location)
        {
            $this->Location = New Location($this->LocationID);
            $this->Location->dbLoad();
        }

        if($this->ExtentUnitID && !$this->ExtentUnit)
        {
            $this->ExtentUnit = New ExtentUnit($this->ExtentUnitID);
            $this->ExtentUnit->dbLoad();
        }

        $url = "?p=core/search&amp;locationid=$this->LocationID";
        $Location = $ConcatinateFieldNames ? $strLocation.': '.$this->Location->toString() : $this->Location->toString();
        $String = ($MakeIntoLink == LINK_EACH) ? "<a href='$url'>{$Location}</a>" : $Location;

        if(isset($this->RangeValue))
        {
            $String .= $Delimiter;
            $url .= "&amp;rangevalue={$this->getString('RangeValue')}";
            $RangeValue = $ConcatinateFieldNames ? $strRangeValue.': '.$this->getString('RangeValue') : $this->getString('RangeValue');
            $String .= ($MakeIntoLink == LINK_EACH) ? "<a href='$url'>{$RangeValue}</a>" : $RangeValue;
        }
        else if($AlwaysDelimit)
        {
            $String .= $Delimiter;
        }

        if(isset($this->Section))
        {
            $String .= $Delimiter;
            $url .= "&amp;section={$this->getString('Section')}";
            $Section = $ConcatinateFieldNames ? $strSection.': '.$this->getString('Section') : $this->getString('Section');
            $String .= ($MakeIntoLink == LINK_EACH) ? "<a href='$url'>{$Section}</a>" : $Section;
        }
        else if($AlwaysDelimit)
        {
            $String .= $Delimiter;
        }

        if(isset($this->Shelf))
        {
            $String .= $Delimiter;
            $url .= "&amp;shelf={$this->getString('Shelf')}";
            $Shelf = $ConcatinateFieldNames ? $strShelf.': '.$this->getString('Shelf') : $this->getString('Shelf');
            $String .= ($MakeIntoLink == LINK_EACH) ? "<a href='$url'>{$Shelf}</a>" : $Shelf;
        }
        else if($AlwaysDelimit)
        {
            $String .= $Delimiter;
        }

        if(isset($this->Extent))
        {
            $String .= $Delimiter;
            $String .= $this->getString('Extent') . ($this->ExtentUnit ? ' ' . $this->ExtentUnit->getString('ExtentUnit') : '');
        }
        else if($AlwaysDelimit)
        {
            $String .= $Delimiter;
        }

        if($MakeIntoLink == LINK_TOTAL)
        {
            $String = "<a href='?p=core/search&amp;locationid=$this->LocationID'>$String</a>";
        }

        if($this->Content)
        {
            $String = $this->getString('Content') . $Delimiter . $String;
        }
        else if($AlwaysDelimit)
        {
            $String = $Delimiter . $String;
        }

        return $String;
    }
    
    
    
    public function verifyDeletePermissions()
    {
    	global $_ARCHON;
    	
        if(!$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, UPDATE))
        {
            return false;
        }

        if(!$this->ID)
        {
            return false;
        }

        if(!is_natural($this->ID))
        {
            return false;
        }

        static $prep = NULL;
        if(!isset($prep))
        {
            $checkquery = "SELECT ID, AccessionID FROM tblAccessions_AccessionLocationIndex WHERE ID = ?";
            $prep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
        }
        $result = $prep->execute($this->ID);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        
        $row = $result->fetchRow();
        $result->free();

        if(!$row['ID'])
        {
            return false;
        }

        $this->AccessionID = $row['AccessionID'];
        
        $this->Accession = New Accession($this->AccessionID);
        $this->Accession->dbLoad();
            
        if(!$this->Accession->verifyRepositoryPermissions())
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    public function verifyStorePermissions()
    {
        // AccessionID set?
        if(!$this->AccessionID)
        {
            return false;
        }

        if(!$this->Accession)
        {
            $this->Accession = New Accession($this->AccessionID);
            $this->Accession->dbLoad();
        }
        if(!$this->Accession->verifyRepositoryPermissions())
        {
            return false;
        }
        
        return true;
    }



    /**
	 * @var integer
	 */
    public $ID = 0;

    /**
     * @var integer
     */
    public $AccessionID = 0;

    /**
     * @var integer
     */
    public $LocationID = 0;

    /**
     * @var string
     */
    public $Content = '';

    /**
     * @var string
     */
    public $RangeValue = '';

    /**
     * @var string
     */
    public $Section = '';

    /**
     * @var string
     */
    public $Shelf = '';

    /**
     * @var integer
     */
    public $Extent = 0.00;

    /**
     * @var integer
     */
    public $ExtentUnitID = 0;

    /**
     * @var Accession
     */
    public $Accession = NULL;

    /**
     * @var Location
     */
    public $Location = NULL;

    /**
     * @var ExtentUnit
     */
    public $ExtentUnit = NULL;

    public $ToStringFields = array('ID', 'LocationID', 'ExtentUnitID', 'Extent', 'RangeValue', 'Section', 'Shelf', 'Content');
}

$_ARCHON->mixClasses('AccessionLocationEntry', 'Accessions_AccessionLocationEntry');
?>