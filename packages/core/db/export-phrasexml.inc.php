<?php
/**
 * Exports language table as xml files
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel, Kyle Fox
 */

isset($_ARCHON) or die();

$UtilityCode = 'phrasexml';

$_ARCHON->addDatabaseExportUtility(PACKAGE_CORE, $UtilityCode, '3.21');

if($_REQUEST['f'] == 'export-' . $UtilityCode)
{
   
   if(!$_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, READ))
   {
      die("Permission Denied.");
   }

   $PackageID = $_REQUEST['packageid'] ? $_REQUEST['packageid'] : ($_REQUEST['aprcode'] ? $_ARCHON->Packages[encoding_strtolower($_REQUEST['aprcode'])]->ID : 0);
   $ModuleID = $_REQUEST['moduleid'] ? $_REQUEST['moduleid'] : ($_REQUEST['module'] ? $_ARCHON->Modules[encoding_strtolower($_REQUEST['module'])]->ID : 0);
   $PhraseTypeID = $_REQUEST['phrasetypeid'] ? $_REQUEST['phrasetypeid'] : ($_REQUEST['phrasetype'] ? $_ARCHON->getPhraseTypeIDFromString(encoding_strtolower($_REQUEST['phrasetype']))->ID : 0);
   $LanguageID = $_REQUEST['languageid'] ? $_REQUEST['languageid'] : 0;
   if(!$LanguageID)
   {
      $LanguageID = $_ARCHON->getLanguageIDFromString($_REQUEST['language']);
   }
   $NotLanguageID = $_REQUEST['notlanguageid'] ? $_REQUEST['notlanguageid'] : 0;
   if(!$NotLanguageID)
   {
      $NotLanguageID = $_ARCHON->getLanguageIDFromString($_REQUEST['notlanguage']);
   }

   if(!$LanguageID)
   {
      die("Language not found.");
   }
   if(!$PackageID)
   {
      die("Package not found.");
   }

   $objLanguage = New Language($LanguageID);
   $objLanguage->dbLoad();

   $arrPackages = $_ARCHON->getAllPackages();
   $arrModules = $_ARCHON->getAllModules();

   $arrPhraseTypes = $_ARCHON->getAllPhraseTypes();

   $arrPhrases = $_ARCHON->searchPhrases('', $PackageID, $ModuleID, $PhraseTypeID, $LanguageID, INF);

// To aid in prepping for another translation.
   if($NotLanguageID)
   {
      foreach($arrPhrases as $ID => $objPhrase)
      {
         $query = "SELECT ID FROM tblCore_Phrases WHERE PhraseName = '" . $_ARCHON->mdb2->escape($objPhrase->PhraseName) . "' AND PackageID = '" . $_ARCHON->mdb2->escape($objPhrase->PackageID) . "' AND ModuleID = '" . $_ARCHON->mdb2->escape($objPhrase->ModuleID) . "' AND PhraseTypeID = '" . $_ARCHON->mdb2->escape($objPhrase->PhraseTypeID) . "' AND LanguageID = '" . $_ARCHON->mdb2->escape($NotLanguageID) . "'";
         $result = $_ARCHON->mdb2->query($query);
         $row = $result->fetchRow();
         $result->free();

         if($row['ID'])
         {
            unset($arrPhrases[$ID]);
            //echo("$objPhrase->PhraseName found in other language.\n");
         }
         else
         {
            //echo("$query\n\n");
         }
      }
   }

   if(empty($arrPhrases))
   {
      die("No phrases found for language " . $objLanguage->LanguageLong);
   }

   $filename = encoding_strtolower($objLanguage->LanguageShort) . '-' . encoding_strtolower($_ARCHON->Packages[$PackageID]->APRCode);

   header('Content-type: text/xml; charset=UTF-8');
   header('Content-Disposition: attachment; filename="' . $filename . '.xml"');

   echo('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
   $strLanguageShort = encode($objLanguage->LanguageShort, ENCODE_HTML);
   echo("<language code='$strLanguageShort'>\n");

   foreach($arrPhrases as $objPhrase)
   {
      if(!$arrPackages[$objPhrase->PackageID]->_displayed)
      {
         if($packagesstarted)
         {
            echo("\t\t\t</phrasetype>\n");
            echo("\t\t</module>\n");
            echo("\t</package>\n");
         }
         else
         {
            $packagesstarted = 1;
         }

         $strAPRCode = encode($arrPackages[$objPhrase->PackageID]->APRCode, ENCODE_HTML);
         $strVersion = encode($arrPackages[$objPhrase->PackageID]->DBVersion, ENCODE_HTML);
         echo("\t<package aprcode='$strAPRCode' version='$strVersion'>\n");
         $arrPackages[$objPhrase->PackageID]->_displayed = true;

         $arrPhraseTypeDisplayed = array();
         $phrasetypesstarted = 0;

         $arrModuleDisplayed = array();
         $modulesstarted = 0;
      }

      if(!$arrModuleDisplayed[$objPhrase->ModuleID])
      {
         if($modulesstarted)
         {
            echo("\t\t\t</phrasetype>\n");
            echo("\t\t</module>\n");
         }
         else
         {
            $modulesstarted = 1;
         }

         $strModuleScript = encode($arrModules[$objPhrase->ModuleID]->Script, ENCODE_HTML);
         echo("\t\t<module script='$strModuleScript'>\n");
         $arrModuleDisplayed[$objPhrase->ModuleID] = true;

         $phrasetypesstarted = 0;
         $arrPhraseTypeDisplayed = array();
      }

      if(!$arrPhraseTypeDisplayed[$objPhrase->PhraseTypeID])
      {
         if($phrasetypesstarted)
         {
            echo("\t\t\t</phrasetype>\n");
         }
         else
         {
            $phrasetypesstarted = 1;
         }

         $strPhraseTypeName = encode($arrPhraseTypes[$objPhrase->PhraseTypeID]->toString(), ENCODE_HTML);
         echo("\t\t\t<phrasetype name='$strPhraseTypeName'>\n");

         $arrPhraseTypeDisplayed[$objPhrase->PhraseTypeID] = true;
      }

      $strPhraseName = encode($objPhrase->PhraseName, ENCODE_HTML);
      $strRegularExpression = encode($objPhrase->RegularExpression, ENCODE_HTML);
      echo("\t\t\t\t<phrase name='$strPhraseName' regularexpression='$strRegularExpression'>");

      echo("\n\t\t\t\t\t");
      echo(str_replace("\n", "\n\t\t\t\t\t", htmlspecialchars(trim($objPhrase->PhraseValue), ENT_NOQUOTES, 'UTF-8')));
      echo("\n\t\t\t\t</phrase>\n");
   }

   echo("\t\t\t</phrasetype>\n");
   echo("\t\t</module>\n");
   echo("\t</package>\n");
   echo("</language>");
}
?>