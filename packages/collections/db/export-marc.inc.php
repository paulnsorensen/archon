<?php
isset($_ARCHON) or die();


$UtilityCode = 'marc';

$_ARCHON->addDatabaseExportUtility(PACKAGE_COLLECTIONS, $UtilityCode, '3.21');

if($_REQUEST['f'] == 'export-' . $UtilityCode)
{
   if(!$_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, READ))
   {
      die("Permission Denied.");
   }

   $repositoryID = $_REQUEST['repositoryid'] ? $_REQUEST['repositoryid'] : 0;

   $classificationID = $_REQUEST['classificationid'] ? $_REQUEST['classificationid'] : 0;

   if($repositoryID == 0 && $classificationID == 0)
   {
      die("RepositoryID and ClassificationID not defined.");
   }

   require_once("packages/collections/lib/php-marc/php-marc.php");

   @set_time_limit(60);


   $arrCollections = $_ARCHON->searchCollections('', SEARCH_COLLECTIONS, 0, 0, 0, $repositoryID, $classificationID, 0, NULL, NULL, NULL, 0);

   $foldername = "archon_{$repositoryID}_{$classificationID}_marc";
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



   foreach($arrCollections as $objCollection)
   {
      $filename = formatFileName($objCollection->getString('SortTitle',0,false,false));

      $handle = fopen($dirname."/".$filename.".mrc", "w");

      $objCollection->dbLoadRelatedObjects();

      ob_start();

      $objMARCRecord = New PHP_MARC();

      $objMARCRecord->leader('00000npcaa        a 4500');

      if($objCollection->Repository->Code)
      {
         $fields['003']->Value = $objCollection->Repository->Code;
      }

      $fields['005']->Value = date('Y').date('m').date('d').date('h').date('i').date('s').".0";

      $field8 = "010101i";
      $field8 .= $objCollection->NormalDateBegin;
      $field8 .= $objCollection->NormalDateEnd;


      $field8 .= "xx";    //$field8 .= "ilu";

      if(!empty($objCollection->Languages))
      {
         $field8 .= current($objCollection->Languages)->LanguageShort;
      }

      $field8 .= "##";
      $fields['008']->Value = $field8;

      if($objCollection->Classification)
      {
         $fields['099']->Indicator1 = 9;
         $fields['099']->SubFields['a'] = $objCollection->Classification->toString(LINK_NONE, true, false, true, false);
      }

      if($objCollection->CollectionIdentifier)
      {
         $fields['099']->SubFields['a'] .= $fields['099']->SubFields['a'] ? '/' . $objCollection->CollectionIdentifier : $objCollection->CollectionIdentifier;
      }

      if($objCollection->Creators)
      {
         foreach($objCollection->Creators as $objCreator)
         {
            if($objCreator->CreatorType->CreatorType == 'Personal Name' || $objCreator->CreatorType->CreatorType == 'Family Name')
            {
               if($objCreator->ID == $objCollection->PrimaryCreator->ID)
               {
                  $fieldnumber = '100';
               }
               else
               {
                  $fieldnumber = '700';
               }

               $count[$fieldnumber]++;
               $fieldnumber .= ".".$count[$fieldnumber];

               if($objCreator->CreatorType == 'Personal Name')
               {
                  $fields[$fieldnumber]->Indicator1 = 1;
               }
               else
               {
                  $fields[$fieldnumber]->Indicator1 = 3;
               }

               if($objCreator->Name)
               {
                  $fields[$fieldnumber]->SubFields['a'] = $objCreator->Name;
               }

               if($objCreator->NameFullerForm)
               {
                  $fields[$fieldnumber]->SubFields['q'] = "({$objCreator->NameFullerForm})";
               }
              
               if($objCreator->Dates)
               {
                  $fields[$fieldnumber]->SubFields['d'] = $objCreator->Dates;
               }

               if($fields[$fieldnumber]->SubFields['a'] && ($objCreator->ID == $objCollection->PrimaryCreator->ID))
               {
                  $creatorset = 1;
               }

               if($objCreator->BiogHist && ($objCreator->ID == $objCollection->PrimaryCreator->ID))
               {
                  $fields['545']->Indicator1 = 0;
                  list($fields['545']->SubFields['a'], $fields['545']->SubFields['b']) = explode("\r\n\r\n", $objCreator->BiogHist);

                  if(!$fields['545']->SubFields['b'])
                  {
                     unset($fields['545']->SubFields['b']);
                  }

                  @ksort($fields['545']->SubFields);
               }
            }
            elseif($objCreator->CreatorType->CreatorType == 'Corporate Name')
            {
               if($objCreator->ID == $objCollection->PrimaryCreator->ID)
               {
                  $fieldnumber = '110';
               }
               else
               {
                  $fieldnumber = '710';
               }

               $count[$fieldnumber]++;
               $fieldnumber .= ".".$count[$fieldnumber];

               $fields[$fieldnumber]->Indicator1 = 2;

               if($objCreator->ParentBody)
               {
                  $fields[$fieldnumber]->SubFields['a'] = $objCreator->ParentBody;

                  if($objCreator->Name)
                  {
                     $fields[$fieldnumber]->SubFields['b'] = $objCreator->Name;
                  }
               }
               elseif($objCreator->Name)
               {
                  $fields[$fieldnumber]->SubFields['a'] = $objCreator->Name;
               }

               if($fields[$fieldnumber]->SubFields['a'] && ($objCreator->ID == $objCollection->PrimaryCreator->ID))
                  $creatorset = 1;

               if($objCreator->BiogHist && ($objCreator->ID == $objCollection->PrimaryCreator->ID))
               {
                  $fields['545']->Indicator1 = 1;
                  list($fields['545']->SubFields['a'], $fields['545']->SubFields['b']) = explode("\r\n\r\n", $objCreator->BiogHist);

                  if(!$fields['545']->SubFields['b'])
                  {
                     unset($fields['545']->SubFields['b']);
                  }
               }
            }
         }
      }




      if($objCollection->Title)
      {
         if($creatorset)
         {
            $fields['245']->Indicator1 = 0;
         }
         else
         {
            $fields['245']->Indicator1 = 1;
         }

         $fields['245']->Indicator2 = 0;

         $fields['245']->SubFields['a'] = $objCollection->Title;

         if($objCollection->InclusiveDates)
         {
            $fields['245']->SubFields['f'] = $objCollection->InclusiveDates;
         }

         if($objCollection->PredominantDates)
         {
            $fields['245']->SubFields['g'] = $objCollection->PredominantDates;
         }
      }


      if($objCollection->Extent)
      {
         $fields['300']->SubFields['a'] = $objCollection->Extent;

         if($objCollection->ExtentUnit)
         {
            $fields['300']->SubFields['f'] = $objCollection->ExtentUnit->ExtentUnit;
         }

         if($objCollection->AltExtentStatement)
         {
            $fields['300']->SubFields['b'] = "({$objCollection->AltExtentStatement})";
         }
      }



      if($objCollection->Arrangement)
      {
         $fields['351']->SubFields['a'] = $objCollection->Arrangement;
      }

      if($objCollection->AccessRestrictions)
      {
         $fields['506']->SubFields['a'] = $objCollection->AccessRestrictions;
      }


      if($objCollection->Scope)
      {
         list($fields['520']->SubFields['a']) = explode("\r\n\r\n", $objCollection->Scope);
         $fields['520']->Indicator1 = 2;
      }


      if($objCollection->AcquisitionSource)
      {
         $fields['541']->SubFields['a'] = $objCollection->AcquisitionSource;
      }

      if($objCollection->AcquisitionMethod)
      {
         list($fields['541']->SubFields['c']) = explode("\r\n\r\n", $objCollection->AcquisitionMethod);
      }

      if($objCollection->AcquisitionDateYear)
      {
         $fields['541']->SubFields['d'] = $objCollection->AcquisitionDateYear;
      }

      if($objCollection->CustodialHistory)
      {
         $fields['561']->SubFields['a'] = $objCollection->CustodialHistory;
      }

      if($objCollection->Subjects)
      {
         foreach($objCollection->Subjects as $objSubject)
         {
            $arrTraversal = $_ARCHON->traverseSubject($objSubject->ID);

            $topsubject = current($arrTraversal);

            $fieldnumber = $topsubject->SubjectType->EncodingAnalog;

            $count[$fieldnumber]++;
            $fieldnumber .= ".".$count[$fieldnumber];

            $fields[$fieldnumber]->SubFields['a'] = $topsubject->Subject;

            $fields[$fieldnumber]->Indicator1 = '#';

            if($topsubject->SubjectSourceID)
            {
               $fields[$fieldnumber]->Indicator2 = 0;
            }
            else
            {
               $fields[$fieldnumber]->Indicator2 = 7;
               $fields[$fieldnumber]->SubFields['2'] = "local";
            }

            array_shift($arrTraversal);

            foreach($arrTraversal as $id => $obj)
            {
               if($obj->SubjectType->SubjectType == "Genre/Form of Material")
               {
                  $subfield = 'v';
               }
               elseif($obj->SubjectType->SubjectType == "Date")
               {
                  $subfield = 'y';
               }
               elseif($obj->SubjectType->SubjectType == "Geographic Name")
               {
                  $subfield = 'z';
               }
               else
               {
                  $subfield = 'x';
               }

               $fields[$fieldnumber]->SubFields[$subfield][] = $obj->Subject;
            }
         }
      }

      $path = encoding_substr($_SERVER['SCRIPT_NAME'], 1, encoding_strrpos($_SERVER['SCRIPT_NAME'], "/"));


      $fields['856']->Indicator1 = 4;
      $fields['856']->Indicator2 = 2;
      $fields['856']->SubFields['3'] = "Control Card";
      $fields['856']->SubFields['u'] = "http://{$_SERVER['HTTP_HOST']}/{$path}?p=collections/controlcard&amp;id=$objCollection->ID";

      if($fields)
      {
         ksort($fields);

         foreach($fields as $number => $obj)
         {
            if($obj->SubFields || $obj->Value)
            {
               if($obj->Value)
               {
                  $field = new PHP_MARC_Field(encoding_substr($number, 0, 3), $obj->Value);
                  $objMARCRecord->append_fields($field);
               }
               else
               {
                  $field = new PHP_MARC_Field(encoding_substr($number, 0, 3), "{$obj->Indicator1}", "{$obj->Indicator2}", $obj->SubFields);
                  $objMARCRecord->append_fields($field);
               }
            }
         }
      }


      $raw = $objMARCRecord->raw();

      $raw = str_replace("\r", '', $raw);

//    header('Content-Disposition: attachment; filename="' . $objCollection->SortTitle . '.mrc"');
      echo(str_replace("\n", ' ', $raw));



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
