<?php
/**
 * Collection-level template for finding aid output
 *
 * The variable:
 *
 *  $objCollection
 *
 * is an instance of a Collection object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Collection class definition in lib/collection.inc.php
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

$repositoryid = $objCollection->RepositoryID;

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");
?>

<?php if($objCollection->FindingAidAuthor)
{ ?><p style="font-weight:bold" class="center">By <?php echo($objCollection->getString('FindingAidAuthor')); ?></p> <?php } ?>


<div id='ccardprintcontact' class='smround'>
   <a href='?p=collections/findingaid&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=print&amp;disabletheme=1'>
      <img src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/printer.png' alt='Printer-friendly' />
   </a>
   <a href='?p=collections/findingaid&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=print&amp;disabletheme=1'>
      Printer-friendly
   </a> |
   <a href='?p=collections/research&amp;f=email&amp;repositoryid=<?php echo($repositoryid); ?>&amp;referer=<?php echo(urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])); ?>'>
      <img src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/email.png' alt='Email Us' />
   </a>
   <a href='?p=collections/research&amp;f=email&amp;repositoryid=<?php echo($repositoryid); ?>&amp;referer=<?php echo(urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])); ?>'>
      Contact Us About This Collection
   </a>
</div>

<h2 style='text-align:left'><a name="overview"></a>Collection Overview</h2>
<div style="margin-left:40px">
   <?php if($objCollection->Title)
   { ?><p><span class='bold'>Title:</span> <?php echo($objCollection->toString());
} ?></p>
   <?php if($objCollection->PredominantDates)
   { ?><p><span class="bold">Predominant Dates:</span><?php echo($objCollection->PredominantDates); ?></p><?php } ?>
      <?php if($objCollection->Classification)
      { ?><p><span class='bold'>ID:</span> <?php echo($objCollection->Classification->toString(LINK_NONE, true, false, true, false)); ?>/<?php echo($objCollection->getString('CollectionIdentifier')); ?></p><?php } ?>
   <?php if(!empty($objCollection->PrimaryCreator))
   { ?><p><span class='bold'>Primary Creator:</span> <?php echo($objCollection->PrimaryCreator->toString(LINK_TOTAL)); ?></p><?php }
   
   $arrOtherCreators = array();
   if(!empty($objCollection->Creators))
   {
      foreach($objCollection->Creators as $objCreator)
      {
         if($objCreator->ID != $objCollection->PrimaryCreator->ID)
         {
            $arrOtherCreators[$objCreator->ID] = $objCreator;
         }
      }
   }

   if(!empty($arrOtherCreators))
   {
      ?>
      <p><span class='bold'>Other Creators:</span> <?php echo($_ARCHON->createStringFromCreatorArray($arrOtherCreators, ", ", LINK_TOTAL)); ?></p>

      <?php
   }
   if($objCollection->Extent)
   {
      ?><p><span class='bold'>Extent:</span> <?php echo(preg_replace('/\.(\d)0/', ".$1", $objCollection->getString('Extent'))); ?> <?php
   echo($objCollection->getString('ExtentUnit'));
   if($objCollection->AltExtentStatement)
   {
      echo(". <a href='#AltExtentStatement'>More info below.</a>");
   }
      ?></p><?php } ?>



   <?php if($objCollection->Arrangement)
   { ?> <p><span class='bold'>Arrangement:</span> <?php echo($objCollection->getString('Arrangement')); ?></p><?php } ?>
   <?php if($objCollection->AcquisitionDate)
   { ?> <p><span class='bold'>Date Acquired:</span> <?php
   echo($objCollection->getString('AcquisitionDateMonth') . '/' . $objCollection->getString('AcquisitionDateDay') . '/' . $objCollection->getString('AcquisitionDateYear'));
   if($objCollection->AccrualInfo)
   {
      echo(". <a href='#accruals'>More info below under Accruals.</a>");
   }
      ?></p><?php
}

if(!empty($objCollection->Subjects))
{
   $GenreSubjectTypeID = $_ARCHON->getSubjectTypeIDFromString('Genre/Form of Material');

   foreach($objCollection->Subjects as $objSubject)
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
         <p><span class='bold'>Subjects:</span> <?php echo($_ARCHON->createStringFromSubjectArray($arrSubjects, ", ", LINK_TOTAL)); ?></p>

         <?php
      }
      if(!empty($arrGenres))
      {
         ?>
         <p><span class='bold'>Forms of Material:</span> <?php echo($_ARCHON->createStringFromSubjectArray($arrGenres, ", ", LINK_TOTAL)); ?></p>

         <?php
      }
   }
   ?>
<?php if(!empty($objCollection->Languages))
{ ?><p><span class='bold'>Languages:</span> <?php echo($_ARCHON->createStringFromLanguageArray($objCollection->Languages, ', ', LINK_TOTAL)); ?></p> <?php } ?>




</div>
<?php if($objCollection->Abstract)
{ ?><h2 style='text-align:left'><a name="abstract"></a>Abstract</h2><div style="margin-left:40px"><?php echo($objCollection->getString('Abstract')); ?></div><?php } ?>
<?php if($objCollection->Scope)
{ ?><h2 style='text-align:left'><a name="scopecontent"></a>Scope and Contents of the Materials</h2><div style="margin-left:40px"><?php echo($objCollection->getString('Scope')); ?></div><?php } ?>
<?php if($objCollection->BiogHist)
{ ?><h2 style='text-align:left'><a name="collectionhist"></a>Collection Historical Note</h2><div style="margin-left:40px"><?php echo($objCollection->getString('BiogHist')); ?></div><?php } ?>
<?php
if($objCollection->PrimaryCreator->BiogHist)
{
   ?><h2 style='text-align:left'><a name="bioghist"></a><?php
   if(trim($objCollection->PrimaryCreator->CreatorType) == "Corporate Name")
   {
      echo ("Historical Note");
   }
   elseif(trim($objCollection->PrimaryCreator->CreatorType) == "Family Name")
   {
      echo ("Family History");
   }
   else
   {
      echo ("Biographical Note");
   }
   ?>
   </h2><div style="margin-left:40px">
   <?php
   echo($objCollection->PrimaryCreator->getString('BiogHist'));
   ?>
   </div>
   <?php
}
?>
<?php if(!empty($arrSubjects))
{ ?><h2 style='text-align:left'><a name="subjects"></a>Subject/Index Terms</h2><div style="margin-left:40px"><p><?php echo($_ARCHON->createStringFromSubjectArray($arrSubjects, "<br/>", LINK_TOTAL)); ?></p></div><?php } ?>

<?php
if(!empty($objCollection->AccessRestrictions) || !empty($objCollection->UseRestrictions) || !empty($objCollection->PhysicalAccessNote) || !empty($objCollection->TechnicalAccessNote) || !empty($objCollection->AcquisitionSource) || !empty($objCollection->AcquisitionMethod) || !empty($objCollection->AppraisalInformation) || !empty($objCollection->OrigCopiesNote) || !empty($objCollection->OrigCopiesURL) || !empty($objCollection->RelatedMaterials) || !empty($objCollection->RelatedMaterialsURL) || !empty($objCollection->RelatedPublications) || !empty($objCollection->PreferredCitation) || !empty($objCollection->ProcessingInfo) || !empty($objCollection->RevisionHistory))
//admin info exists
{
   ?>
   <h2 style='text-align:left'><a name='admininfo'></a>Administrative Information</h2><div style="margin-left:40px">            
         <?php
         if($objCollection->Repository)
         {
            ?>
         <p><span class='bold'><a name='repository'></a>Repository:</span>
         <?php echo$objCollection->Repository->getString('Name'); ?>
         </p>
         <?php
      }

      if($objCollection->AccrualInfo)
      {
         ?>
         <p><span class='bold'><a name='accruals'></a>Accruals:</span>
         <?php echo($objCollection->getString('AccrualInfo')); ?>
         </p>
         <?php
      }

      if($objCollection->AltExtentStatement)
      {
         ?>
         <p><span class='bold'><a name='AltExtentStatement'></a>Alternate Extent Statement:</span>
         <?php echo($objCollection->getString('AltExtentStatement')); ?>
         </p>
         <?php
      }

      if($objCollection->AccessRestrictions)
      {
         ?>
         <p><span class='bold'>Access Restrictions:</span>
         <?php echo($objCollection->getString('AccessRestrictions')); ?>
         </p>
         <?php
      }
      if($objCollection->UseRestrictions)
      {
         ?>
         <p><span class='bold'>Use Restrictions:</span>
         <?php echo ($objCollection->getString('UseRestrictions')); ?>
         </p>
         <?php
      }
      if($objCollection->PhysicalAccess)
      {
         ?>
         <p><span class='bold'>Physical Access Note:</span>
         <?php echo($objCollection->getString('PhysicalAccess')); ?>
         </p>
         <?php
      }
      if($objCollection->TechnicalAccess)
      {
         ?>
         <p><span class='bold'>Technical Access Note: </span>
         <?php echo($objCollection->getString('TechnicalAccess')); ?>
         </p>

         <?php
      }
      if($objCollection->AcquisitionSource)
      {
         ?>
         <p><span class='bold'>Acquisition Source: </span>
         <?php echo($objCollection->getString('AcquisitionSource')); ?>
         </p>
         <?php
      }
      if($objCollection->AcquisitionMethod)
      {
         ?>
         <p><span class='bold'>Acquisition Method: </span>
         <?php echo($objCollection->getString('AcquisitionMethod')); ?>
         </p>
         <?php
      }
      if($objCollection->AppraisalInfo)
      {
         ?>
         <p><span class='bold'>Appraisal Information: </span>
         <?php echo($objCollection->getString('AppraisalInfo')); ?>
         </p>
         <?php
      }
      if($objCollection->SeparatedMaterials)
      {
         ?>
         <p><span class='bold'>Separated Materials:</span>
         <?php echo($objCollection->getString('SeparatedMaterials')); ?>
         </p>

         <?php
      }
      if($objCollection->OrigCopiesNote || $objCollection->OrigCopiesURL)
      {
         ?>
         <p><span class='bold'>Original/Copies Note:</span>
            <?php
            if($objCollection->OrigCopiesNote)
            {
               echo($objCollection->getString('OrigCopiesNote') . "  ");
            }
            if($objCollection->OrigCopiesURL)
            {
               echo("For more information please see <a href='{$objCollection->getString('OrigCopiesURL')}'>{$objCollection->getString('OrigCopiesURL')}</a>.");
            }
            ?>
         </p>
         <?php
      }
      if($objCollection->RelatedMaterials || $objCollection->RelatedMaterialsURL)
      {
         ?>
         <p><span class='bold'>Related Materials: </span>
            <?php
            if($objCollection->RelatedMaterials)
            {
               echo($objCollection->getString('RelatedMaterials') . "  ");
            }
            if($objCollection->RelatedMaterialsURL)
            {
               echo("For more information please see <a href='{$objCollection->getString('RelatedMaterialsURL')}'>{$objCollection->getString('RelatedMaterialsURL')}</a>.");
            }
            ?>
         </p>
         <?php
      }
      if($objCollection->RelatedPublications)
      {
         ?>
         <p><span class='bold'>Related Publications:</span>
         <?php echo($objCollection->getString('RelatedPublications')); ?>
         </p>
         <?php
      }
      if($objCollection->PreferredCitation)
      {
         ?>
         <p><span class='bold'>Preferred Citation:</span>
         <?php echo($objCollection->getString('PreferredCitation')); ?>
         </p>
         <?php
      }

      if($objCollection->ProcessingInfo)
      {
         ?>
         <p><span class='bold'>Processing Information:</span>
         <?php echo($objCollection->getString('ProcessingInfo')); ?>
         </p>
         <?php
      }
      if($objCollection->RevisionHistory)
      {
         ?>
         <p><span class='bold'>Finding Aid Revision History:</span>
         <?php echo($objCollection->getString('RevisionHistory')); ?>
         </p>
         <?php
      }
      if($objCollection->OtherNote)
      {
         ?>
         <p><span class='bold'>Other Note:</span>
         <?php echo($objCollection->getString('OtherNote')); ?>
         </p>
         <?php
      }
      if($objCollection->OtherURL)
      {
         ?>
         <p><span class='bold'>Other URL:</span> <a href="<?php echo ($objCollection->OtherURL); ?>">
         <?php echo($objCollection->getString('OtherURL')); ?>
            </a></p>
      <?php
   }
   ?>
   </div>

   <?php
}
?>

<?php if(!empty($objCollection->Content))
{ ?> <hr style="width: 70%" class='center' /> <h2 style='text-align:left'><a name="boxfolder"></a>Box and Folder Listing</h2> <?php } ?>
<?php
if(!$_ARCHON->PublicInterface->DisableTheme)
{
   $_ARCHON->PublicInterface->DisableTheme = true;

   $arrLinks = array();
   foreach($arrRootContent as $ID => $objContent)
   {
      if($ID != $in_RootContentID && $objContent->enabled())
      {
         $strLink = "[<a href='?p=collections/findingaid&amp;id=$objCollection->ID&amp;q=$_ARCHON->QueryStringURL&amp;rootcontentid=$ID#id$ID'>" . $objContent->toString() . "</a>]";
      }
      else
      {
         $strLink = '[' . $objContent->toString() . ']';
      }

      $arrLinks[] = $strLink;
   }

   $strDivision = reset($arrRootContent)->LevelContainer ? reset($arrRootContent)->LevelContainer->getString('LevelContainer') : '';
   $strFindingAidLinks = "<br/><span class='bold'>Browse by $strDivision:</span><br/><br/>\n";
   $strFindingAidLinks .= implode(",<br/>\n", $arrLinks);

   if($in_RootContentID)
   {
      $strFindingAidLinks .= ",<br/>\n" . "[<a href='?p=collections/findingaid&amp;id=$objCollection->ID&amp;q=$_ARCHON->QueryStringURL'>" . All . "</a>]<br/>\n";
   }
   else
   {
      $strFindingAidLinks .= ",<br/>\n[All]<br/>\n";
   }


   $_ARCHON->PublicInterface->DisableTheme = false;
}
else
{
   $strFindingAidLinks = '';
}

echo($strFindingAidLinks . "<br/>\n");

$contentCount = $objCollection->countContent();
if($contentCount > 0)
{
   echo("<dl>#CONTENT#</dl>");
}

if($contentCount > 20)
{
   echo($strFindingAidLinks . "\n");
}
?>