<?php

/**
 * Locations Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

locations_ui_initialize();

// Determine what to do based upon user input
function locations_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      locations_ui_main();
   }
   elseif($_REQUEST['f'] == "search")
   {
      locations_ui_search();
   }
   elseif($_REQUEST['f'] == 'dialog')
   {
      locations_ui_dialog();
   }
   else
   {
      locations_ui_exec(); // No interface needed, include an execution file.
   }
}

function locations_ui_dialog()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Location');
   $_ARCHON->AdministrativeInterface->setNameField('Location');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/collections/locations', 'store');


   $dialogSection->insertRow('location')->insertTextField('Location', 50, 200)->required();
   $dialogSection->insertRow('description')->insertTextArea('Description');


   $objUser = $_ARCHON->Security->Session->User;

   if(!$objUser->RepositoryLimit)
   {
      $dialogSection->insertRow('repositorylimit')->insertCheckBox('RepositoryLimit');
      $dialogSection->insertRow('repositories')->insertAdvancedSelect('Repositories', array(
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
   }
   elseif(count($objUser->Repositories) == 1)
   {
      $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
      $repositoryName = $objUser->Repositories[key($objUser->Repositories)]->Name;
      $dialogSection->insertRow('repository')->insertInformation('Repository', $repositoryName);
      if(!$_ARCHON->AdministrativeInterface->Object->ID)
      {
         $_ARCHON->AdministrativeInterface->Object->RepositoryLimit = true;
      }
      $dialogSection->insertHiddenField('RepositoryLimit');
      $_ARCHON->AdministrativeInterface->Object->RelatedRepositoryID = $repositoryID;
      $dialogSection->insertHiddenField('RelatedRepositoryID');
   }
   else
   {
      if(!$_ARCHON->AdministrativeInterface->Object->ID)
      {
         $_ARCHON->AdministrativeInterface->Object->RepositoryLimit = true;
      }
      $dialogSection->insertHiddenField('RepositoryLimit');
      $dialogSection->insertRow('repositories')->insertRelationField('Repositories', 'Repository', $objUser->Repositories, 'Repositories', 'dbLoadRepositories', false);
   }
   $dialogSection->getRow('repositories')->setEnableConditions('RepositoryLimit', true);


   $_ARCHON->AdministrativeInterface->outputInterface();
}

// locations_ui_main()
//   - purpose: Creates the primary user interface
//              for the location Manager.
function locations_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Location');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('location')->insertTextField('Location', 50, 200)->required();
   $generalSection->insertRow('description')->insertTextArea('Description');


   $objUser = $_ARCHON->Security->Session->User;

   if(!$objUser->RepositoryLimit)
   {
      $generalSection->insertRow('repositorylimit')->insertCheckBox('RepositoryLimit');
//      $generalSection->insertRow('repositories')->insertRelationField('Repositories', 'Repository', 'searchRepositories', 'Repositories', 'dbLoadRepositories', false);
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
   }
   elseif(count($objUser->Repositories) == 1)
   {
      $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
      $repositoryName = $objUser->Repositories[key($objUser->Repositories)]->Name;
      $generalSection->insertRow('repositories')->insertInformation('Repository', $repositoryName);
      if(!$_ARCHON->AdministrativeInterface->Object->ID)
      {
         $_ARCHON->AdministrativeInterface->Object->RepositoryLimit = true;
      }
      $generalSection->insertHiddenField('RepositoryLimit');
      $_ARCHON->AdministrativeInterface->Object->RelatedRepositoryID = $repositoryID;
      $generalSection->insertHiddenField('RelatedRepositoryID');
   }
   else
   {
      if(!$_ARCHON->AdministrativeInterface->Object->ID)
      {
         $_ARCHON->AdministrativeInterface->Object->RepositoryLimit = true;
      }
      $generalSection->insertHiddenField('RepositoryLimit');
      $generalSection->insertRow('repositories')->insertAdvancedSelect('Repositories', array(
          'Class' => 'Repository',
          'RelatedArrayName' => 'Repositories',
          'RelatedArrayLoadFunction' => 'dbLoadRepositories',
          'Multiple' => true,
          'toStringArguments' => array(),
          'params' => array(
              'p' => 'admin/core/repositories',
              'f' => 'list',
              'strkey' => 'string'
          )
      ));
   }
   $generalSection->getRow('repositories')->setEnableConditions('RepositoryLimit', true);





   $infoSection = $_ARCHON->AdministrativeInterface->insertSection('information');







   if($_ARCHON->AdministrativeInterface->Object->ID)
   {
      $_ARCHON->AdministrativeInterface->Object->dbLoadCollections();
      $collStored = count($_ARCHON->AdministrativeInterface->Object->Collections);
      $infoSection->insertRow('collectionsstored')->insertInformation('CollectionsStored', $collStored);

      if(!empty($_ARCHON->AdministrativeInterface->Object->Collections))
      {
         $arrStrCollections = array();
         $count = 0;
         foreach($_ARCHON->AdministrativeInterface->Object->Collections as $objCollection)
         {
            // So we don't go overboard with the list.
            if($count >= CONFIG_CORE_SEARCH_RESULTS_LIMIT)
            {
               $arrStrCollections[] = '...';
               break;
            }
            $arrStrCollections[] = $objCollection->toString();
            $count++;
         }
         $infoSection->insertRow('collectionslist')->insertInformation('CollectionsList', $arrStrCollections);
      }


      $strExtents = array();

      foreach($_ARCHON->getExtentForLocation($_ARCHON->AdministrativeInterface->Object->ID) as $objExtent)
      {
         $strExtents[] = $objExtent->Extent . ' ' . ($objExtent->ExtentUnit ? $objExtent->ExtentUnit->getString('ExtentUnit') : '');
      }
      $infoSection->insertRow('extentinformation')->insertInformation('ExtentInformation', $strExtents);
   }


   $_ARCHON->AdministrativeInterface->outputInterface();
}

function locations_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchLocations', array('repositoryid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function locations_ui_exec()
{
   global $_ARCHON;


   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['relatedrepositoryid'])
   {
      $_REQUEST['relatedrepositoryids'] = array($_REQUEST['relatedrepositoryid']);
   }

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objLocation = New Location($_REQUEST);
         $objLocation->ID = $ID;
         $objLocation->dbStore();
         $ID = $objLocation->ID;
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objLocation = New Location($ID);
         $objLocation->dbDelete();
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
      $msg = "Location Database Updated Successfully.";
   }

   if($location)
   {
      $_ARCHON->sendMessageAndRedirect($msg, $location);
   }
   else
   {
      $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
   }
}