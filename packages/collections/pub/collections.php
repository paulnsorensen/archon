<?php
/**
 * Output file for browsing by collection
 *
 * @package Archon
 * @author Mamta Singh, Paul Sorensen
 */

isset($_ARCHON) or die();



$in_Char = isset($_REQUEST['char']) ? $_REQUEST['char'] : NULL;

$in_Book = isset($_REQUEST['books']) ? true : false;

$in_Browse = isset($_REQUEST['browse']) ? true : false;

if(!$in_Book)
{
   $objCollectionsTitlePhrase = Phrase::getPhrase('collections_title', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strCollectionsTitle = $objCollectionsTitlePhrase ? $objCollectionsTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse By Collection Title';

   $_ARCHON->PublicInterface->Title = $strCollectionsTitle;
   $_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}");

}
else
{
   $objBooksTitlePhrase = Phrase::getPhrase('collections_booktitle', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strBooksTitle = $objBooksTitlePhrase ? $objBooksTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse By Book Title';

   $_ARCHON->PublicInterface->Title = $strBooksTitle;
   $_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}&amp;books");
}


if($in_Char)
{
   collections_listCollectionsForChar($in_Char, $in_Book);
}
elseif($in_Browse)
{
   $in_Page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

   collections_listAllCollections($in_Page, $in_Book);

}
else
{
   collections_main($in_Book);
}


require_once("footer.inc.php");


function collections_main($ShowBooks)
{

   global $_ARCHON;

   


   $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

   require_once("header.inc.php");

   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

   if($ShowBooks)
   {
      $objBrowseBooksBeginningPhrase = Phrase::getPhrase('collections_browsebooksbeginning', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strBrowseBooksBeginning = $objBrowseBooksBeginningPhrase ? $objBrowseBooksBeginningPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Books Beginning With';

      $arrCollectionCount = $_ARCHON->countBooks(true);
      echo ("<div class='center'><span class='bold'> $strBrowseBooksBeginning:</span><br/><br/>");
   }
   else
   {
      $objBrowseHoldingsBeginningPhrase = Phrase::getPhrase('collections_browseholdingsbeginning', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strBrowseHoldingsBeginning = $objBrowseHoldingsBeginningPhrase ? $objBrowseHoldingsBeginningPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Holdings Beginning With';

      $arrCollectionCount = $_ARCHON->countCollections(true, false, $_SESSION['Archon_RepositoryID']);
      echo ("<div class='center'><span class='bold'> $strBrowseHoldingsBeginning:</span><br/><br/>");
   }

   ?>

<div class='bground beginningwith'>
      <?php

      if(!empty($arrCollectionCount['#']))
      {
         $href = "?p={$_REQUEST['p']}&amp;char=" . urlencode('#');
         if($ShowBooks)
         {
            $href .= "&amp;books";
         }
         echo("<a href='$href'>-#-</a>" . INDENT);
      }
      else
      {
         echo("-#-" . INDENT);
      }

      for($i = 65; $i < 91; $i++)
      {
         $char = chr($i);

         if(!empty($arrCollectionCount[encoding_strtolower($char)]))
         {
            $href = "?p={$_REQUEST['p']}&amp;char=$char";
            if($ShowBooks)
            {
               $href .= "&amp;books";
            }
            echo("<a href='$href'>-$char-</a>" . INDENT);
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
      $bookurl = ($ShowBooks) ? '&amp;books' : '';
      echo("<br/><br/><a href='?p={$_REQUEST['p']}&amp;browse{$bookurl}'>{$strViewAll}</a>");


      ?>
</div>
   <?php
   if($ShowBooks)
   {
      $objBrowseAllCollectionsPhrase = Phrase::getPhrase('collections_browseallcollections', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strBrowseAllCollections = $objBrowseAllCollectionsPhrase ? $objBrowseAllCollectionsPhrase->getPhraseValue(ENCODE_HTML) : 'Browse All Collections';

      echo ("<a href='?p={$_REQUEST['p']}'>{$strBrowseAllCollections}</a>");
   }
   else
   {
      if($_ARCHON->countBooks())
      {
         $objBrowseBooksCollectionPhrase = Phrase::getPhrase('collections_browsebookscollection', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
         $strBrowseBooksCollection = $objBrowseBooksCollectionPhrase ? $objBrowseBooksCollectionPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Books Collection';
         echo ("<a href='?p={$_REQUEST['p']}&amp;books'>{$strBrowseBooksCollection}</a>");
      }
   }

   if(CONFIG_COLLECTIONS_ENABLE_PUBLIC_EAD_LIST)
   {
      $objViewEADListPhrase = Phrase::getPhrase('collections_vieweadlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strViewEADList = $objViewEADListPhrase ? $objViewEADListPhrase->getPhraseValue(ENCODE_HTML) : 'List links to EAD files';

      echo ("<br/><br/><a rel='external' href='?p=collections/eadlist'>{$strViewEADList}</a>");
   }

   ?>
</div>
   <?php
}


function collections_listAllCollections($Page, $ShowBooks)
{
   global $_ARCHON;

   $RepositoryID = $_SESSION['Archon_RepositoryID'] ? $_SESSION['Archon_RepositoryID'] : 0;

   if(!$ShowBooks)
   {
      $arrCollections = $_ARCHON->searchCollections($_REQUEST['q'], SEARCH_COLLECTIONS, 0, 0, 0, $RepositoryID, 0, 0, NULL, NULL, NULL, CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page-1)*CONFIG_CORE_PAGINATION_LIMIT);
      $bookurl = '';
      $template = 'CollectionList';
      $objectName = 'objCollection';

   }
   else
   {
      $arrCollections = $_ARCHON->searchBooks($_REQUEST['q'], 0, 0, 0, CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page-1)*CONFIG_CORE_PAGINATION_LIMIT);
      $bookurl = '&amp;books';
      $template = 'BookList';
      $objectName = 'objBook';


   }
   if(count($arrCollections) > CONFIG_CORE_PAGINATION_LIMIT)
   {
      $morePages = true;
      array_pop($arrCollections);
   }

// Set up a URL for any prev/next buttons or in case $Page
// is too high
   $paginationURL = 'index.php?p=' . $_REQUEST['p'].'&browse'.$bookurl;

   if(empty($arrCollections) && $Page != 1)
   {
      header("Location: $paginationURL");
   }

   

   $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

   $_ARCHON->PublicInterface->addNavigation($strViewAll);

   require_once("header.inc.php");

   if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template])
   {
      $_ARCHON->declareError("Could not list Collections: CollectionList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }

   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");


   if(!$_ARCHON->Error)
   {
      if(!empty($arrCollections))
      {
         echo("<div class='listitemhead bold'>$strViewAll</div><br/><br/>\n");
         echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");

         foreach($arrCollections as ${$objectName})
         {
            eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template]);
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


function collections_listCollectionsForChar($Char, $ShowBooks)
{

   global $_ARCHON;

   

   $objBeginningWithPhrase = Phrase::getPhrase('collections_beginningwith', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strBeginningWith = $objBeginningWithPhrase ? $objBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Beginning With "$1"';


   $_ARCHON->PublicInterface->addNavigation(str_replace('$1', encoding_strtoupper($Char), $strBeginningWith), "?p={$_REQUEST['p']}&amp;char=$Char");


   $template = (!$ShowBooks) ? 'CollectionList' : 'BookList';

   require_once("header.inc.php");

   if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template])
   {
      $_ARCHON->declareError("Could not list Collections: CollectionList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }


   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

   if(!$_ARCHON->Error)
   {
      if(!$ShowBooks)
      {
         $arrCollections = $_ARCHON->getCollectionsForChar($Char, true, $_SESSION['Archon_RepositoryID'], array('ID', 'Title', 'SortTitle', 'ClassificationID', 'InclusiveDates', 'CollectionIdentifier', 'RepositoryID'));
         $objectName = 'objCollection';
      }
      else
      {
         $arrCollections = $_ARCHON->getBooksForChar($Char);
         $objectName = 'objBook';
      }

      if(!empty($arrCollections))
      {
         if(!$ShowBooks)
         {
            $objHoldingsBeginningWithPhrase = Phrase::getPhrase('collections_holdingsbeginningwithlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strHoldingsBeginningWith = $objHoldingsBeginningWithPhrase ? $objHoldingsBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Holdings Beginning With "$1"';
            $strBeginningWithHeader = str_replace('$1', encoding_strtoupper($Char), $strHoldingsBeginningWith);
         }
         else
         {
            $objBooksBeginningWithPhrase = Phrase::getPhrase('collections_booksbeginningwithlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strBooksBeginningWith = $objBooksBeginningWithPhrase ? $objBooksBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Books Beginning With "$1"';
            $strBeginningWithHeader = str_replace('$1', encoding_strtoupper($Char), $strBooksBeginningWith);
         }
         echo("<div class='listitemhead bold'>$strBeginningWithHeader</div><br/><br/>\n<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>\n");

         foreach($arrCollections as ${$objectName})
         {
            eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode][$template]);
         }

         echo("</div>\n");
      }
   }
}

require_once("footer.inc.php");

?>
