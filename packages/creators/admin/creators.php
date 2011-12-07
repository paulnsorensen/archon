<?php

/**
 * Creator Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

creators_ui_initialize();

// Determine what to do based upon user input
function creators_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      creators_ui_main();
   }
   elseif($_REQUEST['f'] == "search")
   {
      creators_ui_search();
   }
   elseif($_REQUEST['f'] == "dialog")
   {
      creators_ui_dialog();
   }
   else
   {
      creators_ui_exec();
   }
}

// creators_ui_main()
//   - purpose: Creates the primary user interface
//              for the Creator Manager.
function creators_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Creator');

   $_ARCHON->AdministrativeInterface->setNameField('Name');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
   $generalSection->insertRow('name')->insertTextField('Name', 50, 100)->required();


   if(!$_ARCHON->Security->Session->User->RepositoryLimit)
   {
      $generalSection->insertRow('repositoryid')->insertSelect('RepositoryID', 'getAllRepositories')->required();
   }
   else
   {
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', $_ARCHON->Security->Session->User->Repositories)->required();
   }

   $generalSection->insertRow('dates')->insertTextField('Dates', 50, 50);
   $generalSection->insertRow('creatortypeid')->insertSelect('CreatorTypeID', 'getAllCreatorTypes')->required();
   $generalSection->insertRow('creatorsourceid')->insertSelect('CreatorSourceID', 'getAllCreatorSources')->required();
   $generalSection->insertRow('namefullerform')->insertTextField('NameFullerForm', 50, 100);
   $generalSection->insertRow('namevariants')->insertTextField('NameVariants', 50, 200);
   $generalSection->insertRow('identifier')->insertTextField('Identifier', 10, 50);

   if($_ARCHON->AdministrativeInterface->Object->ID)
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=creators/creator&id={$_ARCHON->AdministrativeInterface->Object->ID}');
                                    $(this).attr('target', '_blank');", 'publicview', false);

      $_ARCHON->AdministrativeInterface->insertHeaderControl("$(this).attr('href', '?p=creators/eac&templateset=eac&disabletheme=1&id={$_ARCHON->AdministrativeInterface->Object->ID}&output=" . formatFileName($_ARCHON->AdministrativeInterface->Object->getString('Name', 0, false, false)) . "');
                                    $(this).attr('target', '_blank');", 'exporteac');
   }
   else
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=creators/creators');
                                    $(this).attr('target', '_blank');", 'publicview', false);
   }

   $creatorNotesSection = $_ARCHON->AdministrativeInterface->insertSection('creatornotes');
   $creatorNotesSection->insertRow('bioghist')->insertTextArea('BiogHist');
   $creatorNotesSection->insertRow('sources')->insertTextArea('Sources');
   $creatorNotesSection->insertRow('bioghistauthor')->insertTextField('BiogHistAuthor', 30, 100);

   $relatedCreatorSection = $_ARCHON->AdministrativeInterface->insertSection('relatedcreator', 'multiple');
   $relatedCreatorSection->setMultipleArguments('CreatorRelationship', 'CreatorRelationships', 'dbLoadRelatedCreatorsForToString');
//   $relatedCreatorSection->insertRow('creators_list')->insertSelect('RelatedCreatorID', 'getAllCreators', array(true));
   $relatedCreatorSection->insertRow('creators_list')->insertAdvancedSelect('RelatedCreatorID',
           array(
               'Class' => 'Creator',
               'Multiple' => false,
               'MultipleSection' => true,
               'toStringArguments' => array(),
               'params' => array(
                   'p' => 'admin/creators/creators',
                   'f' => 'search',
                   'searchtype' => 'json'
               )
   ));
   $relatedCreatorSection->insertRow('creator_relationship')->insertSelect('CreatorRelationshipTypeID', 'getAllCreatorRelationshipTypes');
   $relatedCreatorSection->insertRow('creator_description')->insertTextArea('Description');

   $controlInformationSection = $_ARCHON->AdministrativeInterface->insertSection('controlinformation');
   $controlInformationSection->insertRow('languageid')->insertAdvancedSelect('LanguageID',
           array(
               'Class' => 'Language',
               'Multiple' => false,
               'params' => array(
                   'p' => 'admin/core/ajax',
                   'f' => 'searchlanguages',
                   'searchtype' => 'json'
               )
   ));
   $controlInformationSection->insertRow('scriptid')->insertAdvancedSelect('ScriptID',
           array(
               'Class' => 'Script',
               'Multiple' => false,
               'params' => array(
                   'p' => 'admin/core/ajax',
                   'f' => 'searchscripts',
                   'searchtype' => 'json'
               )
   ));


   $_ARCHON->AdministrativeInterface->outputInterface();
}

function creators_ui_dialog()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Creator');
   $_ARCHON->AdministrativeInterface->setNameField('Name');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/creators/creators', 'store');

   $dialogSection->insertRow('name')->insertTextField('Name', 50, 100)->required();
   $dialogSection->insertRow('dates')->insertTextField('Dates', 50, 50);
   $dialogSection->insertRow('creatortypeid')->insertSelect('CreatorTypeID', 'getAllCreatorTypes')->required();
   $dialogSection->insertRow('creatorsourceid')->insertSelect('CreatorSourceID', 'getAllCreatorSources')->required();

   if(!$_ARCHON->Security->Session->User->RepositoryLimit)
   {
      $dialogSection->insertRow('repositoryid')->insertSelect('RepositoryID', 'getAllRepositories')->required();
   }
   else
   {
      $dialogSection->insertRow('repository')->insertSelect('RepositoryID', $_ARCHON->Security->Session->User->Repositories)->required();
   }

   $dialogSection->insertRow('bioghist')->insertTextArea('BiogHist');

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function creators_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchCreators', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function creators_ui_exec()
{
   global $_ARCHON;

   $name = NULL;

   $objCreator = New Creator($_REQUEST);
   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objCreator = New Creator($_REQUEST);
         $objCreator->ID = $ID;
         $stored = $objCreator->dbStore();
         $name = $objCreator->getString('Name');

         $ID = $objCreator->ID;

         if($stored && is_array($_REQUEST['creatorrelationships']) && !empty($_REQUEST['creatorrelationships']))
         {
            foreach($_REQUEST['creatorrelationships'] as $CreatorRelationshipID => $array)
            {
               $array['id'] = $CreatorRelationshipID;
               $array['creatorid'] = $ID;

               $objCreatorRelationship = New CreatorRelationship($array);

               if($array['_fdelete'])
               {
                  $objCreatorRelationship->dbDelete();
               }
               elseif($objCreatorRelationship->RelatedCreatorID)
               {
                  $objCreatorRelationship->dbStore();
                  $objCreatorRelationship->CreatorRelationships[] = $objCreatorRelationship;
               }
            }
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objCreator = New Creator($ID);
         $objCreator->dbDelete();
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
      $msg = "Creator Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, false, NULL, NULL, $name);
}

?>
