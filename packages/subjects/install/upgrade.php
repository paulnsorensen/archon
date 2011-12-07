<?php
/**
 * Upgrade Script
 *
 * This script updates the Archon database to continue
 * using new versions of the Subjects Package
 *
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");

$objSubjectsPackageID = $_ARCHON->getPackageIDFromAPRCode('subjects');
$objSubjectsPackage = New Package($objSubjectsPackageID);
$objSubjectsPackage->dbLoad();

include('packages/subjects/index.php'); // for $Version
$DBVersion = $objSubjectsPackage->DBVersion;

@define('PACKAGE_SUBJECTS', $objSubjectsPackageID, false);

if(version_compare($DBVersion, 2.22) < 0)
{
   die("Archon and its packages must be upgraded to at least version 2.22 to upgrade to 3.0");
}
$arrUpgradeDirs = ArchonInstaller::getUpgradeDirs('packages/subjects/install');

//$dh = opendir('packages/subjects/install');
//
//while(false !== ($file = readdir($dh)))
//{
//   if(preg_match('/([\d].[\d]{2})/', $file, $matches))
//   {
//      if(is_dir('packages/subjects/install/'.$matches[0]) && (version_compare($matches[0], $DBVersion) == 1) && (version_compare($matches[0], $_ARCHON->Version) != 1))
//      {
//         $arrUpgradeDirs[] = $matches[0];
//      }
//   }
//}
//if(!empty($arrUpgradeDirs))
//{
//   usort($arrUpgradeDirs, 'version_compare');
//}
//else
//{
//   die("No upgrade directories were found!");
//}

ArchonInstaller::upgradeDB('packages/subjects/install/', $arrUpgradeDirs, 'Subjects');



//foreach($arrUpgradeDirs as $UpgradeDir)
//{
//   $cwd = getcwd();
//   chdir('packages/subjects/install/'.$UpgradeDir);
//
//   echo("<b>Upgrading to version $UpgradeDir...</b><br /><br />");
//
//
//   if($_ARCHON->db->ServerType == 'MySQL' && file_exists("structure-mysql.sql"))
//   {
//      execQueries("structure-mysql.sql");
//   }
//   elseif($_ARCHON->db->ServerType == 'MSSQL' && file_exists("structure-mssql.sql"))
//   {
//      execQueries("structure-mssql.sql");
//   }
//
//   if(file_exists("insert.sql"))
//   {
//      execQueries("insert.sql");
//   }
//
//   if(file_exists("update.sql"))
//   {
//      execQueries("update.sql");
//   }
//
//   if(file_exists("update.php"))
//   {
//      require_once("update.php");
//   }
//
//
//   if(file_exists("drop.sql"))
//   {
//      execQueries("drop.sql");
//   }
//
//   chdir($cwd);
//}


/*
while(false !== ($file = readdir($dh)))
{
    if(preg_match('/upgrade(.*).sql.gz/', $file, $matches))
    {
        $arrUpgrades[] = $matches[1];
    }
}

if(!empty($arrUpgrades))
{
    usort($arrUpgrades, 'version_compare');
}
else
{
    die("No upgrade SQL scripts were found!");
}

ob_start();
foreach($arrUpgrades as $Upgrade)
{
    if(preg_match('/^([\\d.]|(alpha)|(beta)|(RC)|(pl)|(dev)|a|b)+$/ui', $Upgrade) && (version_compare($Upgrade, $DBVersion) == 1) && (version_compare($Upgrade, $Version) != 1))
    {
        echo("<b>Upgrading to version $Upgrade...</b><br><br>");

        $arrQueries = array();

        if(get_class($_ARCHON->db) == 'MySQLDatabase' && file_exists("packages/subjects/install/upgrade$Upgrade-mysql.sql.gz"))
        {
            $arrQueries = $arrQueries = gzfile("packages/subjects/install/upgrade$Upgrade-mysql.sql.gz");
        }
        else if(get_class($_ARCHON->db) == 'MSSQLDatabase' && file_exists("packages/subjects/install/upgrade$Upgrade-mssql.sql.gz"))
        {
            $arrQueries = $arrQueries = gzfile("packages/subjects/install/upgrade$Upgrade-mssql.sql.gz");
        }

        if(file_exists("packages/subjects/install/upgrade$Upgrade.sql.gz"))
        {
            $arrQueries = !empty($arrQueries) ? array_merge($arrQueries, gzfile("packages/subjects/install/upgrade$Upgrade.sql.gz")) : gzfile("packages/subjects/install/upgrade$Upgrade.sql.gz");
        }

        $arrQueries = str_replace("\\n", "\r\n", $arrQueries);
        $arrQueries = preg_replace('/#([\w]+)#/e', '$1', $arrQueries);

        if(!empty($arrQueries))
        {
            $query = '';

            foreach($arrQueries as $linequery)
            {
                if(substr($linequery, 0, 2) != "--")
                {
                    $query .= $linequery;
                    if(substr(trim($linequery), -1, 1) == ';' || substr(trim($linequery), -2, 1) == ';')
                    {
                        $_ARCHON->db->query($query);
                        $query = '';
                    }
                }
            }
        }
    }
}

ob_end_clean();
*/

?>
