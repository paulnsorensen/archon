<?php
/**
 * Digital Library Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

ob_implicit_flush();

digitallibrary_ui_initialize();

// Determine what to do based upon user input
function digitallibrary_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      digitallibrary_ui_main();
   }
   elseif($_REQUEST['f'] == 'search')
   {
      digitallibrary_ui_search();
   }
   elseif($_REQUEST['f'] == "dialog_uploadfile")
   {
      digitallibrary_ui_dialog_uploadfile();
   }
   elseif($_REQUEST['f'] == "dialog_add")
   {
      digitallibrary_ui_dialog_add();
   }
   elseif($_REQUEST['f'] == 'getfilelist')
   {
      digitallibrary_ui_getfilelist();
   }
   else
   {
      digitallibrary_ui_exec();
   }
}

function digitallibrary_ui_getfilelist()
{
   global $_ARCHON;

   $arrFiles = $_ARCHON->getLinkedFileList();

   $arrJSON = array();

   foreach($arrFiles as $id => $file)
   {
      $arrJSON[$id] = '{"id":' . $file['ID'] . ',"dcid":' . $file['DigitalContentID'] . ',"filename":"' . $file['Filename'] . '"}';
   }

   echo("[" . implode(',', $arrJSON) . "]");
}

function digitallibrary_ui_dialog_uploadfile()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('File');
   $_ARCHON->AdministrativeInterface->setNameField('Name');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/digitallibrary/digitallibrary', 'uploadfile');

   $dialogSection->insertRow('files_filecontents')->insertHTML("<input class='uploadfield' type='file' name='FileContents' />");

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function digitallibrary_ui_dialog_add()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('DigitalContent');
   $_ARCHON->AdministrativeInterface->setNameField('Title');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/digitallibrary/digitallibrary', 'store');

   $html = '';

   if($_REQUEST['collectionid'])
   {
      $_ARCHON->AdministrativeInterface->Object->CollectionID = $_REQUEST['collectionid'];
      $html = "<input type='hidden' name='CollectionID' value='" . $_REQUEST['collectionid'] . "' />";
   }
   if($_REQUEST['collectioncontentid'])
   {
      $_ARCHON->AdministrativeInterface->Object->CollectionContentID = $_REQUEST['collectioncontentid'];
      $html .= "<input type='hidden' name='CollectionContentID' value='" . $_REQUEST['collectioncontentid'] . "' />";
   }

   $dialogSection->insertRow('title')->insertNameField('Title')->required();
   $dialogSection->insertRow('browsable')->insertRadioButtons('Browsable');
   $dialogSection->insertRow('identifier')->insertTextField('Identifier', 20, 255);
   $dialogSection->insertRow('date')->insertTextField('Date', 20, 50);
   $dialogSection->insertRow('common_contenturl')->insertTextField('ContentURL', 46, 255);
   $dialogSection->getRow('common_contenturl')->insertCheckBox('HyperlinkURL');
   $objHyperlinkURLPhrase = Phrase::getPhrase('hyperlinkurl', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strHyperlinkURL = $objHyperlinkURLPhrase ? $objHyperlinkURLPhrase->getPhraseValue(ENCODE_HTML) : 'URL is hyperlink';

   $dialogSection->getRow('common_contenturl')->insertHTML("<label id='HyperlinkURLLabel' for='HyperlinkURLCheckboxInput'>{$strHyperlinkURL}</label>");
   $dialogSection->getRow('title')->insertHTML($html);
   
   $_ARCHON->AdministrativeInterface->outputInterface();
}

function digitallibrary_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('DigitalContent');

   $_ARCHON->AdministrativeInterface->setNameField('Title');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('title')->insertNameField('Title')->required();
   $generalSection->insertRow('browsable')->insertRadioButtons('Browsable');
   $generalSection->insertRow('identifier')->insertTextField('Identifier', 20, 255);
   $generalSection->insertRow('date')->insertTextField('Date', 20, 50);
   $generalSection->insertRow('common_contenturl')->insertTextField('ContentURL', 50, 255);
   $generalSection->getRow('common_contenturl')->insertCheckBox('HyperlinkURL');

   $objHyperlinkURLPhrase = Phrase::getPhrase('hyperlinkurl', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strHyperlinkURL = $objHyperlinkURLPhrase ? $objHyperlinkURLPhrase->getPhraseValue(ENCODE_HTML) : 'URL is hyperlink';

   $generalSection->getRow('common_contenturl')->insertHTML("<label id='HyperlinkURLLabel' for='HyperlinkURLCheckboxInput'>{$strHyperlinkURL}</label>");


   ob_start();
?>
   <script type='text/javascript'>
      /* <![CDATA[ */
      var contentURLTimeout;

      function checkContentURLField(){
         if($('#ContentURLInput').val() != ''){
            $('#HyperlinkURLCheckboxInput').removeAttr('disabled');
            $('#HyperlinkURLField').removeClass('disabledfield');
            $('#HyperlinkURLLabel').closest('.adminfieldwrapper').removeClass('disabledfield');
         }else{
            $('#HyperlinkURLCheckboxInput').attr('disabled', true);
            $('#HyperlinkURLField').addClass('disabledfield');
            $('#HyperlinkURLLabel').closest('.adminfieldwrapper').addClass('disabledfield');
         }
      }

      $(function(){
         var filterfield = $('#ContentURLInput');
         filterfield.keypress(function (e) {
            if(e.keyCode == 13)
            {
               return false;
            }
            clearTimeout(contentURLTimeout);
            contentURLTimeout = setTimeout(function () {
               checkContentURLField();
            }, 50);
         });

         if($('#ContentURLInput').val() == ''){
            $('#HyperlinkURLInput').val(1);
            $('#HyperlinkURLCheckboxInput').attr('checked', 'checked');
            $('#HyperlinkURLCheckboxInput').attr('disabled', true);
            $('#HyperlinkURLField').addClass('disabledfield');
            $('#HyperlinkURLLabel').closest('.adminfieldwrapper').addClass('disabledfield');
         }

      });
      /* ]]> */
   </script>
<?php
   $script = ob_get_clean();
   $generalSection->getRow('common_contenturl')->insertHTML($script);
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
                       'noselection' => '{id: 0, text: "' . $strNoSelection . '"}'
                   )
               )
   ));


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


   if(defined('PACKAGE_COLLECTIONS'))
   {
      $objNoSelectionPhrase = Phrase::getPhrase('selectone', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

      $objRepositoryPhrase = Phrase::getPhrase('repository', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strRepository = $objRepositoryPhrase ? $objRepositoryPhrase->getPhraseValue(ENCODE_HTML) : 'Repository';

      $objClassificationPhrase = Phrase::getPhrase('classification', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strClassification = $objClassificationPhrase ? $objClassificationPhrase->getPhraseValue(ENCODE_HTML) : 'Classification';




      $objDigitalContent = $_ARCHON->AdministrativeInterface->Object;

      $strCollection = '';
      $collectionID = 0;
      $strCollectionContent = '';
      $collectionContentID = 0;

      if($objDigitalContent->CollectionID)
      {
         $collectionID = $objDigitalContent->CollectionID;
         $objDigitalContent->Collection = new Collection($collectionID);
         $strCollection = $objDigitalContent->Collection->toString();

         if($objDigitalContent->CollectionContentID)
         {
            $collectionContentID = $objDigitalContent->CollectionContentID;
            $arrContent = array();
            foreach($_ARCHON->traverseCollectionContent($collectionContentID) as $obj)
            {
               $arrContent[] = $obj->toString();
            }
            $strCollectionContent = implode(' » ', $arrContent);
         }
      }




      $generalSection->insertRow('collectionid')->insertAdvancedSelect('CollectionID',
              array(
                  'Class' => 'Collection',
                  'Multiple' => false,
                  'toStringArguments' => array(),
                  'params' => array(
                      'p' => 'admin/collections/collections',
                      'f' => 'search',
                      'searchtype' => 'json',
                  ),
                  'quickAdd' => "advSelectID =\\\"CollectionIDInput\\\"; admin_ui_opendialog(\\\"collections\\\", \\\"collections\\\");",
                  'searchOptions' => array(
                      array(
                          'label' => $strRepository,
                          'name' => 'RepositoryID',
                          'source' => 'index.php?p=admin/core/repositories&f=list'
                      ),
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

      $generalSection->insertRow('collectioncontentid')->insertHTML($script);
   }

   if($_ARCHON->AdministrativeInterface->Object->ID)
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=digitallibrary/digitalcontent&id={$_ARCHON->AdministrativeInterface->Object->ID}'); $(this).attr('target', '_blank');", 'publicview', false);
   }
   else
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=digitallibrary/digitallibrary'); $(this).attr('target', '_blank');", 'publicview', false);
   }

   $fileSection = $_ARCHON->AdministrativeInterface->insertSection('files', 'multiple');
   $fileSection->setMultipleArguments('File', 'Files', 'dbLoadFiles', array('DigitalContentID' => $_ARCHON->AdministrativeInterface->Object->ID));
   $fileSection->insertRow('files_title')->insertTextField('Title', 25, 100);
   $fileSection->insertRow('files_filecontents')->insertUploadField('FileContents');
   $fileSection->insertRow('files_mediatype')->insertInformation('FileType', NULL, false);
   $fileSection->insertRow('files_displayorder')->insertTextField('DisplayOrder', 3, 10);
   $fileSection->insertRow('files_defaultaccesslevel')->insertRadioButtons('DefaultAccessLevel', array(DIGITALLIBRARY_ACCESSLEVEL_FULL => 'full', DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY => 'previewonly', DIGITALLIBRARY_ACCESSLEVEL_NONE => 'none'));
   //$fileSection->insertRow('files_digitalcontentid')->insertSelect('DigitalContentID', 'getAllDigitalContent');
   $fileSection->insertHiddenField('DigitalContentID');
   $fileSection->insertHiddenField('Filename');
   $fileSection->insertHiddenField('FileTypeID');
   $fileSection->insertHiddenField('Size');


//   $creatorsSection = $_ARCHON->AdministrativeInterface->insertSection('creators', 'creatorsrelation');
//   $creatorsSection->setRelationArguments('Creator', 'searchCreators', 'Creators', 'dbLoadCreators');
//   $creatorsSection->setAestheticRelationArguments(array(), MODULE_CREATORS, 'creators');

   $detaileddescriptionSection = $_ARCHON->AdministrativeInterface->insertSection('detaileddescription');
   $detaileddescriptionSection->insertRow('detaileddescription_scope')->insertTextArea('Scope', 10, 65);
   $detaileddescriptionSection->insertRow('detaileddescription_physicaldescription')->insertTextArea('PhysicalDescription', 10, 65);
   $detaileddescriptionSection->insertRow('detaileddescription_contributor')->insertTextField('Contributor', 50, 100);
   $detaileddescriptionSection->insertRow('detaileddescription_publisher')->insertTextField('Publisher', 50, 255);
   $detaileddescriptionSection->insertRow('detaileddescription_rightsstatement')->insertTextArea('RightsStatement', 10, 65);

//   $subjectsSection = $_ARCHON->AdministrativeInterface->insertSection('subjects', 'relation');
//   $subjectsSection->setRelationArguments('Subject', 'searchSubjects', 'Subjects', 'dbLoadSubjects', true, array('parentid' => NULL, 'subjecttypeid' => 0, 'showchildren' => true));
//   $subjectsSection->setAestheticRelationArguments(array(LINK_NONE, true), MODULE_SUBJECTS, 'subjects');
//   $languagesSection = $_ARCHON->AdministrativeInterface->insertSection('languages', 'relation');
//   $languagesSection->setRelationArguments('Language', 'searchLanguages', 'Languages', 'dbLoadLanguages', false);

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function digitallibrary_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchDigitalContent', array('searchflags' => SEARCH_DIGITALCONTENT, 'repositoryid' => 0, 'collectionid' => 0, 'collectioncontentid' => 0, 'subjectid' => 0, 'creatorid' => 0, 'filetypeid' => 0, 'mediatypeid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function digitallibrary_ui_exec()
{
   global $_ARCHON;

   @set_time_limit(0);

   $objDigitalContent = New DigitalContent($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   /*
     if($_REQUEST['f'] == 'deletefile')
     {
     $objFile = New File($_REQUEST['id']);
     $objFile->dbDelete();

     $location = "?p={$_REQUEST['p']}&f=files&ID={$_REQUEST['digitalcontentid']}";
     }

     else */
   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objDigitalContent = New DigitalContent($_REQUEST);
         $objDigitalContent->ID = $ID;

//         $ResetFileDefaultAccessLevel = false;
//         if($ID)
//         {
//            $tmpDigitalContent = New DigitalContent($ID);
//            $tmpDigitalContent->dbLoad();
//            echo($_ARCHON->Error);
//
//            if($objDigitalContent->DefaultAccessLevel != $tmpDigitalContent->DefaultAccessLevel)
//            {
//               $ResetFileDefaultAccessLevel = true;
//            }
//         }

         $stored = $objDigitalContent->dbStore();
         $ID = $objDigitalContent->ID;

         if($stored && is_array($_REQUEST['relatedsubjectids']))
         {
            $objDigitalContent->dbUpdateRelatedSubjects($_REQUEST['relatedsubjectids']);
         }

         if($stored && is_array($_REQUEST['relatedlanguageids']))
         {
            $objDigitalContent->dbUpdateRelatedLanguages($_REQUEST['relatedlanguageids']);
         }


         if($stored && is_array($_REQUEST['relatedcreatorids']))
         {
            $objDigitalContent->dbUpdateRelatedCreators($_REQUEST['relatedcreatorids'], array($_REQUEST['primarycreatorid']));
         }
         //            echo($_ARCHON->Error);

         if($stored && is_array($_REQUEST['files']) && !empty($_REQUEST['files']))
         {
            $aggregateError = "";

            foreach($_REQUEST['files'] as $FileID => $array)
            {
               $array['id'] = $FileID;
//               $array['digitalcontentid'] = $ID;
               $objFile = New File($array);

//               if($ResetFileDefaultAccessLevel)
//               {
//                  $objFile->DefaultAccessLevel = $objDigitalContent->DefaultAccessLevel;
//               }

               if($objFile->ID == 0)
               {
                  if($_REQUEST['files'][0]['filecontents'])
                  {
                     $objFile->ID = $_REQUEST['files'][0]['filecontents']; //actual ID of uploaded file
                     $objFile->loadFileInfoFromID();
                     $objFile->Title = $objFile->Title ? $objFile->Title : $objFile->Filename;
                     $objFile->dbStore();
                     $aggregateError .= $_ARCHON->Error;
                     $totalSuccess = $totalSuccess ? $success : $totalSuccess;
                  }
                  elseif($_REQUEST['files'][0]['title'] != "")
                  {
                     $_ARCHON->declareError("Could not store File: No file chosen.");
                  }
               }
               else
               {
                  if($array['_fdelete'])
                  {
                     $objFile->dbDelete();
                  }
                  else
                  {
                     $objFile->dbStore();
                     $aggregateError .= $_ARCHON->Error;
                     $objDigitalContent->Files[] = $objFile;
                  }
               }
            }
            //            $_ARCHON->Error = $aggregateError;
            //            echo("Total success: ".bool($totalSuccess));
         }
      }
   }
   elseif($_REQUEST['f'] == 'uploadfile')
   {
      if($_FILES['filecontents']['name'])
      {
         $objFile = New File(0);

         if(!$_FILES['filecontents']['error'])
         {
            $objFile->Title = $_REQUEST['title'] ? $_REQUEST['title'] : $_FILES['filecontents']['name'];
            $objFile->Filename = $_FILES['filecontents']['name'];

            $objFile->TempFileName = $_FILES['filecontents']['tmp_name'];
            $objFile->Size = $_FILES['filecontents']['size'];

            $objFile->DefaultAccessLevel = DIGITALLIBRARY_ACCESSLEVEL_NONE;
            $objFile->DigitalContentID = -1;

            $objFile->dbStore();
         }
         else
         {
            $_ARCHON->declareError("Could not store File: The uploaded file was too large.");
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      if(is_array($_REQUEST['ids']) && !empty($_REQUEST['ids']))
      {
         foreach($_REQUEST['ids'] as $ID)
         {
            $objDigitalContent->ID = $ID;
            $objDigitalContent->dbDelete();
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
      $msg = "DigitalLibrary Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
