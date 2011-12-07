<?php
/**
 * Usergroup Manager
 *
 * @author Chris Rishel
 * @package Archon
 * @subpackage AdminUI
 */

isset($_ARCHON) or die();

// Determine what to do based upon usergroup input
if(!$_REQUEST['f'])
{
   usergroups_ui_main();
}
else if($_REQUEST['f'] == "search")
{
   usergroups_ui_search();
}
else
{
   usergroups_ui_exec();
}






/**
 * Creates the primary usergroup interface for the Usergroup Manager
 *
 */
function usergroups_ui_main()
{
   global $_ARCHON;

   $objPermissionsReadPhrase = Phrase::getPhrase('permissions_read', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strPermissionsRead = $objPermissionsReadPhrase ? $objPermissionsReadPhrase->getPhraseValue(ENCODE_HTML) : 'Read';
   $objPermissionsAddPhrase = Phrase::getPhrase('permissions_add', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strPermissionsAdd = $objPermissionsAddPhrase ? $objPermissionsAddPhrase->getPhraseValue(ENCODE_HTML) : 'Add';
   $objPermissionsUpdatePhrase = Phrase::getPhrase('permissions_update', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strPermissionsUpdate = $objPermissionsUpdatePhrase ? $objPermissionsUpdatePhrase->getPhraseValue(ENCODE_HTML) : 'Update';
   $objPermissionsDeletePhrase = Phrase::getPhrase('permissions_delete', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strPermissionsDelete = $objPermissionsDeletePhrase ? $objPermissionsDeletePhrase->getPhraseValue(ENCODE_HTML) : 'Delete';
   $objPermissionsFullControlPhrase = Phrase::getPhrase('permissions_fullcontrol', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strPermissionsFullControl = $objPermissionsFullControlPhrase ? $objPermissionsFullControlPhrase->getPhraseValue(ENCODE_HTML) : 'Full Control';

   $_ARCHON->AdministrativeInterface->setClass('Usergroup');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('usergroup')->insertTextField('Usergroup', 25, 25)->required();

   $defaultPermissionsRow = $generalSection->insertRow('defaultpermissions');
   $defaultPermissionsRow->insertCheckBox('DefaultPermissionsRead');
   $defaultPermissionsRow->insertHTML("$strPermissionsRead ");
   $defaultPermissionsRow->insertNewLine();
   $defaultPermissionsRow->insertCheckBox('DefaultPermissionsAdd');
   $defaultPermissionsRow->insertHTML("$strPermissionsAdd ");
   $defaultPermissionsRow->insertNewLine();
   $defaultPermissionsRow->insertCheckBox('DefaultPermissionsUpdate');
   $defaultPermissionsRow->insertHTML("$strPermissionsUpdate ");
   $defaultPermissionsRow->insertNewLine();
   $defaultPermissionsRow->insertCheckBox('DefaultPermissionsDelete');
   $defaultPermissionsRow->insertHTML("$strPermissionsDelete ");
   $defaultPermissionsRow->insertNewLine();
   $defaultPermissionsRow->insertCheckBox('DefaultPermissionsFullControl');
   $defaultPermissionsRow->insertHTML("$strPermissionsFullControl ");

   $usersSection = $_ARCHON->AdministrativeInterface->insertSection('users');
   $usersSection->insertRow('users')->insertAdvancedSelect('Users', array(
       'Class' => 'User',
       'RelatedArrayName' => 'Users',
       'RelatedArrayLoadFunction' => 'dbLoadUsers',
       'Multiple' => true,
       'toStringArguments' => array(),
       'params' => array(
           'p' => 'admin/core/adminusers',
           'f' => 'search',
           'searchtype' => 'json'
       )
   ));

   $advancedPermissions = $_ARCHON->AdministrativeInterface->insertSection('advancedpermissions', 'permissions');
   $advancedPermissions->setPermissionsArguments('getPermissionsForUsergroup');

   $_ARCHON->AdministrativeInterface->outputInterface();
}





/**
 * Creates the list of usergroups in the list frame of the primary interface
 *
 */
function usergroups_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchUsergroups', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}





function usergroups_ui_exec()
{
   global $_ARCHON;

   // @set_time_limit(0);

   $objUsergroup = New Usergroup($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      $arrModules = $_ARCHON->getAllModules();

      foreach($arrIDs as &$ID)
      {
         $objUsergroup = New Usergroup($_REQUEST);
         $objUsergroup->ID = $ID;
         $stored = $objUsergroup->dbStore();
         $ID = $objUsergroup->ID;

         if(is_array($_REQUEST['relateduserids']))
         {
            $objUsergroup->dbUpdateRelatedUsers($_REQUEST['relateduserids']);
         }

         if($stored && $_REQUEST['setadvpermissions'] == 'true')
         {
            foreach($arrModules as $moduleID => $objModule)
            {
               // Do a bitwise OR on the individual permissions settings to get overall permissions value
               $permissions = intval($_REQUEST['read'][$moduleID])
                       | intval($_REQUEST['add'][$moduleID])
                       | intval($_REQUEST['update'][$moduleID])
                       | intval($_REQUEST['delete'][$moduleID])
                       | intval($_REQUEST['fullcontrol'][$moduleID]);

               if($permissions == $objUsergroup->DefaultPermissions)
               {
                  $objUsergroup->dbUnsetPermissions($moduleID);
               }
               else
               {
                  $objUsergroup->dbSetPermissions($moduleID, $permissions);
               }
            }
         }
      }

   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objUsergroup = New Usergroup($ID);
         $objUsergroup->dbDelete();
      }
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
      // $location = "window.top.frames['main'].location='?p={$_REQUEST['p']}&f=';";
   }

   if($_ARCHON->Error)
   {
      $msg = $_ARCHON->Error;
   }
   else
   {
      $msg = "Usergroup Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
?>