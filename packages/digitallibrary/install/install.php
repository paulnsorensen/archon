<?php
/**
 * Installation Script
 *
 * This script updates the Archon database to either begin
 * using the Digital Library Package
 *
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();
require_once("packages/core/lib/archoninstaller.inc.php");

if(!$_ARCHON->getPackageIDFromAPRCode('digitallibrary'))
{

   $objDigitalLibraryPackage = new Package();
   $objDigitalLibraryPackage->APRCode = 'digitallibrary';
   $objDigitalLibraryPackage->DBVersion = '3.21';
   $objDigitalLibraryPackage->dbStore();

   @define('PACKAGE_DIGITALLIBRARY', $objDigitalLibraryPackage->ID, false);

   ArchonInstaller::installDB('packages/digitallibrary/install');


   $objDigitalLibraryPackage->dbEnable();
}
?>