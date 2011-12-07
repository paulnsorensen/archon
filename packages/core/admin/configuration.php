<?php
/**
 * Configuration Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel, converted to 3.x by Chris Prom, 2/5/2009
 */

isset($_ARCHON) or die();

// Determine what to do based upon user input
if(!$_REQUEST['f'])
{
   configuration_ui_main();
}
elseif($_REQUEST['f'] == "search")
{
   configuration_ui_search();
}
else
{
   configuration_ui_exec();
}

// configuration_ui_main()
//   - purpose: Creates the primary user interface
//              for the configuration Manager.
function configuration_ui_main()
{
   global $_ARCHON, $perms;

   $_ARCHON->AdministrativeInterface->setClass('Configuration', true, false, false);


   $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');
   $DirectivePhraseName = 'configuration_' . encoding_strtolower(str_replace(' ', '', $_ARCHON->AdministrativeInterface->Object->Directive));
   $objDescriptionValuePhrase = Phrase::getPhrase($DirectivePhraseName, $_ARCHON->AdministrativeInterface->Object->PackageID, MODULE_NONE, $DescriptionPhraseTypeID);
   $strDescriptionValue = $objDescriptionValuePhrase ? $objDescriptionValuePhrase->getPhraseValue(ENCODE_HTML) : NULL;



   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('package')->insertInformation("Package", NULL, false);
   $generalSection->insertRow('module')->insertInformation("Module", NULL, false);
   $generalSection->insertRow('directive')->insertInformation("Directive", NULL, false);

   if($_ARCHON->AdministrativeInterface->Object->InputType != 'password')
   {
      $generalSection->insertRow('encrypted')->insertRadioButtons("Encrypted");
   }

   $generalSection->insertRow('description')->insertHTML("$strDescriptionValue");

   if(!$_ARCHON->AdministrativeInterface->Object->ReadOnly && ($_ARCHON->AdministrativeInterface->Object->InputType == 'textfield' || $_ARCHON->AdministrativeInterface->Object->InputType == 'textarea'))
   {
      $generalSection->insertRow('pattern')->insertInformation("Pattern");
   }

   if($_ARCHON->AdministrativeInterface->Object->ReadOnly)
   {
      $generalSection->insertRow('value')->insertInformation("Value");
   }
   elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'radio')
   {
      $generalSection->insertRow('value')->insertRadioButtons("Value");
   }
   elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'textarea')
   {
      $generalSection->insertRow('value')->insertTextArea("Value");
   }
   elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'textfield')
   {
      $generalSection->insertRow('value')->insertTextField("Value", 75);
   }
   elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'timestamp')
   {
      $generalSection->insertRow('value')->insertTimestampField("Value");
   }
   elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'select')     //Template set needs to be fixed
   {
      $generalSection->insertRow('value')->insertSelect('Value', $_ARCHON->AdministrativeInterface->Object->ListDataSource);
   }
   elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'password')
   {
      $generalSection->insertRow('value')->insertPasswordField("Value");
      $generalSection->insertRow('confirmpassword')->insertPasswordField('ConfirmPassword');
   }

   $_ARCHON->AdministrativeInterface->insertSearchOption('PackageID', 'getAllPackages', 'packageid');
   $_ARCHON->AdministrativeInterface->insertSearchOption('ModuleID', 'getAllModules', 'moduleid');

   $_ARCHON->AdministrativeInterface->outputInterface();
}


// configuration_ui_search()
//   - purpose: Searches the configuration setting names
//              frame for the main configuration UI.
function configuration_ui_search()
{
   global $_ARCHON;

   echo($_ARCHON->AdministrativeInterface->searchResults('searchConfiguration', array('packageid' => 0, 'moduleid' => 0, 'excludenoaccessconfiguration' => true, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0)));

}

// configuration_ui_search()
//   - purpose: executes form submissions for the
//      configuration database

function configuration_ui_exec() // need to test if pswd and confirm pswd are same

{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['confirmpassword'] && $_REQUEST['value'] != $_REQUEST['confirmpassword'])
   {
      $_ARCHON->declareError("Could not update Configuration: Passwords do not match.");  
   }

   elseif($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objConfiguration = New Configuration($_REQUEST);
         $objConfiguration->ID = $ID;
         $objConfiguration->dbStore();
         $ID = $objConfiguration->ID;
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
      $msg = "Configuration Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
