<?php
/**
 * DigitalContent template
 *
 * The variable:
 *
 *  $arrFiles
 *
 * is an array of File objects, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the File class definition in packages/digitallibrary/lib/file.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in packages/core/lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Kyle Fox
 */
isset($_ARCHON) or die();

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");
?>
<script type="text/javascript">
   /* <![CDATA[ */
   if(window.jQuery !== undefined && jQuery.cluetip !== undefined)
   {
      $(document).ready(function () {
         $('.thumbnailimg .thumbimglink').cluetip({dropShadow: false, width: 320, tracking: true, showTitle: true, local: true, onActivate: function (e) {
               var src = $(e).find('img').attr('src');
        
               var idExp = /id=(\d+)/;
               var idMatchArray = idExp.exec(src);
               if(idMatchArray !== null)
               {
                  var id = idMatchArray[1];
                  $('#mediumPreview img').attr('src', '?p=digitallibrary/getfile&id=' + id + '&preview=long');

                  return true;
               }
             
               if(src.indexOf('ps_') != -1){                  
                  var newSrc = src.replace('ps_', 'pl_');
                  $('#mediumPreview img').attr('src', newSrc);
                  return true;
               }

               return false;
            }});
      })

   }
   /* ]]> */
</script>
<?php
if($_ARCHON->QueryString)
{
   echo("You searched for \"" . htmlspecialchars($_ARCHON->QueryString) . "\".<br/><br/>");
}

if($in_CollectionID)
{
   $objCollection = New Collection($in_CollectionID);

   if(!$objCollection->dbLoad())
   {
      return;
   }

   echo("Searching within the finding aid for " . $objCollection->toString() . "<br/><br/>");
}
else if($in_CollectionContentID)
{
   $objCollectionContent = New CollectionContent($in_CollectionContentID);

   if(!$objCollectionContent->dbLoad())
   {
      return;
   }

   echo("Searching for Item " . $objCollectionContent->toString(LINK_NONE) . "<br/><br/>");
}

if($in_CreatorID && defined('PACKAGE_CREATORS'))
{
   $objCreator = New Creator($in_CreatorID);

   if(!$objCreator->dbLoad())
   {
      return;
   }

   echo("Searching for Creator: " . $objCreator->toString() . "<br/><br/>\n");
}

if($in_SubjectID && defined('PACKAGE_SUBJECTS'))
{
   $objSubject = New Subject($in_SubjectID);

   if(!$objSubject->dbLoad())
   {
      return;
   }

   echo("Searching for Subject: " . $objSubject->toString(LINK_NONE, true) . "<br/><br/>\n");
}

if(!empty($arrDigitalContent))
{
?>
   <div id="mediumPreview" style="display: none;">
      <img id="mediumpreviewimg" src="" alt="Medium Preview" />
   </div>
<?php
   foreach($arrDigitalContent as $objDigitalContent)
   {
      $count = 0;
      foreach($objDigitalContent->Files as $objFile)
      {
         $count++;
?>
         <div class="thumbnailimg">
            <div class="thumbnailimgwrapper">
               <a class="thumbimglink" href="?p=digitallibrary/digitalcontent&amp;id=<?php echo($objFile->DigitalContentID); ?>" title="<?php echo($objDigitalContent->getString('Title', 30)); ?>" rel="#mediumPreview">
                  <img class='digcontentfile' src='<?php echo($objFile->getFileURL(DIGITALLIBRARY_FILE_PREVIEWSHORT)); ?>' alt='<?php echo($objFile->getString('Title')); ?>'/>
               </a>
            </div>
            <div class="thumbnailcaption">
               <a href="?p=digitallibrary/digitalcontent&amp;id=<?php echo($objFile->DigitalContentID); ?>"><?php echo($objDigitalContent->getString('Title', 20)); ?></a>
      <?php
         if(count($objDigitalContent->Files) > 1)
         {
      ?>
            <br/>(<?php echo($count); ?> out of <?php echo(count($objDigitalContent->Files)); ?>)
      <?php
         }
      ?>
      </div>
   </div>
<?php
      }
   }
}
else
{
   echo("No images found!");
}

if($in_ThumbnailPage > 1 || $_ARCHON->MoreThumbnailPages)
{
   echo("<div id='thumbnailnav'>");

   if($in_ThumbnailPage > 1)
   {
      $prevPage = $in_ThumbnailPage - 1;
      $prevURL = encode($_ARCHON->ThumbnailURL . "&thumbnailpage=$prevPage", ENCODE_HTML);
      echo("<span id='thumbnailprevlink'><a href='$prevURL'>Prev</a></span>");
   }
   if($_ARCHON->MoreThumbnailPages)
   {
      $nextPage = $in_ThumbnailPage + 1;
      $nextURL = encode($_ARCHON->ThumbnailURL . "&thumbnailpage=$nextPage", ENCODE_HTML);
      echo("<span id='thumbnailnextlink'><a href='$nextURL'>Next</a></span>");
   }
   echo("</div>");
}
?>