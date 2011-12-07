<?php
/**
 * Digital Content Metadata importer script.
 *
 * This script takes .csv files in a defined format and creates a new collection record for each row in the database.
 * A sample csv/excel file is provided in the archon/incoming folder, to show the necessary format.
 *
 *
 * this script does not currently support the import and linking of controlled subject or genre terms.
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Paul Sorensen
 */

isset($_ARCHON) or die();

$UtilityCode = 'digitalcontentmetadata_csv';

$_ARCHON->addDatabaseImportUtility(PACKAGE_DIGITALLIBRARY, $UtilityCode, '3.21', array('csv'), true);

if($_REQUEST['f'] == 'import-' . $UtilityCode)
{
   if(!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
   {
      die("Permission Denied.");
   }

   @set_time_limit(0);

   ob_implicit_flush();

   $arrFiles = $_ARCHON->getAllIncomingFiles();

   if(!empty($arrFiles))
   {

      foreach($arrFiles as $Filename => $strCSV)
      {
         echo("Parsing file $Filename...<br /><br />\n\n");

         // Remove byte order mark if it exists.
         $strCSV = ltrim($strCSV, "\xEF\xBB\xBF");

         $arrAllData = getCSVFromString($strCSV);
         // ignore first line?
         foreach($arrAllData as $arrData)
         {
            if(!empty($arrData))
            {
               $objDigitalContent = new DigitalContent();

               $enabled = reset($arrData);
               if($enabled)
               {
                  $enabled = trim(strtolower($enabled));
                  if($enabled == 'yes' || $enabled == 'y')
                  {
                     $enabled = 1;
                  }
                  else
                  {
                     $enabled = 0;
                  }
               }
               else
               {
                  $enabled = 0;
               }

               $objDigitalContent->Browsable = $enabled;

               //TODO: implement check to ensure collection exists
               $objDigitalContent->CollectionID = next($arrData);

               $objDigitalContent->Title = next($arrData);


               $objDigitalContent->Identifier = next($arrData);

               $objDigitalContent->ContentURL = next($arrData);


               $SortTitle = next($arrData);
               $objDigitalContent->SortTitle = $SortTitle ? $SortTitle : $objDigitalContent->Title;

               $objDigitalContent->Date = next($arrData);


               $objDigitalContent->Scope = next($arrData);
               $objDigitalContent->PhysicalDescription = next($arrData);
               $objDigitalContent->Contributor = next($arrData);
               $objDigitalContent->Publisher = next($arrData);
               $objDigitalContent->RightsStatement = next($arrData);


               $objDigitalContent->dbStore();
               if(!$objDigitalContent->ID)
               {
                  echo("Error storing digital content $objDigitalContent->Title: {$_ARCHON->clearError()}<br />\n");
                  continue;
               }


               if($objDigitalContent->ID)
               {
                  echo("Imported {$objDigitalContent->Title}.<br /><br />\n\n");
               }

               flush();
            }
         }
      }

      echo("All files imported!");
   }
}

?>