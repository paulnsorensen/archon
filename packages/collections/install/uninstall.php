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


$objCollectionsPackageID = $_ARCHON->getPackageIDFromAPRCode('collections');
$objCollectionsPackage = New Package($objCollectionsPackageID);

@define('PACKAGE_COLLECTIONS', $objCollectionsPackageID, false);

ArchonInstaller::uninstallDB('packages/collections/install');

$objCollectionsPackage->dbDelete();
?>