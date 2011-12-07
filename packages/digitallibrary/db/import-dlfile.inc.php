<?php
/**
 * MARC Importer script
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
global $_ARCHON;

isset($_ARCHON) or die();

$UtilityCode = 'dlfile';

$_ARCHON->addDatabaseImportUtility(PACKAGE_DIGITALLIBRARY, $UtilityCode, '3.21', array('*'), true);

if($_REQUEST['f'] == 'import-' . $UtilityCode)
{
   if(!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
   {
      die("Permission Denied.");
   }

   ob_implicit_flush();
   @set_time_limit(0);

   $arrFiles = $_ARCHON->getAllIncomingFiles(false);

   if(!empty($arrFiles))
   {            
      foreach($arrFiles as $strFilename => $strFileLocation)
      {
         $objFile = New File(0);
         $objFile->DigitalContentID = -1;
         $objFile->Title = $strFilename;
         $objFile->Filename = $strFilename;
         $objFile->DefaultAccessLevel = DIGITALLIBRARY_ACCESSLEVEL_NONE;

         $objFile->TempFilename = $objFile->TempFileName = $strFileLocation;

         $objFile->dbStore();
      }

      echo("<br />Import Complete!\n");

   }
}
?>