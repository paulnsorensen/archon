<?php
/**
 * Upgrade Script
 * 
 * This script updates the Archon database to continue
 * using new versions of the Accessions Package
 * 
 * @package Archon
 * @author Kyle Fox, Paul Sorensen
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");


$objAccessionsPackageID = $_ARCHON->getPackageIDFromAPRCode('accessions');
$objAccessionsPackage = New Package($objAccessionsPackageID);
$objAccessionsPackage->dbLoad();

include('packages/accessions/index.php'); // for $Version
$DBVersion = $objAccessionsPackage->DBVersion;

@define('PACKAGE_ACCESSIONS', $objAccessionsPackageID, false);

//Upgrade up to 2.23, if need be
if(version_compare($DBVersion, 2.22) < 0)
{
	die("Archon and its packages must be upgraded to at least version 2.22 to upgrade to 3.0");
}

$arrUpgradeDirs = ArchonInstaller::getUpgradeDirs('packages/accessions/install');



ArchonInstaller::upgradeDB('packages/accessions/install/', $arrUpgradeDirs, 'Accessions');


?>
