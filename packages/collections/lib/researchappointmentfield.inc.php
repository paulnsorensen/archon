<?php
abstract class Collections_ResearchAppointmentField
{
    /**
    * Deletes ResearchAppointmentField from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_RESEARCHAPPOINTMENTFIELDS, 'tblCollections_ResearchAppointmentFields'))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    
    /**
    * Loads ResearchAppointmentField
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCollections_ResearchAppointmentFields'))
        {
            return false;
        }
        
        if($this->PatternID)
        {
            $this->Pattern = New Pattern($this->PatternID);
            $this->Pattern->dbLoad();
        }

        return true;
    }





	/**
    * Stores ResearchAppointmentField to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCollections_ResearchAppointmentFields WHERE Name = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->Name, $this->ID);
        $checkqueryerror = "A ResearchAppointmentField with the same Name already exists in the database";
        $problemfields = array('Name', 'InputType');
        $requiredfields = array('Name', 'InputType');
        
        if(!$_ARCHON->storeObject($this, MODULE_RESEARCHAPPOINTMENTFIELDS, 'tblCollections_ResearchAppointmentFields', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    
    /**
     * Outputs ResearchAppointmentField as a string
     *
     * @return string
     */
    public function toString()
    {
        return $this->getString('Name');
    }

    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var integer
     */
    public $DisplayOrder = 1;

    /**
     * @var string
     */
    public $Name = '';

    /**
     * @var string
     */
    public $DefaultValue = '';

    /**
     * @var integer
     */
    public $Required = 0;

    /**
     * @var string
     */
    public $InputType = '';

    /**
     * @var integer
     */
    public $PatternID = 0;

    /**
     * @var integer
     */
    public $Size = 30;

    /**
     * @var integer
     */
    public $MaxLength = 50;

    /**
     * @var string
     */
    public $ListDataSource = '';

    /**
     * @var Pattern
     */
    public $Pattern = NULL;
}

$_ARCHON->mixClasses('ResearchAppointmentField', 'Collections_ResearchAppointmentField');
?>