<?php
/**
 * Accessions template
 *
 * The variable:
 *
 *  $objAccession
 *
 * is an instance of a Accession object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Accession class definition in lib/collection.inc.php
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
 * @author Kyle Fox, Chris Prom
 */
isset($_ARCHON) or die();

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>

<div id='accessionleft'>
   
<div id="accessionpublic" class="mdround">  

<div class='accessioncontent'><div id="accessionnotice" class="smround"><span class='accessionlabel'>NOTE:</span> All or part of the materials may not be immediately available for research.  Please contact us for information about these materials.</div></div>

<?php

if($objAccession->Title)
{
?>
<div class='accessioncontent'><span class='accessionlabel'>Title:</span> <?php echo($objAccession->toString()); ?></div>
<?php
}



if(!empty($objAccession->Creators))
{
?>
<div class='accessioncontent'><span class='accessionlabel'>Created by: </span><?php echo($_ARCHON->createStringFromCreatorArray($objAccession->Creators, ', ', LINK_TOTAL, TRUE)); ?></div>

<?php
}

if($objAccession->PrimaryCreator->BiogHist)
{
?>

<div class='accessioncontent'><span class='accessionlabel'><a href='#' onclick="toggleDisplay('BiogHist'); return false;"><img id='BiogHistImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />


<?php
	if (trim($objAccession->PrimaryCreator->CreatorType)=="Corporate Name")
     	{echo ("Show Historical Note");}
    elseif (trim($objAccession->PrimaryCreator->CreatorType)=="Family Name")
    	{echo ("Show Family History");}
    else
    	{echo ("Show Biographical Note");}
?>
</a></span><br/>
			<div class='accessionshowlist' style='display:none' id='BiogHistResults'>
			<br/>
			<?php
			echo($objAccession->PrimaryCreator->BiogHist);
			if ($objAccession->PrimaryCreator->Sources) {echo("<span class='bold'>Sources:</span><br/>". $objAccession->PrimaryCreator->Sources);}
			?>
			</div>
</div>
<?php
}


if($objAccession->ReceivedExtent)
{
?>
<div class='accessioncontent'><span class='accessionlabel'>Received Extent:</span> <?php echo(preg_replace('/\.(\d)0/', ".$1", $objAccession->ReceivedExtent)); ?> <?php echo($objAccession->ReceivedExtentUnit); ?></div>
<?php
}


if(!empty($objAccession->CollectionEntries))
{
?>
<div class='accessioncontent'><span class='accessionlabel'>Related to:</span>
<?php
foreach($objAccession->CollectionEntries as $objAccessionCollectionEntry)
        {
	$strEntry = '';

	        if ($objAccessionCollectionEntry->ClassificationID)
	        {
	         $strEntry .=$objAccessionCollectionEntry->Classification->toString(LINK_EACH, false, true, false, false);
	        }      
	        if ($objAccessionCollectionEntry->ClassificationID && $objAccessionCollectionEntry->CollectionID)
	        {
	        $strEntry.= $_ARCHON->PublicInterface->Delimiter;
	        }
	        if($objAccessionCollectionEntry->CollectionID)
            {
                $strEntry .= $objAccessionCollectionEntry->Collection->toString(LINK_EACH);
            }
            if($objAccessionCollectionEntry->PrimaryCollection)
            {
                $strEntry .= " (Primary)";
            }

            echo("<br/>&nbsp;&nbsp;".$strEntry);
        }
?>
</div>
<?php
}



if(!empty($objAccession->Subjects))

{
    $GenreSubjectTypeID = $_ARCHON->getSubjectTypeIDFromString('Genre/Form of Material');

    foreach($objAccession->Subjects as $objSubject)
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
	<div class='accessioncontent'><span class='accessionlabel'><a href='#' onclick="toggleDisplay('subjects'); return false;"><img id='subjectsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Show Subjects</a> <span style='font-size:80%'>(links to similar collections)</span></span><br/>
		<div class='accessionshowlist' style='display: none' id='subjectsResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrSubjects, "<br/>\n", LINK_TOTAL)); ?></div>
	</div>
	<?php
	}
  if(!empty($arrGenres))
    {
    ?>
	<div class='accessioncontent'><span class='accessionlabel'><a href='#' onclick="toggleDisplay('genres'); return false;"><img id='genresImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Show Forms of Material</a> <span style='font-size:80%'>(links to similar genres)</span></span><br/>
		<div class='accessionshowlist' style='display: none' id='genresResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrGenres, "<br/>\n", LINK_TOTAL)); ?></div>
	</div>
	<?php
	}

}


if (!empty($objAccession->AccessionDate) || !empty($objAccession->ProcessingPriority) || !empty($objAccession->Donor) || !empty($objAccession->DonorNotes) || !empty($objAccession->DonorContactInformation) || !empty($objAccession->Material) || !empty($objAccession->ExpectedCompletionDate))
    //admin info exists
{
 	?>

	<div class='accessioncontent'><span class='accessionlabel'><a href='#' onclick="toggleDisplay('otherinformation'); return false;"><img id='otherinformationImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Show Administrative Information</a></span><br/>
      <div class='accessionshowlist' style='display:none' id='otherinformationResults'>

    <?php


   


	if($objAccession->AccessionDate)
	{
	?>
	<div class='accessioncontent'><span class='accessionlabel'>Acquired:</span>
	<?php
    	if($objAccession->AccessionDate) { echo($objAccession->AccessionDateMonth . '/' . $objAccession->AccessionDateDay . '/' . $objAccession->AccessionDateYear . " ");}
	?>
	</div>
	<?php
	}

  

    if($objAccession->MaterialType)
    {
    ?>
            <div class='accessioncontent'><span class='accessionlabel'>Material Type:</span> <?php echo($objAccession->getString('MaterialType')); ?></div>
    <?php
    }

   
   

    echo("</div>");  // ending accessionshowlist
echo("</div>");	  // ending admininfo content
}
echo("</div>");	//ending accessionpublic

if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
{
	echo("<div id='accessionstaff' class='mdround'><div class='accessionconent'><span class='accessionstafflabel'>Staff Information</span></div>");
	

if($objAccession->UnprocessedExtent > 0)
    {
    ?>
            <div class='accessioncontent'><span class='accessionlabel'>Unprocessed Extent: </span><?php echo($objAccession->UnprocessedExtent . " " . $objAccession->UnprocessedExtentUnit) ; ?></div>
    <?php
    }

else

    {
    ?>
            <div class='accessioncontent'><span class='accessionlabel'>These materials have been processed.</span></div>
    <?php
    }
  

	if($objAccession->ProcessingPriority && $objAccession->UnprocessedExtent > 0)
    {
    ?>
            <div class='accessioncontent'><span class='accessionlabel'>Processing Priority: </span><?php echo($objAccession->ProcessingPriority); ?></div>
    <?php
    }

  if($objAccession->ExpectedCompletionDate && $objAccession->UnprocessedExtent > 0)
	{
	?>
	<div class='accessioncontent'><span class='accessionlabel'>Expected Date of Completion:</span>
	<?php
    	if($objAccession->ExpectedCompletionDate) { echo($objAccession->ExpectedCompletionDateMonth . '/' . $objAccession->ExpectedCompletionDateDay . '/' . $objAccession->ExpectedCompletionDateYear . " ");}
	?>
	</div>
	<?php
	}    
    if($objAccession->Donor)
    {
    ?>
            <div class='accessioncontent'><span class='accessionlabel'>Donor: </span>
	   <?php
        echo($objAccession->getString('Donor'));
	    ?>
	   </div>
    <?php
    }

    if($objAccession->DonorContactInformation)
    {
    ?>
            <div class='accessioncontent'><span class='accessionlabel'>Donor Contact Information:</span> <?php echo($objAccession->getString('DonorContactInformation')); ?></div>
    <?php
    }

    if($objAccession->DonorNotes)
    {
    ?>
            <div class='accessioncontent'><span class='accessionlabel'>Donor Notes:</span> <?php echo($objAccession->getString('DonorNotes')); ?></div>
    <?php
    }

    if(!empty($objAccession->LocationEntries))
    {

?>
<div class='accessioncontent'><span class='accessionlabel'>Storage Location(s):</span>

<table id='locationtable' border='1'>
       <tr>
        <th>Content</th>
          <th>Location</th>
          <th>Range</th>
          <th>Section</th>
          <th>Shelf</th>
          <th>Extent</th>
      	</tr>
        <tr>
          <td>
            <?php echo($_ARCHON->createStringFromLocationEntryArray($objAccession->LocationEntries, '&nbsp;</td></tr><tr><td>', LINK_EACH, false, '&nbsp;</td><td>')); ?>
          </td>
        </tr>
      </table>
<?php
    }
    else
    {
?>
  <p>No locations are listed for this record series.</p>
<?php
    }
?>
</div>
</div> <!--end accession staff -->

<?php
}
?>
</div> <!--end accession left -->
<?php



if($objAccession->PhysicalDescription || $objAccession->ScopeContent || $objAccession->Comments)
{
?>

<div id="accessionscope" class="mdround">

<?php

    if($objAccession->PhysicalDescription)
    {
?>

<div class='accessioncontent' style='padding-left:.2em'><span class='accessionlabel'>Physical Description:</span> <?php echo($objAccession->getString('PhysicalDescription',true)); ?></div>

<?php
    }

    if($objAccession->ScopeContent)
    {
?>

<div class='accessioncontent' style='padding-left:.2em'><span class='accessionlabel'>Scope and Contents:</span> <?php echo($objAccession->getString('ScopeContent',true)); ?></div>

<?php
    }

    if($objAccession->Comments)
    {
?>

<div class='accessioncontent' style='padding-left:.2em'><span class='accessionlabel'>Comments:</span> <?php echo($objAccession->getString('Comments',true)); ?></div>

<?php
    }

?>
</div>
<?php
}



