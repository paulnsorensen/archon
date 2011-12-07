<?php
/**
 * AVSAP Assessment Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Paul Sorensen, Mamta Singh
 */
isset($_ARCHON) or die();

avsapassessments_ui_initialize();

// Determine what to do based upon user input
function avsapassessments_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      avsapassessments_ui_main();
   }
   elseif($_REQUEST['f'] == 'search')
   {
      avsapassessments_ui_search();
   }
   elseif($_REQUEST['f'] == 'loadformatlist')
   {
      avsapassessments_ui_loadformatlist();
   }
   elseif($_REQUEST['f'] == 'loadbaselist')
   {
      avsapassessments_ui_loadbaselist();
   }
   else
   {
      avsapassessments_ui_exec();
   }
}

function avsapassessments_ui_loadformatlist()
{
   global $_ARCHON;

   $type = ($_REQUEST['subassessmenttype']) ? ($_REQUEST['subassessmenttype']) : NULL;
   $arr = $_ARCHON->getAVSAPFormatList($type);
   echo(json_encode($arr));
}

function avsapassessments_ui_loadbaselist()
{
   global $_ARCHON;

   $type = ($_REQUEST['format']) ? ($_REQUEST['format']) : NULL;
   $subassessmenttype = ($_REQUEST['subassessmenttype']) ? ($_REQUEST['subassessmenttype']) : NULL;
   $arr = $_ARCHON->getAVSAPBaseList($type, $subassessmenttype);
   echo(json_encode($arr));
}

// avsapassessments_ui_main()
//   - purpose: Creates the primary user interface
//              for the AVSAP Assessments Manager.
function avsapassessments_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('AVSAPAssessment');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $objUser = $_ARCHON->Security->Session->User;

   if(!$objUser->RepositoryLimit)
   {
      $repositoryID = 0;
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
      $repositoryID = 0;
      $generalSection->insertRow('repository')->insertSelect('RepositoryID', $objUser->Repositories)->required();
   }


   $generalSection->insertRow('name')->insertTextField('Name', 50, 50)->required();
//   $generalSection->insertRow('assessmentidentifier')->insertTextField('AssessmentIdentifier', 50, 50);
//   $generalSection->insertRow('collectionname')->insertTextField('CollectionName', 50, 50);

   $objAssessment = $_ARCHON->AdministrativeInterface->Object;

   if($repositoryID == 0 && $objAssessment->RepositoryID)
   {
      $repositoryID = $objAssessment->RepositoryID;
   }

   $strCollection = '';
   $collectionID = 0;
   $strCollectionContent = '';
   $collectionContentID = 0;

   if($objAssessment->CollectionID)
   {
      $collectionID = $objAssessment->CollectionID;
      $objAssessment->Collection = new Collection($collectionID);
      $strCollection = $objAssessment->Collection->toString();

      if($objAssessment->CollectionContentID)
      {
         $collectionContentID = $objAssessment->CollectionContentID;
         $arrContent = array();
         foreach($_ARCHON->traverseCollectionContent($collectionContentID) as $obj)
         {
            $arrContent[] = $obj->toString();
         }
         $strCollectionContent = implode(' » ', $arrContent);
      }
   }

   $objNoSelectionPhrase = Phrase::getPhrase('selectone', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

   $objClassificationPhrase = Phrase::getPhrase('classification', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strClassification = $objClassificationPhrase ? $objClassificationPhrase->getPhraseValue(ENCODE_HTML) : 'Classification';

   $objRepositoryPhrase = Phrase::getPhrase('repository', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strRepository = $objRepositoryPhrase ? $objRepositoryPhrase->getPhraseValue(ENCODE_HTML) : 'Repository';



   $generalSection->insertRow('subassessmenttype')->addHelpURL('format', 'intro', true);
   $generalSection->getRow('subassessmenttype')->insertSelect('SubAssessmentType', 'getAVSAPSubAssessmentTypeList')->required();

   $objAssessment = $_ARCHON->AdministrativeInterface->Object;
   $arrFormatList = array();
   $arrBaseCompositionList = array();

   $newObj = true;
   $origSubAssessmentType = 0;

   if($objAssessment->ID)
   {
      $newObj = false;
      if(!$objAssessment->SubAssessment)
      {
         if(!$objAssessment->dbLoadSubAssessment())
            break;
      }
      $origSubAssessmentType = $objAssessment->SubAssessmentType;

      $arrFormats = $objAssessment->SubAssessment->getFormatArray();

      $AdminPhrasePhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Administrative Phrase');

      foreach($arrFormats as $key => $phrase)
      {
         $strPhrase = Phrase::getPhrase($phrase, PACKAGE_AVSAP, MODULE_AVSAPASSESSMENTS, PHRASETYPE_ADMIN);
         $phrase = $strPhrase ? $strPhrase->getPhraseValue(ENCODE_HTML) : $phrase;
         $arrFormatList[$key] = $phrase;
      }

      if($objAssessment->Format)
      {
         $arrBases = $objAssessment->SubAssessment->getBaseArray($objAssessment->Format);

         foreach($arrBases as $key => $phrase)
         {
            $strPhrase = Phrase::getPhrase($phrase, PACKAGE_AVSAP, MODULE_AVSAPASSESSMENTS, PHRASETYPE_ADMIN);
            $phrase = $strPhrase ? $strPhrase->getPhraseValue(ENCODE_HTML) : $phrase;
            $arrBaseCompositionList[$key] = $phrase;
         }
      }
      $type = $objAssessment->SubAssessmentType;
      $format = $objAssessment->Format;
      $hideFormatOnLoad = ($type == AVSAP_GENERAL || $type == AVSAP_GROOVEDCYL || $type == AVSAP_WIREAUDIO);
      $hideBaseOnLoad = ($type == AVSAP_GENERAL || $type == AVSAP_WIREAUDIO || $type == AVSAP_ACASSETTE || $type == AVSAP_VCASSETTE || $type == AVSAP_VOPENREEL) || ($type == AVSAP_FILM && ($format != 4 && $format != 5) || ($type == AVSAP_AOPENREEL && $format != 4) || ($type == AVSAP_OPTICAL && ($format != 1 && $format != 2)));
   }

   $generalSection->getRow('subassessmenttype')->insertSelect('Format', $arrFormatList)->required();
   $generalSection->getRow('subassessmenttype')->insertSelect('BaseComposition', $arrBaseCompositionList)->required();

   ob_start();
   ?>
   <script type='text/javascript'>
      /* <![CDATA[ */
               
      var newObj = <?php echo(bool($newObj)); ?>;
      var hideFormatOnLoad = <?php echo(bool($hideFormatOnLoad)); ?>;
      var hideBaseOnLoad = <?php echo(bool($hideBaseOnLoad)); ?>;
      var origSubAssessmentType = <?php echo($origSubAssessmentType); ?>;

      $(function(){
         $('#FormatInput').css('display','none');
         $('#BaseCompositionInput').css('display','none');

         if(!newObj){
            if(!hideFormatOnLoad){
               $('#FormatInput').show();
            }
            if(!hideBaseOnLoad){
               $('#BaseCompositionInput').show();
            }
         }

         // this should be the "select one" option
         var opt0 = '<option value="0">' + $('#FormatInput option:first').text() + '</option>';

         $('#SubAssessmentTypeInput').change(function() {
            var subassessmenttype = $('#SubAssessmentTypeInput').val();

            $.getJSON('index.php?p=admin/avsap/avsapassessments&f=loadformatlist&subassessmenttype=' + subassessmenttype,
            function(data) {
               var newOpts = opt0;
               $.each(data, function(i, val){
                  newOpts += '<option value="' + i + '">' + val + '</option>';
               });

               $('#FormatInput').html(newOpts);
               if(subassessmenttype == 0)
               {
                  $('#FormatInput option[value=0]').attr('selected', 'selected');
                  $('#FormatInput').hide();
               }
               else if(subassessmenttype == <?php echo(AVSAP_WIREAUDIO); ?> || subassessmenttype == <?php echo (AVSAP_GROOVEDCYL); ?> || subassessmenttype == <?php echo(AVSAP_GROOVEDDISC); ?>)
               {
                  $('#FormatInput option[value=1]').attr('selected', 'selected');
                  $('#FormatInput').hide();
               }
               else
               {
                  $('#FormatInput option[value=0]').attr('selected', 'selected');
                  $('#FormatInput').show();
               }
            });

            $.getJSON('index.php?p=admin/avsap/avsapassessments&f=loadbaselist&subassessmenttype=' + subassessmenttype,
            function(data) {
               var newOpts = opt0;
               $.each(data, function(i, val){
                  newOpts += '<option value="' + i + '">' + val + '</option>';
               });

               $('#BaseCompositionInput').html(newOpts);
               if(subassessmenttype == <?php echo(AVSAP_WIREAUDIO); ?>)
               {
                  $('#BaseCompositionInput option[value=-1]').attr('selected', 'selected');
                  $('#BaseCompositionInput').hide();
               }
               else if(subassessmenttype != <?php echo(AVSAP_GROOVEDCYL); ?> && subassessmenttype != <?php echo(AVSAP_GROOVEDDISC); ?>)
               {
                  $('#BaseCompositionInput option[value=0]').attr('selected', 'selected');
                  $('#BaseCompositionInput').hide();
               }
               else
               {
                  $('#BaseCompositionInput option[value=0]').attr('selected', 'selected');
                  $('#BaseCompositionInput').show();
               }
            });

            if(!newObj && subassessmenttype != origSubAssessmentType)
            {
               var i = 0;
               var disabledTabs = [];
               while(i < $('#moduletabs ul li').length){
                  if(i > 1){
                     disabledTabs.push(i)
                  }
                  i++;
               }
               $('#moduletabs').tabs('option', 'disabled', disabledTabs);
            }

         });

         $('#FormatInput').change(function() {
            var formatval = $('#FormatInput').val();
            var subassessmenttype = $('#SubAssessmentTypeInput').val();

            $.getJSON('index.php?p=admin/avsap/avsapassessments&f=loadbaselist&format=' + formatval + '&subassessmenttype=' + subassessmenttype,
            function(data) {
               var newOpts = opt0;
               var defaultBase = false;
               $.each(data, function(i, val){
                  if(i == -1){
                     defaultBase = true;
                  }
                  newOpts += '<option value="' + i + '">' + val + '</option>';
               });

               $('#BaseCompositionInput').html(newOpts);

               if(defaultBase){
                  $('#BaseCompositionInput option[value=-1]').attr('selected', 'selected');
                  $('#BaseCompositionInput').hide();
               }else{
                  $('#BaseCompositionInput option[value=0]').attr('selected', 'selected');
                  $('#BaseCompositionInput').show();
               }
            });
         });


         $('#RepositoryIDInput').change(function() {
            var opt0 = '<option value="0">' + $('#StorageFacilityIDInput option:first').text() + '</option>';

            $.getJSON('index.php?p=admin/avsap/avsapstoragefacilities&f=search&searchtype=json&repositoryid=' + $('#RepositoryIDInput').val(),
            function(data) {
               var newOpts = opt0;
               if(data){
                  for (i in data.results){
                     newOpts += '<option value="' + data.results[i].id + '">' + data.results[i].string + '</option>';
                  }
               }
               $('#StorageFacilityIDInput').html(newOpts);
            });

            $('#CollectionIDInput').advselect('clear');
         });

      });

      /* ]]> */
   </script>
   <?php
   $script = ob_get_clean();

   $generalSection->getRow('subassessmenttype')->insertHTML($script);


   if(CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS)
   {
      if(!$repositoryID || !$_ARCHON->Security->verifyRepositoryPermissions($repositoryID))
      {
         if($_ARCHON->Security->Session->User->RepositoryLimit)
         {
            $repositoryID = array_keys($_ARCHON->Security->Session->User->Repositories);
         }
      }
   }



   $generalSection->insertRow('storagefacility')->insertSelect('StorageFacilityID', 'searchAVSAPStorageFacilities', array('q' => '', 'repositoryid' => $repositoryID))->required();
   $generalSection->insertRow('significance')->insertRadioButtons('Significance', array(AVSAPVAL_BAD => 'low', AVSAPVAL_FAIR => 'moderate', AVSAPVAL_GOOD => 'high'))->addHelpURL('general', 'significance');

   $generalSection->insertRow('notes')->insertTextArea('Notes');


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



   $_ARCHON->AdministrativeInterface->setNameField('name');



   $RelatedCollectionSection = $_ARCHON->AdministrativeInterface->insertSection('relatedcollection');

   $rid = $objAssessment->RepositoryID ? $objAssessment->RepositoryID : 0;
   $RelatedCollectionSection->insertRow('collectionid')->insertAdvancedSelect('CollectionID', array(
       'Class' => 'Collection',
       'Multiple' => false,
       'toStringArguments' => array(),
       'params' => array(
           'p' => 'admin/collections/collections',
           'f' => 'search',
           'searchtype' => 'json',
           'repositoryid' => $rid
       ),
       'quickAdd' => "advSelectID =\\\"CollectionIDInput\\\"; admin_ui_opendialog(\\\"collections\\\", \\\"collections\\\");",
       'searchOptions' => array(
           array(
               'label' => $strClassification,
               'name' => 'ClassificationID',
               'hierarchical' => true,
               'url' => 'index.php',
               'params' => '{p: "admin/collections/classification", f: "hierarchicalselect"}',
               'noselection' => '{id: 0, text: "' . $strNoSelection . '"}'
           )
       )
   ));

   ob_start();
   ?>

   <script type="text/javascript">
      /* <![CDATA[ */

      $(function() {
         $('#CollectionIDInput').bind('change', function(){
            $('#CollectionContentID').selecttree('option', 'value', {id: 0, text: ''});
            var cid = $('#CollectionIDInput').val();


            if(cid && cid != 0){
               $('#CollectionContentID').selecttree('enable');

               var params = {
                  collectionid: cid,
                  f: 'tree',
                  p: 'admin/collections/collectioncontent',
                  pid: 0
               };

               $('#CollectionContentID').selecttree('option', 'params', params);
               $('#CollectionContentID').selecttree('refresh');

            }else{
               $('#CollectionContentID').selecttree('disable');
            }
         });


         $('#CollectionContentID').selecttree({
            value:{
               id:<?php echo($collectionContentID); ?>,
               text:"<?php echo(str_replace('&#039;', "'", $strCollectionContent)); ?>"
            },
            url: 'index.php',
            params:{
               collectionid: $('#CollectionIDInput').val() ? $('#CollectionIDInput').val() : 0,
               f: 'tree',
               p: 'admin/collections/collectioncontent',
               pid: 0
            }
         });

         if($('#CollectionIDInput').val() == 0 || $('#CollectionIDInput').val() == null){
            $('#CollectionContentID').selecttree('disable');
         }
      });

      /* ]]> */
   </script>


   <input type="hidden" class="watchme" id="CollectionContentID" value="<?php echo($_ARCHON->AdministrativeInterface->Object->CollectionContentID); ?>" name="CollectionContentID" />

   <?php
   $script = ob_get_clean();

   $RelatedCollectionSection->insertRow('collectioncontentid')->insertHTML($script);

   $useSection = $_ARCHON->AdministrativeInterface->insertSection('use');
   $useSection->setClass('avsap');
   $_ARCHON->AdministrativeInterface->addReloadSection($useSection);

   $storageSection = $_ARCHON->AdministrativeInterface->insertSection('storage');
   $storageSection->setClass('avsap');
   $_ARCHON->AdministrativeInterface->addReloadSection($storageSection);

   $conditionSection = $_ARCHON->AdministrativeInterface->insertSection('condition');
   $conditionSection->setClass('avsap');
   $_ARCHON->AdministrativeInterface->addReloadSection($conditionSection);



   $useSection->insertRow('uniquematerial')->insertRadioButtons('UniqueMaterial', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'isunique');
   $useSection->insertRow('originalmaterial')->insertRadioButtons('OriginalMaterial', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'ismaster');
   $useSection->insertRow('isplayed')->insertRadioButtons('IsPlayed', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'isplayed');
   $useSection->insertRow('hasplaybackequip')->insertRadioButtons('HasPlaybackEquip', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'hasplaybackequipment');


   switch($_ARCHON->AdministrativeInterface->Object->SubAssessmentType)
   {

      case(AVSAP_FILM):
         $storageSection->insertRow('film_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'orientedcorrectly');
         $storageSection->insertRow('film_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'appropriatecontainer');
         $storageSection->insertRow('film_oncore')->insertRadioButtons('SubAssessment[OnCore]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'oncore');
         $storageSection->insertRow('film_hasleader')->insertRadioButtons('SubAssessment[HasLeader]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'hasleader');
         $storageSection->insertRow('film_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'haslabeling');

         $conditionSection->insertRow('film_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'physicaldamage');
         $conditionSection->insertRow('film_filmdecay')->insertRadioButtons('SubAssessment[FilmDecay]', array(AVSAPVAL_GOOD => 'nodet', AVSAPVAL_FAIRLYGOOD => 'starteddet', AVSAPVAL_FAIRLYBAD => 'activedet', AVSAPVAL_BAD => 'criticaldet', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'filmdecay');
         $conditionSection->getRow('film_filmdecay')->setEnableConditions('BaseComposition', array(1, 2));
         $conditionSection->insertRow('film_filmtype')->insertRadioButtons('SubAssessment[FilmType]', array(AVSAPVAL_GOOD => 'print', AVSAPVAL_FAIR => 'magstock', AVSAPVAL_BAD => 'negative_reversal_mixed', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'filmtype');
         $conditionSection->insertRow('film_magstockbreakdown')->insertRadioButtons('SubAssessment[MagStockBreakdown]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'magstockbreakdown');
         $conditionSection->getRow('film_magstockbreakdown')->setEnableConditions('SubAssessment[FilmType]', AVSAPVAL_FAIR);
         $conditionSection->insertRow('film_incolor')->insertRadioButtons('SubAssessment[InColor]', array(AVSAPVAL_BAD => 'color', AVSAPVAL_GOOD => 'blackandwhite', AVSAPVAL_FAIR => 'both', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'incolor');
         $conditionSection->insertRow('film_soundtracktype')->insertRadioButtons('SubAssessment[SoundtrackType]', array(AVSAPVAL_FAIR => 'optical', AVSAPVAL_BAD => 'magnetic', AVSAPVAL_GOOD => 'none'))->addHelpURL('film', 'soundtracktype');
         $conditionSection->insertRow('film_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'hasmold');
         $conditionSection->insertRow('film_shrinkage')->insertRadioButtons('SubAssessment[Shrinkage]', array(AVSAPVAL_GOOD => 'noshrinkage', AVSAPVAL_FAIR => 'someshrinkage', AVSAPVAL_BAD => 'moreshrinkage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'shrinkage');
         $conditionSection->insertRow('film_spliceintegrity')->insertRadioButtons('SubAssessment[SpliceIntegrity]', array(AVSAPVAL_GOOD => 'nosplices', AVSAPVAL_FAIR => 'good', AVSAPVAL_BAD => 'bad', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('film', 'spliceintegrity');
         break;


      case(AVSAP_ACASSETTE):
         $storageSection->insertRow('acassette_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'orientedcorrectly');
         $storageSection->insertRow('acassette_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'appropriatecontainer');
         $storageSection->insertRow('acassette_recordprotection')->insertRadioButtons('SubAssessment[RecordProtection]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'recordprotection');
         $storageSection->insertRow('acassette_cartridgecondition')->insertRadioButtons('SubAssessment[CartridgeCondition]', array(AVSAPVAL_GOOD => 'good', AVSAPVAL_FAIR => 'fair', AVSAPVAL_BAD => 'poor', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'cartridgecondition');
         $storageSection->insertRow('acassette_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'haslabeling');
         $conditionSection->insertRow('acassette_cassettelength')->insertRadioButtons('SubAssessment[CassetteLength]', array(AVSAPVAL_GOOD => 'shortcassette', AVSAPVAL_FAIR => 'mediumcassette', AVSAPVAL_BAD => 'longcassette', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'cassettelength');
         $conditionSection->insertRow('acassette_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'physicaldamage');
         $conditionSection->insertRow('acassette_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'moldlevel');
         $conditionSection->insertRow('acassette_stickyshed')->insertRadioButtons('SubAssessment[StickyShed]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'stickyshed');
         $conditionSection->insertRow('acassette_windquality')->insertRadioButtons('SubAssessment[WindQuality]', array(AVSAPVAL_GOOD => 'goodwindquality', AVSAPVAL_FAIR => 'poppedwindquality', AVSAPVAL_BAD => 'poorwindquality'))->addHelpURL('audiocassette', 'windquality');
         $conditionSection->insertRow('acassette_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'hasdocumentation');
         $conditionSection->insertRow('acassette_playbacksqueal')->insertRadioButtons('SubAssessment[PlaybackSqueal]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'playbacksqueal');
         $conditionSection->insertRow('acassette_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audiocassette', 'recentlyplayedback');
         break;


      case(AVSAP_VCASSETTE):
         $storageSection->insertRow('vcassette_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'orientedcorrectly');
         $storageSection->insertRow('vcassette_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'appropriatecontainer');
         $storageSection->insertRow('vcassette_recordprotection')->insertRadioButtons('SubAssessment[RecordProtection]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'copyprotection');
         $storageSection->insertRow('vcassette_cartridgecondition')->insertRadioButtons('SubAssessment[CartridgeCondition]', array(AVSAPVAL_GOOD => 'good', AVSAPVAL_FAIR => 'fair', AVSAPVAL_BAD => 'poor', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'cassettecondition');
         $storageSection->insertRow('vcassette_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'haslabeling');
         $conditionSection->insertRow('vcassette_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'physicaldamage');
         $conditionSection->insertRow('vcassette_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'hasmold');
         $conditionSection->insertRow('vcassette_stickyshed')->insertRadioButtons('SubAssessment[StickyShed]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'hasstickyshed');
         $conditionSection->insertRow('vcassette_windquality')->insertRadioButtons('SubAssessment[WindQuality]', array(AVSAPVAL_GOOD => 'goodwindquality', AVSAPVAL_FAIR => 'poppedwindquality', AVSAPVAL_BAD => 'poorwindquality'))->addHelpURL('videocassette', 'windquality');
         $conditionSection->insertRow('vcassette_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'hasdocumentation');
         $conditionSection->insertRow('vcassette_playbacksqueal')->insertRadioButtons('SubAssessment[PlaybackSqueal]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'playbacksqueal');
         $conditionSection->insertRow('vcassette_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'recentlyplayedback');
         break;


      case(AVSAP_VOPENREEL):
         $storageSection->insertRow('vopenreel_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videoreel', 'orientedcorrectly');
         $storageSection->insertRow('vopenreel_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videoreel', 'appropriatecontainer');
         $storageSection->insertRow('vopenreel_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videoreel', 'haslabeling');
         $conditionSection->insertRow('vopenreel_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videoreel', 'physicaldamage');
         $conditionSection->insertRow('vopenreel_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videoreel', 'hasmold');
         $conditionSection->insertRow('vopenreel_stickyshed')->insertRadioButtons('SubAssessment[StickyShed]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'stickyshed');
         $conditionSection->insertRow('vopenreel_windquality')->insertRadioButtons('SubAssessment[WindQuality]', array(AVSAPVAL_GOOD => 'goodwindquality', AVSAPVAL_FAIR => 'poppedwindquality', AVSAPVAL_BAD => 'poorwindquality'))->addHelpURL('videoreel', 'windquality');
         $conditionSection->insertRow('vopenreel_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'hasdocumentation');
         $conditionSection->insertRow('vopenreel_playbacksqueal')->insertRadioButtons('SubAssessment[PlaybackSqueal]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'playbacksqueal');
         $conditionSection->insertRow('vopenreel_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'recentlyplayedback');
         break;


      case(AVSAP_AOPENREEL):
         $storageSection->insertRow('aopenreel_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'orientedcorrectly');
         $storageSection->insertRow('aopenreel_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'appropriatecontainer');
         $storageSection->insertRow('aopenreel_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'haslabeling');
         $storageSection->insertRow('aopenreel_hasleader')->insertRadioButtons('SubAssessment[HasLeader]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'hasleader');
         $conditionSection->insertRow('aopenreel_aopenreeldecay')->insertRadioButtons('SubAssessment[TapeDecay]', array(AVSAPVAL_GOOD => 'nodet', AVSAPVAL_FAIRLYGOOD => 'starteddet', AVSAPVAL_FAIRLYBAD => 'activedet', AVSAPVAL_BAD => 'criticaldet', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'isacetate');
         $conditionSection->getRow('aopenreel_aopenreeldecay')->setEnableConditions('BaseComposition', 2);
         $conditionSection->insertRow('aopenreel_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'isdamaged');
         $conditionSection->insertRow('aopenreel_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'hasmold');
         $conditionSection->insertRow('aopenreel_stickyshed')->insertRadioButtons('SubAssessment[StickyShed]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'stickyshed');
         $conditionSection->insertRow('aopenreel_windquality')->insertRadioButtons('SubAssessment[WindQuality]', array(AVSAPVAL_GOOD => 'goodwindquality', AVSAPVAL_FAIR => 'poppedwindquality', AVSAPVAL_BAD => 'poorwindquality'))->addHelpURL('audioreel', 'windquality');
         $conditionSection->insertRow('aopenreel_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('audioreel', 'documentation');
         $conditionSection->insertRow('aopenreel_playbacksqueal')->insertRadioButtons('SubAssessment[PlaybackSqueal]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'playbacksqueal');
         $conditionSection->insertRow('aopenreel_spliceintegrity')->insertRadioButtons('SubAssessment[SpliceIntegrity]', array(AVSAPVAL_GOOD => 'nosplices', AVSAPVAL_FAIR => 'goodsplices', AVSAPVAL_BAD => 'badsplices'))->addHelpURL('audioreel', 'hassplices');
         $conditionSection->insertRow('aopenreel_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'recentlyplayedback');
         break;


      case(AVSAP_OPTICAL):
         $storageSection->insertRow('optical_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'orientedcorrectly');
         $storageSection->insertRow('optical_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'appropriatecontainer');
         $storageSection->insertRow('optical_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'haslabeling');
         $conditionSection->insertRow('optical_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'physicaldamage');
         $conditionSection->insertRow('optical_laserrot')->insertRadioButtons('SubAssessment[LaserRot]', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'haslaserrot');
         $conditionSection->insertRow('optical_performedchecksum')->insertRadioButtons('SubAssessment[PerformedChecksum]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'datacheck');
         $conditionSection->insertRow('optical_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('videocassette', 'hasmold');
         $conditionSection->insertRow('optical_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'digitalobsolescence');
         $conditionSection->insertRow('optical_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('optical', 'digitalobsolescence');
         break;


      case(AVSAP_WIREAUDIO):
         $storageSection->insertRow('wireaudio_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('wire', 'orientedcorrectly');
         $storageSection->insertRow('wireaudio_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('wire', 'appropriatecontainer');
         $storageSection->insertRow('wireaudio_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('wire', 'haslabeling');
         $conditionSection->insertRow('wireaudio_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'none', AVSAPVAL_FAIR => 'moderate', AVSAPVAL_BAD => 'high', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('wire', 'hasmold');
         $conditionSection->insertRow('wireaudio_rustlevel')->insertRadioButtons('SubAssessment[RustLevel]', array(AVSAPVAL_GOOD => 'none', AVSAPVAL_FAIR => 'moderate', AVSAPVAL_BAD => 'high', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('wire', 'hasrust');
         $conditionSection->insertRow('wireaudio_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'hasdocumentation');
         $conditionSection->insertRow('wireaudio_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('wire', 'physicaldamage');
         $conditionSection->insertRow('wireaudio_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'recentlyplayedback');
         break;


      case(AVSAP_GROOVEDDISC):
         $storageSection->insertRow('grooveddisc_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'orientedcorrectly');
         $storageSection->insertRow('grooveddisc_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'appropriatecontainer');
         $storageSection->insertRow('grooveddisc_hasinnersleeve')->insertRadioButtons('SubAssessment[HasInnerSleeve]', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'hasinnersleeve');
         $storageSection->insertRow('grooveddisc_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'haslabeling');
         $conditionSection->insertRow('grooveddisc_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'physicaldamage');
         $conditionSection->insertRow('grooveddisc_dustlevel')->insertRadioButtons('SubAssessment[DustLevel]', array(AVSAPVAL_GOOD => 'nodust', AVSAPVAL_FAIR => 'somedust', AVSAPVAL_BAD => 'lotsofdust', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'isdirty');
         $conditionSection->insertRow('grooveddisc_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'hasmold');
         $conditionSection->insertRow('grooveddisc_corematerial')->insertRadioButtons('SubAssessment[CoreMaterial]', array(AVSAPVAL_GOOD => 'aluminum', AVSAPVAL_FAIR => 'cardboard', AVSAPVAL_BAD => 'glass', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'innercore');
         $conditionSection->insertRow('grooveddisc_aciddeposits')->insertRadioButtons('SubAssessment[AcidDeposits]', array(AVSAPVAL_GOOD => 'nopalmetic', AVSAPVAL_FAIR => 'somepalmetic', AVSAPVAL_BAD => 'lotsofpalmetic', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('disk', 'haspalmeticacid');
         $conditionSection->insertRow('grooveddisc_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'recentlyplayedback');
         $conditionSection->insertRow('grooveddisc_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'hasdocumentation');
         break;


      case(AVSAP_GROOVEDCYL):
         $storageSection->insertRow('groovedcyl_orientedcorrectly')->insertRadioButtons('OrientedCorrectly', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('cylinder', 'orientedcorrectly');
         $storageSection->insertRow('groovedcyl_appropriatecontainer')->insertRadioButtons('AppropriateContainer', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('cylinder', 'appropriatecontainer');
         $storageSection->insertRow('groovedcyl_labeling')->insertRadioButtons('Labeling', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('cylinder', 'haslabeling');
         $conditionSection->insertRow('groovedcyl_physicaldamage')->insertRadioButtons('PhysicalDamage', array(AVSAPVAL_GOOD => 'minimaldamage', AVSAPVAL_FAIR => 'moderatedamage', AVSAPVAL_BAD => 'severedamage', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('cylinder', 'physicaldamage');
         $conditionSection->insertRow('groovedcyl_dustlevel')->insertRadioButtons('SubAssessment[DustLevel]', array(AVSAPVAL_GOOD => 'nodust', AVSAPVAL_FAIR => 'somedust', AVSAPVAL_BAD => 'lotsofdust', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('cylinder', 'isdirty');
         $conditionSection->insertRow('groovedcyl_moldlevel')->insertRadioButtons('MoldLevel', array(AVSAPVAL_GOOD => 'nomold', AVSAPVAL_FAIR => 'somemold', AVSAPVAL_BAD => 'lotsofmold', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('cylinder', 'hasmold');
         $conditionSection->insertRow('groovedcyl_recentlyplayedback')->insertRadioButtons('RecentlyPlayedBack', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'recentlyplayedback');
         $conditionSection->insertRow('groovedcyl_hasconditioninfo')->insertRadioButtons('HasConditionInfo', array(AVSAPVAL_GOOD => 'yes', AVSAPVAL_BAD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('cylinder', 'hasdocumentation');
         break;
   }

   $conditionSection->insertRow('pestdamage')->insertRadioButtons('PestDamage', array(AVSAPVAL_BAD => 'yes', AVSAPVAL_GOOD => 'no', AVSAPVAL_UNSURE => 'idontknow'))->addHelpURL('general', 'pestdamage');

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

   $scoreSection->getRow('score')->insertHTML($ScoreProgressBarHTML, 'ScoreProgressBarHTML');

   if(!$_ARCHON->Security->Session->User->RepositoryLimit)
   {
      $_ARCHON->AdministrativeInterface->insertSearchOption('RepositoryID', 'getAllRepositories', 'repositoryid');
   }


   $_ARCHON->AdministrativeInterface->outputInterface();
}

function avsapassessments_ui_search()
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
//
//      }
//   }

   $_ARCHON->AdministrativeInterface->searchResults('searchAVSAPAssessments', array('repositoryid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function avsapassessments_ui_exec()
{
   global $_ARCHON;


   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      $location = NULL;

      foreach($arrIDs as &$ID)
      {
         if($ID != 0)
         {
            $tmpAssessment = New AVSAPAssessment($ID);
            $tmpAssessment->dbLoad();
            $tmpSubAssessmentType = $tmpAssessment->SubAssessmentType;
         }

         $objAVSAPAssessment = New AVSAPAssessment($_REQUEST);
         $objAVSAPAssessment->ID = $ID;
         $objAVSAPAssessment->dbStore();
         $ID = $objAVSAPAssessment->ID;

         if($tmpSubAssessmentType && $objAVSAPAssessment->SubAssessmentType != $tmpSubAssessmentType)
         {
            $location = "index.php?p=" . $_REQUEST['p'] . "&id=" . $ID;
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objAVSAPAssessment = New AVSAPAssessment($ID);
         $objAVSAPAssessment->dbDelete();
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
      $msg = "AVSAPAssessment Database Updated Successfully.";
   }


   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, false, $location);
}