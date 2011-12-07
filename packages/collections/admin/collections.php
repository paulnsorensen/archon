<?php
/**
 * Collections Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

ob_implicit_flush();

collections_ui_initialize();

// Determine what to do based upon user input
function collections_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      collections_ui_main();
   }
   elseif($_REQUEST['f'] == 'search')
   {
      collections_ui_search();
   }
   elseif($_REQUEST['f'] == 'dialog')
   {
      collections_ui_dialog();
   }
   else
   {
      collections_ui_exec();
   }
}

// collections_ui_main()
//   - purpose: Creates the primary user interface
//              for the Collections Manager.
function collections_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Collection');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
   $generalSection->insertRow('title')->insertNameField('Title', 50, 150, array('change' => "if($('#SortTitleInput').val() == '') { $('#SortTitleInput').focus(); $('#SortTitleInput').val(admin_ui_truncatebbcode($(this).val())); $('#SortTitleInput').change(); }"))->required();
   $generalSection->insertRow('enabled')->insertRadioButtons('Enabled');

   if($_ARCHON->AdministrativeInterface->Object->ID)
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=collections/controlcard&id={$_ARCHON->AdministrativeInterface->Object->ID}');
                                    $(this).attr('target', '_blank');", 'publicview', false);

      $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_goto('admin/collections/collectioncontent',{displayrootcontent: true, collectionid: {$_ARCHON->AdministrativeInterface->Object->ID}});", 'editcollectioncontent');

      $action = '';

      if(CONFIG_COLLECTIONS_INVOKE_EXTERNAL_SYSTEM)
      {
         if(CONFIG_COLLECTIONS_EXTERNAL_URL_FOR_EAD_EXPORT)
         {
            $target = (CONFIG_COLLECTIONS_EXTERNAL_TARGET_FOR_EAD_EXPORT) ? CONFIG_COLLECTIONS_EXTERNAL_TARGET_FOR_EAD_EXPORT : "_blank";
            $location = preg_replace('/{ID}/u', $_ARCHON->AdministrativeInterface->Object->ID, CONFIG_COLLECTIONS_EXTERNAL_URL_FOR_EAD_EXPORT);
            $action = "window.open('$location','$target');";
         }
      }

      $_ARCHON->AdministrativeInterface->insertHeaderControl("$(this).attr('href', '?p=collections/ead&templateset=ead&disabletheme=1&id={$_ARCHON->AdministrativeInterface->Object->ID}&output=" . formatFileName($_ARCHON->AdministrativeInterface->Object->getString('SortTitle', 0, false, false)) . "');
                                    $(this).attr('target', '_blank'); $action", 'exportead');
   }
   else
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=collections/collections');
                                    $(this).attr('target', '_blank');", 'publicview', false);
   }

   $objUser = $_ARCHON->Security->Session->User;

   if(!$objUser->RepositoryLimit)
   {
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', 'getAllRepositories')->required();
   }
   elseif(count($objUser->Repositories) == 1)
   {
      $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
      $info = $objUser->Repositories[key($objUser->Repositories)]->Name;
      $generalSection->insertRow('repository')->insertInformation('Repository', $info);
      $_ARCHON->AdministrativeInterface->Object->RepositoryID = $repositoryID;
      $generalSection->insertHiddenField('RepositoryID');
   }
   else
   {
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', $objUser->Repositories)->required();
   }

   $generalSection->insertRow('classificationid')->insertHierarchicalSelect('ClassificationID', 'traverseClassification', 'getChildClassifications', 'Classification');

   $generalSection->insertRow('collectionidentifier')->insertTextField('CollectionIdentifier', 10, 50);
   $generalSection->insertRow('sorttitle')->insertTextField('SortTitle', 50, 150)->required();

   $generalSection->insertRow('normaldates');
   $generalSection->getRow('normaldates')->insertTextField('NormalDateBegin', 4, 4);
   $generalSection->getRow('normaldates')->insertHTML(' - ');
   $generalSection->getRow('normaldates')->insertTextField('NormalDateEnd', 4, 4, array('change' => "if($('#InclusiveDatesInput').val() == '') { $('#InclusiveDatesInput').focus(); $('#InclusiveDatesInput').val($('#NormalDateBeginInput').val() + '-' + $(this).val()); $('#InclusiveDatesInput').change()}"));

   $generalSection->insertRow('inclusivedates')->insertTextField('InclusiveDates', 25, 75);
   $generalSection->insertRow('predominantdates')->insertTextField('PredominantDates', 25, 50);

   $generalSection->insertRow('materialtypeid')->insertSelect('MaterialTypeID', 'getAllMaterialTypes');

   $generalSection->insertRow('extent');
   $generalSection->getRow('extent')->insertTextField('Extent', 5, 12);
   $generalSection->getRow('extent')->insertHTML(' ');
   $generalSection->getRow('extent')->insertSelect('ExtentUnitID', 'getAllExtentUnits');

   $generalSection->insertRow('findingaidauthor')->insertTextField('FindingAidAuthor', 50, 200);


   $generalSection->insertRow('creators')->insertAdvancedSelect('Creators',
           array(
               'Class' => 'Creator',
               'RelatedArrayName' => 'Creators',
               'RelatedArrayLoadFunction' => 'dbLoadCreators',
               'Multiple' => true,
               'toStringArguments' => array(),
               'params' => array(
                   'p' => 'admin/creators/creators',
                   'f' => 'search',
                   'searchtype' => 'json'
               ),
               'quickAdd' => "advSelectID =\\\"CreatorsRelatedCreatorIDs\\\"; admin_ui_opendialog(\\\"creators\\\", \\\"creators\\\");"
   ));

   if($_ARCHON->AdministrativeInterface->Object->ID)
   {
      call_user_func(array($_ARCHON->AdministrativeInterface->Object, 'dbLoadCreators'));
      $arrCreators = $_ARCHON->AdministrativeInterface->Object->Creators;
      $_ARCHON->AdministrativeInterface->Object->PrimaryCreatorID = ($_ARCHON->AdministrativeInterface->Object->PrimaryCreator) ? $_ARCHON->AdministrativeInterface->Object->PrimaryCreator->ID : 0;
      //$arrPrimaryCreators = $_ARCHON->AdministrativeInterface->Object->PrimaryCreators;
   }
   else
   {
      $arrCreators = array();
      //$arrPrimaryCreators = array();
   }

   $generalSection->insertRow('primary_creator')->insertSelect('PrimaryCreatorID', $arrCreators);

   $_ARCHON->AdministrativeInterface->setNameField('Title');

//   $creatorsSection = $_ARCHON->AdministrativeInterface->insertSection('creators', 'creatorsrelation');
//   $creatorsSection->setRelationArguments('Creator', 'searchCreators', 'Creators', 'dbLoadCreators');
//   $creatorsSection->setAestheticRelationArguments(array(), MODULE_CREATORS, 'creators');


   $descriptionSection = $_ARCHON->AdministrativeInterface->insertSection('description');
   $descriptionSection->insertRow('description_abstract')->insertTextArea('Abstract', 10, 70);
   $descriptionSection->insertRow('description_scope')->insertTextArea('Scope', 10, 70);
   $descriptionSection->insertRow('description_arrangement')->insertTextArea('Arrangement', 10, 70);
   $descriptionSection->insertRow('description_altextentstatement')->insertTextField('AltExtentStatement', 50, 200);
   $descriptionSection->insertRow('description_bioghist')->insertTextArea('BiogHist');
   $descriptionSection->insertRow('description_bioghistauthor')->insertTextField('BiogHistAuthor', 50, 100);
   $subjectTypes = $_ARCHON->getSubjectTypeJSONList();

   $descriptionSection->insertRow('subjects')->insertAdvancedSelect('Subjects',
           array(
               'Class' => 'Subject',
               'RelatedArrayName' => 'Subjects',
               'RelatedArrayLoadFunction' => 'dbLoadSubjects',
               'Multiple' => true,
               'toStringArguments' => array(LINK_NONE, true),
               'params' => array(
                   'p' => 'admin/subjects/subjects',
                   'f' => 'search',
                   'searchtype' => 'json',
                   'parentid' => '',
                   'subjecttypeid' => 0,
                   'showchildren' => true
               ),
               'quickAdd' => "advSelectID =\\\"SubjectsRelatedSubjectIDs\\\"; admin_ui_opendialog(\\\"subjects\\\", \\\"subjects\\\");",
               'searchOptions' => array(
                   array(
                       'label' => 'Subject Type',
                       'name' => 'SubjectTypeID',
                       'source' => $subjectTypes,
                       'noselection' => '{id: 0, text: "' . $strNoSelection . '"}'
                   )
               )
   ));

   $descriptionSection->insertRow('languages')->insertAdvancedSelect('Languages',
           array(
               'Class' => 'Language',
               'RelatedArrayName' => 'Languages',
               'RelatedArrayLoadFunction' => 'dbLoadLanguages',
               'Multiple' => true,
               'toStringArguments' => array(),
               'params' => array(
                   'p' => 'admin/core/ajax',
                   'f' => 'searchlanguages',
                   'searchtype' => 'json'
               )
   ));

   $locationSection = $_ARCHON->AdministrativeInterface->insertSection('locations', 'multiple');
   $locationSection->setMultipleArguments('LocationEntry', 'LocationEntries', 'dbLoadLocationEntries');
//   $locationSection->insertRow('locations_locationid')->insertSelect('LocationID', 'getAllLocations', array(true));
   $locationSection->insertRow('locations_locationid')->insertAdvancedSelect('LocationID',
           array(
               'Class' => 'Location',
               'Multiple' => false,
               'MultipleSection' => true,
               'toStringArguments' => array(),
               'params' => array(
                   'p' => 'admin/collections/locations',
                   'f' => 'search',
                   'searchtype' => 'json'
               )
   ));
   $locationSection->insertRow('locations_content')->insertTextField('Content', 10);
   $locationSection->insertRow('locations_rangevalue')->insertTextField('RangeValue', 2);
   $locationSection->insertRow('locations_section')->insertTextField('Section', 2);
   $locationSection->insertRow('locations_shelf')->insertTextField('Shelf', 2);
   $locationSection->insertRow('locations_extent');
   $locationSection->getRow('locations_extent')->insertTextField('Extent', 4, 10);
   $locationSection->getRow('locations_extent')->insertSelect('ExtentUnitID', 'getAllExtentUnits');


   $acquisitionSection = $_ARCHON->AdministrativeInterface->insertSection('acquisition');
   $acquisitionSection->insertRow('acquisition_acquisitiondate');
   $acquisitionSection->getRow('acquisition_acquisitiondate')->insertTextField('AcquisitionDateMonth', 2, 2);
   $acquisitionSection->getRow('acquisition_acquisitiondate')->insertHTML(' / ');
   $acquisitionSection->getRow('acquisition_acquisitiondate')->insertTextField('AcquisitionDateDay', 2, 2);
   $acquisitionSection->getRow('acquisition_acquisitiondate')->insertHTML(' / ');
   $acquisitionSection->getRow('acquisition_acquisitiondate')->insertTextField('AcquisitionDateYear', 4, 4);
   $acquisitionSection->insertRow('acquisition_acquisitionsource')->insertTextField('AcquisitionSource', 50, 200);
   $acquisitionSection->insertRow('acquisition_acquisitionmethod')->insertTextArea('AcquisitionMethod', 10, 70);
   $acquisitionSection->insertRow('acquisition_appraisalinfo')->insertTextArea('AppraisalInfo', 10, 70);
   $acquisitionSection->insertRow('acquisition_accrualinfo')->insertTextArea('AccrualInfo', 10, 70);
   $acquisitionSection->insertRow('acquisition_custodialhistory')->insertTextArea('CustodialHistory', 10, 70);

   $restrictionsSection = $_ARCHON->AdministrativeInterface->insertSection('restrictions');
   $restrictionsSection->insertRow('restrictions_accessrestrictions')->insertTextArea('AccessRestrictions', 10, 70);
   $restrictionsSection->insertRow('restrictions_userestrictions')->insertTextArea('UseRestrictions', 10, 70);
   $restrictionsSection->insertRow('restrictions_physicalaccess')->insertTextArea('PhysicalAccess', 10, 70);
   $restrictionsSection->insertRow('restrictions_technicalaccess')->insertTextArea('TechnicalAccess', 10, 70);

//   $booksSection = $_ARCHON->AdministrativeInterface->insertSection('books', 'relation');
//   $booksSection->setRelationArguments('Book', 'searchBooks', 'Books', 'dbLoadBooks');
//   $booksSection->setAestheticRelationArguments(array(), MODULE_BOOKS, 'collections');

   $otherSection = $_ARCHON->AdministrativeInterface->insertSection('other');
   $otherSection->insertRow('other_otherurl')->insertTextField('OtherURL', 50, 200);
   $otherSection->insertRow('other_othernote')->insertTextArea('OtherNote', 10, 70);
   $otherSection->insertRow('books')->insertAdvancedSelect('Books',
           array(
               'Class' => 'Book',
               'RelatedArrayName' => 'Books',
               'RelatedArrayLoadFunction' => 'dbLoadBooks',
               'Multiple' => true,
               'toStringArguments' => array(),
               'params' => array(
                   'p' => 'admin/collections/books',
                   'f' => 'search',
                   'searchtype' => 'json',
               ),
               'quickAdd' => "advSelectID =\\\"BooksRelatedBookIDs\\\"; admin_ui_opendialog(\\\"collections\\\", \\\"books\\\");"
   ));

   $relatedSection = $_ARCHON->AdministrativeInterface->insertSection('related');
   $relatedSection->insertRow('related_relatedmaterials')->insertTextArea('RelatedMaterials', 10, 70);
   $relatedSection->insertRow('related_relatedmaterialsurl')->insertTextField('RelatedMaterialsURL', 50, 200);
   $relatedSection->insertRow('related_relatedpublications')->insertTextArea('RelatedPublications', 10, 70);
   $relatedSection->insertRow('related_separatedmaterials')->insertTextArea('SeparatedMaterials', 10, 70);
   $relatedSection->insertRow('related_origcopiesnote')->insertTextArea('OrigCopiesNote', 10, 70);
   $relatedSection->insertRow('related_origcopiesurl')->insertTextField('OrigCopiesURL', 50, 200);
   $relatedSection->insertRow('related_preferredcitation')->insertTextArea('PreferredCitation', 10, 70);

   $findingaidSection = $_ARCHON->AdministrativeInterface->insertSection('findingaid');
   $findingaidSection->insertRow('findingaid_descriptiverulesid')->insertSelect('DescriptiveRulesID', 'getAllDescriptiveRules');
   $findingaidSection->insertRow('findingaid_processinginfo')->insertTextArea('ProcessingInfo', 10, 70);
   $findingaidSection->insertRow('findingaid_revisionhistory')->insertTextArea('RevisionHistory', 10, 70);
   $findingaidSection->insertRow('findingaid_publicationdate');
   $findingaidSection->getRow('findingaid_publicationdate')->insertTextField('PublicationDateMonth', 2, 2);
   $findingaidSection->getRow('findingaid_publicationdate')->insertHTML(' / ');
   $findingaidSection->getRow('findingaid_publicationdate')->insertTextField('PublicationDateDay', 2, 2);
   $findingaidSection->getRow('findingaid_publicationdate')->insertHTML(' / ');
   $findingaidSection->getRow('findingaid_publicationdate')->insertTextField('PublicationDateYear', 4, 4);
   $findingaidSection->insertRow('findingaid_publicationnote')->insertTextField('PublicationNote', 50, 200);
   $findingaidSection->insertRow('findingaid_findinglanguageid')->insertSelect('FindingLanguageID', 'getAllLanguages');


   if(defined('PACKAGE_DIGITALLIBRARY'))
   {
      if($_ARCHON->AdministrativeInterface->Object->ID)
      {
         $objDigitalContentPhrase = Phrase::getPhrase('digitalcontent', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strDigitalContent = $objDigitalContentPhrase ? $objDigitalContentPhrase->getPhraseValue(ENCODE_HTML) : 'digitalcontent';

         $objEditPhrase = Phrase::getPhrase('edit', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
         $strEdit = $objEditPhrase ? $objEditPhrase->getPhraseValue(ENCODE_HTML) : 'edit';

//         $digitalContentSection = $_ARCHON->AdministrativeInterface->insertSection('digitalcontent', 'custom');
         $_ARCHON->AdministrativeInterface->Object->dbLoadDigitalContent(LOADCONTENT_NONE);

         ob_start();

         if(!empty($_ARCHON->AdministrativeInterface->Object->DigitalContent))
         {
?>
            <script type="text/javascript">
               /* <![CDATA[ */
               $(function() {
                  $('.addbutton').button({
                     icons:{
                        primary:"ui-icon-plus"
                     },
                     text:false
                  });
               });
               /* ]]> */

            </script>

            <div class="infotablewrapper"><table id="digitalcontenttable" class='infotable'>
                  <tr>
                     <th style='min-width: 90%;'><?php echo($strDigitalContent); ?></th>
                     <th style='text-align:center'><a class='addbutton' href='#' style='' onclick='admin_ui_dialogcallback(function() {admin_ui_reloadsection("other", admin_ui_getboundelements(), function(){ $(".addbutton").button({icons:{primary:"ui-icon-plus"},text:false}); return false});}); admin_ui_opendialog("digitallibrary","digitallibrary", "add", "&amp;collectionid=<?php echo($_ARCHON->AdministrativeInterface->Object->ID); ?>"); return false;'>&nbsp;</a></th>
                  </tr>
      <?php
            $count = 0;
            foreach($_ARCHON->AdministrativeInterface->Object->DigitalContent as $objDigitalContent)
            {

               $strEditButton = "<a class='adminformbutton' href='index.php?p=admin/digitallibrary/digitallibrary&amp;id={$objDigitalContent->ID}' rel='external'>" . $strEdit . "</a>";

               $ListItem = "<td>" . $objDigitalContent->toString() . "</td><td style='text-align:center'>" . $strEditButton . "</td>";
               if($count % 2 == 0)
               {
                  echo("<tr class='evenrow'>{$ListItem}</tr>");
               }
               else
               {
                  echo("<tr>{$ListItem}</tr>");
               }
               $count++;
            }
      ?>
         </table></div>
<?php
         }
         else
         {
            $objAddDigitalContentPhrase = Phrase::getPhrase('adddigitalcontent', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strAddDigitalContent = $objAddDigitalContentPhrase ? $objAddDigitalContentPhrase->getPhraseValue(ENCODE_HTML) : 'adddigitalcontent';
?>
            <div style="margin:20px 0 0 40px">
               <a class='adminformbutton' href='#' onclick='admin_ui_dialogcallback(function() {admin_ui_reloadsection("other", admin_ui_getboundelements(), function(){ $(".addbutton").button({icons:{primary:"ui-icon-plus"},text:false}); return false});}); admin_ui_opendialog("digitallibrary","digitallibrary", "add", "&amp;collectionid=<?php echo($_ARCHON->AdministrativeInterface->Object->ID); ?>"); return false;'><?php echo($strAddDigitalContent); ?></a>
            </div>
<?php
         }

         $digitalContentTable = ob_get_clean();

         $otherSection->insertRow('digitalcontent')->insertHTML($digitalContentTable);
//         $digitalContentSection->setCustomArguments($digitalContentTable);
      }
   }


   if(!$_ARCHON->Security->Session->User->RepositoryLimit)
   {
      $_ARCHON->AdministrativeInterface->insertSearchOption('RepositoryID', 'getAllRepositories', 'repositoryid');
   }

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function collections_ui_dialog()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Collection');
   $_ARCHON->AdministrativeInterface->setNameField('Title');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/collections/collections', 'store');

   //The events on this field are going to be an issue
   $dialogSection->insertRow('title')->insertTextField('Title', 50, 100, array('change' => "if($('#SortTitleInput').val() == '') { $('#SortTitleInput').val($(this).val()); }"))->required();
   $dialogSection->insertRow('enabled')->insertRadioButtons('Enabled');


   $objUser = $_ARCHON->Security->Session->User;

   if(!$objUser->RepositoryLimit)
   {
      $dialogSection->insertRow('repository')->insertSelect('RepositoryID', 'getAllRepositories')->required();
//      $repositoryID = $_REQUEST['repositoryid'] ? $_REQUEST['repositoryid'] : $_ARCHON->AdministrativeInterface->Object->RepositoryID;
   }
   elseif(count($objUser->Repositories) == 1)
   {
      $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
      $info = $objUser->Repositories[key($objUser->Repositories)]->Name;
      $dialogSection->insertRow('repository')->insertInformation('Repository', $info);
      $_ARCHON->AdministrativeInterface->Object->RepositoryID = $repositoryID;
      $dialogSection->insertHiddenField('RepositoryID');
   }
   else
   {
      $dialogSection->insertRow('repository')->insertSelect('RepositoryID', $objUser->Repositories)->required();
//      $repositoryID = $_REQUEST['repositoryid'] ? $_REQUEST['repositoryid'] : $_ARCHON->AdministrativeInterface->Object->RepositoryID;
   }

   $dialogSection->insertRow('classificationid')->insertHierarchicalSelect('ClassificationID', 'traverseClassification', 'getChildClassifications', 'Classification');

   $dialogSection->insertRow('collectionidentifier')->insertTextField('CollectionIdentifier', 10, 50);
   $dialogSection->insertRow('sorttitle')->insertTextField('SortTitle', 25, 50)->required();

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function collections_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchCollections', array('searchflags' => SEARCH_COLLECTIONS, 'subjectid' => 0, 'creatorid' => 0, 'languageid' => 0, 'repositoryid' => 0, 'classificationid' => 0, 'locationid' => 0, 'rangevalue' => NULL, 'section' => NULL, 'shelf' => NULL, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function collections_ui_exec()
{
   global $_ARCHON;

   @set_time_limit(0);

   $name = NULL;

   $objCollection = New Collection($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   $Location = NULL;
   $Target = NULL;

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objCollection = New Collection($_REQUEST);
         $objCollection->ID = $ID;
         $stored = $objCollection->dbStoreCollection();
         $ID = $objCollection->ID;
         $name = $objCollection->getString('Title');

         if(is_array($_REQUEST['relatedsubjectids']))
         {
            $objCollection->dbUpdateRelatedSubjects($_REQUEST['relatedsubjectids']);
         }
         if(is_array($_REQUEST['relatedlanguageids']))
         {
            $objCollection->dbUpdateRelatedLanguages($_REQUEST['relatedlanguageids']);
         }
         if(is_array($_REQUEST['relatedbookids']))
         {
            $objCollection->dbUpdateRelatedBooks($_REQUEST['relatedbookids']);
         }
         if(is_array($_REQUEST['relatedcreatorids']))
         {
            $objCollection->dbUpdateRelatedCreators($_REQUEST['relatedcreatorids'], array($_REQUEST['primarycreatorid']));
         }



         if($stored && is_array($_REQUEST['locationentries']) && !empty($_REQUEST['locationentries']))
         {
            foreach($_REQUEST['locationentries'] as $LocationEntryID => $array)
            {
               $array['id'] = $LocationEntryID;
               $array['collectionid'] = $ID;

               $objLocationEntry = New LocationEntry($array);

               if($array['_fdelete'])
               {
                  $objLocationEntry->dbDelete();
               }
               elseif($objLocationEntry->Content || $objLocationEntry->LocationID)
               {
                  $objLocationEntry->dbStore();
                  $objCollection->LocationEntries[] = $objLocationEntry;
               }
            }
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      $deleted_ids = array();
      foreach($arrIDs as $ID)
      {
         $objCollection = New Collection($ID);
         if($objCollection->dbDeleteCollection())
         {
            $deleted_ids[] = $ID;
         }

         if(CONFIG_COLLECTIONS_INVOKE_EXTERNAL_SYSTEM)
         {
            if(CONFIG_COLLECTIONS_EXTERNAL_URL_FOR_COLLECTION_DELETION)
            {
               $Target = (CONFIG_COLLECTIONS_EXTERNAL_TARGET_FOR_COLLECTION_DELETION) ? CONFIG_COLLECTIONS_EXTERNAL_TARGET_FOR_COLLECTION_DELETION : "_blank";
               $Location = preg_replace('/{IDs}/u', implode(',', $deleted_ids), CONFIG_COLLECTIONS_EXTERNAL_URL_FOR_COLLECTION_DELETION);
               $action = "window.open('$location','$target');";
            }
         }
      }
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
   }

   if($_ARCHON->Error)
   {
      $msg = $_ARCHON->Error;
   }
   else
   {
      $msg = 'Collection Database Updated Successfully.';
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, false, $Location, $Target, $name);
}
?>