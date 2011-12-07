<?php
abstract class Collections_ResearchAppointmentPurpose
{
    /**
    * Deletes ResearchAppointmentPurpose from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_RESEARCHAPPOINTMENTPURPOSES, 'tblCollections_ResearchAppointmentPurposes'))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    
    /**
    * Loads ResearchAppointmentPurpose
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCollections_ResearchAppointmentPurposes'))
        {
            return false;
        }

        return true;
    }





	/**
    * Stores ResearchAppointmentPurpose to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCollections_ResearchAppointmentPurposes WHERE ResearchAppointmentPurpose = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->ResearchAppointmentPurpose, $this->ID);
        $checkqueryerror = "A ResearchAppointmentPurpose with the same ResearchAppointmentPurpose already exists in the database";
        $problemfields = array('ResearchAppointmentPurpose');
        $requiredfields = array('ResearchAppointmentPurpose');
        
        if(!$_ARCHON->storeObject($this, MODULE_RESEARCHAPPOINTMENTPURPOSES, 'tblCollections_ResearchAppointmentPurposes', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    
    /**
     * Outputs ResearchAppointmentPurpose as a string
     *
     * @return string
     */
    public function toString()
    {
        return $this->getString('ResearchAppointmentPurpose');
    }

    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $ResearchAppointmentPurpose = '';
}

$_ARCHON->mixClasses('ResearchAppointmentPurpose', 'Collections_ResearchAppointmentPurpose');
?>