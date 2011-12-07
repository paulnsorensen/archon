<?php
/**
 * Output file for searching
 *
 * @package Archon
 * @subpackage creators
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

$objCreator = New Creator($_REQUEST['id']);
if(!$objCreator->dbLoad())
{
   return;
}

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
         foreach($objDigitalContent->Files as $objFile)
         {
            if($objFile->FileType->MediaType->MediaType == 'Image')
            {
               $containsImages = true;
            }
         }
      }
   }
}

if(!$_ARCHON->PublicInterface->Templates['creators']['Creator'])
{
   $_ARCHON->declareError("Could not display Creator: Creator template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}





$_ARCHON->PublicInterface->Title = $objCreator->toString(); 

$_ARCHON->PublicInterface->addNavigation($objCreator->getString('Name'), "p={$_REQUEST['p']}&amp;id=$objCreator->ID");

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
   eval($_ARCHON->PublicInterface->Templates['creators']['Creator']);
}

require_once("footer.inc.php");

?>



