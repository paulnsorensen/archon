<?php
/**
 * Upgrade Script
 * 
 * This script updates the Archon database to continue
 * using new versions of the AvSAP Package
 * 
 * @package Archon
 * @author Kyle Fox, Paul Sorensen
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");


$objAvSAPPackageID = $_ARCHON->getPackageIDFromAPRCode('avsap');
$objAvSAPPackage = New Package($objAvSAPPackageID);
$objAvSAPPackage->dbLoad();

include('packages/avsap/index.php'); // for $Version
$DBVersion = $objAvSAPPackage->DBVersion;

@define('PACKAGE_AVSAP', $objAvSAPPackageID, false);

//Upgrade up to 2.23, if need be
if(version_compare($DBVersion, 2.22) < 0)
{
	die("Archon and its packages must be upgraded to at least version 2.22 to upgrade to 3.0");
}

$arrUpgradeDirs = ArchonInstaller::getUpgradeDirs('packages/avsap/install');



ArchonInstaller::upgradeDB('packages/avsap/install/', $arrUpgradeDirs, 'AvSAP');


?>
