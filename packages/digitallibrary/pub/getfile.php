<?php

/**
 * Output file for downloading digital library files
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

$PacketSize = 1024 * 1024; // 1 MB

$objFile = New File($_REQUEST['id']);

if($_REQUEST['preview'] == 'short')
{
   $Pointer = DIGITALLIBRARY_FILE_PREVIEWSHORT;
   $prefix = 'ps_';
}
elseif($_REQUEST['preview'] == 'long')
{
   $Pointer = DIGITALLIBRARY_FILE_PREVIEWLONG;
   $prefix = 'pl_';
}
else
{
   $Pointer = DIGITALLIBRARY_FILE_FULL;
   $prefix = '';
}

if((!$objFile->dbLoad($Pointer)))
{
   header("Location: index.php?p=digitallibrary/digitalcontent&id=$objFile->DigitalContentID");
   die();
}

if(!$objFile->Filename)
{
   die("File ID $in_id not found!");
}

header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false); // required for certain browsers
header("Content-type: {$objFile->FileType->ContentType}");
header("Content-Disposition: attachment; filename=\"{$objFile->Filename}\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: {$objFile->Size}");

if($_ARCHON->config->CacheFiles && file_exists('packages/digitallibrary/files/' . $objFile->ID . '/' .$prefix. $objFile->Filename))
{
   $fp = fopen('packages/digitallibrary/files/' . $objFile->ID . '/' .$prefix. $objFile->Filename,'rb');
   while(!feof($fp))
   {
      echo(fread($fp, $PacketSize));
   }
   fclose($fp);
   die();
}
else
{
   if($objFile->fopen($Pointer))
   {
      while(!$objFile->feof())
      {
         echo($objFile->fread($PacketSize));
      }

      $objFile->fclose();
      die();
   }
   else
   {
      header("Location: index.php?p=digitallibrary/digitalcontent&id=$objFile->DigitalContentID");
      die();
   }
}
?>