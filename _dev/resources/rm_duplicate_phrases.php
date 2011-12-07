<?php

$_ARCHON->db = NULL;
require_once('../../config.inc.php');
include_once('MDB2.php');

//if(!$_REQUEST['filepath'] || !file_exists($_REQUEST['filepath']))
//{
//   die('filepath invalid');
//}

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

//$_ARCHON->QueryLog = New QueryLog();

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

//$arrPackages = $_ARCHON->getAllPackages();
//$arrModules = $_ARCHON->getAllModules();
$arrLanguages = array(2081,2372); //english, spanish
$arrPhraseTypeIDs = array(3,5,6); //description, admin, public


//$query = "SELECT COUNT(DISTINCT PhraseName) AS cdp, COUNT(PhraseName) AS cp, PhraseName, PackageID, ModuleID FROM tblCore_Phrases WHERE PhraseTypeID = ? AND LanguageID = ? GROUP BY PackageID,ModuleID,PhraseName";
//$prep = $mdb2->prepare($query, array('integer','integer'), MDB2_PREPARE_RESULT);

$duplicatePrep = NULL;
$deletePrep = NULL;

foreach($arrLanguages as $lid)
{
   foreach($arrPhraseTypeIDs as $ptid)
   {
//      $result = $prep->execute($ptid, $lid);
     $query = "SELECT PhraseName, PackageID, ModuleID
FROM tblCore_Phrases
WHERE PhraseTypeID = {$ptid}
AND LanguageID = {$lid}
GROUP BY PackageID, ModuleID, PhraseName
HAVING COUNT( DISTINCT PhraseName ) < COUNT( PhraseName )";
      $result = $mdb2->query($query);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
//         if($row['cdp'] != $row['cp'])
//         {
//            if(!isset($duplicatePrep))
//            {
//               $query = "SELECT ID FROM tblCore_Phrases WHERE PackageID = ? AND ModuleID = ? AND LanguageID = ? AND PhraseTypeID = ? AND PhraseName = ?";
//               $duplicatePrep = $mdb2->prepare($query, array('integer','integer', 'integer', 'integer', 'text'), MDB2_PREPARE_RESULT);
//            }

//            $duplicateResult = $duplicatePrep->execute($row['PackageID'], $row['ModuleID'], $lid, $ptid, $row['PhraseName']);
                        $query = "SELECT ID FROM tblCore_Phrases WHERE PackageID = {$row['PackageID']} AND ModuleID = {$row['ModuleID']} AND LanguageID = {$lid} AND PhraseTypeID = {$ptid} AND PhraseName = '{$row['PhraseName']}'";

                $duplicateResult = $mdb2->query($query);
            if (PEAR::isError($duplicateResult))
            {
               trigger_error($duplicateResult->getMessage(), E_USER_ERROR);
            }
            $count = 0;
            while($dupRow = $duplicateResult->fetchRow())
            {
               if($count > 0)
               {
                  if(!isset($deletePrep))
                  {
                     $query = "DELETE FROM tblCore_Phrases WHERE ID = ?";
                     $deletePrep = $mdb2->prepare($query, array('integer'), MDB2_PREPARE_MANIP);
                  }
                  $affected = $deletePrep->execute($dupRow['ID']);
                  if (PEAR::isError($affected))
                  {
                     trigger_error($affected->getMessage(), E_USER_ERROR);
                  }
                  else
                  {
                  echo("<br /> Deleted Phrase ID:".$dupRow['ID']." - PhraseName:".$row['PhraseName']." - {lid,ptid,pid,mid}:{".$lid." ,".$ptid." ,".$row['PackageID']." ,".$row['ModuleID']."}");
                  }
               }
               $count++;
            }
            $duplicateResult->free();
         }
      }
      $result->free();
//   }
}

echo("<br /><br /> Duplicate Phrases Deleted!");

?>
