<?php
/**
 * Phrase Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

phrases_ui_initialize();

// Determine what to do based upon user input
function phrases_ui_initialize()
{
   global $_ARCHON;
   
   if(!$_REQUEST['f'])
   {
      phrases_ui_main();
   }
   elseif($_REQUEST['f'] == 'search')
   {
      phrases_ui_search();
   }
   elseif($_REQUEST['f'] == 'predict')
   {
      $objPhrase = Phrase::getPhrase($_REQUEST['phrasename'], $_REQUEST['packageid'], $_REQUEST['moduleid'], $_REQUEST['phrasetypeid'], $_REQUEST['languageid']);
      if($objPhrase)
      {
         $_REQUEST['id'] = $objPhrase->ID;
      }
      $_REQUEST['selectedtab'] = 1;

      if(!$_REQUEST['languageid'])
      {
         $_REQUEST['languageid'] = $_ARCHON->Security->Session->getLanguageID();
      }
      phrases_ui_main();
   }
   else
   {
      phrases_ui_exec();
   }
}




// phrases_ui_main()
//   - purpose: Creates the primary user interface
//              for the Phrases Manager.
function phrases_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Phrase');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
   $generalSection->insertRow('packageid')->insertSelect('PackageID', 'getAllPackages');
   $generalSection->insertRow('moduleid')->insertSelect('ModuleID', 'getAllModules');
   $generalSection->insertRow('phrasename')->insertTextField('PhraseName', 30, 100);
   $generalSection->insertRow('phrasetypeid')->insertSelect('PhraseTypeID', 'getAllPhraseTypes');
   $generalSection->insertRow('languageid')->insertSelect('LanguageID', 'getAllLanguages');
   $generalSection->insertRow('phrasevalue')->insertTextArea('PhraseValue');
   $generalSection->insertRow('regularexpression')->insertTextField('RegularExpression', 30, 1000);


   $_ARCHON->AdministrativeInterface->setNameField('PhraseName');

   $_ARCHON->AdministrativeInterface->insertSearchOption('PackageID', 'getAllPackages', 'packageid');
   $_ARCHON->AdministrativeInterface->insertSearchOption('ModuleID', 'getAllModules', 'moduleid');
   $_ARCHON->AdministrativeInterface->insertSearchOption('PhraseTypeID', 'getAllPhraseTypes', 'phrasetypeid');
   $_ARCHON->AdministrativeInterface->insertSearchOption('LanguageID', 'getAllLanguages', 'languageid', NULL, NULL, CONFIG_CORE_DEFAULT_LANGUAGE);
   $_ARCHON->AdministrativeInterface->setCarryOverFields(array('PackageID', 'ModuleID', 'LanguageID', 'PhraseTypeID'));

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function phrases_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchPhrases', array('packageid' => 0, 'moduleid' => 0, 'phrasetypeid' => 0, 'languageid' => CONFIG_CORE_DEFAULT_LANGUAGE, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function phrases_ui_exec()
{
   global $_ARCHON;

   // @set_time_limit(0);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objPhrase = New Phrase($_REQUEST);
         $objPhrase->ID = $ID;
         $objPhrase->dbStore();
         $ID = $objPhrase->ID;
      }
   }
   else if($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objPhrase = New Phrase($ID);
         $objPhrase->dbDelete();
      }
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
   }

   if($_ARCHON->Error)
   {
      $msg = "$_ARCHON->Error";
   }
   else
   {
      $msg = "Phrase Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
?>