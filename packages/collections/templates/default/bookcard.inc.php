<?php
/**
 * Book Card template
 *
 * The variable:
 *
 *
 *  $objBook
 *
 * is an instance of a Book object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Book class definition in lib/book.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();


if(defined('PACKAGE_COLLECTIONS'))
{
    $objBook->dbLoadCollections();
    
}


 echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>


<div id='ccardleft'>        <!--begin div ccardleft -->
<div id="ccardpublic" class='mdround'>  <!-- begin div ccardcontents -->
<?php


if(!empty($objBook->Creators))
{
?>
<div class='ccardcontent'><span class='ccardlabel'>Author: </span><?php echo($_ARCHON->createStringFromCreatorArray($objBook->Creators, ', ', LINK_TOTAL, TRUE)); ?></div>

<?php
}

?>
<div class='ccardcontent'><span class='ccardlabel'>Title, Edition:</span> <?php echo($objBook->toString(). ", ".$objBook->Edition); ?></div>
<?php

if($objBook->CopyNumber >= 0)
{
?>
<div class='ccardcontent'><span class='ccardlabel'>Copy Number:</span> <?php echo($objBook->CopyNumber); ?></div>
<?php
}

if($objBook->PlaceOfPublication)
{
?>
<div class='ccardcontent'><span class='ccardlabel'>Place of Publication:</span> <?php echo($objBook->PlaceOfPublication); ?></div>
<?php
}
if($objBook->Publisher)
{
?>
<div class='ccardcontent'><span class='ccardlabel'>Publisher:</span> <?php echo($objBook->Publisher); ?></div>
<?php
}
if($objBook->PublicationDate)
{
?>
<div class='ccardcontent'><span class='ccardlabel'>Publication Date:</span> <?php echo($objBook->PublicationDate); ?></div>
<?php
}

if($objBook->NumberOfPages)
{
?>
<div class='ccardcontent'><span class='ccardlabel'>Number of Pages:</span> <?php echo($objBook->NumberOfPages); ?></div>
<?php
}

if($objBook->Series)
{
?>
<div class='ccardcontent'><span class='ccardlabel'>Series:</span> <?php echo($objBook->Series); ?></div>
<?php
}

if(!empty($objBook->Subjects))

{
    $GenreSubjectTypeID = $_ARCHON->getSubjectTypeIDFromString('Genre/Form of Material');

    foreach($objBook->Subjects as $objSubject)
    {
        if($objSubject->SubjectTypeID == $GenreSubjectTypeID)
        {
            $arrGenres[$objSubject->ID] = $objSubject;
        }
        else
        {
            $arrSubjects[$objSubject->ID] = $objSubject;
        }
    }

    if(!empty($arrSubjects))
    {
    ?>
	<div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('subjects'); return false;"><img id='subjectsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Show Subjects</a> <span style='font-size:80%'>(links to similar collections)</span></span><br/>
		<div class='ccardshowlist' style='display: none' id='subjectsResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrSubjects, "<br/>\n", LINK_TOTAL)); ?></div>
	</div>
	<?php
	}
  if(!empty($arrGenres))
    {
    ?>
	<div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('genres'); return false;"><img id='genresImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Show Forms of Material</a> <span style='font-size:80%'>(links to similar genres)</span></span><br/>
		<div class='ccardshowlist' style='display: none' id='genresResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrGenres, "<br/>\n", LINK_TOTAL)); ?></div>
	</div>
	<?php
	}

}


if(!empty($objBook->Languages))


{
    ?>
	<div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('langs'); return false;"><img id='langsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Show Languages of Materials</a></span><br/>
		<div class='ccardshowlist' style='display: none' id='langsResults'><?php echo($_ARCHON->createStringFromLanguageArray($objBook->Languages, "<br/>\n", LINK_TOTAL)); ?></div>
	</div>
	<?php
	}

if ($objBook->Collections)
{
   ?>
   <div class='ccardcontent'><span class='bold'><a href='#' onclick="toggleDisplay('LinkedCollections'); return false;"><img id='LinkedCollectionsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Records or Manuscript Collections of the Book</a></span><br/>
   <div class='ccardshowlist' style='display:none' id='LinkedCollectionsResults'><br/>
   <?php        
   foreach ($objBook->Collections as $objCollection)
   {
      echo ("&nbsp;&nbsp;".$objCollection->toString(LINK_EACH). "<br/>");
   }
   echo ("</div>\n</div>");
} 
	
echo("</div>");	//end ccardleft


if($_ARCHON->Security->verifyPermissions(MODULE_BOOKS, READ))
{
	?>
	<div id='ccardstaff' class='mdround'>
	
	<div class="ccardcontents"><br/><span class='ccardlabel'>Show this record as:</span><br/><br/>
		<a href='?p=collections/marcbook&amp;id=<?php echo($objBook->ID); ?>'>MARC</a><br/> 
		<a href='?p=collections/bookcard&amp;id=<?php echo($objBook->ID); ?>&amp;templateset=kardexcontrolcard&amp;disabletheme=1'>5 by 8 Kardex</a><br/>
		<a href='?p=collections/bookcard&amp;id=<?php echo($objBook->ID); ?>&amp;templateset=draftcontrolcard&amp;disabletheme=1'>Review copy/draft</a>
	</div>
	</div>

<?php
}


echo("</div>");
echo ("<div id='ccardprintcontact' class='smround'> <a href='?p=collections/bookcard&amp;id=". $objBook->ID. "&amp;templateset=print&amp;disabletheme=1'><img src='". $_ARCHON->PublicInterface->ImagePath . "/printer.png'/></a> <a href='?p=collections/bookcard&amp;id=". $objBook->ID. "&amp;templateset=print&amp;disabletheme=1'>Printer-friendly</a> | <a href='?p=research/research&amp;f=email&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'><img src='". $_ARCHON->PublicInterface->ImagePath . "/email.png'/> </a><a href='?p=research/research&amp;f=email&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'>Email Us</a></div>");


if($objBook->Description)
{
?>
<div id="ccardscope" class="mdround">
<div class='ccardcontent' style='padding-left:.2em'><span class='ccardlabel'>Description:</span> <?php echo($objBook->getString('Description')); ?></div>

<?php
}
if($objBook->Notes)
{
?>
	<div class='ccardcontent' style='padding-left:.2em'><span class='ccardlabel'>Note:</span> <?php echo($objBook->getString('Notes')); ?></div>
<?php 
}
?>
</div>

