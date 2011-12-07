<?php
/**
 * Output file for searching
 *
 * @package Archon
 * @subpackage digitallibrary
 * @author Chris Rishel
 */
isset($_ARCHON) or die();



$in_FileTypeID = $_REQUEST['filetypeid'] ? $_REQUEST['filetypeid'] : 0;
$in_MediaTypeID = $_REQUEST['mediatypeid'] ? $_REQUEST['mediatypeid'] : 0;

$in_CollectionID = $_REQUEST['collectionid'] ? $_REQUEST['collectionid'] : 0;
$in_CollectionContentID = $_REQUEST['collectioncontentid'] ? $_REQUEST['collectioncontentid'] : 0;
$in_SubjectID = $_REQUEST['subjectid'] ? $_REQUEST['subjectid'] : NULL;
$in_CreatorID = $_REQUEST['creatorid'] ? $_REQUEST['creatorid'] : 0;

if($in_FileTypeID)
{
   $objFileType = New FileType($in_FileTypeID);

   if(!$objFileType->dbLoad())
   {
      return;
   }

   $objSearchForFileTypePhrase = Phrase::getPhrase('search_searchforfiletype', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
   $strSearchForFileType = $objSearchForFileTypePhrase ? $objSearchForFileTypePhrase->getPhraseValue(ENCODE_HTML) : 'Searching for FileType: $1';

   echo(str_replace('$1', $objFileType->toString(LINK_NONE), $strSearchForFileType) . "<br/><br/>\n");
}
else if($in_MediaTypeID)
{
   $objMediaType = New MediaType($in_MediaTypeID);

   if(!$objMediaType->dbLoad())
   {
      return;
   }

   $objSearchForMediaTypePhrase = Phrase::getPhrase('search_searchformediatype', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
   $strSearchForMediaType = $objSearchForMediaTypePhrase ? $objSearchForMediaTypePhrase->getPhraseValue(ENCODE_HTML) : 'Searching for MediaType: $1';

   echo(str_replace('$1', $objMediaType->toString(LINK_NONE), $strSearchForMediaType) . "<br/><br/>\n");
}

function digitallibrary_search()
{
   global $_ARCHON, $in_SearchFlags, $ResultCount;
   global $in_FileTypeID, $in_MediaTypeID, $in_CollectionID, $in_CollectionContentID, $in_SubjectID, $in_CreatorID;

   

   $RepositoryID = $_SESSION['Archon_RepositoryID'] ? $_SESSION['Archon_RepositoryID'] : 0;

   if($_ARCHON->QueryString || $RepositoryID || $in_CollectionID || $in_CollectionContentID || ($in_SubjectID && defined('PACKAGE_SUBJECTS')) || ($in_CreatorID && defined('PACKAGE_CREATORS')) || $in_FileTypeID || $in_MediaTypeID)
   {
      $SearchFlags = $in_SearchFlags ? $in_SearchFlags : (SEARCH_DIGITALCONTENT ^ SEARCH_NOTBROWSABLE);

      $arrDigitalContent = $_ARCHON->searchDigitalContent($_ARCHON->QueryString, $SearchFlags, $RepositoryID, $in_CollectionID, $in_CollectionContentID, $in_SubjectID, $in_CreatorID, $in_FileTypeID, $in_MediaTypeID);

      if(!empty($arrDigitalContent))
      {
         $foundImages = false;

         $MediaTypeID = $_ARCHON->getMediaTypeIDFromString('Image');
         if((!$in_MediaTypeID && $MediaTypeID) || $in_MediaTypeID == $MediaTypeID)
         {
            $arrImageContent = $_ARCHON->searchDigitalContent($_ARCHON->QueryString, $SearchFlags, $RepositoryID, $in_CollectionID, $in_CollectionContentID, $in_SubjectID, $in_CreatorID, $in_FileTypeID, $MediaTypeID);
         }

         $objDigitalImagesPhrase = Phrase::getPhrase('search_digitalimages', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
         $strDigitalImages = $objDigitalImagesPhrase ? $objDigitalImagesPhrase->getPhraseValue(ENCODE_HTML) : 'Digital Images and Records';
         $objMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
         $strMatches = $objMatchesPhrase ? $objMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';
?>
         <div class='searchTitleAndResults searchlistitem'>
            <span id='DigitalContentTitle'>
               <a href="#" onclick="toggleDisplay('DigitalContent'); return false;"><img id="DigitalContentImage" src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif" alt="expand/collapse" /><?php echo(" " . $strDigitalImages); ?></a>
            </span> (<span id='DigitalContentCount'><?php echo(count($arrDigitalContent)); ?></span> <?php echo($strMatches); ?>)<br/>
            <dl id='DigitalContentResults' style='display: none;'>
<?php
         if(!empty($arrImageContent))
         {
            $thumbsURL = 'index.php?p=digitallibrary/thumbnails';
            if($_ARCHON->QueryString)
            {
               $thumbsURL .= '&amp;q=' . $_ARCHON->QueryStringURL;
            }
            if($in_CollectionID)
            {
               $thumbsURL .= '&amp;collectionid=' . $in_CollectionID;
            }
            if($in_CollectionContentID)
            {
               $thumbsURL .= '&amp;collectioncontentid=' . $in_CollectionContentID;
            }
            if(defined('PACKAGE_SUBJECTS') && $in_SubjectID)
            {
               $thumbsURL .= '&amp;subjectid=' . $in_SubjectID;
            }
            if(defined('PACKAGE_CREATORS') && $in_CreatorID)
            {
               $thumbsURL .= '&amp;creatorid=' . $in_CreatorID;
            }

            $objSearchThumbnailsPhrase = Phrase::getPhrase('search_searchthumbnails', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
            $strSearchThumbnails = $objSearchThumbnailsPhrase ? $objSearchThumbnailsPhrase->getPhraseValue(ENCODE_HTML) : 'Search image thumbnails';
            $objNumThumbnailsPhrase = Phrase::getPhrase('search_numthumbnails', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
            $strNumThumbnails = $objNumThumbnailsPhrase ? $objNumThumbnailsPhrase->getPhraseValue(ENCODE_HTML) : '$1 or more images found';

            echo("<dt><a href='$thumbsURL'>$strSearchThumbnails</a> (" . str_replace('$1', count($arrImageContent), $strNumThumbnails) . ")</dt>\n");
         }

         foreach($arrDigitalContent as $objDigitalContent)
         {
            $objDigitalContent->dbLoadFiles();
            if(count($objDigitalContent->Files))
            {
               $onlyImages = true;
               foreach($objDigitalContent->Files as $objFile)
               {
                  $onlyImages &= ( $objFile->FileType->MediaTypeID == $MediaTypeID && ($objFile->DefaultAccessLevel != DIGITALLIBRARY_ACCESSLEVEL_NONE || $_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ)));
               }
            }
            else
            {
               $onlyImages = false;
            }

            if(!$onlyImages)
            {
               echo("<dt>" . $objDigitalContent->toString(LINK_TOTAL) . "</dt>\n");
            }
            $ResultCount++;
         }
?>
      </dl>
   </div>
      <?php
      }
   }
}

$_ARCHON->addPublicSearchFunction('digitallibrary_search', 20);
      ?>