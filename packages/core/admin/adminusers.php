<?php

/**
 * User Manager
 *
 * @author Chris Rishel
 * @package Archon
 * @subpackage AdminUI
 */
isset($_ARCHON) or die();

// Determine what to do based upon user input
if (!$_REQUEST['f'])
{
   users_ui_main();
}
elseif ($_REQUEST['f'] == "search")
{
   users_ui_search();
}
else
{
   users_ui_exec();
}

/**
 * Creates the primary user interface for the User Manager
 *
 */
function users_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('User');

   if ($_ARCHON->AdministrativeInterface->Object->ID != 0 && !$_ARCHON->AdministrativeInterface->Object->IsAdminUser)
   {
      $_ARCHON->AdministrativeInterface->Redirect = "admin/core/publicusers&id={$_ARCHON->AdministrativeInterface->Object->ID}";
      $_ARCHON->AdministrativeInterface->outputInterface();
   }

   $_ARCHON->AdministrativeInterface->setNameField('Login');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   if ($_ARCHON->AdministrativeInterface->Object->ID != -1)
   {
      $generalSection->insertRow('login')->insertTextField('Login', 15, 50)->required();
      $generalSection->insertRow('email')->insertTextField('Email', 30, 50);

      $password = $generalSection->insertRow('password')->insertPasswordField('Password');
      $confirmpassword = $generalSection->insertRow('confirmpassword')->insertPasswordField('ConfirmPassword');
      if (!$_ARCHON->AdministrativeInterface->Object->ID)
      {
         $password->required();
         $confirmpassword->required();
      }

      $generalSection->insertRow('firstname')->insertTextField('FirstName', 30, 50);
      $generalSection->insertRow('lastname')->insertTextField('LastName', 30, 50);
      $generalSection->insertRow('displayname')->insertTextField('DisplayName', 30, 100);
      $generalSection->insertRow('languageid')->insertAdvancedSelect('LanguageID',
           array(
               'Class' => 'Language',
               'RelatedArrayName' => 'Languages',
               'RelatedArrayLoadFunction' => 'dbLoadLanguages',
               'Multiple' => false,
               'toStringArguments' => array(),
               'params' => array(
                   'p' => 'admin/core/ajax',
                   'f' => 'searchlanguages',
                   'searchtype' => 'json'
               )
   ));
      $generalSection->insertRow('locked')->insertRadioButtons('Locked');
      $generalSection->insertRow('usergroups')->insertAdvancedSelect('Usergroups', array(
          'Class' => 'Usergroup',
          'RelatedArrayName' => 'Usergroups',
          'RelatedArrayLoadFunction' => 'dbLoadUsergroups',
          'Multiple' => true,
          'toStringArguments' => array(),
          'params' => array(
              'p' => 'admin/core/usergroups',
              'f' => 'search',
              'searchtype' => 'json'
          )
      ));
      //implement required()

      $generalSection->insertRow('users_repositorylimit')->insertRadioButtons('RepositoryLimit');
      $generalSection->insertRow('repositories')->insertAdvancedSelect('Repositories', array(
          'Class' => 'Repository',
          'RelatedArrayName' => 'Repositories',
          'RelatedArrayLoadFunction' => 'dbLoadRepositories',
          'Multiple' => true,
          'toStringArguments' => array(),
          'params' => array(
              'p' => 'admin/core/repositories',
              'f' => 'search',
              'searchtype' => 'json'
          )
      ));

      $advancedPermissions = $_ARCHON->AdministrativeInterface->insertSection('advancedpermissions', 'permissions');
      $advancedPermissions->setPermissionsArguments('getPermissionsForUser');
   }

   $_ARCHON->AdministrativeInterface->outputInterface();
}

/**
 * Creates the list of users in the list frame of the primary interface
 *
 */
function users_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchUsers', array('isadminuser' => 1, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function users_ui_exec()
{
   global $_ARCHON;

   $objUser = New User($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if ($_REQUEST['f'] == 'store')
   {
      if ($_REQUEST['password'] && $_REQUEST['password'] != $_REQUEST['confirmpassword'])
      {
         $_ARCHON->declareError("Could not store User: Passwords do not match.");
      }
      elseif (empty($_REQUEST['relatedusergroupids']) || $_REQUEST['relatedusergroupids'] == array(0))
      {
         $_ARCHON->declareError("Could not store User: The User must be related to at least one Usergroup.");
      }
      elseif (empty($_REQUEST['relatedrepositoryids']) || $_REQUEST['relatedrepositoryids'] == array(0))
      {
         $_ARCHON->declareError("Could not store User: The User must be related to at least one Repository.");
      }
      else
      {
         $arrModules = $_ARCHON->getAllModules();
         // $arrUserProfileFields = $_ARCHON->getAllUserProfileFields();

         foreach ($arrIDs as &$ID)
         {
            $objUser = New User($_REQUEST);
            $objUser->ID = $ID;
            $objUser->IsAdminUser = 1;


            if ($objUser->dbStore())
            {
               $ID = $objUser->ID;

               $objUser->dbUpdateRelatedUsergroups($_REQUEST['relatedusergroupids']);
               //Create a variable so the advanced permissions will work
               $objUser->UsergroupIDs = $_REQUEST['relatedusergroupids'];

               $objUser->dbUpdateRelatedRepositories($_REQUEST['relatedrepositoryids']);


               //               foreach($_REQUEST['userprofilefields'] as $UserProfileFieldID => $arr)
               //               {
               //                  $objUser->dbSetUserProfileField($UserProfileFieldID, $arr['value']);
               //               }

               if ($_REQUEST['setadvpermissions'] == 'true')
               {
                  $arrUsergroupPermissions = array();

                  foreach ($objUser->UsergroupIDs as $usergroupID)
                  {
                     $arrUsergroupPermissions[$usergroupID] = $_ARCHON->getPermissionsForUsergroup($usergroupID);
                  }

                  foreach ($arrModules as $moduleID => $objModule)
                  {
                     // Do a bitwise OR on the individual permissions settings to get overall permissions value
                     $permissions = intval($_REQUEST['read'][$moduleID])
                             | intval($_REQUEST['add'][$moduleID])
                             | intval($_REQUEST['update'][$moduleID])
                             | intval($_REQUEST['delete'][$moduleID])
                             | intval($_REQUEST['fullcontrol'][$moduleID]);

                     foreach ($arrUsergroupPermissions as $arrPermissions)
                     {
                        $defaultpermissions |= $arrPermissions[$moduleID];
                     }

                     if ($permissions == $defaultpermissions)
                     {
                        $objUser->dbUnsetPermissions($moduleID);
                     }
                     else
                     {
                        $objUser->dbSetPermissions($moduleID, $permissions);
                     }
                  }
               }
            }
         }
      }
   }
   elseif ($_REQUEST['f'] == 'delete')
   {
      foreach ($arrIDs as $ID)
      {
         $objUser = New User($ID);
         $objUser->IsAdminUser = 1;
         $objUser->dbDelete();
      }
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
      // $location = "window.top.frames['main'].location='?p={$_REQUEST['p']}&f=';";
   }

   if ($_ARCHON->Error)
   {
      $msg = $_ARCHON->Error;
   }
   else
   {
      $msg = "User Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}

?>
