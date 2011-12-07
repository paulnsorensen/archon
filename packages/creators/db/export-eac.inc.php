<?php

isset($_ARCHON) or die();


$UtilityCode = 'eac';

$_ARCHON->addDatabaseExportUtility(PACKAGE_CREATORS, $UtilityCode, '3.21');

if($_REQUEST['f'] == 'export-' . $UtilityCode)
{
   if(!$_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, READ))
   {
      die("Permission Denied.");
   }

   $repositoryID = $_REQUEST['repositoryid'] ? $_REQUEST['repositoryid'] : 0;


   if($repositoryID == 0)
   {
      die("RepositoryID not defined.");
   }

   @set_time_limit(60);


   $arrCreators = $_ARCHON->getCreatorsForRepository($repositoryID);

   $foldername = "archon_{$repositoryID}_eac";
   $dirname = sys_get_temp_dir()."/".$foldername;

   if(file_exists($dirname))
   {
      $d = dir($dirname);
      while($entry = $d->read())
      {
         if ($entry!= "." && $entry!= "..")
         {
            unlink($dirname."/".$entry);
         }
      }
      $d->close();
      rmdir($dirname);
   }

   mkdir($dirname, 0755);


   header("Content-Type: archive/zip");
   header("Content-Disposition: attachment; filename={$foldername}.zip");


   $_ARCHON->PublicInterface = new PublicInterface();
   $_ARCHON->PublicInterface->initialize(CONFIG_CORE_DEFAULT_THEME, "eac");
   $_ARCHON->PublicInterface->DisableTheme = true;



   foreach($arrCreators as $objCreator)
   {
      $_REQUEST['id'] = $objCreator->ID;
      $_REQUEST['output'] = formatFileName($objCreator->getString('Name',0,false,false));

      $handle = fopen($dirname."/".$_REQUEST['output'].".xml", "w");


      ob_start();

      $objCreator->dbLoadRelatedObjects();
      $objCreator->dbLoadRelatedCreators();

      if(defined('PACKAGE_COLLECTIONS'))
      {
         $objCreator->dbLoadCollections();
         $objCreator->dbLoadBooks();

         foreach($objCreator->Collections as $ID => $collection)
         {
            if(!$collection->enabled())
            {
               unset($objCreator->Collections[$ID]);
            }
         }
         unset($collection);
      }

      if(defined('PACKAGE_ACCESSIONS'))
      {
         $objCreator->dbLoadAccessions();
         foreach($objCreator->Accessions as $ID => $accession)
         {
            if(!$accession->enabled())
            {
               unset($objCreator->Accessions[$ID]);
            }
         }
         unset($accession);
      }

      if(defined('PACKAGE_DIGITALLIBRARY'))
      {
         $objCreator->dbLoadDigitalContent();

         $containsImages = false;

         foreach($objCreator->DigitalContent as $ID => $objDigitalContent)
         {
            $objDigitalContent->dbLoadFiles();
            if(count($objDigitalContent->Files))
            {
               $onlyImages = true;
               foreach($objDigitalContent->Files as $objFile)
               {
                  if($objFile->FileType->MediaType->MediaType != 'Image')
                  {
                     $onlyImages = false;
                  }
               }
            }
            else
            {
               $onlyImages = false;
            }

            if($onlyImages)
            {
               unset($objCreator->DigitalContent[$ID]);
            }
         }
      }


      if(!$_ARCHON->PublicInterface->Templates['creators']['Creator'])
      {
         $_ARCHON->declareError("Could not display Creator: Creator template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
      }

      if(!$_ARCHON->Error)
      {
         eval($_ARCHON->PublicInterface->Templates['creators']['Creator']);
      }
      else
      {
         echo($_ARCHON->Error);
      }

      $file = ob_get_clean();
      fwrite($handle, $file);
      fclose($handle);
   }
   


   chdir(sys_get_temp_dir());

   $tmp_zip = tempnam ("tmp", "tempname") . ".zip";

   exec("zip -r $tmp_zip $foldername");


   $filesize = filesize($tmp_zip);
   header("Content-Length: $filesize");

   // deliver the zip file
   $fp = fopen("$tmp_zip","r");
   echo fpassthru($fp);

   // clean up the tmp zip file
   exec("rm $tmp_zip");

   $d = dir($foldername);
   while($entry = $d->read())
   {
      if ($entry!= "." && $entry!= "..")
      {
         unlink($foldername."/".$entry);
      }
   }
   $d->close();
   rmdir($foldername);


}

?>
