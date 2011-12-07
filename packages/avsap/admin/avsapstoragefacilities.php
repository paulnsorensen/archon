<?php
/**
 * AVSAP Storage Facility Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Paul Sorensen
 */
isset($_ARCHON) or die();

avsapstoragefacilities_ui_initialize();

// Determine what to do based upon user input
function avsapstoragefacilities_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      avsapstoragefacilities_ui_main();
   }
   else if($_REQUEST['f'] == 'search')
   {
      avsapstoragefacilities_ui_search();
   }
   else
   {
      avsapstoragefacilities_ui_exec();
   }
}

// avsapstoragefacilities_ui_main()
//   - purpose: Creates the primary user interface
//              for the Processing Priorities Manager.
function avsapstoragefacilities_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('AVSAPStorageFacility');

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


   $objUser = $_ARCHON->Security->Session->User;

   $events = array('change' => 'admin_ui_reloadfield("LocationID", {repositoryid: $(this).val()})');
   if(!$objUser->RepositoryLimit)
   {
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', 'getAllRepositories', array(), NULL, 50, $events)->required();
      $repositoryID = $_REQUEST['repositoryid'] ? $_REQUEST['repositoryid'] : $_ARCHON->AdministrativeInterface->Object->RepositoryID;
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
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', $objUser->Repositories, array(), NULL, 50, $events)->required();
      $repositoryID = $_REQUEST['repositoryid'] ? $_REQUEST['repositoryid'] : $_ARCHON->AdministrativeInterface->Object->RepositoryID;
   }

   $generalSection->insertRow('name')->insertTextField('Name', 50, 50)->required();

   $locationField = $generalSection->insertRow('location')->insertSelect('LocationID', 'searchLocations', array('q' => '', 'repositoryid' => $repositoryID));
   $locationField->required();
   ob_start();
?>
   <a id="addlocation" href="#" onclick="admin_ui_dialogcallback(function() {admin_ui_reloadfield('LocationID', {repositoryid: $('#RepositoryIDInput').val()});}); admin_ui_opendialog('collections', 'locations'); return false;">
      &nbsp;
   </a>
   <script type="text/javascript">
      /* <![CDATA[ */
      $(function(){
         $('#addlocation').button({
            icons:{
               primary:"ui-icon-plus"
            },
            text:false
         })
      })
      /* ]]> */
   </script>

<?php
   $quickadd = ob_get_clean();
   $generalSection->getRow('location')->insertHTML($quickadd);

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

   $climateSection = $_ARCHON->AdministrativeInterface->insertSection('climatecontrol');
   $climateSection->setClass('avsap');
   $climateSection->insertRow('avgtemp')->insertRadioButtons('AvgTemp', array(AVSAPTEMP_VERYLOW => 'verylowtemp', AVSAPTEMP_LOW => 'lowtemp', AVSAPTEMP_MEDIUMLOW => 'mediumlowtemp', AVSAPTEMP_MEDIUMHIGH => 'mediumhightemp', AVSAPTEMP_HIGH => 'hightemp', AVSAPTEMP_VERYHIGH => 'veryhightemp'))->addHelpURL('storage', 'avgtemp');
   $climateSection->insertRow('tempvariance')->insertRadioButtons('TempVariance', array(AVSAPVAL_GOOD => 'lowtempvar', AVSAPVAL_FAIR => 'mediumtempvar', AVSAPVAL_BAD => 'hightempvar'))->addHelpURL('storage', 'tempvariance');
   $climateSection->insertRow('avghumidity')->insertRadioButtons('AvgHumidity', array('0.5' => 'lowrh', '1.0' => 'medlowrh', '0.75' => 'medrh', '0.25' => 'medhighrh', '0.0' => 'highrh'))->addHelpURL('storage', 'avghumidity');
   $climateSection->insertRow('humidityvariance')->insertRadioButtons('HumidityVariance', array(AVSAPVAL_GOOD => 'lowhumvar', AVSAPVAL_FAIR => 'mediumhumvar', AVSAPVAL_BAD => 'highhumvar'))->addHelpURL('storage', 'humidityvariance');


   $disasterSection = $_ARCHON->AdministrativeInterface->insertSection('disasterpreparedness');
   $disasterSection->setClass('avsap');
   $disasterSection->insertRow('hasfiredetection')->insertRadioButtons('HasFireDetection', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('storage', 'hasfiredetection');
   $disasterSection->insertRow('hasfiresuppression')->insertRadioButtons('HasFireSuppression', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('storage', 'hasfiresuppression');
   $disasterSection->insertRow('haswaterdetection')->insertRadioButtons('HasWaterDetection', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('storage', 'haswaterdetection');
   $disasterSection->insertRow('materialsonfloor')->insertRadioButtons('MaterialsOnFloor', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('storage', 'materialstorage');
   $disasterSection->insertRow('securitylevel')->insertRadioButtons('SecurityLevel', array(AVSAPVAL_BAD => 'none', AVSAPVAL_FAIR => 'moderate', AVSAPVAL_GOOD => 'high'))->addHelpURL('storage', 'securitylevel');

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

function avsapstoragefacilities_ui_search()
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
//         //  $repositoryID = current($objUser->Repositories);
//         $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
//      }
//   }


   $_ARCHON->AdministrativeInterface->searchResults('searchAVSAPStorageFacilities', array('repositoryid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function avsapstoragefacilities_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objAVSAPStorageFacility = New AVSAPStorageFacility($_REQUEST);
         $objAVSAPStorageFacility->ID = $ID;
         $objAVSAPStorageFacility->dbStore();
         $ID = $objAVSAPStorageFacility->ID;
      }
   }
   else if($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objAVSAPStorageFacility = New AVSAPStorageFacility($ID);
         $objAVSAPStorageFacility->dbDelete();
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
      $msg = "AVSAPStorageFacility Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}