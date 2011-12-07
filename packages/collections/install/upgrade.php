<?php
/**
 * Upgrade Script
 * 
 * This script updates the Archon database to continue
 * using new versions of the Collections Package
 * 
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");

$objCollectionsPackageID = $_ARCHON->getPackageIDFromAPRCode('collections');
$objCollectionsPackage = New Package($objCollectionsPackageID);
$objCollectionsPackage->dbLoad();

include('packages/collections/index.php'); // for $Version
$DBVersion = $objCollectionsPackage->DBVersion;

@define('PACKAGE_COLLECTIONS', $objCollectionsPackageID, false);

//Upgrade up to 2.23, if need be
if(version_compare($DBVersion, 2.22) < 0)
{
	die("Archon and its packages must be upgraded to at least version 2.22 to upgrade to 3.0");
}

$arrUpgradeDirs = ArchonInstaller::getUpgradeDirs('packages/collections/install');



ArchonInstaller::upgradeDB('packages/collections/install/', $arrUpgradeDirs, 'Collections');


?>
