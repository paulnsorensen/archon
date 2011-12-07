<?php
/**
 * Output file for searching
 *
 * @package Archon
 * @subpackage collections
 * @author Chris Rishel
 */

isset($_ARCHON) or die();



$in_RepositoryID = isset($_REQUEST['repositoryid']) ? $_REQUEST['repositoryid'] : $_ARCHON->Security->Session->getRemoteVariable('RepositoryID');
$in_RepositoryID = !isset($in_RepositoryID) && CONFIG_CORE_LIMIT_REPOSITORY_SEARCH_RESULTS ? $_ARCHON->Repository->ID : $in_RepositoryID;
$in_ClassificationID = isset($_REQUEST['classificationid']) ? $_REQUEST['classificationid'] : 0;


$in_LocationID = $_REQUEST['locationid'] ? $_REQUEST['locationid'] : 0;
$in_RangeValue = $_REQUEST['rangevalue'] ? $_REQUEST['rangevalue'] : NULL;
$in_Section = $_REQUEST['section'] ? $_REQUEST['section'] : NULL;
$in_Shelf = $_REQUEST['shelf'] ? $_REQUEST['shelf'] : NULL;
$in_CollectionID = $_REQUEST['collectionid'] ? $_REQUEST['collectionid'] : 0;
$in_CollectionContentID = $_REQUEST['collectioncontentid'] ? $_REQUEST['collectioncontentid'] : 0;

$in_SubjectID = $_REQUEST['subjectid'] ? $_REQUEST['subjectid'] : NULL;
$in_CreatorID = $_REQUEST['creatorid'] ? $_REQUEST['creatorid'] : 0;
$in_LanguageID = $_REQUEST['languageid'] ? $_REQUEST['languageid'] : 0;
$in_BookID = $_REQUEST['bookid'] ? $_REQUEST['bookid'] : 0;

if(!$_ARCHON->Security->isAuthenticated())
{
   $in_LocationID = 0;
}


if($in_CollectionID)
{
   $objCollection = New Collection($in_CollectionID);

   if(!$objCollection->dbLoad())
   {
      return;
   }
   elseif(!$objCollection->enabled())
   {
      return;
   }

   $objSearchingFindingAidPhrase = Phrase::getPhrase('search_searchingfindingaid', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strSearchingFindingAid = $objSearchingFindingAidPhrase ? $objSearchingFindingAidPhrase->getPhraseValue(ENCODE_HTML) : 'Searching within the finding aid for $1';

   echo(str_replace('$1', $objCollection->toString(), $strSearchingFindingAid)."<br/><br/>");
}
elseif($in_CollectionContentID)
{
   $objCollectionContent = New CollectionContent($in_CollectionContentID);

   if(!$objCollectionContent->dbLoad())
   {
      return;
   }
   elseif(!$objCollectionContent->enabled())
   {
      return;
   }

   $objSearchingForContentPhrase = Phrase::getPhrase('search_searchingforcontent', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strSearchingForContent = $objSearchingForContentPhrase ? $objSearchingForContentPhrase->getPhraseValue(ENCODE_HTML) : 'Searching for $1';

   echo(str_replace('$1', $objCollectionContent->Collection->toString() . $_ARCHON->PublicInterface->Delimiter . $objCollectionContent->toString(LINK_NONE, true, true, true, true, $_ARCHON->PublicInterface->Delimiter), $strSearchingForContent)."<br/><br/>");
}
elseif($in_LocationID)
{
   $objLocationEntry = New LocationEntry(0);

   $objLocationEntry->LocationID = $in_LocationID;
   $objLocationEntry->RangeValue = $in_RangeValue;
   $objLocationEntry->Section = $in_Section;
   $objLocationEntry->Shelf = $in_Shelf;

   $objSearchingForLocationPhrase = Phrase::getPhrase('search_searchingforlocation', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strSearchingForLocation = $objSearchingForLocationPhrase ? $objSearchingForLocationPhrase->getPhraseValue(ENCODE_HTML) : 'Searching for $1';

   echo(str_replace('$1', $objLocationEntry->toString(), $strSearchingForLocation));
}
elseif($in_BookID)
{
   $objBook = New Book($in_BookID);

   if(!$objBook->dbLoad())
   {
      return;
   }

   //   $objSearchingFindingAidPhrase = Phrase::getPhrase('search_searchingfindingaid', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   // $strSearchingFindingAid = $objSearchingFindingAidPhrase ? $objSearchingFindingAidPhrase->getPhraseValue(ENCODE_HTML) : 'Searching within the finding aid for $1';

   //   echo(str_replace('$1', $objCollection->toString(), $strSearchingFindingAid)."<br/><br/>");
}

function collections_search()
{
   global $_ARCHON, $in_SearchFlags, $ResultCount;
   global $in_RepositoryID, $in_LocationID, $in_RangeValue, $in_Section, $in_Shelf, $in_CollectionID, $in_CollectionContentID, $in_SubjectID, $in_CreatorID, $in_LanguageID;

   

   $SearchFlags = $in_SearchFlags ? $in_SearchFlags : SEARCH_COLLECTIONS;

   if($_REQUEST['content'])
   {
      $SearchFlags |= SEARCH_COLLECTIONCONTENT;
      $SearchFlags |= SEARCH_USERFIELDS;
   }

   if(!$in_CollectionID && !$in_CollectionContentID)
   {
      if(CONFIG_COLLECTIONS_SEARCH_BY_CLASSIFICATION)
      {
         /**
          * Explanation of processing of search by classification results
          *
          * 1.   Make API Call to search, which returns an array of Classification objects
          *
          * 2.   These Classification objects can be nested, the objects can have multiple
          *      child Classifications.  This means we must use a stack to help us do a
          *      Depth-First Search traversal of the "tree".
          *
          * 3.   For each Classification in the array, display any Collection objects
          *
          * 4.   These Collection objects can have multiple CollectionContent objects within
          *      Content[].  These objects are already pre-sorted, and we can just display them
          *      outright, and let toString take care of displaying the parents.
          */
         $arrClassifications = $_ARCHON->searchCollectionsByClassification($_ARCHON->QueryString, $SearchFlags, $in_SubjectID , $in_CreatorID, $in_LanguageID, $in_RepositoryID, $in_LocationID, $in_RangeValue, $in_Section, $in_Shelf);

         if(!empty($arrClassifications))
         {
            $objClassRecordsAndMansPhrase = Phrase::getPhrase('search_classrecordsandmans', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strClassRecordsAndMans = $objClassRecordsAndMansPhrase ? $objClassRecordsAndMansPhrase->getPhraseValue(ENCODE_HTML) : 'Records and Manuscripts';
            $objMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strMatches = $objMatchesPhrase ? $objMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';
            $objNoClassificationPhrase = Phrase::getPhrase('search_noclassification', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strNoClassification = $objNoClassificationPhrase ? $objNoClassificationPhrase->getPhraseValue(ENCODE_HTML) : 'No Classification';
            $objInBoxListPhrase = Phrase::getPhrase('search_inboxlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strInBoxList = $objInBoxListPhrase ? $objInBoxListPhrase->getPhraseValue(ENCODE_HTML) : 'Results Found Within Box List';

            // Count number of collections.
            $numCollectionResults = 0;
            foreach($arrClassifications as $objClassification)
            {
               foreach($objClassification->Collections as $objCollection)
               {
                  $numCollectionResults++;
               }
            }

            reset($arrClassifications);
            ?>
<div class="searchTitleAndResults searchlistitem">
   <span id='CollectionTitle'>
      <a href="#" onclick="toggleDisplay('Collection'); return false;"><img id="CollectionImage" src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif" alt="expand/collapse" /><?php echo("  ".$strClassRecordsAndMans); ?></a>
   </span> (<span id='CollectionCount'><?php echo($numCollectionResults); ?></span> <?php echo($strMatches); ?>)<br/>
   <dl id='CollectionResults' style='display: none;'>
                  <?php
                  $objRootClassification = current($arrClassifications);

                  while($objRootClassification)
                  {
                     if($objRootClassification->ParentID != 0)
                     {
                        $objRootClassification = next($arrClassifications);
                        continue;
                     }

                     // Put root-level node onto the end of the stack
                     $arrClassificationTraversal[] = $objRootClassification;

                     while(!empty($arrClassificationTraversal))
                     {
                        // Get the front element and display it and its collections
                        $objClassification = array_shift($arrClassificationTraversal);

                        if(!empty($objClassification->Collections))
                        {
                           if($objClassification->ID)
                           {
                              echo("<dt>" . $objClassification->toString(LINK_NONE, false, true, false, true, $_ARCHON->PublicInterface->Delimiter) . "</dt>\n");
                           }
                           else
                           {
                              echo("<dt>$strNoClassification</dt>\n");
                           }

                           if(!empty($objClassification->Collections))
                           {
                              echo("<dd><dl class='CollectionClassEnabledResults'>\n");

                              foreach($objClassification->Collections as &$objCollection)
                              {
                                 if($objClassification->ID)
                                 {
                                    $collectionSubstring = $objCollection->getString('CollectionIdentifier') ? '/' . $objCollection->getString('CollectionIdentifier') : '';
                                    echo("<dt>" . $objCollection->toString(LINK_TOTAL) . ' ' . $objClassification->toString(LINK_NONE, true, false, true, false) . $collectionSubstring . "</dt>\n");
                                 }
                                 else
                                 {
                                    echo("<dt>" . $objCollection->toString(LINK_TOTAL) . ' ' . $objCollection->getString('CollectionIdentifier') . "</dt>\n");
                                 }
                                 $ResultCount++;

                                 if(!empty($objCollection->Content))
                                 {
                                    echo("<dd><div class='InnerContentTitleAndResults'>\n");
                                    echo("<span class='InnerContentResultsToggle'>");
                                    echo("<a href='#' onclick='toggleDisplay(\"CollectionContent{$objCollection->ID}\"); return false;'>");
                                    echo("<img id='CollectionContent{$objCollection->ID}Image' src='{$_ARCHON->PublicInterface->ImagePath}/plus.gif' alt='expand/collapse' />");
                                    echo(" $strInBoxList</a></span>\n");
                                    echo("<dl id='CollectionContent{$objCollection->ID}Results' class='InnerCollectionContentResults' style='display: none;'>");
                                    foreach($objCollection->Content as &$objContent)
                                    {
                                       echo("<dt>" . $objContent->toString(LINK_EACH, true, true, true, true, $_ARCHON->PublicInterface->Delimiter) . "</dt>\n");
                                    }

                                    echo("</dl></div></dd>\n");
                                 }
                              }
                              echo("</dl></dd>\n");
                           }
                        }

                        // Push any children onto the end of the stack
                        if(!empty($objClassification->Classifications))
                        {
                           // Reverse the children, so after unshifting, they will be in the proper order
                           $arrChildClassifications = array_reverse($objClassification->Classifications, true);

                           foreach($arrChildClassifications as &$objClassification)
                           {
                              array_unshift($arrClassificationTraversal, $objClassification);
                           }
                        }
                     }

                     $objRootClassification = next($arrClassifications);
                  }
                  ?>
   </dl>
</div>
            <?php
         }
      }
      else
      {
         $arrCollections = $_ARCHON->searchCollections($_ARCHON->QueryString, $SearchFlags, $in_SubjectID, $in_CreatorID, $in_LanguageID, $in_RepositoryID, $in_ClassificationID, $in_LocationID, $in_RangeValue, $in_Section, $in_Shelf);

         if(!empty($arrCollections))
         {
            $objPlainRecordsAndMansPhrase = Phrase::getPhrase('search_plainrecordsandmans', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strPlainRecordsAndMans = $objPlainRecordsAndMansPhrase ? $objPlainRecordsAndMansPhrase->getPhraseValue(ENCODE_HTML) : 'Records and Manuscripts';
            $objMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strMatches = $objMatchesPhrase ? $objMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';
            $objInBoxListPhrase = Phrase::getPhrase('search_inboxlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strInBoxList = $objInBoxListPhrase ? $objInBoxListPhrase->getPhraseValue(ENCODE_HTML) : 'Results Found Within Box List';
            ?>
<div class="searchTitleAndResults searchlistitem">
   <span id='CollectionTitle'>
      <a href="#" onclick="toggleDisplay('Collection'); return false;"><img id="CollectionImage" src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif" alt="expand/collapse" /><?php echo(" ". $strPlainRecordsAndMans); ?></a>
   </span> (<span id='CollectionCount'><?php echo(count($arrCollections)); ?></span> <?php echo($strMatches); ?>)<br/>
   <dl id='CollectionResults' style='display: none;'>
                  <?php
                  foreach($arrCollections as &$objCollection)
                  {
                     echo("<dt>" . $objCollection->toString(LINK_TOTAL) . "</dt>\n");
                     $ResultCount++;

                     if(!empty($objCollection->Content))
                     {
                        echo("<dd><div class='InnerContentTitleAndResults'>\n");
                        echo("<span class='InnerContentResultsToggle'>");
                        echo("<a href='#' onclick='toggleDisplay(\"CollectionContent{$objCollection->ID}\"); return false;'>");
                        echo("<img id='CollectionContent{$objCollection->ID}Image' src='{$_ARCHON->PublicInterface->ImagePath}/plus.gif' alt='expand/collapse' />");
                        echo(" $strInBoxList</a></span>\n<dl id='CollectionContent{$objCollection->ID}Results' class='InnerCollectionContentResults' style='display: none;'>");

                        foreach($objCollection->Content as &$objContent)
                        {
                           echo("<dt>" . $objContent->toString(LINK_EACH, true, true, true, true, $_ARCHON->PublicInterface->Delimiter) . "</dt>\n");
                        }

                        echo("</dl></div></dd>\n");
                     }
                  }
                  ?>
   </dl>
</div>
            <?php
         }
      }
   }
   elseif(!$in_CollectionContentID && ($SearchFlags & SEARCH_COLLECTIONCONTENT) && ($_ARCHON->QueryString || $in_CollectionID))
   {
      $arrContent = $_ARCHON->searchCollectionContent($_ARCHON->QueryString, $SearchFlags, $in_CollectionID, $in_RepositoryID);

      if(!empty($arrContent))
      {
         $objItemsPhrase = Phrase::getPhrase('search_items', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
         $strItems = $objItemsPhrase ? $objItemsPhrase->getPhraseValue(ENCODE_HTML) : 'Series, Boxes, Folders or Items';
         $objMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
         $strMatches = $objMatchesPhrase ? $objMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';
         ?>
<div class='searchTitleAndResults searchlistitem'><span id='CollectionContentTitle'><?php echo($strItems); ?></span> (<span id='CollectionContentCount'>0</span> <?php echo($strMatches); ?>)<br/>
   <dl id='CollectionContentResults' style='display: none;'><br/>
               <?php

               foreach($arrContent as $objContent)
               {
                  echo("<dt>" . $objContent->toString(LINK_EACH, true, true, true, true, $_ARCHON->PublicInterface->Delimiter) . "\n");
                  echo("<script type='text/javascript'>
                    <!--
                    incrementCount('CollectionContent');
                    -->
                    </script>\n</dt>\n");
                  $ResultCount++;
               }

               echo("\n");
               ?>
   </dl>
</div>
         <?php
      }
   }
}


function books_search()
{
   global $_ARCHON, $in_SearchFlags, $ResultCount;
   global $in_BookID, $in_SubjectID, $in_CreatorID, $in_LanguageID;

   

   $SearchFlags = $in_SearchFlags ? $in_SearchFlags : SEARCH_BOOKS;

   $objClassBookPhrase = Phrase::getPhrase('search_bookcollection', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strClassBook = $objClassRecordsAndMansPhrase ? $objClassRecordsAndMansPhrase->getPhraseValue(ENCODE_HTML) : 'Books Collection';
   $objBookMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
   $strBookMatches = $objBookMatchesPhrase ? $objBookMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';

//   var_dump($in_SubjectID);
//   var_dump($in_SearchFlags);
//   var_dump(SEARCH_BOOKS);

   if(!$in_BookID && ($_ARCHON->QueryString || $in_SubjectID || $in_CreatorID || $in_LanguageID) && (!$in_SearchFlags || ($in_SearchFlags & SEARCH_BOOKS)))
   {
      $arrBooks = $_ARCHON->searchBooks($_ARCHON->QueryString, $in_SubjectID, $in_CreatorID, $in_LanguageID);

//      var_dump($arrBooks);

      if(!empty($arrBooks))
      {
         ?>
<div class="searchTitleAndResults searchlistitem">
   <span id='BookTitle'>
      <a href="#" onclick="toggleDisplay('Book'); return false;"><img id="BookImage" src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif" alt="expand/collapse" /><?php echo("  ".$strClassBook); ?></a>
   </span>(<span id='BookCount'><?php echo(count($arrBooks)); ?></span> <?php echo($strBookMatches); ?>) <br/>
   <dl id='BookResults' style='display: none;'>
               <?php

               foreach($arrBooks as $objBook)
               {
                  echo("<dt>" . $objBook->toString(LINK_TOTAL) . "</dt>\n");
                  $ResultCount++;
               }
               ?>
   </dl>
</div>

         <?php
      }
   }
}

$_ARCHON->addPublicSearchFunction('collections_search', 10);
$_ARCHON->addPublicSearchFunction('books_search', 10);
?>