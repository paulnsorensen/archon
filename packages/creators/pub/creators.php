<?php
/**
 * Output file for browsing by creator
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();


if(!$_ARCHON->PublicInterface->Templates['creators']['Creator'])
{
   $_ARCHON->declareError("Could not display Creator: Creator template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}




$in_Char = isset($_REQUEST['char']) ? $_REQUEST['char'] : NULL;

$in_Browse = isset($_REQUEST['browse']) ? true : false;


$objCreatorsTitlePhrase = Phrase::getPhrase('creators_title', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
$strCreatorsTitle = $objCreatorsTitlePhrase ? $objCreatorsTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse by Creator';

$_ARCHON->PublicInterface->Title = $strCreatorsTitle;
$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}");


if($in_Char)
{
   creators_listCreatorsForChar($in_Char);
}
elseif($in_Browse)
{
   $in_Page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

   creators_listAllCreators($in_Page);

}
else
{
   creators_main();
}


require_once("footer.inc.php");


function creators_main()
{
   global $_ARCHON;

   

   $objShowBeginningWithPhrase = Phrase::getPhrase('creators_showbeginningwith', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
   $strShowBeginningWith = $objShowBeginningWithPhrase ? $objShowBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Show Creators Beginning with';

   $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

   require_once("header.inc.php");

   $arrCreatorCount = $_ARCHON->countCreators(true);

   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");



   ?>
<div class='center'>
   <span class='bold'><?php echo($strShowBeginningWith); ?>:</span><br/><br/>
   <div class='bground beginningwith'>
         <?php
         if(!empty($arrCreatorCount['#']))
         {
            echo("<a href='?p={$_REQUEST['p']}&amp;char=" . urlencode('#'). "'>-#-</a>" . INDENT);
         }
         else
         {
            echo("-#-" . INDENT);
         }

         for($i = 65; $i < 91; $i++)
         {
            $char = chr($i);

            if(!empty($arrCreatorCount[encoding_strtolower($char)]))
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

         ?>
   </div>
</div>
   <?php
}

function creators_listCreatorsForChar($Char)
{
   global $_ARCHON;

   

   $objNavBeginningWithPhrase = Phrase::getPhrase('creators_navbeginningwith', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
   $strNavBeginningWith = $objNavBeginningWithPhrase ? $objNavBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Beginning with "$1"';
   $objCreatorsBeginningWithPhrase = Phrase::getPhrase('creators_creatorsbeginningwith', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
   $strCreatorsBeginningWith = $objCreatorsBeginningWithPhrase ? $objCreatorsBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Creators Beginning with "$1"';

   $_ARCHON->PublicInterface->addNavigation(str_replace('$1', encoding_strtoupper($Char), $strNavBeginningWith), "?p={$_REQUEST['p']}&amp;char=$Char");

   if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['CreatorList'])
   {
      $_ARCHON->declareError("Could not list Creators: CreatorList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }

   require_once("header.inc.php");
   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

   if(!$_ARCHON->Error)
   {
      $arrCreators = $_ARCHON->getCreatorsForChar($Char);

      if(!empty($arrCreators))
      {
         echo("<div class='listitemhead bold'>" . str_replace('$1', encoding_strtoupper($Char), $strCreatorsBeginningWith) . "</div><br/><br/>\n");

         echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");

         foreach($arrCreators as $objCreator)
         {
            eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['CreatorList']);
         }

         echo("</div>");
      }
   }
}

function creators_listAllCreators($Page)
{
   global $_ARCHON;

   $arrCreators = $_ARCHON->searchCreators($_REQUEST['q'], CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page-1)*CONFIG_CORE_PAGINATION_LIMIT);

   if(count($arrCreators) > CONFIG_CORE_PAGINATION_LIMIT)
   {
      $morePages = true;
      array_pop($arrCreators);
   }

// Set up a URL for any prev/next buttons or in case $Page
// is too high
   $paginationURL = 'index.php?p=' . $_REQUEST['p'].'&browse';

   if(empty($arrCreators) && $Page != 1)
   {
      header("Location: $paginationURL");
   }

   

   $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

   $_ARCHON->PublicInterface->addNavigation($strViewAll);

   require_once("header.inc.php");

   if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['CreatorList'])
   {
      $_ARCHON->declareError("Could not list Creators: CreatorList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }

   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");


   if(!$_ARCHON->Error)
   {
      if(!empty($arrCreators))
      {
         echo("<div class='listitemhead bold'>$strViewAll</div><br/><br/>\n");
         echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");

         foreach($arrCreators as $objCreator)
         {
            eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['CreatorList']);
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


require_once("footer.inc.php");


?>