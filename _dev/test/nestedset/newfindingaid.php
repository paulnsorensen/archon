<?php
/**
 * Output file for finding aids
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

@set_time_limit(60);

$in_RootContentID = $_REQUEST['rootcontentid'] ? $_REQUEST['rootcontentid'] : 0;

// Load the collection and all of its items (pre-processed)
// into one object
$objCollection = New Collection($_REQUEST['id']);
$objCollection->dbLoadRootContent();
$arrRootContent = $objCollection->Content;

$objCollection->dbLoadAll(LOADCONTENT_NONE);


if(!$objCollection->enabled())
{
   $_ARCHON->AdministrativeInterface = true;
   $_ARCHON->declareError("Could not access Collection \"" . $objCollection->toString() . "\": Public access disallowed.");
   $_ARCHON->AdministrativeInterface = false;
}


if($objCollection->TemplateSet && $_REQUEST['templateset'] != 'EAD')
{
   $_ARCHON->PublicInterface->TemplateSet = $objCollection->TemplateSet;
   $_ARCHON->PublicInterface->Templates = $_ARCHON->loadTemplates($_ARCHON->PublicInterface->TemplateSet);
}

if(!$_ARCHON->PublicInterface->Templates['collections']['Collection'])
{
   $_ARCHON->declareError("Could not display FindingAid: Collection template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
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



$objFindingAidPhrase = Phrase::getPhrase('findingaid_findingaid', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strFindingAid = $objFindingAidPhrase ? $objFindingAidPhrase->getPhraseValue(ENCODE_HTML) : 'Finding Aid';

$_ARCHON->PublicInterface->addNavigation($objCollection->getString('Title', 30), "?p=collections/controlcard&amp;id=$objCollection->ID");
$_ARCHON->PublicInterface->addNavigation($strFindingAid, "?p={$_REQUEST['p']}&amp;id=$objCollection->ID");

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
   // Stop automatic highlighting.
   ob_end_flush();
   flush();
   // Turn on highlighting buffer
   ob_start();

   // Process the collection template.
   eval($_ARCHON->PublicInterface->Templates['collections']['Collection']);

   $output = ob_get_clean();
   if($output)
   {
      $arrWords = $_ARCHON->createSearchWordArray($_ARCHON->QueryString);

      $count = 0;
      if(!empty($arrWords))
      {
         foreach($arrWords as $word)
         {
            if($word && $word{0} != "-")
            {
               $output = preg_replace("/(\A|\>)([^\<]*[^\w^=^\<^\+^\/]|)(" . preg_quote($word, '/') . ")(|[^\w^=^\>\+][^\>]*)(\<|\z)/ui", "$1$2<span class='highlight$count bold'>$3</span>$4$5", $output);
               $count++;
            }
         }
      }
   }


   // Break the collection template where %Items% occurs
   // into two strings, containing the template data before
   // and after the processed items should be inserted.
   list($outputnow, $outputlater) = explode("#CONTENT#", $output);

   echo($outputnow);
   flush();

   $cid = $objCollection->ID;

   $query = "SELECT node.*, (COUNT(parent.ID) - 1) AS depth
FROM tblCollections_Content AS node,
tblCollections_Content AS parent
WHERE node.CollectionID = $cid AND parent.CollectionID = $cid AND node.Lft BETWEEN parent.Lft AND parent.Rgt
GROUP BY node.ID ORDER BY node.Lft";
//   $start = microtime(true);
   $result = $_ARCHON->mdb2->query($query);
//   $time = microtime(true) - $start;
//   echo("Depth Query Time: $time <br/>");

   if(PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
//   $time = 0;

   $arrLevelContainers = $_ARCHON->getAllLevelContainers();
   $imagepath = $_ARCHON->PublicInterface->ImagePath;


      if(!$objCollection->enabled())
      {
         $readPermissions = false;
      }
      else
      {
         $readPermissions = false;


         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, READ)
                 || ($_ARCHON->Security->userHasAdministrativeAccess() && !CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS)
                 || (CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS && $_ARCHON->Security->verifyRepositoryPermissions($objCollection->RepositoryID)))
         {
            $readPermissions = true;
         }
      }



   function do_nothing($pid, $id)
   {
      return '';
   }

   function admin_string($pid, $id)
   {
      global $cid, $imagepath, $strEditThis;

      return "<a href='?p=admin/collections/collectioncontent&amp;collectionid={$cid}&amp;parentid={$pid}&amp;id={$id}' rel='external'><img class='edit' src='{$imagepath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";

   }

   function public_string($pid, $id)
   {
      global $cid, $url, $imagepath, $strAddTo, $strRemove, $cartEmpty, $cartArray;

      if(!$cartEmpty && $cartArray[$id])
      {
         return "<a href='?p=collections/research&amp;f=delete&amp;collectionid={$cid}&amp;collectioncontentid={$id}&amp;go=" . $url . "'><img class='cart' src='{$imagepath}/removefromcart.gif' title='$strRemove' alt='$strRemove'/></a>";
      }
      else
      {
         return "<a href='?p=collections/research&amp;f=add&amp;collectionid={$cid}&amp;collectioncontentid={$id}&amp;go=" . $url . "'><img class='cart' src='{$imagepath}/addtocart.gif' title='$strAddTo' alt='$strAddTo'/></a>";
      }
   }

   if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE))
   {
      $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
      $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

      $function = 'admin_string';

//      $String .= "<a href='?p=admin/collections/collectioncontent&amp;collectionid={$cid}&amp;parentid={$this->ParentID}&amp;id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
   }
   elseif(!$_ARCHON->Security->userHasAdministrativeAccess()) // && $this->enabled())

   {
      $objRemovePhrase = Phrase::getPhrase('tostring_remove', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strRemove = $objRemovePhrase ? $objRemovePhrase->getPhraseValue(ENCODE_HTML) : 'Remove from your cart.';
      $objAddToPhrase = Phrase::getPhrase('tostring_addto', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strAddTo = $objAddToPhrase ? $objAddToPhrase->getPhraseValue(ENCODE_HTML) : 'Add to your cart.';

      $url = urlencode($_SERVER['REQUEST_URI']);

      $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();

      $cartArray = isset($arrCart->Collections[$cid]->Content) ? $arrCart->Collections[$cid]->Content : array();
      $cartEmpty = empty($cartArray) ? true : false;

      $function = 'public_string';

//      if($arrCart->Collections[$this->CollectionID]->Content[$this->ID])
//      {
//         $String .= "<a href='?p=collections/research&amp;f=delete&amp;collectionid={$cid}&amp;collectioncontentid={$this->ID}&amp;go=" . urlencode($_SERVER['REQUEST_URI']) . "'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/removefromcart.gif' title='$strRemove' alt='$strRemove'/></a>";
//      }
//      else
//      {
//         $String .= "<a href='?p=collections/research&amp;f=add&amp;collectionid={$cid}&amp;collectioncontentid={$this->ID}&amp;go=" . urlencode($_SERVER['REQUEST_URI']) . "'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/addtocart.gif' title='$strAddTo' alt='$strAddTo'/></a>";
//      }
   }
   else
   {
      $function = 'do_nothing';
   }

   $arrContent = array();

   while($row = $result->fetchRow())
   {

      if($row['LevelContainerID'])
      {
         $encoding_substring = $arrLevelContainers[$row['LevelContainerID']]->getString('LevelContainer');
      }

      if($row['LevelContainerIdentifier'])
      {
         $encoding_substring .= ' ' . $row['LevelContainerIdentifier'];
      }

      if(($row['Title'] || $row['Date']) && $encoding_substring)
      {
         $encoding_substring .= ': ';
      }

      if($row['Title'])
      {
         $encoding_substring .= $row['Title'];
      }

      if($row['Date'])
      {
         if($row['Title'])
         {
            $encoding_substring .= ', ';
         }

         $encoding_substring .= $row['Date'];
      }

      $string = $encoding_substring . $function($row['ParentID'], $row['ID']);

      $arrContent[$row['ID']] = array();
      $arrContent[$row['ID']]['ID']=$row['ID'];
      $arrContent[$row['ID']]['String'] = $string;
      $String = $row['Description'];
      if($_ARCHON->db->ServerType == 'MSSQL')
      {
         $String = encoding_convert_encoding($String, 'UTF-8', 'ISO-8859-1');
      }

      $String = trim($String);

      if(CONFIG_CORE_ESCAPE_XML)
      {
         $String = encode($String, ENCODE_BBCODE);
      }
      $arrContent[$row['ID']]['Description'] = $String;
      $arrContent[$row['ID']]['Enabled'] = $row['Enabled'];
      $arrContent[$row['ID']]['Depth'] = $row['depth'];
      $arrContent[$row['ID']]['Userfields'] = array();
//      echo($string."<br/>");

//      $time += microtime(true) - $start;

   }
   $result->free();

   $query = "SELECT tblCollections_UserFields.* FROM tblCollections_UserFields JOIN tblCollections_Content ON tblCollections_Content.ID = tblCollections_UserFields.ContentID WHERE tblCollections_Content.CollectionID = $cid";

   $result = $_ARCHON->mdb2->query($query);//
   if(PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
   while($row = $result->fetchRow())
   {
      $string =  $row['Title']. ': ';
      $String = $row['Value'];
      if($_ARCHON->db->ServerType == 'MSSQL')
      {
         $String = encoding_convert_encoding($String, 'UTF-8', 'ISO-8859-1');
      }

      $String = trim($String);

      if(CONFIG_CORE_ESCAPE_XML)
      {
         $String = encode($String, ENCODE_BBCODE);
      }
      $string .= $String;

      $arrContent[$row['ContentID']]['Userfields'][$row['ID']] = $string;

   }
   $result->free();

   $lastDepth = 0;

   foreach($arrContent as $ID => $Content)
   {
      $depthChange = $Content['Depth'] - $lastDepth;
      $lastDepth = $Content['Depth'];


      if($_ARCHON->PublicInterface->Templates['collections'][$Content['LevelContainer']])
      {
         $ItemType = $Content['LevelContainer'];
      }
      else
      {
         $ItemType = "DefaultContent2";
      }

      if(!$_ARCHON->PublicInterface->Templates['collections'][$ItemType])
      {
         $_ARCHON->declareError("Could not display $ItemType: $ItemType template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
         return;
      }
      ob_start();

      eval($_ARCHON->PublicInterface->Templates['collections'][$ItemType]);

      $output = ob_get_clean();
      if($output)
      {
         $arrWords = $_ARCHON->createSearchWordArray($_ARCHON->QueryString);

         $count = 0;
         if(!empty($arrWords))
         {
            foreach($arrWords as $word)
            {
               if($word && $word{0} != "-")
               {
                  $output = preg_replace("/(\A|\>)([^\<]*[^\w^=^\<^\+^\/]|)(" . preg_quote($word, '/') . ")(|[^\w^=^\>\+][^\>]*)(\<|\z)/ui", "$1$2<span class='highlight$count bold'>$3</span>$4$5", $output);
                  $count++;
               }
            }
         }
      }
      echo($output);
      flush();
   }




//   $objCollection->dbLoadContent2();
//$arrContent = $objCollection->Content;
//
//
//   // If the collection contains items, process and display them.
//   // Note: $objCollection->Content was created (and ordered) by the Collection
//   // constructor and consists of a one-dimensional array of items,
//   // with the ID field from the tblCollections_Collections table acting as the array keys.
//
//   if(!empty($arrRootContent))
//   {
//      $objContent = reset($arrRootContent);
////      $rootContentIDsSet = $objCollection->rootContentIDsSet();
////
////      if(!$rootContentIDsSet)
////      {
////         $objCollection->dbLoadAll(LOADCONTENT_ALL);
////      }
//
//      do
//      {
////         if(!$in_RootContentID || $objContent->ID == $in_RootContentID)
////         {
////            if($rootContentIDsSet)
////            {
////               $objCollection->dbLoadAll($objContent->ID);
////            }
//            // Process and display the current item.
//            findingaid_DisplayContent($objContent->ID);
//
//            // remove circular references for garbage collector
//            foreach($objCollection->Content as $objContent)
//            {
//               $objContent->Parent = NULL;
//            }
//
//            //$objCollection->Content[$objContent->ID] = NULL;
////         }
//
//
//         // Advance the array pointer to the next item
//         $objContent = next($arrRootContent);
//
//         // If there are no more items, we are done.
//         // If the item is contained by another item, then the item
//         // has already been displayed (since root-level items occur
//         // first in the array), we are also done.
//      } while($objContent && !$objContent->ParentID);
//   }

   echo($outputlater);
   flush();

   // END
}
else
{
   echo($_ARCHON->Error);
}

require_once("footer.inc.php");




// ***********************************************
// * findingaid_DisplayContent()                 *
// ***********************************************
//
//   - Purpose: Prepares an item for processing.
//
//   - Incoming Variables:
//
//      - $id (Integer) (REQUIRED):
//           Contains the ID value from
//           tblCollections_Collections for the item being
//           displayed.
//
// ***********************************************
function findingaid_DisplayContent($id)
{
   // Put our collection and templates objects into the global scope.
   // $objCollection was initiated by the Collection constructor called by
   // this script.
   // The templates object was initiated by another file loaded by includes.inc.php
   global $_ARCHON, $objCollection, $arrWords;

   $objContent = $objCollection->Content[$id];

   if($_ARCHON->PublicInterface->Templates['collections'][$objContent->LevelContainer->LevelContainer])
   {
      $ItemType = $objContent->LevelContainer->LevelContainer;
   }
   else
   {
      $ItemType = "DefaultContent";
   }

   if(!$_ARCHON->PublicInterface->Templates['collections'][$ItemType])
   {
      $_ARCHON->declareError("Could not display $ItemType: $ItemType template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
      return;
   }

   // Process and send the first portion of the item template.
   // TODO: Finish content level suppression

   ob_start();
   eval($_ARCHON->PublicInterface->Templates['collections'][$ItemType]);

   $output = ob_get_clean();
   if($output)
   {
      $arrWords = $_ARCHON->createSearchWordArray($_ARCHON->QueryString);

      $count = 0;
      if(!empty($arrWords))
      {
         foreach($arrWords as $word)
         {
            if($word && $word{0} != "-")
            {
               $output = preg_replace("/(\A|\>)([^\<]*[^\w^=^\<^\+^\/]|)(" . preg_quote($word, '/') . ")(|[^\w^=^\>\+][^\>]*)(\<|\z)/ui", "$1$2<span class='highlight$count bold'>$3</span>$4$5", $output);
               $count++;
            }
         }
      }
   }

   // Break the item template where %Items% occurs
   // into two strings, containing the template data before
   // and after the processed items should be inserted.
   list($outputnow, $outputlater) = explode("#CONTENT#", $output);

   if(function_exists("template_ContentPreProcess"))
   {
      $outputnow = template_ContentPreProcess($outputnow, $objContent);
   }

   echo($outputnow);

   flush();

//   }
//   if($objContent->Enabled ||$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, READ)
//           || ($_ARCHON->Security->userHasAdministrativeAccess() && !CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS)
//           || CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS && $_ARCHON->Security->verifyRepositoryPermissions($objCollection->RepositoryID))
   if($objContent->enabled())
   {
      // Process and display all the children recursively.
      if(!empty($objContent->Content))
      {
         foreach($objContent->Content as $objChild)
         {
            findingaid_DisplayContent($objChild->ID);
         }

         /* //For deallocing as you go.
        
        $objChild = reset($objContent->Content);

        do
        {
            // Process and display the current item.
            findingaid_DisplayContent($objChild->ID);
            $objContent->Content[$objChild->ID] = NULL;
            $objCollection->Content[$objChild->ID] = NULL;

            // Advance the array pointer to the next item
            $objChild = next($objContent->Content);
        } while($objChild); */

      }

   }

   if(function_exists("template_ContentPostProcess"))
   {
      $outputlater = template_ContentPostProcess($outputlater, $objContent);
   }

   echo($outputlater);
   flush();


//    else
//    {
//
//       $outputmessage = "Information restricted, please contact us for additional information.";
//        echo($objContent);
//        echo($outputmessage);
//    }

}
?>
