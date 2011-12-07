<?php
/**
 * Uninstall Script
 * 
 * This script updates the Archon database to discontinue
 * using the Subjects Package
 * 
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();
require_once("packages/core/lib/archoninstaller.inc.php");


$objSubjectsPackageID = $_ARCHON->getPackageIDFromAPRCode('subjects');
$objSubjectsPackage = New Package($objSubjectsPackageID);

@define('PACKAGE_SUBJECTS', $objSubjectsPackageID, false);

ArchonInstaller::uninstallDB('packages/subjects/install');


$objSubjectsPackage->dbDelete();
?>