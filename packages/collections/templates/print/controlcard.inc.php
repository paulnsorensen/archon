<?php
/**
 * Control Card template
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

$objCollection->dbLoadDigitalContent();
foreach($objCollection->DigitalContent as $ID => $objDigitalContent)
{
   if(!$objDigitalContent->Browsable && !$_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
   {
      unset($objCollection->DigitalContent[$ID]);
   }
}

header('Content-type: text/html; charset=UTF-8');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
   <head><title><?php echo(strip_tags($_ARCHON->PublicInterface->Title)); ?></title>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <style type='text/css'>

         #ccardrepositoryinfo

         {
            float:left;
            clear:left;
            font-weight:bold;
            font-size:1em;
            margin-bottom:.5em;
         }

         #ccardprintcontact
         {
            float:right;
            clear:right;
            font-weight:bold;
            margin-bottom:1em;
         }

         #ccardtitle
         {
            float:left;
            clear:left;
            margin: .5em .5em .5em 0;
            font-weight:bold;
            text-align:center;
            font-size:1.2em;
            font-family: "Verdana", sans-serif;

         }

         #ccardwrapper
         {
            max-width:800px;
            float:left;
            clear:left;
         }

         #ccardscope
         {
            float:left;
            clear:left;
            padding: 0 .5em;
            /*border-style:outset inset inset outset;
            border-width:2px;
            border-color:black;*/
            border: 2px solid #ccc;
            line-height: 1.5em;
         }


         #ccardmain
         {
            float:left;
            clear:left;
            margin-left: 1em;
            width:100%;
         }



         #ccardcontents
         {
            float:left;
            clear:right;
            margin-left: 1em;
         }


         #ccardstaff
         {
            float:left;
            clear:right;
            margin-left: 1em;
         }

         #alternateccards
         {
            margin-left: 1em;
         }


         .ccardlabel
         {
            font-weight:bold;
         }

         .ccardcontent
         {
            display:block;
            float:left;
            clear:left;
            margin: .5em .5em .5em 0;
            width:100%;
         }


         .ccardcontactinfo
         {
            display:block;
            float:left;
            clear:left;
            margin-left:.5em;
            width:100%;
         }

         .ccardserieslist
         {
            font-size:90%;
            margin-left:1em;
            text-indent:-.5em;
         }

         .ccardshowlist
         {
            font-size:90%;
            margin-left:1em;
         }

         #locationtable
         {
            border-collapse: collapse;
         }

         body
         {
            font-family: "Times", serif;
            background-color: #FFFFFF;
            padding: 0 0 0 0;
         }

         .cart
         {
            display:none;
         }

         .edit
         {
            display:none;
         }


      </style>


      <meta name="robots" content="noindex, nofollow"/>
   </head>
   <body>

      <div id='ccardtop'>

         <?php
         if($objCollection->Title)
         {
         ?>
            <div id='ccardtitle'><?php echo($objCollection->toString()); ?></div>
<?php
         }
?>


         <div id='ccardprintcontact'>[<?php echo("<a href='?p=collections/controlcard&amp;id=" . $objCollection->ID . "'>"); ?>Back to Formatted Version</a>]</div>

      </div>  <!-- end ccardtop -->

<?php
         if($objCollection->Scope)
         {
?>
            <div id='ccardscope'>
               <div class='ccardcontent'><span class='ccardlabel'>Brief Description:</span> <?php echo($objCollection->getString('Scope')); ?></div>
            </div>
<?php
         }
?>

         <div id="ccardmain">  <!-- begin div for righthand information -->


<?php
         if(!empty($objCollection->Repository) || !empty($objCollection->Address))
         {
?>
            <div class='ccardcontent'><span class='ccardlabel'>Held at:</span></div>


<?php
            if(!empty($objCollection->Repository))
            {
?>
               <div class='ccardcontactinfo'><?php echo($objCollection->Repository ? $objCollection->Repository->getString('Name') : ''); ?></div>
         <?php
            }


            if(!empty($objCollection->Repository->Address))
            {
         ?>
               <div class='ccardcontactinfo'><?php echo($objCollection->Repository->Address); ?></div>
         <?php
            }


            if(!empty($objCollection->Repository->Address2))
            {
         ?>
               <div class='ccardcontactinfo'><?php echo($objCollection->Repository->Address2); ?></div>
         <?php
            }

            if(!empty($objCollection->Repository->City))
            {
         ?>
               <div class='ccardcontactinfo'><?php echo($objCollection->Repository->City . ', ' . $objCollection->Repository->State . ' ' . $objCollection->Repository->ZIPCode . ' ' . $objCollection->Repository->ZIPPlusFour); ?></div>
         <?php
            }


            if(!empty($objCollection->Repository->Phone))
            {
         ?>
               <div class='ccardcontactinfo'><?php echo('Phone: ' . $objCollection->Repository->Phone); ?></div>
         <?php
            }


            if(!empty($objCollection->Repository->PhoneExtension))
            {
         ?>
               <div class='ccardcontactinfo'><?php echo('extension ' . $objCollection->Repository->PhoneExtension); ?></div>
         <?php
            }


            if(!empty($objCollection->Repository->Fax))
            {
         ?>
               <div class='ccardcontactinfo'><?php echo('Fax: ' . $objCollection->Repository->Fax); ?></div>
         <?php
            }

            if(!empty($objCollection->Repository->Email))
            {
         ?>
               <div class='ccardcontactinfo'><?php echo('Email: ' . str_replace("@", " [at] ", $objCollection->Repository->Email)); ?></div>
         <?php
            }
         }

         if(!empty($objCollection->Classification))
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Record Series Number:</span> <?php echo($objCollection->Classification->toString(LINK_NONE, true, false, true, false)); ?>/<?php echo($objCollection->getString('CollectionIdentifier')); ?></div>
         <?php
         }


         if(!empty($objCollection->Creators))
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Created by: </span><?php echo($_ARCHON->createStringFromCreatorArray($objCollection->Creators, ', ', LINK_NONE, TRUE)); ?></div>

         <?php
         }


         if($objCollection->Extent)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Volume:</span> <?php echo(preg_replace('/\.(\d)0/', ".$1", $objCollection->Extent)); ?> <?php echo($objCollection->getString('ExtentUnit')); ?></div>
         <?php
         }


         if($objCollection->AcquisitionDate || $objCollection->AccrualInfo)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Acquired:</span>
         <?php
            if($objCollection->AcquisitionDate)
            {
               echo($objCollection->getString('AcquisitionDateMonth') . '/' . $objCollection->getString('AcquisitionDateDay') . '/' . $objCollection->getString('AcquisitionDateYear') . ".  ");
            }
            if($objCollection->AccrualInfo)
            {
               echo($objCollection->getString('AccrualInfo'));
            }
         ?>
         </div>
<?php
         }

         if(!empty($objCollection->OtherURL))
         {
            $pdf = encoding_substr($objCollection->OtherURL, -4, 0) == '.pdf' ? "<span style='font-size:80%'> (pdf)</span>" : '';
?>
            <div class='ccardcontent'><span class='ccardlabel'>More information is available at <a href='<?php echo($objCollection->getString('OtherURL')); ?>'><?php echo($objCollection->getString('OtherURL')); ?></a><?php echo($pdf); ?></span></div>
<?php
         }
?>


<?php
         if($objCollection->Arrangement)
         {
?>
            <div class='ccardcontent'><span class='ccardlabel'>Arrangement:</span> <?php echo($objCollection->getString('Arrangement')); ?></div>
         <?php
         }


         if($objCollection->PrimaryCreator->BiogHist)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>
         <?php
            if((trim($objCollection->PrimaryCreator->CreatorType) == "Offical Records--Non-University") || (trim($objCollection->PrimaryCreator->CreatorType) == "Offical Records") || (trim($objCollection->PrimaryCreator->CreatorType) == "Publications"))
            {
               echo ("Historical Note for ");
            }
            elseif(trim($objCollection->PrimaryCreator->CreatorType) == "Family Name")
            {
               echo ("Family History for ");
            }
            else
            {
               echo ("Biographical Note for ");
            }

            echo ($objCollection->PrimaryCreator->toString());
         ?>
               </span>: <?php echo($objCollection->PrimaryCreator->getString('BiogHist')); ?>
            </div>
         <?php
         }



         if($objCollection->AccessRestrictions)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Access Restrictions:</span> <?php echo($objCollection->getString('AccessRestrictions')); ?></div>
         <?php
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
               <div class='ccardcontent'><span class='ccardlabel'>Subject Index</span><br/>
                  <div class='ccardshowlist' id='subjectsResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrSubjects, "<br/>\n", LINK_NONE)); ?></div>
               </div>
         <?php
            }
            if(!empty($arrGenres))
            {
         ?>
               <div class='ccardcontent'><span class='ccardlabel'>Genres/Forms of Material</span><br/>
                  <div class='ccardshowlist'><?php echo($_ARCHON->createStringFromSubjectArray($arrGenres, "<br/>\n", LINK_NONE)); ?></div>
               </div>
         <?php
            }
         }


         if(!empty($objCollection->Languages))
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Languages of Materials</span><br/>
               <div class='ccardshowlist'><?php echo($_ARCHON->createStringFromLanguageArray($objCollection->Languages, "<br/>\n", LINK_NONE)); ?></div>
            </div>
         <?php
         }



         if($objCollection->UseRestrictions)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Rights/Use Restrictions:</span> <?php echo($objCollection->getString('UseRestrictions')); ?></div>
         <?php
         }

         if($objCollection->PhysicalAccess)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Physical Access Notes: </span><?php echo($objCollection->getString('PhysicalAccess')); ?></div>
         <?php
         }

         if($objCollection->TechnicalAccess)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Technical Access Notes: </span><?php echo($objCollection->getString('TechnicalAccess')); ?></div>

         <?php
         }

         if($objCollection->AcquisitionSource || $objCollection->AcquisitionMethod)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Acquisition Notes: </span>
            <?php
            if($objCollection->AcquisitionSource)
            {
               echo($objCollection->getString('AcquisitionSource'));
            }
            if($objCollection->AcquisitionMethod)
            {
               echo("&nbsp;&nbsp;" . $objCollection->getString('AcquisitionMethod'));
            }
            ?>
            </div>
<?php
         }

         if($objCollection->AppraisalInformation)
         {
?>
            <div class='ccardcontent'><span class='ccardlabel'>Appraisal Notes:</span> <?php echo($objCollection->getString('AppraisalInformation')); ?></div>
<?php
         }

         if($objCollection->OrigCopiesNote || $objCollection->OrigCopiesURL)
         {
?>
         <div class='ccardcontent'><span class='ccardlabel'>Other Formats:</span>
            <?php
            if($objCollection->OrigCopiesNote)
            {
               echo($objCollection->getString('OrigCopiesNote'));
            }
            if($objCollection->OrigCopiesURL)
            {
               echo(" For more information please see <a href='{$objCollection->getString('OrigCopiesURL')}'>{$objCollection->getString('OrigCopiesURL')}</a>.");
            }
            ?>
            </div>
<?php
         }

         if($objCollection->RelatedMaterials || $objCollection->RelatedMaterialsURL)
         {
?>
         <div class='ccardcontent'><span class='ccardlabel'>Related Materials:</span>
            <?php
            if($objCollection->RelatedMaterials)
            {
               echo($objCollection->getString('RelatedMaterials'));
            }
            if($objCollection->RelatedMaterialsURL)
            {
               echo(" For more information please see <a href='{$objCollection->getString('RelatedMaterialsURL')}'>{$objCollection->getString('RelatedMaterialsURL')}</a>.");
            }
            ?>
            </div>
         <?php
         }


         if($objCollection->RelatedPublications)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Related Publications:</span> <?php echo($objCollection->getString('RelatedPublications')); ?></div>
         <?php
         }


         if($objCollection->PreferredCitation)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>PreferredCitation:</span> <?php echo($objCollection->getString('PreferredCitation')); ?></div>
         <?php
         }


         if($objCollection->ProcessingInformation)
         {
         ?>
            <div class='ccardcontent'><span class='ccardlabel'>Processing Note</span>: <?php echo($objCollection->getString('ProcessingInformation')); ?></div>
         <?php
         }


         if($objCollection->RevisionHistory)
         {
         ?>
               <div class='ccardcontent'><span class='ccardlabel'>Finding Aid Revisions:</span> <?php echo($objCollection->getString('RevisionHistory')); ?></div>
      <?php
         }
      ?>
         </div>  <!--ending ccardcontents div -->
      <?php
         if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
         {
            echo("<div id='ccardstaff'><p class='ccardlabel'>Staff Information:</p>");
            if(!empty($objCollection->LocationEntries))
            {
      ?>
               <table id='locationtable' border='1'>
                  <tr>
                     <th>Boxes</th>
                     <th>Location</th>
                     <th>Range</th>
                     <th>Section</th>
                     <th>Shelf</th>
                     <th>Extent</th>
                  </tr>
                  <tr>
                     <td>
      <?php echo($_ARCHON->createStringFromLocationEntryArray($objCollection->LocationEntries, '&nbsp;</td></tr><tr><td>', LINK_EACH, false, '&nbsp;</td><td>')); ?>
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
<?php
         }
?>
   </body>
</html>







