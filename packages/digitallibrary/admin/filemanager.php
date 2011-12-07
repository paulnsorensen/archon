<?php

/**
 * File Types Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

files_ui_initialize();

// Determine what to do based upon user input

function files_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      files_ui_main();
   }
   elseif($_REQUEST['f'] == 'search')
   {
      files_ui_search();
   }
   elseif($_REQUEST['f'] == 'cachefiles')
   {
      files_ui_cachefiles();
   }
   else
   {
      files_ui_exec(); // No interface needed, include an execution file.
   }
}

function files_ui_cachefiles()
{
   global $_ARCHON;

   while(@ob_end_clean())
      ;


   ob_implicit_flush();


   if(!$_ARCHON->config->CacheFiles)
   {
      echo('Caching is not enabled... quitting.');
      die();
   }

   $arrFiles = $_ARCHON->getLinkedFiles();
   foreach($arrFiles as $file)
   {
      if(!$file->cached())
      {
         echo('Caching file ' . $file->getString('Filename') . ' to packages/digitallibrary/files/' . $file->ID . '/<br />');
         if(!$file->dbStore())
         {
            echo('<span style="color:red">' . $_ARCHON->Error . '</span><br/>');
            $_ARCHON->Error = NULL;
         }
         ob_flush();
      }
   }

   echo('<br/>All files are cached!');
}

// files_ui_main()
//   - purpose: Creates the primary user interface
//              for the filetype Manager.

function files_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('File');



   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('files_title')->insertTextField('Title', 25, 100);
//   $fileSection->insertRow('files_filecontents')->insertUploadField('FileContents');
   $generalSection->insertRow('files_mediatype')->insertInformation('FileType', NULL, false);
   $generalSection->insertRow('files_filename')->insertInformation('Filename');
   $generalSection->insertRow('files_digitalcontentid')->insertAdvancedSelect('DigitalContentID', array(
       'Class' => 'DigitalContent',
       'Multiple' => false,
       'params' => array(
           'p' => 'admin/digitallibrary/digitallibrary',
           'f' => 'search',
           'searchtype' => 'json'
       )
   ));
   $generalSection->insertRow('files_defaultaccesslevel')->insertRadioButtons('DefaultAccessLevel', array(DIGITALLIBRARY_ACCESSLEVEL_FULL => 'full', DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY => 'previewonly', DIGITALLIBRARY_ACCESSLEVEL_NONE => 'none'));
   $generalSection->getRow('files_defaultaccesslevel')->setEnableConditions('DigitalContentID', array(0, -1, 'null'), true, true);

   $generalSection->insertRow('files_displayorder')->insertTextField('DisplayOrder', 3, 10);
   $generalSection->getRow('files_displayorder')->setEnableConditions('DigitalContentID', array(0, -1, 'null'), true, true);

//   $fileSection->insertHiddenField('DigitalContentID');
   $generalSection->insertHiddenField('Filename');
   $generalSection->insertHiddenField('FileTypeID');
   $generalSection->insertHiddenField('Size');

   if(!$_ARCHON->config->CacheFiles)
   {
      $strCached = '<span style="color:#bbb;font-weight:bolder">Caching not enabled</span>';
//      $cachedFiles = '<span style="color:#bbb;">N/A</span>';
   }
   else
   {
      $objFile = $_ARCHON->AdministrativeInterface->Object;
      if($objFile->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_NONE)
      {
         $strCached = '<span style="color:#bbb;font-weight:bolder">Access Level does not permit caching</span>';
//         $cachedFiles = '<span style="color:#bbb;">None</span>';
      }
      else
      {
         if($objFile->cached())
         {
            $strCached = '<span style="color:lime;font-weight:bolder">YES</span>';
            $file_cache_path = 'packages/digitiallibrary/files/' . $objFile->ID;
         }
         else
         {
            $strCached = '<span style="color:red;font-weight:bolder">NO</span>';
         }
      }
   }


   $cacheField = $generalSection->insertRow('files_cached')->insertHTML($strCached);
   $_ARCHON->AdministrativeInterface->addReloadField($cacheField);

   $cachedFiles = $objFile ? $objFile->getCachedFileArray() : array();

   $cachedFilesField = $generalSection->insertRow('files_cachedfiles')->insertInformation('CachedFiles', $cachedFiles, true);

   if($_ARCHON->config->CacheFiles)
   {
      $display_button = false;
      $arrFiles = $_ARCHON->getLinkedFiles();
      foreach($arrFiles as $file)
      {
         if(!$file->cached())
         {
            $display_button = true;
            break;
         }
      }

      if($display_button)
      {
         $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_cachefilesdialog()", 'cachefiles', false);

         ob_start();
         ?>
         <script type='text/javascript'>
            /* <![CDATA[ */

            function admin_ui_cachefilesdialog(){
               var response = $('#response');
               response.dialog('option', 'width', 600);
               var loading = $('<div />')
               .text('Caching files...')
               .css('background', 'transparent url("adminthemes/default/images/indicator.gif") top left no-repeat')
               .css('padding-left', '20px')
               .appendTo(response);
               var log = $('<div />')
               .css('padding', '6px')
               .css('height', '400px')
               .css('overflow', 'auto')
               .css('color', 'white')
               .css('background', '#333')
               .css('font-size', '12px')
               .css('font-weight', 'normal')
               .appendTo(response);
               response.dialog('open');

               $.ajax({
                  url: 'index.php',
                  data: {
                     p: 'admin/digitallibrary/filemanager',
                     f: 'cachefiles'
                  },
                  dataType: 'html',
                  success: function (html) {

                     log.html(html);
                     loading.remove();
                  }
               });
            }

            /* ]]> */
         </script>
         <?php

         $script = ob_get_clean();
         $generalSection->getRow('files_mediatype')->insertHTML($script);
      }
   }
   $_ARCHON->AdministrativeInterface->insertSearchOption('SearchFlags', 'getFileSearchOptions', 'filesearchoptions');

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function files_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchFiles', array('searchflags' => SEARCH_FILES_UNLINKED, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function files_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objFileType = New File($_REQUEST);
         $objFileType->ID = $ID;
         if(!$objFileType->DigitalContentID)
         {
            $objFileType->DigitalContentID = -1;
         }
         $objFileType->dbStore();
         $ID = $objFileType->ID;
      }
   }
   else if($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objFileType = New File($ID);
         $objFileType->dbDelete();
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
      $msg = "FileType Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
