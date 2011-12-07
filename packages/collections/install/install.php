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


//$objCollectionsPackageID = $_ARCHON->getPackageIDFromAPRCode('collections');

if(!$_ARCHON->getPackageIDFromAPRCode('collections'))
{
    $objCollectionsPackage = new Package();
    $objCollectionsPackage->APRCode = 'collections';
    $objCollectionsPackage->DBVersion = '3.21';
    $objCollectionsPackage->dbStore();

    @define('PACKAGE_COLLECTIONS', $objCollectionsPackage->ID, false);

    ArchonInstaller::installDB('packages/collections/install');



    // Set default usergroup permissions
    $currentSecurity = $_ARCHON->Security->Disabled;
    $_ARCHON->Security->Disabled = true;

    $UsergroupID = $_ARCHON->getUsergroupIDFromName('Power Users');
    if($UsergroupID)
    {
        $objUsergroup = New Usergroup($UsergroupID);
        $ModuleID = $_ARCHON->getModuleIDFromScript('locations');
        $objUsergroup->dbSetPermissions($ModuleID, 7);
    }

    $UsergroupID = $_ARCHON->getUsergroupIDFromName('Users');
    if($UsergroupID)
    {
        $objUsergroup = New Usergroup($UsergroupID);

        $arrPermissions = array('classification' => 5, 'extentunits' => 0, 'levelcontainers' => 0, 'locations' => 0, 'materialtypes' => 0, 'repositories' => 0);
        foreach($arrPermissions as $Script => $Permissions)
        {
            $ModuleID = $_ARCHON->getModuleIDFromScript($Script);
            $objUsergroup->dbSetPermissions($ModuleID, $Permissions);
        }
    }

    $_ARCHON->Security->Disabled = $currentSecurity;

//    $objCollectionsPackage = New Package($objCollectionsPackageID);
    $objCollectionsPackage->dbEnable();
}
?>