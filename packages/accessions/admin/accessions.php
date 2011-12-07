<?php

/**
 * Accessions Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox
 */
isset($_ARCHON) or die();

accessions_ui_initialize();

// Determine what to do based upon user input
function accessions_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      accessions_ui_main();
   }
   else if($_REQUEST['f'] == "search")
   {
      accessions_ui_search();
   }
   else
   {
      accessions_ui_exec();
   }
}

// accessions_ui_main()
//   - purpose: Creates the primary user interface
//              for the Accessions Manager.
function accessions_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Accession');
   $_ARCHON->AdministrativeInterface->setNameField('Title');


   if($_ARCHON->AdministrativeInterface->Object->ID)
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl("$('#fInput').val('createcollection'); $('#mainform').submit(); $('#fInput').val('store')", 'createcollectionrecord');
   }

   $objNoSelectionPhrase = Phrase::getPhrase('selectone', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';


   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
   $generalSection->insertRow('enabled')->insertRadioButtons('Enabled');
   $generalSection->insertRow('accessiondate')->insertTextField('AccessionDateMonth', 2, 2)->required();
   $generalSection->getRow('accessiondate')->insertHTML(' / ');
   $generalSection->getRow('accessiondate')->insertTextField('AccessionDateDay', 2, 2)->required();
   $generalSection->getRow('accessiondate')->insertHTML(' / ');
   $generalSection->getRow('accessiondate')->insertTextField('AccessionDateYear', 4, 4)->required();
   $generalSection->insertRow('title')->insertNameField('Title');
   $generalSection->insertRow('identifier')->insertTextField('Identifier', 10, 50)->required();
   $generalSection->insertRow('inclusivedates')->insertTextField('InclusiveDates', 25, 75);
   $generalSection->insertRow('receivedextent')->insertTextField('ReceivedExtent', 5, 9);
   $generalSection->getRow('receivedextent')->insertSelect('ReceivedExtentUnitID', 'getAllExtentUnits');
   $generalSection->insertRow('unprocessedextent')->insertTextField('UnprocessedExtent', 5, 9);
   $generalSection->getRow('unprocessedextent')->insertSelect('UnprocessedExtentUnitID', 'getAllExtentUnits');


   $generalSection->insertRow('materialtypeid')->insertSelect('MaterialTypeID', 'getAllMaterialTypes');
   $generalSection->insertRow('processingpriorityid')->insertSelect('ProcessingPriorityID', 'getAllProcessingPriorities');
   $generalSection->insertRow('expectedcompletiondate')->insertTextField('ExpectedCompletionDateMonth', 2, 2);
   $generalSection->getRow('expectedcompletiondate')->insertHTML(' / ');
   $generalSection->getRow('expectedcompletiondate')->insertTextField('ExpectedCompletionDateDay', 2, 2);
   $generalSection->getRow('expectedcompletiondate')->insertHTML(' / ');
   $generalSection->getRow('expectedcompletiondate')->insertTextField('ExpectedCompletionDateYear', 4, 4);


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
   }
   else
   {
      $arrCreators = array();
   }

   $generalSection->insertRow('primary_creator')->insertSelect('PrimaryCreatorID', $arrCreators);


   $locationSection = $_ARCHON->AdministrativeInterface->insertSection('locations', 'multiple');
   $locationSection->setMultipleArguments('AccessionLocationEntry', 'LocationEntries', 'dbLoadLocationEntries');
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


   $relationSection = $_ARCHON->AdministrativeInterface->insertSection('collections', 'multiple');
   $relationSection->setMultipleArguments('AccessionCollectionEntry', 'CollectionEntries', 'dbLoadCollectionEntries');
   $relationSection->insertRow('collections_collection')->insertHierarchicalSelect(
           array('ClassificationID', 'CollectionID'),
           array('traverseClassification', 'getCollectionsForClassification'),
           array('getChildClassifications', NULL),
           array('Classification', 'Collection')
   );

   $donorInfoSection = $_ARCHON->AdministrativeInterface->insertSection('donor');
   $donorInfoSection->insertRow('donor_donor')->insertTextField('Donor');
   $donorInfoSection->insertRow('donor_donorcontactinformation')->insertTextArea('DonorContactInformation');
   $donorInfoSection->insertRow('donor_donornotes')->insertTextArea('DonorNotes');

   $accessionDescriptionSection = $_ARCHON->AdministrativeInterface->insertSection('description');
   $accessionDescriptionSection->insertRow('description_physicaldescription')->insertTextArea('PhysicalDescription');
   $accessionDescriptionSection->insertRow('description_scopecontent')->insertTextArea('ScopeContent');
   $accessionDescriptionSection->insertRow('description_comments')->insertTextArea('Comments');

   $subjectTypes = $_ARCHON->getSubjectTypeJSONList();

   $accessionDescriptionSection->insertRow('subjects')->insertAdvancedSelect('Subjects',
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

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function accessions_ui_search()
{
   global $_ARCHON;

   echo($_ARCHON->AdministrativeInterface->searchResults('searchAccessions', array('searchflags' => SEARCH_ACCESSIONS, 'classificationid' => 0, 'collectionid' => 0, 'subjectid' => 0, 'creatorid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0)));
}

function accessions_ui_exec()
{
   global $_ARCHON;

   @set_time_limit(0);
   
   $objAccession = New Accession($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objAccession = New Accession($_REQUEST);
         $objAccession->ID = $ID;
         $stored = $objAccession->dbStore();
         $ID = $objAccession->ID;

         if($stored && is_array($_REQUEST['relatedsubjectids']))
         {
            $objAccession->dbUpdateRelatedSubjects($_REQUEST['relatedsubjectids']);
         }
         if($stored && is_array($_REQUEST['relatedcreatorids']))
         {
            $objAccession->dbUpdateRelatedCreators($_REQUEST['relatedcreatorids'], array($_REQUEST['primarycreatorid']));
         }

         if($stored && is_array($_REQUEST['locationentries']) && !empty($_REQUEST['locationentries']))
         {
            foreach($_REQUEST['locationentries'] as $AccessionLocationEntryID => $array)
            {
               $array['id'] = $AccessionLocationEntryID;
               $array['accessionid'] = $ID;

               $objAccessionLocationEntry = New AccessionLocationEntry($array);

               if($array['_fdelete'])
               {
                  $objAccessionLocationEntry->dbDelete();
               }
               elseif($objAccessionLocationEntry->Content || $objAccessionLocationEntry->LocationID)
               {
                  $objAccessionLocationEntry->dbStore();
                  $objAccession->LocationEntries[] = $objAccessionLocationEntry;
               }
            }
         }

         if($stored && is_array($_REQUEST['collectionentries']) && !empty($_REQUEST['collectionentries']))
         {
            foreach($_REQUEST['collectionentries'] as $AccessionCollectionEntryID => $array)
            {
               $array['id'] = $AccessionCollectionEntryID;
               $array['accessionid'] = $ID;

               $objAccessionCollectionEntry = New AccessionCollectionEntry($array);

               if($array['_fdelete'])
               {
                  $objAccessionCollectionEntry->dbDelete();
               }
               else if($objAccessionCollectionEntry->ClassificationID || $objAccessionCollectionEntry->CollectionID)
               {
                  $objAccessionCollectionEntry->dbStore();
                  $objAccession->CollectionEntries[] = $objAccessionCollectionEntry;
               }
            }
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objAccession = New Accession($ID);
         $objAccession->dbDelete();
      }
   }
   elseif($_REQUEST['f'] == 'createcollection')
   {
      foreach($arrIDs as &$ID)
      {
         $objAccession = New Accession($ID);
         $objCollection = $objAccession->createCollection();
         if(!$_ARCHON->Error)
         {
            $msg = 'Collection Successfully Created';
            $_ARCHON->AdministrativeInterface->sendResponse($msg, array($objCollection->ID), $_ARCHON->Error, true, "index.php?p=admin/collections/collections&id={$objCollection->ID}");
            die();
         }
         else
         {
            $_ARCHON->AdministrativeInterface->sendResponse($_ARCHON->Error, array($objCollection->ID), $_ARCHON->Error);
            die();
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
      $msg = 'Accession Database Updated Successfully.';
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}