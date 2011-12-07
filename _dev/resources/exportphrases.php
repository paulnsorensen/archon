<?php

class Archon
{
   public function __call($method, $args)
   {
      if (isset($this->$method))
      {
         $func = $this->$method;
         $func();
      }
   }
   public function declareError($str)
   {
      echo($str."\n");
   }
}

$_ARCHON = New Archon();

chdir("../..");

$_ARCHON->RootDirectory = getcwd();

if(defined('E_DEPRECATED'))
{
   error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
}
else
{
   error_reporting(E_ALL ^ E_NOTICE);
}

$_ARCHON->db = NULL;
require_once('common.inc.php');
require_once('config.inc.php');
include_once('MDB2.php');

require_once('packages/core/lib/aobject.inc.php');
require_once('packages/core/lib/jsonobject.inc.php');
require_once('packages/core/lib/phrasetype.inc.php');
require_once('packages/core/lib/language.inc.php');


if(!$_ARCHON->db->ServerType)
{
   trigger_error("Fatal Error: The database server type is not configured in config.inc.php.", E_USER_ERROR);
}
else if(!$_ARCHON->db->ServerAddress)
{
   trigger_error("Fatal Error: The database server address is not configured in config.inc.php.", E_USER_ERROR);
}
else if(!$_ARCHON->db->Login)
{
   trigger_error("Fatal Error: The database server login is not configured in config.inc.php.", E_USER_ERROR);
}

// Connect to the database
if($_ARCHON->db->ServerType == 'MSSQL')
{
   // these are necessary to prevent freetds from truncating large fields
   putenv("TDSVER=70");

   ini_set("mssql.textsize", 2147483647);
   ini_set("mssql.textlimit", 2147483647);
}

$phpservertype = strtolower($_ARCHON->db->ServerType);
$dbdsn = $phpservertype."://".$_ARCHON->db->Login.":".$_ARCHON->db->Password."@".$_ARCHON->db->ServerAddress."/".$_ARCHON->db->DatabaseName;


$dboptions = array('debug' => 1, 'portability' => MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE);

$mdb2 =& MDB2::connect($dbdsn, $dboptions);

if (PEAR::isError($mdb2))
{
   echo("Error connecting to database!<br />\n");
   trigger_error($mdb2->getMessage(), E_USER_ERROR);
}

//$_ARCHON->mdb2 =& $mdb2;
$mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

if($_ARCHON->db->ServerType == 'MySQL' || $_ARCHON->db->ServerType == 'MySQLi')
{
   // this makes sure the encoding for the queries is expected
   $query = "SET NAMES 'utf8'";
   $affected = $mdb2->exec($query);
   if (PEAR::isError($affected))
   {
      trigger_error($affected->getMessage(), E_USER_ERROR);
   }

   // This might fail.
   $query = "SET max_allowed_packet=1073741824";
   $affected = $mdb2->exec($query);
}

$UtilityCode = 'phrasexml';

if($_REQUEST['f'] == 'export-' . $UtilityCode)
{

   $arrPackages = array();
   $result = $mdb2->query("SELECT * FROM tblCore_Packages");
   if (PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
   while($row = $result->fetchRow())
   {
      $arrPackages[$row['ID']]['ID'] = $row['ID'];
      $arrPackages[$row['ID']]['APRCode'] = $row['APRCode'];
      $arrPackages[$row['ID']]['DBVersion'] = $row['DBVersion'];
      $arrPackages[$row['ID']]['displayed'] = false;

   }

   $aprcodeMap = array();
   foreach ($arrPackages as $ID => $array)
   {
      $aprcode = encoding_strtolower($array['APRCode']);
      $aprcodeMap[$aprcode] = $ID;
   }

   $arrModules = array();
   $result = $mdb2->query("SELECT * FROM tblCore_Modules");
   if (PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
   while($row = $result->fetchRow())
   {
      $arrModules[$row['ID']]['ID'] = $row['ID'];
      $arrModules[$row['ID']]['PackageID'] = $row['PackageID'];
      $arrModules[$row['ID']]['Script'] = $row['Script'];
      $arrModules[$row['ID']]['displayed'] = false;

   }

   $moduleMap = array();
   foreach ($arrModules as $ID => $array)
   {
      $module = encoding_strtolower($array['Script']);
      $moduleMap[$module] = $ID;
   }


   $PackageID = $_REQUEST['packageid'] ? $_REQUEST['packageid'] : ($_REQUEST['aprcode'] ? $aprcodeMap[encoding_strtolower($_REQUEST['aprcode'])] : 0);
   $ModuleID = $_REQUEST['moduleid'] ? $_REQUEST['moduleid'] : ($_REQUEST['module'] ? $moduleMap[encoding_strtolower($_REQUEST['module'])] : 0);
   $PhraseTypeID = $_REQUEST['phrasetypeid'] ? $_REQUEST['phrasetypeid'] : ($_REQUEST['phrasetype'] ? PhraseType::getPhraseTypeIDFromString(encoding_strtolower($_REQUEST['phrasetype'])) : 0);
   $LanguageID = $_REQUEST['languageid'] ? $_REQUEST['languageid'] : 0;

   if(!$LanguageID)
   {
      $LanguageID = Language::getLanguageIDFromString($_REQUEST['language']);
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


   $arrPhraseTypes = PhraseType::getAllPhraseTypes();

   $arrPhrases = array();
   $result = $mdb2->query("SELECT * FROM tblCore_Phrases WHERE PackageID = $PackageID AND LanguageID = $LanguageID");
   if (PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
   while($row = $result->fetchRow())
   {
      $arrPhrases[$row['ID']]['ID'] = $row['ID'];
      $arrPhrases[$row['ID']]['PackageID'] = $row['PackageID'];
      $arrPhrases[$row['ID']]['ModuleID'] = $row['ModuleID'];
      $arrPhrases[$row['ID']]['PhraseTypeID'] = $row['PhraseTypeID'];
      $arrPhrases[$row['ID']]['PhraseName'] = $row['PhraseName'];
      $arrPhrases[$row['ID']]['PhraseValue'] = $row['PhraseValue'];
      $arrPhrases[$row['ID']]['RegularExpression'] = $row['RegularExpression'];
   }


   if(empty($arrPhrases))
   {
      die("No phrases found for language " . $objLanguage->LanguageLong);
   }

   $filename = encoding_strtolower($objLanguage->LanguageShort) . '-' . encoding_strtolower($arrPackages[$PackageID]['APRCode']);

   header('Content-type: text/xml; charset=UTF-8');
   header('Content-Disposition: attachment; filename="' . $filename . '.xml"');

   echo('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
   $strLanguageShort = encode($objLanguage->LanguageShort, ENCODE_HTML);
   echo("<language code='$strLanguageShort'>\n");

   foreach($arrPhrases as $phrase)
   {
      if(!$arrPackages[$phrase['PackageID']]['displayed'])
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

         $strAPRCode = encode($arrPackages[$phrase['PackageID']]['APRCode'], ENCODE_HTML);
         $strVersion = encode($arrPackages[$phrase['PackageID']]['DBVersion'], ENCODE_HTML);
         echo("\t<package aprcode='$strAPRCode' version='$strVersion'>\n");
         $arrPackages[$phrase['PackageID']]['displayed'] = true;

         $arrPhraseTypeDisplayed = array();
         $phrasetypesstarted = 0;

         $arrModuleDisplayed = array();
         $modulesstarted = 0;
      }

      if(!$arrModuleDisplayed[$phrase['ModuleID']])
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

         $strModuleScript = encode($arrModules[$phrase['ModuleID']]['Script'], ENCODE_HTML);
         echo("\t\t<module script='$strModuleScript'>\n");
         $arrModuleDisplayed[$phrase['ModuleID']] = true;

         $phrasetypesstarted = 0;
         $arrPhraseTypeDisplayed = array();
      }

      if(!$arrPhraseTypeDisplayed[$phrase['PhraseTypeID']])
      {
         if($phrasetypesstarted)
         {
            echo("\t\t\t</phrasetype>\n");
         }
         else
         {
            $phrasetypesstarted = 1;
         }

         $strPhraseTypeName = encode($arrPhraseTypes[$phrase['PhraseTypeID']]->toString(), ENCODE_HTML);
         echo("\t\t\t<phrasetype name='$strPhraseTypeName'>\n");

         $arrPhraseTypeDisplayed[$phrase['PhraseTypeID']] = true;
      }

      $strPhraseName = encode($phrase['PhraseName'], ENCODE_HTML);
      $strRegularExpression = encode($phrase['RegularExpression'], ENCODE_HTML);
      echo("\t\t\t\t<phrase name='$strPhraseName' regularexpression='$strRegularExpression'>");

      echo("\n\t\t\t\t\t");
      echo(str_replace("\n", "\n\t\t\t\t\t", htmlspecialchars(trim($phrase['PhraseValue']), ENT_NOQUOTES, 'UTF-8')));
      echo("\n\t\t\t\t</phrase>\n");
   }

   echo("\t\t\t</phrasetype>\n");
   echo("\t\t</module>\n");
   echo("\t</package>\n");
   echo("</language>");
}

?>
