<?php
/**
 * Initalizes the Archon system
 *
 * @package Archon
 * @author Chris Rishel
 */

set_magic_quotes_runtime(0);

if(!$_REQUEST)
{
   $_REQUEST = array();
}

if(get_magic_quotes_gpc())
{
   $_REQUEST = map_recursive('stripslashes', $_REQUEST);
}

$arrP = explode('/', $_REQUEST['p']);

if(file_exists('packages/core/install/install.php') && $arrP[0] != 'admin' && $_REQUEST['p'] != 'install' && $_REQUEST['p'] != 'upgrade')
{
   trigger_error("packages/core/install/install.php MUST be deleted before Archon will function!", E_USER_ERROR);
}

$_ARCHON->DefaultOBLevel = ob_get_level();

if($_FILES)
{   
   $_FILES = array_change_key_case_recursive($_FILES);
}

$_REQUEST = array_change_key_case_recursive($_REQUEST);

$_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "{$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}";

if(!$_ARCHON->db->ServerType)
{
   trigger_error("Fatal Error: The database server type is not configured in config.inc.php.", E_USER_ERROR);
}
elseif(!$_ARCHON->db->ServerAddress)
{
   trigger_error("Fatal Error: The database server address is not configured in config.inc.php.", E_USER_ERROR);
}
elseif(!$_ARCHON->db->Login)
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

$_ARCHON->QueryLog = New QueryLog();

$dboptions = array('debug' => 1, 'debug_handler' => array($_ARCHON->QueryLog, 'logQuery'), 'portability' => MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE);

$mdb2 =& MDB2::connect($dbdsn, $dboptions);

if (PEAR::isError($mdb2))
{
   echo("Error connecting to database!<br />\n");
   trigger_error($mdb2->getMessage(), E_USER_ERROR);
}

$_ARCHON->mdb2 =& $mdb2;
$_ARCHON->mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

if($_ARCHON->db->ServerType == 'MySQL' || $_ARCHON->db->ServerType == 'MySQLi')
{
   // this makes sure the encoding for the queries is expected
   $query = "SET NAMES 'utf8'";
   $affected = $_ARCHON->mdb2->exec($query);
   if (PEAR::isError($affected))
   {
      trigger_error($affected->getMessage(), E_USER_ERROR);
   }

   // This might fail.
   $query = "SET max_allowed_packet=1073741824";
   $affected = $_ARCHON->mdb2->exec($query);
}

if($_ARCHON->mdb2)
{
   if($_REQUEST['p'] != 'install' && $_REQUEST['p'] != 'upgrade')
   {
      $_ARCHON->initialize();
   }
   elseif($_REQUEST['p'] == 'install')
   {
      $_ARCHON->Script = 'packages/core/install/install.php';
   }
   else
   {
      $_ARCHON->Script = 'packages/core/install/upgrade.php';
   }

   if($_ARCHON->Disabled || (!CONFIG_CORE_PUBLIC_ENABLED && $arrP[0] != 'admin' && $_REQUEST['p'] != 'install' && $_REQUEST['p'] != 'upgrade'))
   {
      if(!$_ARCHON->Security->userHasAdministrativeAccess())
      {
         include('header.inc.php');
         echo('<div class="center bold">' . CONFIG_CORE_DISABLED_MESSAGE . '</div>');
         include('footer.inc.php');
         exit();
      }
      else
      {
         $_ARCHON->PublicInterface->Header->Message = $_ARCHON->PublicInterface->Header->Message ? $_ARCHON->PublicInterface->Header->Message."\n Archon is currently closed to the public.\n" : "Archon is currently closed to the public.\n";
      }
   }
}

?>