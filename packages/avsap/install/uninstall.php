<?php
/**
 * Uninstall Script
 * 
 * This script updates the Archon database to discontinue
 * using the Collections Package
 * 
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");


$objAVSAPPackageID = $_ARCHON->getPackageIDFromAPRCode('avsap');
$objAVSAPPackage = New Package($objAVSAPPackageID);

@define('PACKAGE_AVSAP', $objAVSAPPackageID, false);

ArchonInstaller::uninstallDB('packages/avsap/install');

$objAVSAPPackage->dbDelete();
?>