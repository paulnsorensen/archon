<?php

/**
 * Output thumbnails for digital images
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

$in_CollectionID = $_REQUEST['collectionid'] ? $_REQUEST['collectionid'] : 0;
$in_CollectionContentID = $_REQUEST['collectioncontentid'] ? $_REQUEST['collectioncontentid'] : 0;
$in_SubjectID = $_REQUEST['subjectid'] ? $_REQUEST['subjectid'] : NULL;
$in_CreatorID = $_REQUEST['creatorid'] ? $_REQUEST['creatorid'] : 0;

$in_ThumbnailPage = $_REQUEST['thumbnailpage'] ? $_REQUEST['thumbnailpage'] : 1;

$SearchFlags = SEARCH_DIGITALCONTENT; // ^ SEARCH_NOTBROWSABLE;

$MediaTypeID = $_ARCHON->getMediaTypeIDFromString('Image');

$RepositoryID = $_SESSION['Archon_RepositoryID'] ? $_SESSION['Archon_RepositoryID'] : 0;

$arrDigitalContent = $_ARCHON->searchDigitalContent($_REQUEST['q'], $SearchFlags, $RepositoryID, $in_CollectionID, $in_CollectionContentID, $in_SubjectID, $in_CreatorID, 0, $MediaTypeID, CONFIG_DIGITALLIBRARY_MAX_THUMBNAILS + 1, ($in_ThumbnailPage - 1) * CONFIG_DIGITALLIBRARY_MAX_THUMBNAILS);

if(count($arrDigitalContent) > CONFIG_DIGITALLIBRARY_MAX_THUMBNAILS)
{
   $_ARCHON->MoreThumbnailPages = true;
   array_pop($arrDigitalContent);
}

foreach($arrDigitalContent as $ID => $objDigitalContent)
{
   if(!$objDigitalContent->Files)
   {
      $objDigitalContent->dbLoadFiles();
   }
   foreach($objDigitalContent->Files as $ID => $objFile)
   {
      if($objFile->FileType->MediaType->MediaType != 'Image' || (!$_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) && $objFile->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_NONE))
      {
         unset($objDigitalContent->Files[$ID]);
      }
   }
}

// Set up a URL for any prev/next buttons or in case $in_ThumbnailPage
// is too high
$_ARCHON->ThumbnailURL = 'index.php?p=' . $_REQUEST['p'];
if($_REQUEST['q'])
{
   $_ARCHON->ThumbnailURL .= '&q=' . ($_ARCHON->QueryStringURL ? $_ARCHON->QueryStringURL : urlencode($_REQUEST['q']));
}
if($in_CollectionID)
{
   $_ARCHON->ThumbnailURL .= '&collectionid=' . $in_CollectionID;
}
if($in_CollectionContentID)
{
   $_ARCHON->ThumbnailURL .= '&collectioncontentid=' . $in_CollectionContentID;
}
if($in_SubjectID && defined('PACKAGE_SUBJECTS'))
{
   $_ARCHON->ThumbnailURL .= '&subjectid=' . $in_SubjectID;
}
if($in_CreatorID && defined('PACKAGE_CREATORS'))
{
   $_ARCHON->ThumbnailURL .= '&creatorid=' . $in_CreatorID;
}

if(empty($arrDigitalContent) && $in_ThumbnailPage != 1)
{
   header("Location: $_ARCHON->ThumbnailURL");
}



$objThumbnailTitlePhrase = Phrase::getPhrase('thumbnails_title', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
$strThumbnailTitle = $objThumbnailTitlePhrase ? $objThumbnailTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Image Thumbnails';
$objDigitalArchivesPhrase = Phrase::getPhrase('thumbnails_digitalarchives', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
$strDigitalArchives = $objDigitalArchivesPhrase ? $objDigitalArchivesPhrase->getPhraseValue(ENCODE_HTML) : 'Digital Archives';

$_ARCHON->PublicInterface->Title = $strThumbnailTitle;

$_ARCHON->PublicInterface->addNavigation($strDigitalArchives, "?p=digitallibrary/digitallibrary");
$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "index.php?p=digitallibrary/thumbnails");

if(!$_ARCHON->PublicInterface->Templates['digitallibrary']['Thumbnails'])
{
   $_ARCHON->declareError("Could not display Thumbnails: Thumbnails template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
   eval($_ARCHON->PublicInterface->Templates['digitallibrary']['Thumbnails']);
}

require_once("footer.inc.php");