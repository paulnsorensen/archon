<?php

abstract class Collections_ResearchAppointment
{

   /**
    * Deletes a Appointment from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_RESEARCHAPPOINTMENTS, 'tblCollections_ResearchAppointments'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads Appointment
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCollections_ResearchAppointments'))
      {
         return false;
      }

      if($this->ResearcherID)
      {
         $this->Researcher = New User($this->ResearcherID);
         $this->Researcher->dbLoad();
      }

      if($this->AppointmentPurposeID)
      {
         $this->AppointmentPurpose = New ResearchAppointmentPurpose($this->AppointmentPurposeID);
         $this->AppointmentPurpose->dbLoad();
      }

      return true;
   }

   /**
    * Loads Appointment Materials
    *
    * @return boolean
    */
   public function dbLoadMaterials()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Appointment Materials: Appointment ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Appointment Materials: Appointment ID must be numeric.");
         return false;
      }

      static $existPrep = NULL;
      if(!isset($existPrep))
      {
         $existPrep = $_ARCHON->mdb2->prepare('SELECT ID FROM tblCollections_ResearchAppointments WHERE ID = ?', 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $existPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not load Appointment Materials: Appointment ID $this->ID not found in database.");
         return false;
      }

      static $loadPrep = NULL;
      if(!isset($loadPrep))
      {
         $loadPrep = $_ARCHON->mdb2->prepare('SELECT * FROM tblCollections_ResearchAppointmentMaterialsIndex WHERE AppointmentID = ?', 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $loadPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrEntries[] = New ResearchAppointmentMaterials($row);
      }
      $result->free();

      $this->Materials = $_ARCHON->createCartFromArray($arrEntries);

      return true;
   }

   /**
    * Loads Appointment Researcher
    *
    * @return boolean
    */
   public function dbLoadResearcher()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Appointment Researcher: Appointment ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Appointment Researcher: Appointment ID must be numeric.");
         return false;
      }

      if(!$this->ResearcherID)
      {
         return $this->dbLoad();
      }
      else
      {
         $this->Researcher = New User($this->ResearcherID);
         return $this->Researcher->dbLoad();
      }
   }

   /**
    * Relate Materials to Appointment
    *
    * @param integer $CollectionID
    * @param integer $CollectionContentID[optional]
    * 
    * @return boolean
    */
   public function dbRelateMaterials($CollectionID, $CollectionContentID = 0)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not relate Material: Appointment ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not relate Material: Appointment ID must be numeric.");
         return false;
      }

      if(!$this->ResearcherID)
      {
         if(!$this->dbLoad())
         {
            $_ARCHON->declareError("Could not relate Material: There was already an error.");
            return false;
         }
      }

      // Check Permissions
      if((!$_ARCHON->Security->userHasAdministrativeAccess && $this->ResearcherID != $_ARCHON->Security->Session->getUserID()) || ($_ARCHON->Security->userHasAdministrativeAccess && !$_ARCHON->Security->verifyPermissions(MODULE_RESEARCHAPPOINTMENTS, UPDATE)))
      {
         $_ARCHON->declareError("Could not relate Material: Permission Denied.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $_ARCHON->declareError("Could not relate Material: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($CollectionContentID))
      {
         $_ARCHON->declareError("Could not relate Material: CollectionContent ID must be numeric.");
         return false;
      }

      static $collectionPrep = NULL;
      if(!isset($collectionPrep))
      {
         $query = "SELECT ID FROM tblCollections_Collections WHERE ID = ?";
         $collectionPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $collectionPrep->execute($CollectionID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not relate Material: Collection ID $CollectionID not found in database.");
         return false;
      }

      if($CollectionContentID)
      {
         static $contentPrep = NULL;
         if(!isset($contentPrep))
         {
            $query = "SELECT ID FROM tblCollections_Content WHERE ID = ?";
            $contentPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $contentPrep->execute($CollectionContentID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['ID'])
         {
            $_ARCHON->declareError("Could not relate Material: CollectionContent ID $CollectionContentID not found in database.");
            return false;
         }

         $obj = New CollectionContent($CollectionContentID);
      }
      else
      {
         $obj = New Collection($CollectionID);
      }

      static $checkprep = NULL;
      if(!isset($checkprep))
      {
         $checkquery = "SELECT ID FROM tblCollections_ResearchAppointmentMaterialsIndex WHERE AppointmentID = ? AND CollectionID = ? AND CollectionContentID = ?";
         $checkprep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkprep->execute(array($this->ID, $CollectionID, $CollectionContentID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not relate Material: Material already related to Appointment ID $this->ID.");
         return false;
      }

      static $insertPrep = NULL;
      if(!isset($insertPrep))
      {
         $query = "INSERT INTO tblCollections_ResearchAppointmentMaterialsIndex (AppointmentID, CollectionID, CollectionContentID) VALUES (?, ?, ?)";
         $insertPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $insertPrep->execute(array($this->ID, $CollectionID, $CollectionContentID));
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $result = $checkprep->execute(array($this->ID, $CollectionID, $CollectionContentID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not relate Material: Unable to update the database table.");
         return false;
      }

      // Add the language to the Collections's Materials[] array
      $this->Materials[$CollectionID][$CollectionContentID] = $obj;

      $_ARCHON->log("tblCollections_ResearchAppointmentMaterialsIndex", $row['ID']);
      $_ARCHON->log("tblCollections_ResearchAppointments", $this->ID);

      return true;
   }

   /**
    * Sets AppointmentMaterials retrieval time and user
    *
    * @param integer $CollectionID[optional]
    * 
    * @return boolean
    */
   public function dbRetrieveMaterials($CollectionID = 0)
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_RESEARCHAPPOINTMENTS, UPDATE))
      {
         $_ARCHON->declareError("Could not retrieve AppointmentMaterials: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not retrieve AppointmentMaterial: Appointment ID not defined.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $_ARCHON->declareError("Could not relate Material: Collection ID must be numeric.");
         return false;
      }

      if($CollectionID)
      {
         $query = "UPDATE tblCollections_ResearchAppointmentMaterialsIndex SET RetrievalTime = ?, RetrievalUserID = ?, ReturnTime = '0', ReturnUserID = '0' WHERE AppointmentID = ? AND CollectionID = ?";
         $types = array('integer', 'integer', 'integer', 'integer');
         $vars = array(time(), $_ARCHON->Security->Session->getUserID(), $this->ID, $CollectionID);
      }
      else
      {
         $query = "UPDATE tblCollections_ResearchAppointmentMaterialsIndex SET RetrievalTime = ?, RetrievalUserID = ?, ReturnTime = '0', ReturnUserID = '0' WHERE AppointmentID = ?";
         $types = array('integer', 'integer', 'integer');
         $vars = array(time(), $_ARCHON->Security->Session->getUserID(), $this->ID);
      }

      $prep = $_ARCHON->mdb2->prepare($query, $types, MDB2_PREPARE_MANIP);
      $affected = $prep->execute($vars);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      if(true)
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
    * @param integer $CollectionID[optional]
    * 
    * @return boolean
    */
   public function dbReturnMaterials($CollectionID = 0)
   {
      global $_ARCHON;

      // Check Permissions
      if(!$_ARCHON->Security->verifyPermissions(MODULE_RESEARCHAPPOINTMENTS, UPDATE))
      {
         $_ARCHON->declareError("Could not return AppointmentMaterials: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not return AppointmentMaterial: Appointment ID not defined.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $_ARCHON->declareError("Could not return AppointmentMaterials: Collection ID must be numeric.");
         return false;
      }

      if($CollectionID)
      {
         $query = "UPDATE tblCollections_ResearchAppointmentMaterialsIndex SET ReturnTime = ?, ReturnUserID = ? WHERE AppointmentID = ? AND CollectionID = ?";
         $types = array('integer', 'integer', 'integer', 'integer');
         $vars = array(time(), $_ARCHON->Security->Session->getUserID(), $this->ID, $CollectionID);
      }
      else
      {
         $query = "UPDATE tblCollections_ResearchAppointmentMaterialsIndex SET ReturnTime = ?, ReturnUserID = ? WHERE AppointmentID = ?";
         $types = array('integer', 'integer', 'integer');
         $vars = array(time(), $_ARCHON->Security->Session->getUserID(), $this->ID);
      }

      $prep = $_ARCHON->mdb2->prepare($query, $types, MDB2_PREPARE_MANIP);
      $affected = $prep->execute($vars);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
      $prep->free();

      if(true)
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

   /**
    * Stores Appointment to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      $checkquery = "SELECT ID FROM tblCollections_ResearchAppointments WHERE ResearcherID = ? AND ArrivalTime = ? AND ID != ?";
      $checktypes = array('integer', 'integer', 'integer');
      $checkvars = array($this->ResearcherID, $this->ArrivalTime, $this->ID);
      $checkqueryerror = "A Appointment with the same ResearcherAndArrivalTime already exists in the database";
      $problemfields = array('ResearcherID', 'ArrivalTime');
      $requiredfields = array('ResearcherID', 'ArrivalTime');

      if(!$_ARCHON->storeObject($this, MODULE_RESEARCHAPPOINTMENTS, 'tblCollections_ResearchAppointments', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }

   /**
    * Sends e-mail to researcher and archivist
    *
    * @return boolean
    */
   public function sendEmails($RepositoryID)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not send appointment emails: Appointment ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not send appointment emails: Appointment ID must be numeric.");
         return false;
      }

      if(!$this->SubmitTime)
      {
         if(!$this->dbLoad())
         {
            $_ARCHON->declareError("Could not send appointment emails: There was already an error.");
            return false;
         }
      }

      if(!$RepositoryID)
      {
         $_ARCHON->declareError("Could not send appointment emails: RepositoryID invalid.");
         return false;
      }

      if(!$this->Researcher || ($this->AppointmentPurposeID && !$this->AppointmentPurpose))
      {
         if(!$this->dbLoad())
         {
            $_ARCHON->declareError("Could not send appointment emails: There was already an error.");
            return false;
         }
      }

      if(!$this->dbLoadMaterials())
      {
         $_ARCHON->declareError("Could not send appointment emails: There was already an error.");
         return false;
      }

      $Repository = New Repository($RepositoryID);
      $Repository->dbLoad();
      
      $MailFrom = $Repository->Email ? $Repository->Email : "noreply@" . $_SERVER['HTTP_HOST'];

      if($Repository->Email)
      {
         $ContactInfo = "reply to this email";
      }

      if($Repository->Phone)
      {
         if($ContactInfo)
         {
            $ContactInfo .= " or ";
         }

         $ContactInfo .= "call " . $Repository->Phone;

         if($Repository->PhoneExtension)
         {
            $ContactInfo .= " ext. " . $Repository->PhoneExtension;
         }
      }

      if(!$ContactInfo)
      {
         "contact the archives.";
      }

      $DisableStyle = $_ARCHON->PublicInterface->DisableTheme;
      $_ARCHON->PublicInterface->DisableTheme = true;

      $Summary = "Arrival Time: " . date(CONFIG_CORE_DATE_FORMAT, $this->ArrivalTime) . "\n";
      $Summary .= $this->DepartureTime ? "Departure Time: " . date(CONFIG_CORE_DATE_FORMAT, $this->DepartureTime) . "\n" : '';
      $Summary .= $this->AppointmentPurpose ? "Purpose: " . $this->AppointmentPurpose->toString() . "\n" : '';
      $Summary .= $this->Topic ? "Topic of Research: " . $this->Topic . "\n" : '';
      $Summary .= $this->ResearcherComments ? "Additional Comments for the Archivist: " . $this->ResearcherComments . "\n" : '';

      if(!empty($this->Materials->Collections))
      {
         $Summary .= "\n\n";

         foreach($this->Materials->Collections as $CollectionID => $arrContent)
         {

            foreach($arrContent->Content as $ContentID => $objMaterials)
            {
               $objCollection = $objMaterials->Collection;
               $objContent = $objMaterials->CollectionContent;

               if(CONFIG_COLLECTIONS_SEARCH_BY_CLASSIFICATION && $objCollection->ClassificationID && $objCollection->ClassificationID != $PrevClassificationID)
               {
                  $Summary .= "{$objCollection->Classification->toString(LINK_NONE, true, false, true, false)}/$objCollection->CollectionIdentifier ";
                  $Summary .= $objCollection->Classification->toString(LINK_NONE, false, true, false, true, '/') . " -- ";
               }
               else
               {
                  $Summary .= "$objCollection->CollectionIdentifier ";
               }

               $Summary .= $objCollection->toString(LINK_NONE) . ". ";


               if($objContent)
               {
                  $Summary .= $objContent->toString(LINK_NONE, true, true, true, true, ', ');
               }

               $Summary .= "\n";
            }
         }
      }

      $ResearcherMessage = "{$this->Researcher->FirstName} {$this->Researcher->LastName},

Thank you for making an appointment for research.  We have received your request, and look forward to your arrival.  A copy of your research request has been sent to the archives staff.  If there are any problems with your appointment, you will be contacted by e-mail by a representative of the archives.  Also, if you need to reach the archives to discuss your appointment further, feel free to $ContactInfo.

A copy of your research request follows:

$Summary\n\n";

      if(empty($this->Materials->Collections))
      {
         $ResearcherMessage .= "Please note that no specific materials were requested for this appointment. If you wish to have anything in particular available when you arrive, please contact us before the appointment.\n\n";
      }

      $ResearcherMessage .= "Take Care,\n";
      $ResearcherMessage .= $Repository->Administrator ? $Repository->Administrator . "\n" : '';
      $ResearcherMessage .= $Repository->Name ? $Repository->Name . "\n" : '';
      $ResearcherMessage .= $Repository->Address ? $Repository->Address . "\n" : '';
      $ResearcherMessage .= $Repository->Address2 ? $Repository->Address2 . "\n" : '';
      $ResearcherMessage .= $Repository->City ? $Repository->City . ', ' . $Repository->State . ', ' . $Repository->ZIPCode : '';
      $ResearcherMessage .= $Repository->ZIPPlusFour ? "-" . $Repository->ZIPPlusFour : '';
      $ResearcherMessage .= $Repository->City ? "\n" : '';
      $ResearcherMessage .= $Repository->Phone ? "Phone: " . $Repository->Phone : '';
      $ResearcherMessage .= $Repository->PhoneExtension ? " ext. " . $Repository->PhoneExtension : '';
      $ResearcherMessage .= $Repository->Phone ? "\n" : '';
      $ResearcherMessage .= $Repository->Fax ? "Fax: " . $Repository->Fax . "\n" : '';

      $ArchivistMessage = "A research request has been submitted by {$this->Researcher->FirstName} {$this->Researcher->LastName}.  To contact the researcher about the appointment, please reply directly to this e-mail or email him/her at {$this->Researcher->Email}.

Researcher Information:
";
      $ArchivistMessage .= "Name: {$this->Researcher->FirstName} {$this->Researcher->LastName}\n";
      //$ArchivistMessage .= "Researcher Type: " . $this->Researcher->ResearcherType->toString() . "\n";
      $ArchivistMessage .= $this->Researcher->Email . "\n";

      //TODO: these should now be userprofile fields
//        $ArchivistMessage .= $this->Researcher->Address ? $this->Researcher->Address . "\n" : '';
//        $ArchivistMessage .= $this->Researcher->Address2 ? $this->Researcher->Address2 . "\n" : '';
//        $ArchivistMessage .= $this->Researcher->City ? $this->Researcher->City . ', ' . $this->Researcher->State . ', ' . $this->Researcher->ZIPCode : '';
//        $ArchivistMessage .= $this->Researcher->ZIPPlusFour ? "-" . $this->Researcher->ZIPPlusFour : '';
//        $ArchivistMessage .= $this->Researcher->City ? "\n" : '';
//        $ArchivistMessage .= $this->Researcher->Phone ? "Phone: " . $this->Researcher->Phone : '';
//        $ArchivistMessage .= $this->Researcher->PhoneExtension ? " ext. " . $this->Researcher->PhoneExtension : '';
//        $ArchivistMessage .= $this->Researcher->Phone ? "\n" : '';

      $ArchivistMessage .= "
Appointment Details:

$Summary\n\n";

      if(empty($this->Materials->Collections))
      {
         $ArchivistMessage .= "No specific materials were requested for this appiontment. You may wish to contact the researcher about what he/she wishes to see before he/she arrives.";
      }

      $_ARCHON->PublicInterface->DisableTheme = $DisableStyle;

      $HttpHost = preg_match('/^[\d]+\.[\d]+\.[\d]+\.[\d]+$/u', $_SERVER['HTTP_HOST']) ? gethostbyaddr($_SERVER['HTTP_HOST']) : $_SERVER['HTTP_HOST'];
      $MailFrom = $Repository->Email ? $Repository->Email : "noreply@" . $HttpHost;
      $ResearcherResult = mail(encoding_convert_encoding($this->Researcher->Email, 'ISO-8859-1'), encoding_convert_encoding($Repository->Name, 'ISO-8859-1') . ": Research Request Submitted", encoding_convert_encoding($ResearcherMessage, 'ISO-8859-1'), "From: $MailFrom");

      if($Repository->Email)
      {
         if(!mail(encoding_convert_encoding($Repository->Email, 'ISO-8859-1'), encoding_convert_encoding($Repository->Name, 'ISO-8859-1') . ": Research Request Received", encoding_convert_encoding($ArchivistMessage, 'ISO-8859-1'), "From: " . encoding_convert_encoding($this->Researcher->Email, 'ISO-8859-1')))
         {
            $_ARCHON->declareError("Could not send appointment emails: mail() reported an error for ArchivistMessage.");
            return false;
         }
      }
      else
      {
         $_ARCHON->declareError("Could not send appointment emails: repository email not defined.");
         return false;
      }

      if(!$ResearcherResult)
      {
         $_ARCHON->declareError("Could not send appointment emails: mail() reported an error for ResearcherMessage.");
         return false;
      }

      return true;
   }

   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if($this->ID != 0 && !$_ARCHON->Security->verifyPermissions(MODULE_RESEARCHAPPOINTMENTS, UPDATE))
      {
         $_ARCHON->declareError("Could not store Appointment: Permission Denied.");
         return false;
      }

      return true;
   }

   /**
    * Outputs Appointment if Appointment is cast to string
    *
    * @return string
    */
   public function toString()
   {
      $String = date(CONFIG_CORE_DATE_FORMAT, $this->ArrivalTime);
      if($this->ResearcherID && !$this->Researcher)
      {
         $this->dbLoadResearcher();
      }

      if($this->Researcher)
      {
         $String .= ": " . $this->Researcher->getString('FirstName') . " " . $this->Researcher->getString('LastName');
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
   public $SubmitTime = 0;
   /**
    * @var integer
    */
   public $ResearcherID = 0;
   /**
    * @var integer
    */
   public $AppointmentPurposeID = 0;
   /**
    * @var integer
    */
   public $ArrivalTime = 0;
   /**
    * @var integer
    */
   public $DepartureTime = 0;
   /**
    * @var string
    */
   public $Topic = '';
   /**
    * @var string
    */
   public $ResearcherComments = '';
   /**
    * @var string
    */
   public $ArchivistComments = '';
   /**
    * @var Researcher
    */
   public $Researcher = NULL;
   /**
    * @var AppointmentPurpose
    */
   public $AppointmentPurpose = NULL;
   /**
    * @var Collection[]|CollectionContent[]
    */
   public $Materials = array();
}

$_ARCHON->mixClasses('ResearchAppointment', 'Collections_ResearchAppointment');
?>