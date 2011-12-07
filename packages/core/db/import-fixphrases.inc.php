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

$UtilityCode = 'fixphrases';

$_ARCHON->addDatabaseImportUtility(PACKAGE_CORE, $UtilityCode, '3.21', array('xml'), true);

if($_REQUEST['f'] == 'import-' . $UtilityCode)
{
   if(!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
   {
      die("Permission Denied.");
   }
   if(!$_ARCHON->Security->verifyPermissions(MODULE_PHRASES, UPDATE))
   {
      die("Permission Denied.");
   }


   @set_time_limit(0);

   $StartTime = microtime(true);


   $PackageID = $_REQUEST['packageid'] ? $_REQUEST['packageid'] : ($_REQUEST['aprcode'] ? $_ARCHON->Packages[encoding_strtolower($_REQUEST['aprcode'])]->ID : 0);
   $ModuleID = $_REQUEST['moduleid'] ? $_REQUEST['moduleid'] : ($_REQUEST['module'] ? $_ARCHON->Modules[encoding_strtolower($_REQUEST['module'])]->ID : 0);
   $PhraseTypeID = $_REQUEST['phrasetypeid'] ? $_REQUEST['phrasetypeid'] : ($_REQUEST['phrasetype'] ? $_ARCHON->getPhraseTypeIDFromString(encoding_strtolower($_REQUEST['phrasetype']))->ID : 0);
   $LanguageID = $_REQUEST['languageid'] ? $_REQUEST['languageid'] : 0;
   if(!$LanguageID)
   {
      $LanguageID = $_ARCHON->getLanguageIDFromString($_REQUEST['language']);
   }

   if(!$LanguageID)
   {
      die("Language not found.");
   }
//   if(!$PackageID)
//   {
//      die("Package not found.");
//   }

//$objLanguage = New Language($LanguageID);
//$objLanguage->dbLoad();
//
//$arrPackages = $_ARCHON->getAllPackages();
//$arrModules = $_ARCHON->getAllModules();
//
//$arrPhraseTypes = $_ARCHON->getAllPhraseTypes();

   $arrPhrases = $_ARCHON->searchPhrases('', $PackageID, $ModuleID, $PhraseTypeID, $LanguageID, INF);

   if(empty($arrPhrases))
   {
      die("No phrases found for language " . $objLanguage->LanguageLong);
   }

   ob_implicit_flush();


   foreach($arrPhrases as $objPhrase)
   {
      $count = array();
      $String = $objPhrase->PhraseValue;

      $String = preg_replace('/\&amp;/u','&',$String, -1, $count[]);
      $String = preg_replace('/\&quot;/u','"',$String, -1, $count[]);
      $String = preg_replace('/\&#0?39;/u',"'",$String, -1, $count[]);
      $String = preg_replace('/\&lt;/u','<',$String, -1, $count[]);
      $String = preg_replace('/\&gt;/u','>',$String, -1, $count[]);
      $String = preg_replace('/\&nbsp;/u',' ',$String, -1, $count[]);
      $String = preg_replace('/<(?:br|br[ ]?\/)>[\s]*<(?:br|br[ ]?\/)>/u','\n',$String, -1, $count[]);
      $String = preg_replace('/<(?:br|br[ ]?\/)>/u','',$String, -1, $count[]);
      $String = preg_replace('/<p>/u','',$String, -1, $count[]);
      $String = preg_replace('/<\/p>/u','\n',$String, -1, $count[]);
      $tmpStr = trim($String);
      if(strcmp($tmpStr, $String) != 0)
      {
         $count[] = 1;
      }
      $String = $tmpStr;
      $String = preg_replace('/<(?:b|strong)>(.+?)<\/(?:b|strong)>/u','[b]$1[/b]',$String, -1, $count[]);
      $String = preg_replace('/<(?:i|em)>(.+?)<\/(?:i|em)>/u','[i]$1[/i]',$String, -1, $count[]);
      $String = preg_replace('/<(?:i|em)>(.+?)<\/(?:i|em)>/u','[i]$1[/i]',$String, -1, $count[]);
      $String = preg_replace('/<u>(.+?)<\/u>/u','[u]$1[/u]',$String, -1, $count[]);
      $String = preg_replace('/<sup>(.+?)<\/sup>/u','[sup]$1[/sup]',$String, -1, $count[]);
      $String = preg_replace('/<sub>(.+?)<\/sub>/u','[sub]$1[/sub]',$String, -1, $count[]);
      $String = preg_replace('/<a .*?href=(["\'])(.+?)\1.*?>[\s]*(.+?)[\s]*<\/a>/u','[url=$2]$3[/url]',$String, -1, $count[]);

      if(array_sum($count))
      {
         if($_ARCHON->db->ServerType == 'MSSQL')
         {
            $String = encoding_convert_encoding($String, 'ISO-8859-1');
         }
//                     echo("Edited phrase: ".$objPhrase->PhraseName."  ".$objPhrase->PackageID."  ".$objPhrase->ModuleID."<br />\n");
//
//                     echo($objPhrase->PhraseValue."<br /><br />");

         $objPhrase->PhraseValue = $String;
         if(!$objPhrase->dbStore())
         {
            echo("Error with phrase: " . $_ARCHON->clearError() . "<br />\n");
         }
         else
         {
            echo("Edited phrase: ".$objPhrase->PhraseName."  ".$objPhrase->PackageID."  ".$objPhrase->ModuleID."<br />\n");
            echo($objPhrase->PhraseValue."<br /><br />");
         }
      }
      unset($String);
      unset($objPhrase); //attempt to save memory, though it may be a losing battle;
   }













   echo(" DONE<br />");


}
