<?php

/**
 * AVSAP Institution Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Paul Sorensen
 */
isset($_ARCHON) or die();

avsapinstitutions_ui_initialize();

// Determine what to do based upon user input
function avsapinstitutions_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      avsapinstitutions_ui_main();
   }
   else if($_REQUEST['f'] == 'search')
   {
      avsapinstitutions_ui_search();
   }
   else
   {
      avsapinstitutions_ui_exec();
   }
}

// avsapinstitutions_ui_main()

function avsapinstitutions_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('AVSAPInstitution');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');


   $matches = array();
   if(preg_match('/([d|m|Y])[\W]+([d|m|Y])[\W]+([d|m|Y]).*/', CONFIG_CORE_DATE_FORMAT, $matches))
   {
      $format = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
   }
   else
   {
      $format = 'm/d/Y';
   }
   $date = date($format);

   $newObj = !$_ARCHON->AdministrativeInterface->Object->ID ? true : false;
   $js_newObj = bool($newObj);


   $objUser = $_ARCHON->Security->Session->User;

   if(!$objUser->RepositoryLimit)
   {
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', 'getAllRepositories', array(), NULL, 50, array('change' => "if({$js_newObj}) $('#NameInput').val($(this).children(':selected').text()+' ({$date})');"))->required();
   }
   elseif(count($objUser->Repositories) == 1)
   {
      $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
      $repositoryName = $objUser->Repositories[key($objUser->Repositories)]->Name;
      $generalSection->insertRow('repository')->insertInformation('Repository', $repositoryName);
      $_ARCHON->AdministrativeInterface->Object->RepositoryID = $repositoryID;
      $generalSection->insertHiddenField('RepositoryID');
   }
   else
   {
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', $objUser->Repositories, array(), NULL, 50, array('change' => "if({$js_newObj}) $('#NameInput').val($(this).children(':selected').text()+' ({$date})');"))->required();
   }

   if($newObj && $repositoryName)
   {

      $_ARCHON->AdministrativeInterface->Object->Name = $repositoryName . " (" . $date . ")";
   }

   $generalSection->insertRow('name')->insertTextField('Name', 50, 50)->required();


   $lang = $_ARCHON->getLanguageShortFromID($_ARCHON->Security->Session->getLanguageID());

   if(!file_exists("packages/avsap/adminhelp/glossary/" . $lang . "/glossary.html"))
   {
      $lang = 'eng'; //assuming english files will always exist
   }
   $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_loadadvancedhelp('packages/avsap/adminhelp/glossary/{$lang}/glossary.html')", 'glossary', false);

   $lang = $_ARCHON->getLanguageShortFromID($_ARCHON->Security->Session->getLanguageID());

   if(!file_exists("packages/avsap/adminhelp/bibliography/" . $lang . "/bibliography.html"))
   {
      $lang = 'eng'; //assuming english files will always exist
   }
   $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_loadadvancedhelp('packages/avsap/adminhelp/bibliography/{$lang}/bibliography.html')", 'bibliography', false);



   $_ARCHON->AdministrativeInterface->setNameField('Name');

   $preservationSection = $_ARCHON->AdministrativeInterface->insertSection('preservation');
   $preservationSection->setClass('avsap');
   $preservationSection->insertRow('preservationplan')->insertRadioButtons('PreservationPlan', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'preservationplan');
   $preservationSection->insertRow('avpreservationplan')->insertRadioButtons('AVPreservationPlan', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'avpreservationplan');
   $preservationSection->insertRow('collectionpolicy')->insertRadioButtons('CollectionPolicy', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'collectionpolicy');


   $accessSection = $_ARCHON->AdministrativeInterface->insertSection('access');
   $accessSection->setClass('avsap');
   $accessSection->insertRow('catalogcollections')->insertRadioButtons('CatalogCollections', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'catalogcollections');
   $accessSection->insertRow('accesscopies')->insertRadioButtons('AccessCopies', array(AVSAPVAL_GOOD => 'most', AVSAPVAL_FAIR => 'some', AVSAPVAL_BAD => 'none', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'accesscopies');
   $accessSection->insertRow('digitalcopies')->insertRadioButtons('DigitalCopies', array(AVSAPVAL_GOOD => 'most', AVSAPVAL_FAIR => 'some', AVSAPVAL_BAD => 'none', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'digitalcopies');
   $accessSection->insertRow('ownershiprecords')->insertRadioButtons('OwnershipRecords', array(AVSAPVAL_GOOD => 'most', AVSAPVAL_FAIR => 'some', AVSAPVAL_BAD => 'none', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'ownershiprecords');
   $accessSection->insertRow('allowplayback')->insertRadioButtons('AllowPlayBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'allowplayback');
   $accessSection->insertRow('allowloaninginstitutions')->insertRadioButtons('AllowLoaningInstitutions', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'allowloaninginstitutions');
   $accessSection->insertRow('allowloaningother')->insertRadioButtons('AllowLoaningOther', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'allowloaningother');

   $inspectionSection = $_ARCHON->AdministrativeInterface->insertSection('inspection');
   $inspectionSection->setClass('avsap');
   $inspectionSection->insertRow('staffcleanrepair')->insertRadioButtons('StaffCleanRepair', array(AVSAPVAL_GOOD => 'most', AVSAPVAL_FAIR => 'some', AVSAPVAL_BAD => 'none', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'staffcleanrepair');
   $inspectionSection->insertRow('staffvisualinspections')->insertRadioButtons('StaffVisualInspections', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'staffvisualinspections');
   $inspectionSection->insertRow('staffplaybackinspections')->insertRadioButtons('StaffPlayBackInspections', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'staffplaybackinspections');
   $inspectionSection->insertRow('dedicatedinspectionspace')->insertRadioButtons('DedicatedInspectionSpace', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'dedicatedinspectionspace');


   $equipmentSection = $_ARCHON->AdministrativeInterface->insertSection('equipment');
   $equipmentSection->setClass('avsap');
   $equipmentSection->insertRow('maintainplaybackequipment')->insertRadioButtons('MaintainPlaybackEquipment', array(AVSAPVAL_GOOD => 'most', AVSAPVAL_FAIR => 'some', AVSAPVAL_BAD => 'none', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'maintainplaybackequipment');
   $equipmentSection->insertRow('equipmentmanuals')->insertRadioButtons('EquipmentManuals', array(AVSAPVAL_GOOD => 'most', AVSAPVAL_FAIR => 'some', AVSAPVAL_BAD => 'none', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'equipmentmanuals');
   $equipmentSection->insertRow('equipmentpartsservice')->insertRadioButtons('EquipmentPartsService', array(AVSAPVAL_GOOD => 'most', AVSAPVAL_FAIR => 'some', AVSAPVAL_BAD => 'none', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'equipmentpartsservice');



   $disasterplanningSection = $_ARCHON->AdministrativeInterface->insertSection('disasterplanning');
   $disasterplanningSection->setClass('avsap');
   $disasterplanningSection->insertRow('disasterrecoveryplan')->insertRadioButtons('DisasterRecoveryPlan', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'disasterrecoveryplan');
   $disasterplanningSection->insertRow('avdisasterrecoveryplan')->insertRadioButtons('AVDisasterRecoveryPlan', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'avdisasterrecoveryplan');
   $disasterplanningSection->insertRow('accessavdisasterrecoverytools')->insertRadioButtons('AccessAVDisasterRecoveryTools', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('repository', 'accessavdisasterrecoverytools');

   $scoreSection = $_ARCHON->AdministrativeInterface->insertSection('score');
   $scoreSection->insertRow('score')->insertInformation('Score');

   $ScoreProgressBarHTML = "
      <div id='ScoreProgressBar'></div>
      <script type='text/javascript'>
      /* <![CDATA[ */
        $(function() {
            var v = $('#Score span').text();

            $('#ScoreProgressBar').progressbar({
               value: parseFloat(v)
            });
        });

    $('#ScoreField').change(function () {
        var v = $('#Score span').text();
        $('#ScoreProgressBar').progressbar('option', 'value', parseFloat(v));
    });
      /* ]]> */
      </script>
   ";

   $scoreSection->getRow('score')->insertHTML($ScoreProgressBarHTML);

   if(!$_ARCHON->Security->Session->User->RepositoryLimit)
   {
      $_ARCHON->AdministrativeInterface->insertSearchOption('RepositoryID', 'getAllRepositories', 'repositoryid');
   }

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function avsapinstitutions_ui_search()
{
   global $_ARCHON;
//   $repositoryID =0;
//
//   $objUser = $_ARCHON->Security->Session->User;
//
//   if(!empty($objUser->Repositories))
//   {
//      if(count($objUser->Repositories) == 1)
//      {
//         //   $repositoryID = current($objUser->Repositories);
//         $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
//      }
//   }
//   if($_ARCHON->Security->Session->User->RepositoryLimit)
//    {
//        $_REQUEST['repositoryid'] = $_ARCHON->Security->Session->User->RepositoryID;
//    }


   $_ARCHON->AdministrativeInterface->searchResults('searchAVSAPInstitutions', array('repositoryid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function avsapinstitutions_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objAVSAPInstitution = New AVSAPInstitution($_REQUEST);
         $objAVSAPInstitution->ID = $ID;
         $objAVSAPInstitution->dbStore();
         $ID = $objAVSAPInstitution->ID;
      }
   }
   else if($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objAVSAPInstitution = New AVSAPInstitution($ID);
         $objAVSAPInstitution->dbDelete();
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
      $msg = "AVSAPInstitution Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}