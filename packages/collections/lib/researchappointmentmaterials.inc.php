<?php
abstract class Collections_ResearchAppointmentMaterials
{
    /**
     * Deletes a AppointmentMaterials from the database
     *
     * @return boolean
     */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_RESEARCHAPPOINTMENTS, 'tblCollections_ResearchAppointmentMaterialsIndex'))
        {
            return false;
        }
        
        return true;
    }





    /**
    * Loads AppointmentMaterials
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCollections_ResearchAppointmentMaterialsIndex'))
        {
            return false;
        }

        return true;
    }





    /**
    * Loads AppointmentMaterials
    *
    * @return boolean
    */
    public function dbLoadCollectionAndCollectionContent()
    {
        global $_ARCHON;

        if(!$this->CollectionID)
        {
            if(!$this->dbLoad())
            {
                $_ARCHON->declareError("Could not load Collection for AppointmentMaterials: There was already an error.");
                return false;
            }
        }

        if(!$this->CollectionID)
        {
            $_ARCHON->declareError("Could not load Collection for AppointmentMaterials: Collection ID not defined.");
            return false;
        }

        $this->Collection = New Collection($this->CollectionID);

        if(!$this->Collection->dbLoad())
        {
            $_ARCHON->declareError("Could not load Collection for AppointmentMaterials: There was already an error.");
            return false;
        }

        if($this->CollectionContentID)
        {
            $this->CollectionContent = New CollectionContent($this->CollectionContentID);
            $this->CollectionContent->dbLoad();
        }

        return true;
    }





    /**
    * Sets AppointmentMaterials retrieval time and user
    *
    * @return boolean
    */
    public function dbRetrieve()
    {
        global $_ARCHON;

        // Check Permissions
        if(!$_ARCHON->Security->verifyPermissions(MODULE_RESEARCHAPPOINTMENTS, UPDATE))
        {
            $_ARCHON->declareError("Could not retrieve AppointmentMaterials: Permission Denied.");
            return false;
        }

        if(!$this->dbLoad())
        {
            $_ARCHON->declareError("Could not retrieve AppointmentMaterials: There was already an error.");
            return false;
        }

        if($this->RetrievalTime && !$this->ReturnTime)
        {
            $_ARCHON->declareError("Could not retrieve AppointmentMaterials: AppointmentMaterials have already been retrieved.");
            return false;
        }

        static $prep = NULL;
        if(!isset($prep))
        {
            $query = "UPDATE tblCollections_ResearchAppointmentMaterialsIndex SET RetrievalTime = ?, RetrievalUserID = ?, ReturnTime = '0', ReturnUserID = '0' WHERE ID = ?";
            $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
        }
        $affected = $prep->execute(array(time(), $_ARCHON->Security->Session->getUserID(), $this->ID));
        if (PEAR::isError($affected)) {
            trigger_error($affected->getMessage(), E_USER_ERROR);
        }

        if($affected > 0)
        {
            $_ARCHON->log("tblCollections_ResearchAppointmentMaterialsIndex", $this->ID);
        }
        else
        {
            $_ARCHON->declareError("Could not retrieve AppointmentMaterials: Unable to update the database table.");
            return false;
        }

        return true;
    }




    /**
    * Sets AppointmentMaterials return time and user
    *
    * @return boolean
    */
    public function dbReturn()
    {
        global $_ARCHON;

        // Check Permissions
        if(!$_ARCHON->Security->verifyPermissions(MODULE_RESEARCHAPPOINTMENTS, UPDATE))
        {
            $_ARCHON->declareError("Could not return AppointmentMaterials: Permission Denied.");
            return false;
        }

        if(!$this->dbLoad())
        {
            $_ARCHON->declareError("Could not return AppointmentMaterials: There was already an error.");
            return false;
        }

        if($this->ReturnTime)
        {
            $_ARCHON->declareError("Could not return AppointmentMaterials: AppointmentMaterials have already been returned.");
            return false;
        }

        static $prep = NULL;
        if(!isset($prep))
        {
            $query = "UPDATE tblCollections_ResearchAppointmentMaterialsIndex SET ReturnTime = ?, ReturnUserID = ? WHERE ID = ?";
            $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
        }
        $affected = $prep->execute(array(time(), $_ARCHON->Security->Session->getUserID(), $this->ID));

        if($affected > 0)
        {
            $_ARCHON->log("tblCollections_ResearchAppointmentMaterialsIndex", $this->ID);
        }
        else
        {
            $_ARCHON->declareError("Could not return AppointmentMaterials: Unable to update the database table.");
            return false;
        }

        return true;
    }
    
    
    
    public function verifyDeletePermissions()
    {
    	return $this->Security->verifyPermissions(MODULE_RESEARCHAPPOINTMENTS, UPDATE);
    }




    /**
    * Outputs AppointmentMaterials if AppointmentMaterials is cast to string
    *
    * @return string
    */
    public function toString()
    {
        return $this->getString('AppointmentMaterials');
    }

    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var integer
     */
    public $AppointmentID = 0;

    /**
     * @var integer
     */
    public $CollectionID = 0;

    /**
     * @var integer
     */
    public $CollectionContentID = 0;

    /**
     * @var integer
     */
    public $RetrievalTime = 0;

    /**
     * @var integer
     */
    public $RetrievalUserID = 0;

    /**
     * @var integer
     */
    public $ReturnTime = 0;

    /**
     * @var integer
     */
    public $ReturnUserID = 0;


    /**
     * @var Appointment
     */
    public $Appointment = NULL;

    /**
     * @var Collection
     */
    public $Collection = NULL;

    /**
     * @var Collection
     */
    public $CollectionContent = NULL;
}

$_ARCHON->mixClasses('ResearchAppointmentMaterials', 'Collections_ResearchAppointmentMaterials');
?>