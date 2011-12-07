<?php
abstract class Collections_UserField
{
    /**
    * Deletes UserField from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_COLLECTIONCONTENT, 'tblCollections_UserFields'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads UserField
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCollections_UserFields'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Stores UserField
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCollections_UserFields WHERE Title = ? AND Value LIKE ? AND EADElementID = ? AND ContentID = ? AND ID != ?";
        $checktypes = array('text', 'text', 'integer', 'integer', 'integer');
        $checkvars = array($this->Title, $this->Value, $this->EADElementID, $this->ContentID, $this->ID);
        $checkqueryerror = "A UserDefinedField with the same TitleAndValue already exists in the database";
        $problemfields = array('Title', 'Value', 'EADElementID', 'ContentID');
        $requiredfields = array('ContentID', 'Value');
        
        if(!$_ARCHON->storeObject($this, MODULE_COLLECTIONCONTENT, 'tblCollections_UserFields', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    public function verifyDeletePermissions()
    {
        global $_ARCHON;

        if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE))
        {
            return false;
        }
        
        if($this->ContentID && !$this->Content)
        {
            $this->Content = New CollectionContent($this->ContentID);
            $this->Content->dbLoad();
        }

        // Make sure user isn't dealing with a collection from another repository if they're limited
        if(array_key_exists($this->Content->Collection->RepositoryID, $_ARCHON->Security->Session->User->Repositories) == false && $_ARCHON->Security->Session->User->RepositoryLimit)
        {
            $_ARCHON->declareError("Could not delete UserDefinedField: Collections may only be altered for the primary repository.");
            return false;
        }
        
        return true;
    }
    
    
    
    
    public function verifyStorePermissions()
    {
        global $_ARCHON;

        if(!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE))
        {
            return false;
        }
        
        // Make sure content id is set.
        if(!$this->ContentID)
        {
            return false;
        }

        if(!$this->Content)
        {
            $this->Content = New CollectionContent($this->ContentID);
            $this->Content->dbLoad();
        }

        // Make sure user isn't dealing with a user-defined field from another repository if they're limited
        if(array_key_exists($this->Content->Collection->RepositoryID, $_ARCHON->Security->Session->User->Repositories) == false && $_ARCHON->Security->Session->User->RepositoryLimit)
        {
            $_ARCHON->declareError("Could not store UserDefinedField: CollectionContent may only be altered for the primary repository.");
            return false;
        }
        
        return true;
    }





    /**
    * Generates a formatted string of the UserField object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @param boolean $ConcatinateTitle[optional]
    * @return string
    */
    public function toString($MakeIntoLink = LINK_NONE, $ConcatinateTitle = true)
    {
        if($ConcatinateTitle)
        {
            $String = $this->getString('Title') . ': ';
        }

        if($MakeIntoLink == LINK_EACH)
        {
            $String .= "<a href='?p=core/search&amp;q=" . encode(urlencode($this->Value), ENCODE_HTML) . "'>{$this->getString('Value')}</a>";
        }
        else
        {
            $String .= $this->getString('Value');
        }

        if($MakeIntoLink == LINK_TOTAL)
        {
            $String = "<a href='?p=core/search&amp;q=" . encode(urlencode($this->Value), ENCODE_HTML) . "'>$String</a>";
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
    public $ContentID = 0;

    /** 
     * @var string
     */
    public $Title = '';

    /**
     * @var string
     */
    public $Value = '';

    /** 
     * @var integer
     */
    public $EADElementID = 0;

    /** 
     * @var string
     */
    public $Content = NULL;

    /** 
     * @var EADElement
     */
    public $EADElement = NULL;

    public $ToStringFields = array('ID','Title', 'Value');
}

$_ARCHON->mixClasses('UserField', 'Collections_UserField');
?>