<?php
/**
 * Output file for browsing the digital library
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();



$objDLTitlePhrase = Phrase::getPhrase('digitallibrary_title', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
$strDLTitle = $objDLTitlePhrase ? $objDLTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse Digital Content';

$_ARCHON->PublicInterface->Title = $strDLTitle;

$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}");

$in_Char = isset($_REQUEST['char']) ? $_REQUEST['char'] : NULL;

$in_Browse = isset($_REQUEST['browse']) ? true : false;

if($in_Char)
{
//   $objDCBeginningTitlePhrase = Phrase::getPhrase('digitallibrary_dcbeginningtitle', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
//   $strDCBeginningTitle = $objDCBeginningTitlePhrase ? $objDCBeginningTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Digital Content Beginning with $1';
//
//   $_ARCHON->PublicInterface->Title = str_replace('$1', encoding_strtoupper($in_Char), $strDCBeginningTitle);
   digitallibrary_listDigitalContentForChar($in_Char);
}
elseif($in_Browse)
{
   $in_Page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

   digitallibrary_listAllDigitalContent($in_Page);
}
else
{
   digitallibrary_main();
}


require_once("footer.inc.php");

function digitallibrary_main()
{
   global $_ARCHON;

   

   $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

   $objShowBeginningPhrase = Phrase::getPhrase('digitallibrary_showbeginning', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
   $strShowBeginning = $objShowBeginningPhrase ? $objShowBeginningPhrase->getPhraseValue(ENCODE_HTML) : 'Show Digital Content Titles Beginning with';

   require_once("header.inc.php");

   $arrDigitalContentCount = $_ARCHON->countDigitalContent(true);
   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");
?>


   <div class='center'>
      <div class='listitemhead bold'><?php echo($strShowBeginning); ?>:</div><br/><br/>
      <div class='bground beginningwith'>
      <?php
      if(!empty($arrDigitalContentCount['#']))
      {
         echo("<a href='?p={$_REQUEST['p']}&amp;char=" . urlencode('#') . "'>-#-</a>" . INDENT);
      }
      else
      {
         echo("-#-" . INDENT);
      }

      for($i = 65; $i < 91; $i++)
      {
         $char = chr($i);

         if(!empty($arrDigitalContentCount[encoding_strtolower($char)]))
         {
            echo("<a href='?p={$_REQUEST['p']}&amp;char=$char'>-$char-</a>" . INDENT);
         }
         else
         {
            echo("-$char-" . INDENT);
         }

         if($char == 'M')
         {
            echo("<br/><br/>\n");
         }
      }
      echo("<br/><br/><a href='?p={$_REQUEST['p']}&amp;browse'>{$strViewAll}</a>");

      $objPleaseEnterPhrase = Phrase::getPhrase('digitallibrary_pleaseenter', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
      $strPleaseEnter = $objPleaseEnterPhrase ? $objPleaseEnterPhrase->getPhraseValue(ENCODE_JAVASCRIPTTHENHTML) : 'Please enter search terms.';
      $objSearchImagesPhrase = Phrase::getPhrase('digitallibrary_searchimages', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
      $strSearchImages = $objSearchImagesPhrase ? $objSearchImagesPhrase->getPhraseValue(ENCODE_HTML) : 'Search Images';
      $objBrowseThumbnailsPhrase = Phrase::getPhrase('digitallibrary_browsethumbnails', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
      $strBrowseThumbnails = $objBrowseThumbnailsPhrase ? $objBrowseThumbnailsPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Image Thumbnails';
      ?>
   </div>
   <form action="index.php" accept-charset="UTF-8" method="get" onsubmit="if(!this.q.value) { alert('<?php echo($strPleaseEnter); ?>'); return false; } else { return true; }">
      <div id="dlsearchblock">
         <input type="hidden" name="p" value="digitallibrary/thumbnails" />
         <input type="text" size="20" title="search" maxlength="150" name="q" value="<?php echo(encode($_ARCHON->QueryString, ENCODE_HTML)); ?>" tabindex="50" />
         <input type="submit" value="<?php echo($strSearchImages); ?>" tabindex="51" id='imagesbutton' class='button' /><br/>
         <span class='bold'><a href="index.php?p=digitallibrary/thumbnails"><?php echo($strBrowseThumbnails); ?></a></span>
      </div>
   </form>
</div>
<?php
   }

   function digitallibrary_listAllDigitalContent($Page)
   {
      global $_ARCHON;

      $SearchFlags = SEARCH_DIGITALCONTENT;

      $RepositoryID = $_SESSION['Archon_RepositoryID'] ? $_SESSION['Archon_RepositoryID'] : 0;

      $arrDigitalContent = $_ARCHON->searchDigitalContent($_REQUEST['q'], $SearchFlags, $RepositoryID, 0, 0, 0, 0, 0, 0, CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page - 1) * CONFIG_CORE_PAGINATION_LIMIT);

      if(count($arrDigitalContent) > CONFIG_CORE_PAGINATION_LIMIT)
      {
         $morePages = true;
         array_pop($arrDigitalContent);
      }

// Set up a URL for any prev/next buttons or in case $Page
// is too high
      $paginationURL = 'index.php?p=' . $_REQUEST['p'] . '&browse';

      if(empty($arrDigitalContent) && $Page != 1)
      {
         header("Location: $paginationURL");
      }

      

      $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
      $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

      $_ARCHON->PublicInterface->addNavigation($strViewAll);

      require_once("header.inc.php");

      if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['DigitalContentList'])
      {
         $_ARCHON->declareError("Could not list DigitalContent: DigitalContentList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
      }

      echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");


      if(!$_ARCHON->Error)
      {
         if(!empty($arrDigitalContent))
         {
            echo("<div class='listitemhead bold'>$strViewAll</div><br/><br/>\n");
            echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");

            foreach($arrDigitalContent as $objDigitalContent)
            {
               eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['DigitalContentList']);
            }

            echo("</div>");
         }

         if($Page > 1 || $morePages)
         {
            echo("<div class='paginationnav'>");

            if($Page > 1)
            {
               $prevPage = $Page - 1;
               $prevURL = encode($paginationURL . "&page=$prevPage", ENCODE_HTML);
               echo("<span class='paginationprevlink'><a href='$prevURL'>Prev</a></span>");
            }
            if($morePages)
            {
               $nextPage = $Page + 1;
               $nextURL = encode($paginationURL . "&page=$nextPage", ENCODE_HTML);
               echo("<span class='paginationnextlink'><a href='$nextURL'>Next</a></span>");
            }
            echo("</div>");
         }
      }
   }

   function digitallibrary_listDigitalContentForChar($Char)
   {
      global $_ARCHON;

      $RepositoryID = $_SESSION['Archon_RepositoryID'] ? $_SESSION['Archon_RepositoryID'] : 0;


      

      $objBeginningWithPhrase = Phrase::getPhrase('digitallibrary_beginningwith', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
      $strBeginningWith = $objBeginningWithPhrase ? $objBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Beginning with "$1"';

      $_ARCHON->PublicInterface->addNavigation(str_replace('$1', encoding_strtoupper($Char), $strBeginningWith), $_SERVER['SCRIPT_NAME'] . "?char=$Char");

      if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['DigitalContentList'])
      {
         $_ARCHON->declareError("Could not list DigitalContent: DigitalContentList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
      }

      require_once("header.inc.php");
      echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

      if(!$_ARCHON->Error)
      {
         $arrDigitalContent = $_ARCHON->getDigitalContentForChar($Char, false, $RepositoryID);

         if(!empty($arrDigitalContent))
         {
            $objDCBeginningWithPhrase = Phrase::getPhrase('digitallibrary_dcbeginningwith', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
            $strDCBeginningWith = $objDCBeginningWithPhrase ? $objDCBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Digital Content Titles Beginning with "$1"';
            $strDCBeginningWith = str_replace('$1', encoding_strtoupper($Char), $strDCBeginningWith);

            echo("<div class='listitemhead bold'>$strDCBeginningWith</div><br/><br/>\n");

            echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");

            foreach($arrDigitalContent as $objDigitalContent)
            {
               eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['DigitalContentList']);
            }

            echo("</div>");
         }
      }
   }