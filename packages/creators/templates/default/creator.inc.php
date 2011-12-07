<?php
/**
 * Output file for creator view
 *
 * @package Archon
 * @subpackage creators
 * @author Chris Rishel
 */

isset($_ARCHON) or die();


$objBiogHistPhrase = Phrase::getPhrase('search_bioghist', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
$strBiogHist = $objBiogHistPhrase ? $objBiogHistPhrase->getPhraseValue(ENCODE_HTML) : 'Biographical/Historical Note';
$objSearchForCreatorPhrase = Phrase::getPhrase('search_searchforcreator', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
$strSearchForCreator = $objSearchForCreatorPhrase ? $objSearchForCreatorPhrase->getPhraseValue(ENCODE_HTML) : 'Searching for Creator: $1';


echo("<h1>" . $_ARCHON->PublicInterface->Title. "</h1>\n");
echo("<div id='CreatorNote' class='bground'>");

if ($objCreator->Name)
{      
   echo ("<div class='CreatorEntry'><span class='bold'>Name:</span> ". $objCreator->toString())."</div>";
}

if ($objCreator->NameVariants)
{      
   echo ("<div class='CreatorEntry'><span class='bold'>Variant Name:</span> ". $objCreator->NameVariants)."</div>";
}

if ($objCreator->NameFullerForm)
{      
   echo ("<div class='CreatorEntry'><span class='bold'>Fuller Form:</span> ". $objCreator->NameFullerForm. "</div>");
}


if(!empty($objCreator->CreatorRelationships))
{
   ?>
<div class='CreatorEntry'><span class='bold'><a href='#' onclick="toggleDisplay('relatedcreators'); return false;"><img id='relatedcreatorsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Show Related Creators</a> <!--<span style='font-size:80%'>(links to similar collections)</span> --></span><br />
   <div class='ccardshowlist' style='display: none' id='relatedcreatorsResults'><?php echo($_ARCHON->createStringFromCreatorArray($objCreator->CreatorRelationships, "<br/>\n", LINK_TOTAL)); ?></div>
</div>
   <?php
}



if($objCreator->BiogHist)
{
   echo ("<hr><br/><div class='CreatorEntry'><span class='bold'>");

   switch ($objCreator->CreatorType)
   {
      case "Personal Name":
         echo ("Biographical Note");
         break;

      case "Family Name":
         echo ("Family History");
         break;

      case "Corporate Name" || "Unassigned" || "Name":
         echo ("Historical Note");
         break;
   }
   echo (": </span>". $objCreator->getString('BiogHist') . "</div>");
}    

if ($objCreator->Sources)
{      
   echo ("<div class='CreatorEntry'><span class='bold'>Sources:</span> ". $objCreator->getString("Sources"). "</div>");
}

if ($objCreator->BiogHistAuthor)
{      
   echo ("<div class='CreatorEntry'><span class='bold'>Note Author:</span> ". $objCreator->BiogHistAuthor. "</div>");
}

if ($objCreator->Collections || $objCreator->Books || $objCreator->DigitalContent)
{
   echo ("<hr><br/>");
}


if ($objCreator->Collections)
{
   ?>
<div class='CreatorEntry'><span class='bold'><a href='#' onclick="toggleDisplay('LinkedCollections'); return false;"><img id='LinkedCollectionsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Records or Manuscript Collections Created by <?php echo($objCreator->Name)?></a></span><br/>
   <div class='CreatorEntryShowList' style='display:none' id='LinkedCollectionsResults'><br/>
         <?php
         foreach ($objCreator->Collections as $objCollection)
         {
            echo ("&nbsp;&nbsp;".$objCollection->toString(LINK_EACH). "<br/>");
         }
         ?>
   </div>
</div>
   <?php
}
if ($objCreator->Accessions)
{
   ?>
<div class='CreatorEntry'><span class='bold'><a href='#' onclick="toggleDisplay('LinkedAccessions'); return false;"><img id='LinkedAccessionsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Unprocessed Holdings Created by <?php echo($objCreator->Name)?></a></span><br/>
   <div class='CreatorEntryShowList' style='display:none' id='LinkedAccessionsResults'><br/>
         <?php
         foreach ($objCreator->Accessions as $objAccession)
         {
            echo ("&nbsp;&nbsp;".$objAccession->toString(LINK_EACH). "<br/>");
         }
         ?>
   </div>
</div>
   <?php
}



if ($objCreator->Books)
{
   ?>
<div class='CreatorEntry'><span class='bold'><a href='#' onclick="toggleDisplay('LinkedBooks'); return false;"><img id='LinkedBooksImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Books Authored by <?php echo($objCreator->Name)?></a></span><br/>
   <div class='CreatorEntryShowList' style='display:none' id='LinkedBooksResults'><br/>
         <?php
         foreach ($objCreator->Books as $objBook)
         {
            echo ("&nbsp;&nbsp;".$objBook->toString(LINK_EACH). "<br/>");
         }
         ?>
   </div>
</div>
   <?php
}

if ($objCreator->DigitalContent)
{
   ?>
<div class='CreatorEntry'><span class='bold'><a href='#' onclick="toggleDisplay('LinkedDigitalContent'); return false;"><img id='LinkedDigitalContentImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Digital Content Created by <?php echo($objCreator->Name)?></a></span><br/>
   <div class='CreatorEntryShowList' style='display:none' id='LinkedDigitalContentResults'><br/>
         <?php
         if($containsImages)
         {
            echo("&nbsp;&nbsp;<span class='bold'><a href='index.php?p=digitallibrary/thumbnails&amp;creatorid={$objCreator->ID}'>Image Thumbnails</a></span><br/><br/>\n\n");
         } ?>
         <?php echo("&nbsp;&nbsp;<span class='bold'>Other Files:</span><br/>&nbsp;&nbsp;&nbsp;" . $_ARCHON->createStringFromDigitalContentArray($objCreator->DigitalContent, "<br/>\n&nbsp;&nbsp;&nbsp;", LINK_TOTAL));
         ?>
   </div>
</div>
   <?php
}

echo ("</div>\n<br/>");  //end CreatorNote div


//echo ("<div class='center'><br/><a href='?p=core/search&creatorid=". $objCreator->ID. "'><span class='bold'>Show Links to Records and Digital Archives Created by ". $objCreator->Name . "</a></span><br/><br/></div>");	 		

if($_ARCHON->Security->verifyPermissions(MODULE_CREATORS, READ))
{
   ?>
<div id='CreatorStaff' class='mdround'>
   <div class='ccardstafflabel'>Staff Information</div>
   <div class='ccardcontents'><br/>
      <a href='?p=creators/eac&amp;id=<?php echo($objCreator->ID); ?>&amp;templateset=eac&amp;disabletheme=1&amp;output=<?php echo(formatFileName($objCreator->getString('Name',0,false,false))); ?>'>EAC-CPF</a>
   </div>
</div>
   <?php
}

?>