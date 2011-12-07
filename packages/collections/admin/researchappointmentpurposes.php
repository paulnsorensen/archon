<?php
/**
 * Appointment Purposes Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox
 */


isset($_ARCHON) or die();

appointmentpurposes_ui_initialize();

// Determine what to do based upon user input
function appointmentpurposes_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      appointmentpurposes_ui_main();
   }

   elseif($_REQUEST['f'] == 'search')
   {
      appointmentpurposes_ui_search();
   }
   else
   {
      appointmentpurposes_ui_exec(); // No interface needed, include an execution file.
   }
}



// appointmentpurposes_ui_main()
//   - purpose: Creates the primary user interface
//              for the appointmentpurpose Manager.
function appointmentpurposes_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('ResearchAppointmentPurpose');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
   $generalSection->insertRow('appointmentpurpose')->insertTextField('ResearchAppointmentPurpose', 50, 50)->required();

   $_ARCHON->AdministrativeInterface->setNameField('ResearchAppointmentPurpose');

   $_ARCHON->AdministrativeInterface->outputInterface();
}




function appointmentpurposes_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchResearchAppointmentPurposes', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function appointmentpurposes_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objAppointmentPurpose = New ResearchAppointmentPurpose($_REQUEST);
         $objAppointmentPurpose->ID = $ID;
         $objAppointmentPurpose->dbStore();
         $ID = $objAppointmentPurpose->ID;
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objAppointmentPurpose = New ResearchAppointmentPurpose($ID);
         $objAppointmentPurpose->dbDelete();
      }
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
   }

   if($_ARCHON->Error)
   {
      $msg = $_ARCHON->Error;
   }
   else
   {
      $msg = "ResearchAppointmentPurpose Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}