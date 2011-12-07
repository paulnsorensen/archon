<?php
/**
 * Installation Script
 *
 * This script updates the Archon database to either begin
 * using the Accessions Package
 *
 * @package Archon
 * @author Kyle Fox, Paul Sorensen
 */

isset($_ARCHON) or die();

require_once("packages/core/lib/archoninstaller.inc.php");

if(!$_ARCHON->getPackageIDFromAPRCode('accessions'))
{
   $objAccessionsPackage = new Package();
   $objAccessionsPackage->APRCode = 'accessions';
   $objAccessionsPackage->DBVersion = '3.21';
   $objAccessionsPackage->dbStore();

   @define('PACKAGE_ACCESSIONS', $objAccessionsPackage->ID, false);

   ArchonInstaller::installDB('packages/accessions/install');
   
   $objAccessionsPackage->dbEnable();
}
?>