<?php
/**
 * Installation Script
 *
 * This script updates the Archon database to either begin
 * using the Collections Package
 *
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");


//$objAVSAPPackageID = $_ARCHON->getPackageIDFromAPRCode('avsap');
//
//if(!$objAVSAPPackageID)
if(!$_ARCHON->getPackageIDFromAPRCode('avsap'))
{
   $objAVSAPPackage = new Package();
   $objAVSAPPackage->APRCode = 'avsap';
   $objAVSAPPackage->DBVersion = '3.21';
   $objAVSAPPackage->dbStore();

   @define('PACKAGE_AVSAP', $objAVSAPPackage->ID, false);

   ArchonInstaller::installDB('packages/avsap/install');

   $objAVSAPPackage->dbEnable();
}

?>