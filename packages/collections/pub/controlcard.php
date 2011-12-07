<?php
/**
 * Output file for control card
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

$objCollection = New Collection($_REQUEST['id']);
$objCollection->dbLoad();
$objCollection->dbLoadRelatedObjects();

if(!$objCollection->enabled())
{
   $_ARCHON->AdministrativeInterface = true;
   $_ARCHON->declareError("Could not access Collection \"" . $objCollection->toString() . "\": Public access disallowed.");
   $_ARCHON->AdministrativeInterface = false;
}


$objCollection->dbLoadBooks();

if(defined('PACKAGE_DIGITALLIBRARY'))
{
   $objCollection->dbLoadDigitalContent();

   $containsImages = false;

   foreach($objCollection->DigitalContent as $ID => $objDigitalContent)
   {
      $objDigitalContent->dbLoadFiles();
      if(count($objDigitalContent->Files))
      {
         $onlyImages = true;
         foreach($objDigitalContent->Files as $objFile)
         {
            if($objFile->FileType->MediaType->MediaType == 'Image')
            {
               $containsImages = true;
            }
            else
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
         unset($objCollection->DigitalContent[$ID]);
      }
   }
}

if(defined('PACKAGE_ACCESSIONS'))
{
   $SearchFlags = $in_SearchFlags ? $in_SearchFlags : SEARCH_ACCESSIONS;

   $arrAccessions = $_ARCHON->searchAccessions('', $SearchFlags, 0, $objCollection->ID);

   $arrDisplayAccessions = array();

   //show only unprocessed accessions to public
   if (!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
   {
      foreach($arrAccessions as $ID => $objAccession)
      {
         if($objAccession->enabled() && $objAccession->UnprocessedExtent > 0)
         {
            $arrDisplayAccessions[$ID] = $objAccession;
         }
      }
   }
   //show all accessions for authenticated users
   else
   {
      foreach($arrAccessions as $ID => $objAccession)
      {
         $arrDisplayAccessions[$ID] = $objAccession;
      }
   }
}


if($objCollection->Repository->TemplateSet)
{
   $_ARCHON->PublicInterface->TemplateSet = $objCollection->Repository->TemplateSet;
   $_ARCHON->PublicInterface->Templates = $_ARCHON->loadTemplates($_ARCHON->PublicInterface->TemplateSet);
}

if(!$_ARCHON->PublicInterface->Templates['collections']['ControlCard'])
{
   $_ARCHON->declareError("Could not display ControlCard: ControlCard template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}

$_ARCHON->PublicInterface->Title = $objCollection->toString();

if($objCollection->Classification)
{
   $arrClassifications = $_ARCHON->traverseClassification($objCollection->ClassificationID);

   foreach($arrClassifications as $objClassification)
   {
      $_ARCHON->PublicInterface->addNavigation($objClassification->getString('Title', 30), "?p=collections/classifications&amp;id=$objClassification->ID");
   }
}

$_ARCHON->PublicInterface->addNavigation($objCollection->getString('Title', 30), "p={$_REQUEST['p']}&amp;id=$objCollection->ID");

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
   eval($_ARCHON->PublicInterface->Templates['collections']['ControlCard']);
}

require_once("footer.inc.php");