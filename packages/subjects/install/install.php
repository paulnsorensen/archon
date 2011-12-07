<?php
/**
 * Installation Script
 *
 * This script updates the Archon database to either begin
 * using the Subjects Package
 *
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();
require_once("packages/core/lib/archoninstaller.inc.php");

if(!$_ARCHON->getPackageIDFromAPRCode('subjects'))
{

   $objSubjectsPackage = new Package();
   $objSubjectsPackage->APRCode = 'subjects';
   $objSubjectsPackage->DBVersion = '3.21';
   $objSubjectsPackage->dbStore();

   @define('PACKAGE_SUBJECTS', $objSubjectsPackage->ID, false);

   ArchonInstaller::installDB('packages/subjects/install');


// Set default usergroup permissions
   $currentSecurity = $_ARCHON->Security->Disabled;
   $_ARCHON->Security->Disabled = true;

   $UsergroupID = $_ARCHON->getUsergroupIDFromName('Users');
   if($UsergroupID)
   {
      $objUsergroup = New Usergroup($UsergroupID);

      $arrPermissions = array('subjectsources' => 0);
      foreach($arrPermissions as $Script => $Permissions)
      {
         $ModuleID = $_ARCHON->getModuleIDFromScript($Script);
         $objUsergroup->dbSetPermissions($ModuleID, $Permissions);
      }
   }

   $_ARCHON->Security->Disabled = $currentSecurity;

   $objSubjectsPackage->dbEnable();

}
?>