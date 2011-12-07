<?php
/**
 * Uninstall Script
 * 
 * This script updates the Archon database to discontinue
 * using the Creators Package
 * 
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();
require_once("packages/core/lib/archoninstaller.inc.php");

$objCreatorsPackageID = $_ARCHON->getPackageIDFromAPRCode('creators');
$objCreatorsPackage = New Package($objCreatorsPackageID);

@define('PACKAGE_CREATORS', $objCreatorsPackageID, false);

ArchonInstaller::uninstallDB('packages/creators/install');


//// Ready go for install scripts
//if(get_class($_ARCHON->db) == 'MySQLDatabase' && file_exists("packages/creators/install/uninstall-mysql.sql.gz"))
//{
//    $arrQueries = $arrQueries = gzfile("packages/creators/install/uninstall-mysql.sql.gz");
//}
//else if(get_class($_ARCHON->db) == 'MSSQLDatabase' && file_exists("packages/creators/install/uninstall-mssql.sql.gz"))
//{
//    $arrQueries = $arrQueries = gzfile("packages/creators/install/uninstall-mssql.sql.gz");
//}
//
//if(file_exists("packages/creators/install/uninstall.sql.gz"))
//{
//    $arrQueries = !empty($arrQueries) ? array_merge($arrQueries, gzfile("packages/creators/install/uninstall.sql.gz")) : gzfile("packages/creators/install/uninstall.sql.gz");
//}
//
//$arrQueries = str_replace("\\n", "\r\n", $arrQueries);
//$arrQueries = preg_replace('/#([\w]+)#/e', '$1', $arrQueries);
//
//if(!empty($arrQueries))
//{
//    ob_start();
//    foreach($arrQueries as $linequery)
//    {
//        if(encoding_substr($linequery, 0, 2) != "--")
//        {
//            $query .= $linequery;
//            if(encoding_substr(trim($linequery), -1, 1) == ';' || encoding_substr(trim($linequery), -2, 1) == ';')
//            {
//                $_ARCHON->db->query($query);
//                $query = '';
//            }
//        }
//    }
//    ob_end_clean();
//}

$objCreatorsPackage->dbDelete();
?>