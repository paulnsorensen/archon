<?php
/**
 * Uninstall Script
 * 
 * This script updates the Archon database to discontinue
 * using the Accessions Package
 * 
 * @package Archon
 * @author Kyle Fox, Paul Sorensen
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");


$objAccessionsPackageID = $_ARCHON->getPackageIDFromAPRCode('accessions');
$objAccessionsPackage = New Package($objAccessionsPackageID);

@define('PACKAGE_ACCESSIONS', $objAccessionsPackageID, false);

ArchonInstaller::uninstallDB('packages/accessions/install');

$objAccessionsPackage->dbDelete();
?>