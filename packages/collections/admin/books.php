<?php

/**
 * Book Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 */
isset($_ARCHON) or die();

books_ui_initialize();

// Determine what to do based upon user input
function books_ui_initialize()
{

   if(!$_REQUEST['f'])
   {
      books_ui_main();
   }
   else if($_REQUEST['f'] == "search")
   {
      books_ui_search();
   }
   else if($_REQUEST['f'] == "dialog")
   {
      books_ui_dialog();
   }
   else
   {
      books_ui_exec(); // No interface needed, include an execution file
   }
}

// books_ui_main()
//   - purpose: Books the primary user interface
//              for the Books Manager.
function books_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Book');

   $_ARCHON->AdministrativeInterface->setNameField('Title');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('title')->insertTextField('Title', 50, 100)->required();

   $generalSection->insertRow('edition')->insertTextField('Edition', 15, 15);

   $generalSection->insertRow('copynumber')->insertTextField('CopyNumber', 4, 4);

   $generalSection->insertRow('publicationdate')->insertTextField('PublicationDate', 50, 50);

   $generalSection->insertRow('placeofpublication')->insertTextField('PlaceOfPublication', 50, 50);

   $generalSection->insertRow('publisher')->insertTextField('Publisher', 50, 50);

   $generalSection->insertRow('description')->insertTextArea('Description');

   $generalSection->insertRow('notes')->insertTextArea('Notes');

   $generalSection->insertRow('numberofpages')->insertTextField('NumberOfPages', 4, 4);

   $generalSection->insertRow('series')->insertTextField('Series', 50, 50);

   $generalSection->insertRow('identifier')->insertTextField('Identifier', 10, 50);


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




   $subjectTypes = $_ARCHON->getSubjectTypeJSONList();

   $generalSection->insertRow('subjects')->insertAdvancedSelect('Subjects',
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
                       'noselection' => '{id: 0, text: "'.$strNoSelection.'"}'
                   )
               )
   ));

   $generalSection->insertRow('languages')->insertAdvancedSelect('Languages',
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

   $generalSection->insertRow('collections')->insertAdvancedSelect('Collections',
           array(
               'Class' => 'Collection',
               'RelatedArrayName' => 'Collections',
               'RelatedArrayLoadFunction' => 'dbLoadCollectionsByBook',
               'Multiple' => true,
               'toStringArguments' => array(),
               'params' => array(
                   'p' => 'admin/collections/collections',
                   'f' => 'search',
                   'searchtype' => 'json'
               )

   ));

//   $subjectsSection = $_ARCHON->AdministrativeInterface->insertSection('subjects', 'relation');
//   $subjectsSection->setRelationArguments('Subject', 'searchSubjects', 'Subjects', 'dbLoadSubjects', true, array('parentid' => NULL, 'subjecttypeid' => 0, 'showchildren' => true));
//   $subjectsSection->setAestheticRelationArguments(array(LINK_NONE, true), MODULE_SUBJECTS, 'subjects');
//   $subjectsSection->insertSearchOption('SubjectTypeID', 'getAllSubjectTypes', 'subjecttypeid');

//   $languagesSection = $_ARCHON->AdministrativeInterface->insertSection('languages', 'relation');
//   $languagesSection->setRelationArguments('Language', 'searchLanguages', 'Languages', 'dbLoadLanguages', false);
//   $languagesSection->setAestheticRelationArguments(array(), MODULE_LANGUAGES, 'core');
//
//   $collectionsSection = $_ARCHON->AdministrativeInterface->insertSection('collections', 'relation');
//   $collectionsSection->setRelationArguments('Collection', 'searchCollectionsByBook', 'Collections', 'dbLoadCollectionsByBook', false);
//   $collectionsSection->setAestheticRelationArguments(array(LINK_NONE, true), MODULE_COLLECTIONS, 'collections');
//

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function books_ui_search()
{
   global $_ARCHON;

    $_ARCHON->AdministrativeInterface->searchResults('searchBooks', array('subjectid' => 0, 'creatorid' => 0, 'languageid'=> 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function books_ui_dialog()
{
   global $_ARCHON;
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Book');
   $_ARCHON->AdministrativeInterface->setNameField('Title');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/collections/books', 'store');


   $dialogSection->insertRow('title')->insertTextField('Title', 50, 100)->required();

   $dialogSection->insertRow('edition')->insertTextField('Edition', 15, 15);

   $dialogSection->insertRow('copynumber')->insertTextField('CopyNumber', 4, 4);

   $dialogSection->insertRow('publicationdate')->insertTextField('PublicationDate', 50, 50);

   $dialogSection->insertRow('placeofpublication')->insertTextField('PlaceOfPublication', 50, 50);

   $dialogSection->insertRow('publisher')->insertTextField('Publisher', 50, 50);

   //$dialogSection->insertRow('description')->insertTextArea('Description');
   //$dialogSection->insertRow('notes')->insertTextArea('Notes');
   //$dialogSection->insertRow('numberofpages')->insertTextField('NumberOfPages', 4, 4);

   $dialogSection->insertRow('series')->insertTextField('Series', 50, 50);
   $_ARCHON->AdministrativeInterface->outputInterface();
}

function books_ui_exec()
{
   global $_ARCHON;

   $name = NULL;

   $objBook = New Book($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {

      foreach($arrIDs as &$ID)
      {
         $objBook = New Book($_REQUEST);
         $objBook->ID = $ID;
         $stored = $objBook->dbStore();
         $ID = $objBook->ID;
         $name = $objBook->getString('Title');

         if($stored && is_array($_REQUEST['relatedsubjectids']))
         {
            $objBook->dbUpdateRelatedSubjects($_REQUEST['relatedsubjectids']);
         }

         if($stored && is_array($_REQUEST['relatedlanguageids']))
         {
            $objBook->dbUpdateRelatedLanguages($_REQUEST['relatedlanguageids']);
         }

         if($stored && is_array($_REQUEST['relatedcollectionids']))
         {
            $objBook->dbUpdateRelatedCollections($_REQUEST['relatedcollectionids']);
         }

         if($stored && is_array($_REQUEST['relatedcreatorids']))
         {
            $objBook->dbUpdateRelatedCreators($_REQUEST['relatedcreatorids'], array($_REQUEST['primarycreatorid']));
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objBook = New Book($ID);
         $objBook->dbDelete();
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
      $msg = "Book Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, false, NULL, NULL, $name);
}

?>