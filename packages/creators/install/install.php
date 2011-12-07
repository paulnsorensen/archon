<?php
/**
 * Installation Script
 *
 * This script updates the Archon database to either begin
 * using the Creators Package
 *
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();
require_once("packages/core/lib/archoninstaller.inc.php");

if(!$_ARCHON->getPackageIDFromAPRCode('creators'))
{
   
   $objCreatorsPackage = new Package();
   $objCreatorsPackage->APRCode = 'creators';
   $objCreatorsPackage->DBVersion = '3.21';
   $objCreatorsPackage->dbStore();

   @define('PACKAGE_CREATORS', $objCreatorsPackage->ID, false);


   ArchonInstaller::installDB('packages/creators/install');

   // Set default usergroup permissions
   $currentSecurity = $_ARCHON->Security->Disabled;
   $_ARCHON->Security->Disabled = true;

   $UsergroupID = $_ARCHON->getUsergroupIDFromName('Users');
   if($UsergroupID)
   {
      $objUsergroup = New Usergroup($UsergroupID);

      $arrPermissions = array('creatorsources' => 0);
      foreach($arrPermissions as $Script => $Permissions)
      {
         $ModuleID = $_ARCHON->getModuleIDFromScript($Script);
         $objUsergroup->dbSetPermissions($ModuleID, $Permissions);
      }
   }

   $_ARCHON->Security->Disabled = $currentSecurity;


   $objCreatorsPackage->dbEnable();
}
?>
