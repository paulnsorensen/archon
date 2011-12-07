<?php
abstract class Creators_CreatorSource
{
    /**
    * Deletes CreatorSource from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_CREATORSOURCES, 'tblCreators_CreatorSources'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads CreatorSource from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCreators_CreatorSources'))
        {
            return false;
        }

        return true;
    }






	/**
    * Stores CreatorSource to the database
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

        $checkqueries[] = "SELECT ID FROM tblCreators_CreatorSources WHERE CreatorSource = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->CreatorSource, $this->ID);
        $checkqueryerrors[] = "A CreatorSource with the same Name already exists in the database";
        $problemfields[] = array('CreatorSource');
        
        $checkqueries[] = "SELECT ID FROM tblCreators_CreatorSources WHERE SourceAbbreviation = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->SourceAbbreviation, $this->ID);
        $checkqueryerrors[] = "A CreatorSource with the same SourceAbbreviation already exists in the database";
        $problemfields[] = array('SourceAbbreviation');
        
        $requiredfields = array('CreatorSource', 'SourceAbbreviation');
        
        if(!$_ARCHON->storeObject($this, MODULE_CREATORSOURCES, 'tblCreators_CreatorSources', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Outputs CreatorSource if CreatorSource is cast to string
    *
    * @magic
    * @return string
    */
    public function toString()
    {
        return $this->getString('CreatorSource');
    }


    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $CreatorSource = '';

    /**
     * @var string
     */
    public $SourceAbbreviation = '';

    /**
     * @var string
     */
    public $Citation = '';

    /**
     * @var string
     */
    public $Description = '';
}

$_ARCHON->mixClasses('CreatorSource', 'Creators_CreatorSource');
?>