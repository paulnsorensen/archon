<?php
abstract class Collections_LocationEntry
{
	/**
    * Deletes LocationEntry from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;
        
        static $checkprep = NULL;
        if(!isset($checkprep))
        {
        	$checkquery = "SELECT CollectionID FROM tblCollections_CollectionLocationIndex WHERE ID = ?";
        	$checkprep = $_ARCHON->mdb2->prepare($checkquery, 'integer', MDB2_PREPARE_RESULT);
        }
        $result = $checkprep->execute($this->ID);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }
        
        $CollectionID = $row['CollectionID'] ? $row['CollectionID'] : 0;

        if(!$_ARCHON->deleteObject($this, MODULE_COLLECTIONS, 'tblCollections_CollectionLocationIndex'))
        {
            return false;
        }
        
        $_ARCHON->log("tblCollections_Collections", $CollectionID);

        return true;
    }




	/**
    * Loads LocationEntry from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCollections_CollectionLocationIndex'))
        {
            return false;
        }

        $this->Location = New Location($this->LocationID);
        $this->Collection = New Collection($this->CollectionID);

        return true;
    }






	/**
    * Stores LocationEntry to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
        global $_ARCHON;
        
        $this->Extent = str_replace(',', '.', $this->Extent);
        
        $checkquery = "SELECT ID FROM tblCollections_CollectionLocationIndex WHERE CollectionID = ? AND LocationID = ? AND Content = ? AND ID != ?";
        $checktypes = array('integer', 'integer', 'text', 'integer');
        $checkvars = array($this->CollectionID, $this->LocationID, $this->Content, $this->ID);
        $checkqueryerror = "A LocationEntry with the same CollectionContent already exists in the database";
        $problemfields = array('CollectionID', 'LocationID', 'Content');
        $requiredfields = array('CollectionID', 'LocationID', 'Content');
        
        if(!$_ARCHON->storeObject($this, MODULE_COLLECTIONS, 'tblCollections_CollectionLocationIndex', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        $_ARCHON->log("tblCollections_Collections", $this->CollectionID);

        return true;
    }
    
    
    
    
    public function verifyDeletePermissions()
    {
        global $_ARCHON;

        if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
        {
            return false;
        }
        
        // If CollectionID is not present, try to load.
        // If the content has been somehow orphaned, we still want to go
        // ahead with the deletion as long as the user is not limited to a specific
        // repository, which is checked later.
        if(!$this->CollectionID)
        {
            $this->dbLoad();
        }

        if(!$this->Collection)
        {
            $this->Collection = New Collection($this->CollectionID);
            $this->Collection->dbLoad();
        }

        // Make sure user isn't dealing with a content from another repository if they're limited
        if(array_key_exists($this->Collection->RepositoryID, $_ARCHON->Security->Session->User->Repositories) == false && $_ARCHON->Security->Session->User->RepositoryLimit)
        {
            $_ARCHON->declareError("Could not delete LocationEntry: LocationEntries may only be altered for the primary repository.");
            return false;
        }
        
        return true;
    }
    
    
    
    
    public function verifyStorePermissions()
    {
    	global $_ARCHON;

        if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, UPDATE))
        {
            return false;
        }
        
        // If CollectionID is not present, try to load.
        // If the content has been somehow orphaned, we still want to go
        // ahead with the deletion as long as the user is not limited to a specific
        // repository, which is checked later.
        if(!$this->CollectionID)
        {
            $this->dbLoad();
        }

        if(!$this->Collection)
        {
            $this->Collection = New Collection($this->CollectionID);
            $this->Collection->dbLoad();
        }

        // Make sure user isn't dealing with a content from another repository if they're limited
        if(array_key_exists($this->Collection->RepositoryID, $_ARCHON->Security->Session->User->Repositories) == false && $_ARCHON->Security->Session->User->RepositoryLimit)
        {
            $_ARCHON->declareError("Could not delete LocationEntry: LocationEntries may only be altered for the primary repository.");
            return false;
        }
        
        return true;
    }





    /**
    * Generates a formatted string of the LocationEntry object
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
        
        $objLocationPhrase = Phrase::getPhrase('locationentries_location', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
        $strLocation =  $objLocationPhrase ? $objLocationPhrase->getPhraseValue(ENCODE_HTML) : 'Location';
        $objRangeValuePhrase = Phrase::getPhrase('locationentries_rangevalue', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
        $strRangeValue =  $objRangeValuePhrase ? $objRangeValuePhrase->getPhraseValue(ENCODE_HTML) : 'Range';
        $objSectionPhrase = Phrase::getPhrase('locationentries_section', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
        $strSection =  $objSectionPhrase ? $objSectionPhrase->getPhraseValue(ENCODE_HTML) : 'Section';
        $objShelfPhrase = Phrase::getPhrase('locationentries_shelf', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
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

	/**
	 * @var integer
	 */
    public $ID = 0;

    /**
     * @var integer
     */
    public $CollectionID = 0;

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
     * @var Collection
     */
    public $Collection = NULL;

    /**
     * @var Location
     */
    public $Location = NULL;

    /**
     * @var ExtentUnit
     */
    public $ExtentUnit = NULL;

    public $ToStringFields = array('ID', 'LocationID', 'ExtentUnitID', 'RangeValue', 'Section', 'Shelf', 'Extent', 'Content');
}

$_ARCHON->mixClasses('LocationEntry', 'Collections_LocationEntry');
?>