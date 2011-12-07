<?php
/**
 * Control Card template for "illinois" templateset
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
 * @author Chris Rishel, Chris Prom, Paul Sorensen
 */
isset($_ARCHON) or die();

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");
?>

<div id='ccardleft'>
   <div id="ccardpublic" class='mdround'>
      <?php
      if($objCollection->Title)
      {
         ?>
         <div class='ccardcontent'><span class='ccardlabel'>Title:</span> <?php echo($objCollection->toString()); ?></div>
         <?php
      }

      if($objCollection->Classification)
      {
         ?>
         <div class='ccardcontent'><span class='ccardlabel'>Series Number:</span> <?php echo($objCollection->Classification->toString(LINK_NONE, true, false, true, false)); ?>/<?php echo($objCollection->getString('CollectionIdentifier')); ?></div>
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
         if($objCollection->Extent)
         {
            ?>
         <div class='ccardcontent'><span class='ccardlabel'>Volume:</span> <?php echo(preg_replace('/\.(\d)0/', ".$1", $objCollection->getString('Extent'))) . " " . $objCollection->getString('ExtentUnit'); ?>
         </div>
   <?php
}

if($objCollection->AltExtentStatement)
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionAltExtent'); return false;"><img id='CollectionAltExtentImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />
   <?php
   echo ("More Extent Information");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionAltExtentResults'>
   <?php echo($objCollection->getString('AltExtentStatement')); ?>
            </div>
         </div>
   <?php
}

if($objCollection->PredominantDates)
{
   ?>
         <div class='ccardcontent'><span class='ccardlabel'>Predominant Dates:</span> <?php echo($objCollection->getString('PredominantDates')); ?></div>
         <?php
      }

      if($objCollection->Arrangement)
      {
         ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionArrangement'); return false;"><img id='CollectionArrangementImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />
   <?php
   echo ("Arrangement");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionArrangementResults'>
   <?php echo($objCollection->getString('Arrangement')); ?>
            </div>
         </div>
   <?php
}

if($objCollection->Abstract)
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('CollectionAbstract'); return false;"><img id='CollectionAbstractImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' />
   <?php
   echo ("Abstract");
   ?>
               </a></span>
            <div class='ccardshowlist' style='display:none' id='CollectionAbstractResults'>
   <?php echo($objCollection->getString('Abstract')); ?>
            </div>
         </div>
   <?php
}


if(!empty($objCollection->Creators))
{
   ?>
         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('creators'); return false;"><img id='creatorsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon'/> Creator(s)</a></span><br/>
            <div class='ccardshowlist' style="display: none;" id="creatorsResults"><?php echo($_ARCHON->createStringFromDigitalContentArray($objCollection->Creators, "<br/>\n", LINK_TOTAL)); ?></div></div>

   <?php
}

if($objCollection->PrimaryCreator->BiogHist)
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('BiogHist'); return false;"><img id='BiogHistImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon'/>

   <?php
   if(trim($objCollection->PrimaryCreator->CreatorType) == "Family Name")
   {
      echo ("Family History");
   }
   elseif(trim($objCollection->PrimaryCreator->CreatorType) == "Corporate Name")
   {
      echo ("Adminstrative History");
   }
   else
   {
      echo ("Biographical Note");
   }
   ?>
               </a></span><br/>
            <div class='ccardshowlist' style='display:none' id='BiogHistResults'>

   <?php
   echo($objCollection->PrimaryCreator->getString('BiogHist'));
   if($objCollection->PrimaryCreator->Sources)
   {
      echo("<br/><br/><span class='bold'>Sources:</span><br/>" . $objCollection->PrimaryCreator->getString('Sources'));
   }
   ?>
            </div>

         </div>
   <?php
}


if($objCollection->AccessRestrictions)
{
   ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('restriction'); return false;"><img id='restrictionImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon'/> Access Restrictions</a></span><br/>
            <div class='ccardshowlist' style="display: none;" id="restrictionResults"><?php echo($objCollection->getString('AccessRestrictions')); ?></div>
         </div>
   <?php
}

if(!empty($objCollection->Subjects))
{
   $GenreSubjectTypeID = $_ARCHON->getSubjectTypeIDFromString('Genre/Form of Material');

   foreach($objCollection->Subjects as $objSubject)  //filters out papers as genre since it is so common
   {
      if($objSubject->SubjectTypeID == $GenreSubjectTypeID && preg_replace("/<a.*<\/a>/", "", $objSubject) != "Papers")
      {
         $arrGenres[$objSubject->ID] = $objSubject;
      }
      elseif($objSubject != "Papers")
      {
         $arrSubjects[$objSubject->ID] = $objSubject;
      }
   }

   if(!empty($arrSubjects))
   {
      ?>
            <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('subjects'); return false;"><img id='subjectsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon'/> Subjects</a> <span style='font-size:80%'>(links to similar materials)</span></span><br/>
               <div class='ccardshowlist' style='display: none' id='subjectsResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrSubjects, "<br/>\n", LINK_TOTAL)); ?></div>
            </div>
      <?php
   }
   if(!empty($arrGenres))
   {
      ?>
            <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('genres'); return false;"><img id='genresImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon'/> Special Formats</a> <span style='font-size:80%'>(links to similar genres)</span></span><br/>
               <div class='ccardshowlist' style='display: none' id='genresResults'><?php echo($_ARCHON->createStringFromSubjectArray($arrGenres, "<br/>\n", LINK_TOTAL)); ?></div>
            </div>
      <?php
   }
}

if(!empty($objCollection->Languages))  //show only non-english
{
   if(isset($objCollection->Languages[2081])){
      unset($objCollection->Languages[2081]);
   }
   
   if(!empty($objCollection->Languages))
   {
      ?>
            <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('langs'); return false;"><img id='langsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon'/> Special Languages</a><span style='font-size:80%'> (non-English)</span></span><br/>
               <div class='ccardshowlist' style='display: none' id='langsResults'><?php echo($_ARCHON->createStringFromLanguageArray($objCollection->Languages, "<br/>\n", LINK_TOTAL)); ?></div>
            </div>
            <?php
         }
      }

      if($objCollection->Books)
      {
         ?>
         <div class='ccardcontent'><span class='bold'><a href='#' onclick="toggleDisplay('LinkedBooks'); return false;"><img id='LinkedBooksImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Books </a></span><br/>
            <div class='ccardshowlist' style='display: none' id='LinkedBooksResults'><?php echo($_ARCHON->createStringFromBookArray($objCollection->Books, "<br/>\n", LINK_TOTAL)); ?></div>
         </div>

         <?php
      }

      if(!empty($objCollection->BiogHist) || !empty($objCollection->UseRestrictions) || !empty($objCollection->PhysicalAccess) || !empty($objCollection->TechnicalAccess) || !empty($objCollection->PhysicalAccessNote) || !empty($objCollection->TechnicalAccessNote) || !empty($objCollection->AcquisitionSource) and $_ARCHON->Security->userHasAdministrativeAccess() || !empty($objCollection->AcquisitionMethod) || !empty($objCollection->AppraisalInformation) || !empty($objCollection->OrigCopiesNote) || !empty($objCollection->OrigCopiesURL) || !empty($objCollection->RelatedMaterials) || !empty($objCollection->RelatedMaterialsURL) || !empty($objCollection->RelatedPublications) || !empty($objCollection->PreferredCitation) || !empty($objCollection->ProcessingInfo) || !empty($objCollection->RevisionHistory))
//admin info exists
      {
         ?>

         <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('otherinformation'); return false;"><img id='otherinformationImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon'/> Administrative Information</a></span><br/>
            <div class='ccardshowlist' style='display:none' id='otherinformationResults'>

         <?php
         if($objCollection->BiogHist)
         {
            ?>

                  <div class='ccardcontent'><span class='ccardlabel'>Adiminsrative/Biographical History:</span>
                  <?php
                  echo($objCollection->getString('BiogHist'));
                  if($objCollection->BiogHistAuthor)
                  {
                     echo(" <span class='bold'> Note Author:</span> " . $objCollection->getString('BiogHistAuthor'));
                  }
                  ?>
                  </div>

                     <?php
                  }



                  if($objCollection->UseRestrictions)
                  {
                     ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Rights:</span> <?php echo($objCollection->getString('UseRestrictions')); ?></div>
                  <?php
               }

               if($objCollection->PhysicalAccess)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Access Notes: </span><?php echo($objCollection->getString('PhysicalAccess')); ?></div>
                  <?php
               }

               if($objCollection->TechnicalAccess)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Technical Notes: </span><?php echo($objCollection->getString('TechnicalAccess')); ?></div>

                  <?php
               }

               if($objCollection->AcquisitionSource and $_ARCHON->Security->userHasAdministrativeAccess() || $objCollection->AcquisitionMethod)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Acquisition Note: </span>
                  <?php
                  if($objCollection->AcquisitionSource && $_ARCHON->Security->userHasAdministrativeAccess())
                  {
                     echo("&nbsp;<em>Source:</em> " . $objCollection->getString('AcquisitionSource') . ".<br/>");
                  }
                  if($objCollection->AcquisitionMethod)
                  {
                     echo($objCollection->getString('AcquisitionMethod'));
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
                     echo("<br/>For more information please see <a href='{$objCollection->getString('OrigCopiesURL')}'>{$objCollection->getString('OrigCopiesURL')}</a>.");
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
                     echo("<br/>For more information please see <a href='{$objCollection->getString('RelatedMaterialsURL')}'>{$objCollection->getString('RelatedMaterialsURL')}</a>.");
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
                  <div class='ccardcontent'><span class='ccardlabel'>Preferred Citation:</span> <?php echo($objCollection->getString('PreferredCitation')); ?></div>
                  <?php
               }


               if($objCollection->ProcessingInfo)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Processing Note</span>: <?php echo($objCollection->getString('ProcessingInfo')); ?></div>
                  <?php
               }


               if($objCollection->RevisionHistory)
               {
                  ?>
                  <div class='ccardcontent'><span class='ccardlabel'>Finding Aid Revisions:</span> <?php echo($objCollection->getString('RevisionHistory')); ?></div>
                  <?php
               }
               echo("</div>");  // ending ccardshowlist
               echo("</div>");  // ending admininfo content
            }

            if(!empty($arrDisplayAccessions))
            {
               ?>
               <div class='ccardcontent'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('accessions'); return false;"><img id='subjectsImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> Unprocessed Materials<?php
            if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
            {
               echo (" and Processed Accessions");
            }
               ?></a></span><br/>

               <?php
               echo ("<div class='ccardshowlist' style='display: none' id='accessionsResults'>");

               foreach($arrDisplayAccessions as $objAccession)
               {
                  echo($objAccession->toString(LINK_EACH) . "<br/>\n");
                  $ResultCount++;
               }
               ?>

               </div>
            </div>
                  <?php
               }



               echo("</div>"); //ending public div



               if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))
               {
                  echo("<div id='ccardstaff' class='mdround'><div class='ccardstafflabel'>Staff Information</div>");
                  if(!empty($objCollection->LocationEntries))
                  {
                     ?>
               <div class='ccardcontent'><br/><span class='ccardlabel'>Storage Locations:</span></div>
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
            <div class="ccardcontents"><br/><span class='ccardlabel'>Show this record as:</span><br/><br/>
               <a href='?p=collections/ead&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=ead&amp;disabletheme=1&amp;output=<?php echo(formatFileName($objCollection->getString('SortTitle', 0, false, false))); ?>'>EAD</a><br/>
               <a href='?p=collections/marc&amp;id=<?php echo($objCollection->ID); ?>'>MARC</a><br/>
               <a href='?p=collections/controlcard&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=kardexcontrolcard&amp;disabletheme=1'>5 by 8 Kardex</a><br/>
               <a href='?p=collections/controlcard&amp;id=<?php echo($objCollection->ID); ?>&amp;templateset=draftcontrolcard&amp;disabletheme=1'>Review copy/draft</a>
            </div>
         </div>   <!--end ccardstaffdiv -->

            <?php
         }
         else            //user is not authenticated
         {
            if(!empty($objCollection->LocationEntries))
            {
               ?>
            <div id='ccardstaff' class='mdround'><span class='ccardlabel'>Available for use at:</span><br/><br/>


               <table id='locationtable' border='1' style='margin-left:0'>
                  <tr>

                     <th style='width:400px'>Service Location</th>
                     <th style='width:100px'>Boxes</th>
                  </tr>



      <?php
      foreach($objCollection->LocationEntries as $loc)
      {
         echo("<tr>");

         if($loc->LocationID < 5 || ($loc->LocationID > 7 and $loc->LocationID < 22))
         {
            echo("<td>Archives Research Center, 1707 S. Orchard</td>");
         }
         elseif($loc->LocationID == 23)
         {
            echo("<td>SACAM, Band Building</td>");
         }
         elseif($loc->LocationID == 33)
         {
            echo("<td>Online: See links above or <a style='font-weight:bold' href='http://www.library.uiuc.edu/email-ahx.php'>contact us for help.</a></td>");
         }
         elseif($loc->LocationID >= 27)
         {
            echo("<td>19 Library, 1408 W. Gregory Drive</td>");
         }
         else
         {
            echo("<td>Offsite: Prior notice required</td>");
         }
         echo ("<td>" . $loc->Content . "</td></tr>");
      }
      echo ('</table>');
   }
   else
   {
      echo("<div id='ccardstaff'><span class='ccardlabel'>Service Location:</span><br/>Please <a href='http://www.library.uiuc.edu/arhives/email-ahx.php'>contact the Archives</a> for assistance. </span>");
   }
   ?>

         </div> <!--end ccardstaffdiv -->
               <?php
            }


            echo("</div>"); //ending left div

            echo ("<div id='ccardprintcontact' class='smround'> <a href='?p=research/research&amp;f=email&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'><img src='" . $_ARCHON->PublicInterface->ImagePath . "/email.png' alt='email' /> </a> <a href='http://www.library.uiuc.edu/archives/email-ahx.php'>Email us about these ");

            if($objCollection->MaterialType == 'Official Records--Non-University' || $objCollection->MaterialType == 'Official Records')
            {
               echo ('records');
            }
            elseif($objCollection->MaterialType == 'Publications')
            {
               echo ('publications');
            }
            else
            {
               echo ('papers');
            }

            echo("</a> | <a href='?p=collections/controlcard&amp;id=" . $objCollection->ID . "&amp;templateset=print&amp;disabletheme=1'><img src='" . $_ARCHON->PublicInterface->ImagePath . "/printer.png' alt='printer' /></a> <a href='?p=collections/controlcard&amp;id=$objCollection->ID&amp;templateset=print&amp;disabletheme=1'>Printer-friendly</a></div>");  //ending printcontact div


            if($objCollection->Scope || !empty($objCollection->Content) || ($objCollection->DigitalContent || $containsImages) || !empty($objCollection->OtherURL))
            {
               ?>
         <div id="ccardscope" class="mdround">
         <?php
         if($objCollection->Scope)
         {
            ?>
               <div class='ccardcontent expandable' style='padding-left:.2em'><span class='ccardlabel'>Description:</span> <?php echo($objCollection->getString('Scope')); ?></div>
            <?php
         }
         if($objCollection->DigitalContent || $containsImages)
         {
            ?>

               <div class='ccardcontent' style='padding-left:.2em'><span class='ccardlabel'><a href='#' onclick="toggleDisplay('digitalcontent'); return false;"><img id='digitalcontentImage' src='<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif' alt='expand icon' /> On-line Images / Records</a></span><br/>
                  <div class='ccardshowlist' style="display: none;" id="digitalcontentResults">
            <?php
            if($containsImages)
            {
               echo("<span class='bold'><a href='index.php?p=digitallibrary/thumbnails&amp;collectionid={$objCollection->ID}'>Images</a></span> (browse thumbnails)<br/>\n\n");
            }
            if($objCollection->DigitalContent)
            {
               echo("<br/><span class='bold'>Documents and Files:</span><br/></br>&nbsp;" . $_ARCHON->createStringFromDigitalContentArray($objCollection->DigitalContent, "<br/>\n&nbsp;", LINK_TOTAL));
            }
            ?>
                  </div>
               </div>
               <?php
            }
            if(!empty($objCollection->Content))
            {
               ?>
               <div class='ccardcontent'><span class='ccardlabel'><a href='?p=collections/findingaid&amp;id=<?php echo($objCollection->ID); ?>&amp;q=<?php echo($_ARCHON->QueryStringURL); ?>'>Detailed Description</a></span><br/>
      <?php
      $DisableTheme = $_ARCHON->PublicInterface->DisableTheme;
      $_ARCHON->PublicInterface->DisableTheme = true;

      foreach($objCollection->Content as $ID => $objContent)
      {
         if(!$objContent->ParentID)
         {
            if($objContent->enabled())
            {
               echo("<span class='ccardserieslist'><a href='?p=collections/findingaid&amp;id=$objCollection->ID&amp;q=$_ARCHON->QueryStringURL&amp;rootcontentid=$ID#id$ID'>" . $objContent->toString() . "</a></span><br/>\n");
            }
            else
            {

               $objInfoRestrictedPhrase = Phrase::getPhrase('informationrestricted', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
               $strInfoRestricted = $objInfoRestrictedPhrase ? $objInfoRestrictedPhrase->getPhraseValue(ENCODE_HTML) : 'Information restricted, please contact us for additional information.';
               echo("<span class='ccardserieslist'>{$strInfoRestricted}</span><br/>\n");
            }
         }
      }

      $_ARCHON->PublicInterface->DisableTheme = $DisableTheme;
      ?>
               </div>
                  <?php
               }


               if(!empty($objCollection->OtherURL))
               {
                  $onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GACollectionsURL) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GACollectionsURL}\");'" : "";
                  ?>
               <div id='ccardboxlist' style='margin-top:.7em'><span class='ccardlabel'><a href='<?php echo(trim($objCollection->OtherURL)); ?>' <?php echo($onclick); ?>>Download Box / Folder List</a><span style='font-size:80%'>&nbsp;(pdf)</span></span></div>
                  <?php
               }
               ?>
         </div> <!--end ccard scope -->
               <?php
            }
            ?>
