<?php
/**
 * Sessions Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

if(!$_REQUEST['f'])  // Determine what to do based upon user input

{
   sessions_ui_main();
}

else if($_REQUEST['f'] == "search")
{
   sessions_ui_search();
}

else
{
   sessions_ui_exec();
}

// sessions_ui_main()
//   - purpose: Creates the primary user interface
//              for the sessions Manager.

function sessions_ui_main()
{
   global $_ARCHON, $perms;

   $_ARCHON->AdministrativeInterface->setClass('Session', false, false, true);  //can't update, can't add, can delete

   if ($_ARCHON->AdministrativeInterface->Object->Expires)  //don't display anything when user first enters page

   {
      $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
      $generalSection->insertRow('remotehost')->insertInformation("RemoteHost");
      $generalSection->insertRow('user')->insertInformation("User");
      if ($_ARCHON->AdministrativeInterface->Object->Persistent == 0)
      {
         $generalSection->insertRow('persistent')->insertHTML("No");
      }
      else
      {
         $generalSection->insertRow('persistent')->insertHTML("Yes");
      }
      $generalSection->insertRow('expiration')->insertHTML(date('l jS \of F Y h:i:s A' , $_ARCHON->AdministrativeInterface->Object->Expires));
   }

   $_ARCHON->AdministrativeInterface->outputInterface();

}

function sessions_ui_search()
{
   global $_ARCHON;
   echo($_ARCHON->AdministrativeInterface->searchResults('searchSessions', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0)));
}

function sessions_ui_exec()
{
   global $_ARCHON;
   @set_time_limit(0);
   if($_REQUEST['f'] == 'delete')
   {
      $objSession = New Session($_REQUEST['id']);
      $objSession->dbDelete();
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
      $location = "window.top.frames['main'].location='?p={$_REQUEST['p']}&f=';";
   }
   $location = "?p={$_REQUEST['p']}";
   if($_ARCHON->Error)
   {
      $msg = "$_ARCHON->Error";
   }
   else
   {
      $msg = "Session Deleted Successfully.";
   }
   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}