<?php
/**
 * Includes file for Archon
 *
 * @package Archon
 * @author Chris Rishel
 */

// do a check to see if '.' is in the include path
// ini_set('include_path', '.:' . get_include_path());


if(defined('E_DEPRECATED'))
{
   error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
}
else
{
   error_reporting(E_ALL ^ E_NOTICE);
}

if(file_exists('cryptor.inc.php'))
{
   require_once('cryptor.inc.php');
}
else
{
   require_once('cryptorblank.inc.php');
}

// Let core installer handle it's own includes.
if($_REQUEST['p'] == 'install')
{
   require_once('packages/core/install/install.php');
   return;
}
elseif($_REQUEST['p'] == 'upgrade')
{
   require_once('packages/core/install/upgrade.php');
   return;
}

$arrP = explode('/', $_REQUEST['p']);

if(file_exists('packages/core/install/install.php') && $arrP[0] != 'admin' && $_REQUEST['p'] != 'install')
{
   header('Location: index.php?p=install');
}

if(!file_exists('packages/core/index.php'))
{
   die('The Archon Core could not be found in packages/core/');
}

require_once('common.inc.php');


$cwd = getcwd();
chdir('packages/core/lib/');
require_once('index.php');
chdir($cwd);

ob_start();
$success = include_once('MDB2.php');
ob_get_clean();

if($success == false)
{
   die("MDB2 is either not correctly installed or not in your include paths. <br /><br /> <a href='http://archon.org/mdb2.html'>Click here</a> for more information on how to make sure MDB2 is installed and correctly configured.");
}
require_once('config.inc.php');
require_once('start.inc.php');
?>
