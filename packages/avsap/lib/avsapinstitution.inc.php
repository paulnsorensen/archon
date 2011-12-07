<?php
abstract class AVSAP_AVSAPInstitution
{
   /**
    * Deletes Institution from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;

        if(!$_ARCHON->deleteObject($this, MODULE_AVSAPINSTITUTIONS, 'tblAVSAP_AVSAPInstitutions'))
        {
            return false;
        }

        return true;
    }





   /**
    * Loads AvSAPInstitution from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblAVSAP_AVSAPInstitutions'))
        {
            return false;
        }

        return true;
    }





   /**
    * Stores AvSAPInstitution to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
        global $_ARCHON;

        $this->calculateScore();

        $checkquery = "SELECT ID FROM tblAVSAP_AVSAPInstitutions WHERE Name = ? AND RepositoryID =? AND ID != ?";
        $checktypes = array('text', 'integer', 'integer');
        $checkvars = array($this->Name, $this->RepositoryID, $this->ID);
        $checkqueryerror="Could not store Institution: An Institution with the same Name and RepositoryID already exists in the database";
        $problemfields = array('Name','RepositoryID');
        $requiredfields = array('Name','RepositoryID');


        if (!$_ARCHON->storeObject($this, MODULE_AVSAPINSTITUTIONS, 'tblAVSAP_AVSAPInstitutions', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }

        return true;
    }

    public function calculateScore()
    {

        $score = new AVSAPScore();
        $score->setType(AVSAP_CLASS_INSTITUTION);
        $score->loadCoefficients();
        $this->Score = $score->calculateScore($this) / $score->getTotalWeight() * 100;

    }

    public function verifyDeletePermissions()
    {
        global $_ARCHON;

        if(!$_ARCHON->Security->verifyPermissions(MODULE_AVSAPINSTITUTIONS, DELETE))
        {
            return false;
        }

        if(!$this->verifyRepositoryPermissions())
        {
            $_ARCHON->declareError("Could not delete Institution: Institutions may only be altered for the primary repository.");
            return false;
        }

        return true;
    }

    public function verifyRepositoryPermissions()
    {
        global $_ARCHON;

        if(!$_ARCHON->Security->Session->User->RepositoryLimit)
        {
            return true;
        }

        if($this->ID) // Old repository may be disallowed or maybe an empty object

        {
            static $prep = NULL;
            if(!isset($prep))
            {
                $query = "SELECT RepositoryID FROM tblAVSAP_AVSAPInstitutions WHERE ID = ?";
                $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
            }
            $result = $prep->execute($this->ID);
            if (PEAR::isError($result))
            {
                trigger_error($result->getMessage(), E_USER_ERROR);
            }

            if($row = $result->fetchRow())
            {
                $prevRepositoryID = $row['RepositoryID'];
            }
            $result->free();

            if(!$prevRepositoryID || !$_ARCHON->Security->verifyRepositoryPermissions($prevRepositoryID))
            {
                return false;
            }

            if(!$this->RepositoryID)
            {
                $this->RepositoryID = $prevRepositoryID;
                return true; //no sense in re-running the same permissions test below
            }
        }

        if(!$this->RepositoryID || !$_ARCHON->Security->verifyRepositoryPermissions($this->RepositoryID))
        {
            return false;
        }


        return true;
    }




    public function verifyStorePermissions()
    {
        global $_ARCHON;

        if(($this->ID == 0 && !$_ARCHON->Security->verifyPermissions(MODULE_AVSAPINSTITUTIONS, ADD)) || ($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_AVSAPINSTITUTIONS, UPDATE)))
        {
            return false;
        }

        if(!$this->verifyRepositoryPermissions())
        {
            $_ARCHON->declareError("Could not store Institution: Institutions may only be altered for the primary repository.");
            return false;
        }

        return true;
    }




   /**
    * Generates a formatted string of the Institution object
    *
    *   @return string
    */
    public function toString()
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert AVSAPInstitution to string: AVSAPInstitution ID not defined.");
            return false;
        }

        if(!$this->Name)
        {
            if(!$this->dbLoad())
            {
                return false;
            }
        }

        $String = $this->getString('Name');

        return $String;
    }

    // These public variables correspond directly to the fields in the tblAVSAP_AVSAPInstitutions table
   /**
    * @var integer
    */
    public $ID = 0;

   /**
    * @var integer
    */
    public $RepositoryID = 0;

   /**
    * @var string
    */
    public $Name = '';



    public $PreservationPlan = '0.01';

    public $AVPreservationPlan = '0.01';

    public $CollectionPolicy = '0.01';

    public $CatalogCollections = '0.01';

    public $AccessCopies = '0.01';

    public $DigitalCopies = '0.01';

    public $OwnershipRecords = '0.01';

    public $AllowPlayBack = '0.01';

    public $AllowLoaningInstitutions = '0.01';

    public $AllowLoaningOther = '0.01';

    public $StaffCleanRepair = '0.01';

    public $StaffVisualInspections = '0.01';

    public $StaffPlayBackInspections = '0.01';

    public $DedicatedInspectionSpace = '0.01';

    public $MaintainPlaybackEquipment = '0.01';

    public $EquipmentManuals = '0.01';

    public $EquipmentPartsService = '0.01';

    public $DisasterRecoveryPlan = '0.01';

    public $AVDisasterRecoveryPlan = '0.01';

    public $AccessAVDisasterRecoveryTools = '0.01';

    public $Score = '0.0';

    public $ToStringFields = array('ID', 'Name');

}

$_ARCHON->mixClasses('AVSAPInstitution', 'AVSAP_AVSAPInstitution');

?>