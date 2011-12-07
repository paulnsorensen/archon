<?php
abstract class Collections_ResearcherType
{
    /**
     * Deletes a ResearcherType from the database
     *
     * @return boolean
     */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_RESEARCHERTYPES, 'tblCollections_ResearcherTypes'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads ResearcherType
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCollections_ResearcherTypes'))
        {
            return false;
        }

        return true;
    }





    /**
     * Stores ResearcherType to the database
     *
     * @return boolean
     */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCollections_ResearcherTypes WHERE ResearcherType = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->ResearcherType, $this->ID);
        $checkqueryerror = "A ResearcherType with the same Name already exists in the database";
        $problemfields = array('ResearcherType');
        $requiredfields = array('ResearcherType');
        
        if(!$_ARCHON->storeObject($this, MODULE_RESEARCHERTYPES, 'tblCollections_ResearcherTypes', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }




    /**
    * Outputs ResearcherType if ResearcherType is cast to string
    *
    * @return string
    */
    public function toString()
    {
        return $this->getString('ResearcherType');
    }

    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $ResearcherType = '';
}

$_ARCHON->mixClasses('ResearcherType', 'Collections_ResearcherType');
?>