<?php

/**
 * Repository Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

repositories_ui_initialize();

// Determine what to do based upon user input
function repositories_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      repositories_ui_main();
   }
   elseif($_REQUEST['f'] == 'search')
   {
      repositories_ui_search();
   }
   elseif($_REQUEST['f'] == 'list')
   {
      repositories_ui_list();
   }
   else
   {
      repositories_ui_exec();
   }
}

// repositories_ui_main()
//   - purpose: Creates the primary user interface
//              for the Repository Manager.
function repositories_ui_main()
{
   global $_ARCHON;

   $objExtPhrase = Phrase::getPhrase('ext', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strExt = $objExtPhrase ? $objExtPhrase->getPhraseValue(ENCODE_HTML) : 'Ext';

   $_ARCHON->AdministrativeInterface->setClass('Repository');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('name')->insertTextField('Name', 50, 100)->required();
   $generalSection->insertRow('administrator')->insertTextField('Administrator', 50, 50);
   $generalSection->insertRow('code')->insertTextField('Code', 6, 10);
   $generalSection->insertRow('country')->insertSelect('CountryID', 'getAllCountries')->required();

   $generalSection->insertRow('address');
   $generalSection->getRow('address')->insertTextField('Address', 40, 100);
   $generalSection->getRow('address')->insertNewLine();
   $generalSection->getRow('address')->insertTextField('Address2', 40, 100);

   $generalSection->insertRow('city');
   $generalSection->getRow('city')->insertTextField('City', 15, 75);
   $generalSection->getRow('city')->insertHTML(', ');
   $generalSection->getRow('city')->insertTextField('State', 2, 2);
   $generalSection->getRow('city')->insertHTML(', ');
   $generalSection->getRow('city')->insertTextField('ZIPCode', 5, 5);
   $generalSection->getRow('city')->insertHTML('-');
   $generalSection->getRow('city')->insertTextField('ZIPPlusFour', 4, 4);

   $generalSection->insertRow('phone');
   $generalSection->getRow('phone')->insertTextField('Phone', 15, 25);
   $generalSection->getRow('phone')->insertHTML($strExt . ':');
   $generalSection->getRow('phone')->insertTextField('PhoneExtension', 5, 10);

   $generalSection->insertRow('fax')->insertTextField('Fax', 15, 25);

   $generalSection->insertRow('email')->insertTextField('Email', 20, 50);

   $generalSection->insertRow('url')->insertTextField('URL', 50, 255);

   $generalSection->insertRow('emailsignature')->insertTextArea('EmailSignature');

   if(defined('PACKAGE_COLLECTIONS'))
   {
      $arrTemplates = $_ARCHON->getPackageTemplates('collections');
      $special_templates = array('ead', 'print', 'kardexcontrolcard');
      foreach($special_templates as $template)
      {
         $sp_key = array_search($template, $arrTemplates);
         if($sp_key)
         {
            unset($arrTemplates[$sp_key]);
         }
      }

      $generalSection->insertRow('templateset')->insertSelect('TemplateSet', $arrTemplates, 'systemdefault');
   }

   //need to check for packages
   $arrResearchOpts = array();

   $objResearchNonePhrase = Phrase::getPhrase('research_none', PACKAGE_CORE, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strResearchNone = $objResearchNonePhrase ? $objResearchNonePhrase->getPhraseValue(ENCODE_HTML) : 'Disable Research Features';

   $arrResearchOpts[RESEARCH_NONE] = $strResearchNone;
   if(defined('PACKAGE_COLLECTIONS'))
   {
      $objResearchCollectionsPhrase = Phrase::getPhrase('research_collections_only', PACKAGE_CORE, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strResearchCollections = $objResearchCollectionsPhrase ? $objResearchCollectionsPhrase->getPhraseValue(ENCODE_HTML) : 'Enable Collections Research Only';

      $arrResearchOpts[RESEARCH_COLLECTIONS] = $strResearchCollections;
   }
   if(defined('PACKAGE_DIGITALLIBRARY'))
   {
      $objResearchDigitalLibPhrase = Phrase::getPhrase('research_digitallib_only', PACKAGE_CORE, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strResearchDigitalLib = $objResearchDigitalLibPhrase ? $objResearchDigitalLibPhrase->getPhraseValue(ENCODE_HTML) : 'Enable Digital Library Research Only';

      $arrResearchOpts[RESEARCH_DIGITALLIB] = $strResearchDigitalLib;
   }
   $objResearchAllPhrase = Phrase::getPhrase('research_all', PACKAGE_CORE, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strResearchAll = $objResearchAllPhrase ? $objResearchAllPhrase->getPhraseValue(ENCODE_HTML) : 'Enable All Research Features';

   $arrResearchOpts[RESEARCH_ALL] = $strResearchAll;

   $generalSection->insertRow('research_features')->insertSelect('ResearchFunctionality', $arrResearchOpts);



   $_ARCHON->AdministrativeInterface->setNameField('Name');

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

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function repositories_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchRepositories', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function repositories_ui_list()
{
   global $_ARCHON;

   $objUser = $_ARCHON->Security->Session->User;

   $strkey = ($_REQUEST['strkey']) ? $_REQUEST['strkey'] : 'text';

   if(!$objUser->RepositoryLimit)
   {
      $arrObjects = $_ARCHON->getAllRepositories();
   }
   else
   {
      $arrObjects = $objUser->Repositories;
   }

   $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

   header('Content-type: application/json; charset=UTF-8');

   $arrResults = array();
   foreach($arrObjects as $ID => $obj)
   {
      $arrResults[] = '{"id":"' . $ID . '","' . $strkey . '":' . json_encode(caplength(call_user_func_array(array($obj, 'toString'), array()), CONFIG_CORE_RELATED_OPTION_MAX_LENGTH)) . '}';
   }

   if($callback)
   {
      echo($callback . "(");
   }
   echo("{\"results\":[" . implode(",", $arrResults) . "]}");
   if($callback)
   {
      echo(");");
   }

   die();
}

function repositories_ui_exec()
{
   global $_ARCHON;

   $objRepository = New Repository($_REQUEST);

   // @set_time_limit(0);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objRepository = New Repository($_REQUEST);
         $objRepository->ID = $ID;
         $objRepository->dbStore();
         $ID = $objRepository->ID;

         if(is_array($_REQUEST['relateduserids']))
         {
            $objRepository->dbUpdateRelatedUsers($_REQUEST['relateduserids']);
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objRepository = New Repository($ID);
         $objRepository->dbDelete();
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
      $msg = "Repository Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}