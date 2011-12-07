<?php
abstract class Subjects_SubjectSource
{
    /**
    * Deletes SubjectSource from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_SUBJECTSOURCES, 'tblSubjects_SubjectSources'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads SubjectSource from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblSubjects_SubjectSources'))
        {
            return false;
        }

        return true;
    }






	/**
    * Stores SubjectSource to the database
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

        $checkqueries[] = "SELECT ID FROM tblSubjects_SubjectSources WHERE SubjectSource = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->SubjectSource, $this->ID);
        $checkqueryerrors[] = "A SubjectSource with the same Name already exists in the database";
        $problemfields[] = array('SubjectSource');
        
        $checkqueries[] = "SELECT ID FROM tblSubjects_SubjectSources WHERE EADSource = ? AND ID != ?";
        $checktypes[] = array('text', 'integer');
        $checkvars[] = array($this->EADSource, $this->ID);
        $checkqueryerrors[] = "A SubjectSource with the same EADSource already exists in the database";
        $problemfields[] = array('EADSource');
        
        $requiredfields = array('SubjectSource', 'EADSource');
        
        if(!$_ARCHON->storeObject($this, MODULE_SUBJECTSOURCES, 'tblSubjects_SubjectSources', $checkqueries, $checktypes, $checkvars, $checkqueryerrors, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Outputs SubjectSource if SubjectSource is cast to string
    *
    * @magic
    * @return string
    */
    public function toString()
    {
        return $this->getString('SubjectSource');
    }


    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $SubjectSource = '';

    /**
     * @var string
     */
    public $EADSource = '';
}

$_ARCHON->mixClasses('SubjectSource', 'Subjects_SubjectSource');
?>