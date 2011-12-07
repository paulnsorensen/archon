<?php
abstract class Accessions_ProcessingPriority
{
    /**
     * Deletes a ProcessingPriority from the database
     *
     * @return boolean
     */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_PROCESSINGPRIORITIES, 'tblAccessions_ProcessingPriorities'))
        {
        	return false;
        }
        
        return true;
    }


    /**
    * Loads ProcessingPriority from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblAccessions_ProcessingPriorities'))
        {
            return false;
        }
        
        return true;
    }





    /**
     * Stores ProcessingPriority to the database
     *
     * @return boolean
     */
    public function dbStore()
    {
        global $_ARCHON;

        $checkquery = "SELECT ID FROM tblAccessions_ProcessingPriorities WHERE ProcessingPriority = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->ProcessingPriority, $this->ID);
        $checkqueryerror = "A ProcessingPriority with the same Name already exists in the database";
        $problemfields = array('ProcessingPriority');
        $requiredfields = array('ProcessingPriority', 'DisplayOrder');
        
        if(!$_ARCHON->storeObject($this, MODULE_PROCESSINGPRIORITIES, 'tblAccessions_ProcessingPriorities', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }



    /**
    * Generates a formatted string of the ProcessingPriority object
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
            $_ARCHON->declareError("Could not convert ProcessingPriority to string: ProcessingPriority ID not defined.");
            return false;
        }

        return $this->getString('ProcessingPriority');
    }




    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $ProcessingPriority = '';
    
    /**
     * @var string
     */
    public $Description = '';
    
    /**
     * @var integer
     */
    public $DisplayOrder = 0;

    public $ToStringFields = array('ID', 'ProcessingPriority');
}

$_ARCHON->mixClasses('ProcessingPriority', 'Accessions_ProcessingPriority');
?>