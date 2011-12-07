<?php
/**
 * Phrases Importer Script
 *
 * Note: This script is still experimental
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel, Kyle Fox
 */

global $_ARCHON;

isset($_ARCHON) or die();

$UtilityCode = 'phrasexml';

$_ARCHON->addDatabaseImportUtility(PACKAGE_CORE, $UtilityCode, '3.21', array('xml'), true);

if($_REQUEST['f'] == 'import-' . $UtilityCode)
{
   if(!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
   {
      die("Permission Denied.");
   }

   @set_time_limit(0);

   $StartTime = microtime(true);

   $arrFiles = $_ARCHON->getAllIncomingFiles();

   $arrAPRCodes = $_REQUEST['aprcodes'] ? $_REQUEST['aprcodes'] : array();
   if($_REQUEST['aprcode'])
   {
      $arrAPRCodes[] = $_REQUEST['aprcode'];
   }
   $arrAPRCodes = array_unique($arrAPRCodes);

   $arrLanguageIDs = $_REQUEST['languageids'] ? $_REQUEST['languageids'] : array();
   foreach($arrLanguageIDs as $languageID)
   {
      $objLanguage = New Language($languageID);
      $objLanguage->dbLoad();
      $strRequest = 'language_'.$objLanguage->LanguageShort;
      $_REQUEST[$strRequest]=true;
   }
   if($_REQUEST['languageid'])
   {
      $objLanguage = New Language($_REQUEST['languageid']);
      $objLanguage->dbLoad();
      $strRequest = 'language_'.$objLanguage->LanguageShort;
      $_REQUEST[$strRequest]=true;
   }


   // Grab phrase xml files from within packages
   foreach($arrAPRCodes as $APRCode)
   {
      $APRCode = preg_replace('/[\\/\\\\]/u', '', $APRCode);

      if(file_exists("packages/{$APRCode}/install/phrasexml/"))
      {
         if($handle = opendir("packages/{$APRCode}/install/phrasexml/"))
         {
            while(false !== ($file = readdir($handle)))
            {
               if(preg_match("/([\\w]+)-$APRCode\\.xml/ui", $file, $arrMatch) && $_REQUEST["language_{$arrMatch[1]}"])
               {
                  $arrFiles = array_merge($arrFiles, file_get_contents_array("packages/{$APRCode}/install/phrasexml/$file"));
               }
            }
         }
      }
   }

   if($_REQUEST['allpackages'])
   {
      $arrPhrasePackages = $_ARCHON->getAllPackages(false);

      foreach($arrPhrasePackages as $ID => $objPackage)
      {
         $APRCode = $objPackage->APRCode;
         if(!in_array($APRCode, $arrAPRCodes) && file_exists("packages/{$APRCode}/install/phrasexml/"))
         {
            if($handle = opendir("packages/{$APRCode}/install/phrasexml/"))
            {
               while(false !== ($file = readdir($handle)))
               {
                  if(preg_match("/([\\w]+)-$APRCode\\.xml/ui", $file, $arrMatch) && $_REQUEST["language_{$arrMatch[1]}"])
                  {
                     $arrFiles = array_merge($arrFiles, file_get_contents_array("packages/{$APRCode}/install/phrasexml/$file"));
                  }
               }
            }
         }
      }
   }

   ob_implicit_flush();

   if(!empty($arrFiles))
   {
      $arrModules = $_ARCHON->getAllModules(false);
      foreach($arrModules as $ID => $objModule)
      {
         $arrModules[$objModule->Script] =& $arrModules[$ID];
      }

      foreach($arrFiles as $Filename => $strXML)
      {
         echo("<br /><br />\n");

         echo("Parsing file $Filename...<br />\n");

         $objXML = simplexml_load_string($strXML);

         echo($Filename . " loaded and parsed.<br /><br />\n");

         if(!$objXML)
         {
            echo("The file is not a valid Phrases XML file.<br /><br />\n");
            continue;
         }

         $LanguageShort = $objXML['code'];

//         if(!isset($languagePrep))
//         {
//            $query = "SELECT ID FROM tblCore_Languages WHERE LanguageShort = ?";
//            $languagePrep = $_ARCHON->mdb2->prepare($query, 'text', MDB2_PREPARE_RESULT);
//         }
//         $result = $languagePrep->execute($LanguageShort);
//         if (PEAR::isError($result))
//         {
//            trigger_error($result->getMessage(), E_USER_ERROR);
//         }
//
//         if($result->numRows() != 1)
//         {
//            echo("Found {$result->numRows()} matches for language.");
//            continue;
//         }
//         $row = $result->fetchRow();
//         $result->free();
//
//         $LanguageID = $row['ID'];

         $LanguageID = $_ARCHON->getLanguageIDFromString($LanguageShort);

         $arrPackageElements = count($objXML->package) > 1 ? $objXML->package : array($objXML->package);
         foreach($arrPackageElements as $packagelement)
         {
            $APRCode = (string) $packagelement['aprcode'];
            echo("<br /><br />Importing package $APRCode...<br />\n");

            $arrPhrasePackages = $_ARCHON->getAllPackages(false);
            foreach($arrPhrasePackages as $obj)
            {
               if($obj->APRCode == $APRCode)
               {
                  $objPhrasePackage = $obj;
               }
            }

            if(!$objPhrasePackage)
            {
               echo("Package $APRCode not installed!<br />\n");
               continue;
            }
            if($objPhrasePackage->DBVersion != $packagelement['version'])
            {
               echo("Warning: Phrases are for a different version of the package!<br />\n");
            }
            $PackageID = $objPhrasePackage->ID;

            ob_flush();
            flush();

            $arrModuleElements = count($packagelement->module) > 1 ? $packagelement->module : array($packagelement->module);
            $startModule = ($_REQUEST['startmodule']) ? true : false;
            foreach($arrModuleElements as $moduleelement)
            {
               $Script = (string) $moduleelement['script'];
               $ModuleID = $Script ? $arrModules[$Script]->ID : MODULE_NONE;
               if(!isset($ModuleID) || ($ModuleID && $arrModules[$ModuleID]->PackageID != $PackageID))
               {
                  echo("Invalid module script: {$Script}.<br />\n");
                  continue;
               }
               else
               {
                  if($startModule)
                  {
                     if($_REQUEST['startmodule'] != $Script)
                     {
                        continue;
                     }
                     else
                     {
                        $startModule = false;
                     }
                  }

                  echo("Starting import for module script: {$Script}...");
               }

               $arrPhraseTypeElements = count($moduleelement->phrasetype) > 1 ? $moduleelement->phrasetype : array($moduleelement->phrasetype);
               foreach($arrPhraseTypeElements as $phrasetypeelement)
               {
                  $PhraseTypeName = (string) $phrasetypeelement['name'];
                  $PhraseTypeID = $_ARCHON->getPhraseTypeIDFromString($PhraseTypeName);
                  if(!$PhraseTypeID)
                  {
                     echo("Invalid phrase type name: $PhraseTypeName.<br />\n");
                     continue;
                  }

                  $arrPhrase = count($phrasetypeelement->phrase) > 1 ? $phrasetypeelement->phrase : array($phrasetypeelement->phrase);
                  foreach($arrPhrase as $phraseelement)
                  {
                     $PhraseName = (string) $phraseelement['name'];
                     if(!$PhraseName)
                     {
                        echo("Name missing from phrase.<br />\n");
                        continue;
                     }

                     if(!isset($checkPrep))
                     {
                        $query = "SELECT ID FROM tblCore_Phrases WHERE LanguageID = ? AND PackageID = ? AND ModuleID = ? AND PhraseTypeID = ? AND PhraseName = ?";
                        $_ARCHON->mdb2->setLimit(1);
                        $checkPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer', 'integer', 'text'), MDB2_PREPARE_RESULT);
                     }
                     $result = $checkPrep->execute(array($LanguageID, $PackageID, $ModuleID, $PhraseTypeID, $PhraseName));
                     if (PEAR::isError($result))
                     {
                        trigger_error($result->getMessage(), E_USER_ERROR);
                     }

                     if($result->numRows())
                     {
                        $row = $result->fetchRow();
                        $result->free();

                        $objPhrase = new Phrase($row);
                        if($objPhrase)
                        {
                           $objPhrase->dbDelete();
                        }
                     }

                     $objPhrase = new Phrase();
                     $objPhrase->LanguageID = $LanguageID;
                     $objPhrase->PackageID = $PackageID;
                     $objPhrase->ModuleID = $ModuleID;
                     $objPhrase->PhraseName = $PhraseName;
                     $objPhrase->PhraseTypeID = $PhraseTypeID;
                     $objPhrase->RegularExpression = (string) $phraseelement['regularexpression'];

                     $PhraseValue = trim((string) $phraseelement);
                     $PhraseValue = str_replace("\n\t\t\t\t\t", "\n", $PhraseValue);

                     if($_ARCHON->db->ServerType == 'MSSQL')
                     {
                        $PhraseValue = encoding_convert_encoding($PhraseValue, 'ISO-8859-1');
                     }

                     $objPhrase->PhraseValue = $PhraseValue;

                     $objPhrase->dbStore();

                     if(!$objPhrase->ID)
                     {
                        echo("Error with phrase $PhraseName: " . $_ARCHON->clearError() . "<br />\n");
                     }
                  }
               }

               echo(" DONE<br />");
            }
         }

         echo("Import of " . $row['LanguageLong'] . " phrases successful.<br /><br />\n");
      }
   }

   echo("<strong>All files imported!</strong>");
}
