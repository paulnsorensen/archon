<?php
/**
 * Database Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel; converted to new interface by Chris Prom, 1/29/2009
 */

isset($_ARCHON) or die();

@set_time_limit(0);

database_ui_initialize();

function database_ui_initialize()
{
   global $_ARCHON;

   if(!$_REQUEST['f'])
   {
      database_ui_main();
   }
   elseif($_REQUEST['f'] == 'dialog_import')
   {
      database_ui_dialog_import();
   }
   elseif($_REQUEST['f'] == 'dialog_export')
   {
      database_ui_dialog_export();
   }
   elseif($_REQUEST['f'] == 'import')
   {
      list($APRCode, $ImportUtility) = explode('/', $_REQUEST['importutility']);

      if($_ARCHON->Packages[$APRCode] && file_exists("packages/$APRCode/db/import-$ImportUtility.inc.php"))
      {
         $_REQUEST['f'] = "import-$ImportUtility";

         // Remove output buffers for database scripts.
         while (@ob_end_flush());

         require("packages/$APRCode/db/import-$ImportUtility.inc.php");

         if($_REQUEST['go'])
         {
            echo("<script type='text/javascript'>\n");
            echo("location = '{$_REQUEST['go']}';");
            echo("</script>\n");
         }
      }
   }
   elseif($_REQUEST['f'] == 'export')
   {
      list($APRCode, $ExportUtility) = explode('/', $_REQUEST['exportutility']);

      if($_ARCHON->Packages[$APRCode] && file_exists("packages/$APRCode/db/export-$ExportUtility.inc.php"))
      {
         $_REQUEST['f'] = "export-$ExportUtility";

         // Remove output buffers for database scripts.
         while (@ob_end_clean());

         require("packages/$APRCode/db/export-$ExportUtility.inc.php");
      }
   }
}


function database_ui_dialog_export()
{
   global $_ARCHON;

   $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/core/database', 'export');

   list($APRCode, $ExportUtility) = explode('/', $_REQUEST['exportutility']);

   $objNamePhrase = Phrase::getPhrase('export_' . $ExportUtility, $_ARCHON->Packages[$APRCode]->ID, 0, PHRASETYPE_ADMIN);
   $strNamePhrase = $objNamePhrase ? $objNamePhrase->getPhraseValue(ENCODE_HTML) : $ExportUtility;
   $objDescriptionPhrase = Phrase::getPhrase('export_' . $ExportUtility, $_ARCHON->Packages[$APRCode]->ID, 0, $DescriptionPhraseTypeID);
   $strDescriptionPhrase = $objDescriptionPhrase ? $objDescriptionPhrase->getPhraseValue(ENCODE_HTML) : NULL;

   $dialogSection->insertRow('name')->insertInformation('Name', $strNamePhrase);
   $dialogSection->getRow('name')->insertHTML("<input type='hidden' class='reloadparam' name='exportutility' value='{$_REQUEST['exportutility']}' />");

   if($strDescriptionPhrase)
   {
      $dialogSection->insertRow('description')->insertInformation('Description', $strDescriptionPhrase);
   }

   $arrExportUtilities = $_ARCHON->getAllDatabaseExportUtilities();


   $objExportUtility = $arrExportUtilities[$_ARCHON->Packages[$APRCode]->ID][$ExportUtility];

   if($objExportUtility->InterfaceFile)
   {
      include($objExportUtility->InterfaceFile);
   }

   $_ARCHON->AdministrativeInterface->outputInterface();
}



function database_ui_dialog_import()
{
   global $_ARCHON;

   $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');

//   $objInvalidUtilityPhrase = Phrase::getPhrase('invalidutility', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
//   $strInvalidUtility = $objInvalidUtilityPhrase ? $objInvalidUtilityPhrase->getPhraseValue(ENCODE_HTML) : 'Invalid Utility';
//
//   $objRequiresFilesPhrase = Phrase::getPhrase('requiresfiles', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
//   $strRequiresFiles = $objRequiresFilesPhrase ? $objRequiresFilesPhrase->getPhraseValue(ENCODE_HTML) : 'This utility requires a file as input';
//   $objOrPhrase = Phrase::getPhrase('or', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
//   $strOr = $objOrPhrase ? $objOrPhrase->getPhraseValue(ENCODE_HTML) : 'or';
//
//   $objAreYouSurePhrase = Phrase::getPhrase('areyousure', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
//   $strAreYouSure = $objAreYouSurePhrase ? $objAreYouSurePhrase->getPhraseValue(ENCODE_JAVASCRIPT) : 'Are you sure?';
//
//   $objPleaseWaitPhrase = Phrase::getPhrase('pleasewait', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
//   $strPleaseWait = $objPleaseWaitPhrase ? $objPleaseWaitPhrase->getPhraseValue(ENCODE_JAVASCRIPT) : 'Please wait...';

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/core/database', 'import');

   list($APRCode, $ImportUtility) = explode('/', $_REQUEST['importutility']);

   $objNamePhrase = Phrase::getPhrase('import_' . $ImportUtility, $_ARCHON->Packages[$APRCode]->ID, 0, PHRASETYPE_ADMIN);
   $strNamePhrase = $objNamePhrase ? $objNamePhrase->getPhraseValue(ENCODE_HTML) : $ImportUtility;
   $objDescriptionPhrase = Phrase::getPhrase('import_' . $ImportUtility, $_ARCHON->Packages[$APRCode]->ID, 0, $DescriptionPhraseTypeID);
   $strDescriptionPhrase = $objDescriptionPhrase ? $objDescriptionPhrase->getPhraseValue(ENCODE_HTML) : NULL;

   $dialogSection->insertRow('name')->insertInformation('Name', $strNamePhrase);
   $dialogSection->getRow('name')->insertHTML("<input type='hidden' class='reloadparam' name='importutility' value='{$_REQUEST['importutility']}' />");

   if($strDescriptionPhrase)
   {
      $dialogSection->insertRow('description')->insertInformation('Description', $strDescriptionPhrase);
   }

   $arrImportUtilities = $_ARCHON->getAllDatabaseImportUtilities();


   $objImportUtility = $arrImportUtilities[$_ARCHON->Packages[$APRCode]->ID][$ImportUtility];

   if($objImportUtility->InterfaceFile)
   {
      include($objImportUtility->InterfaceFile);
   }
   

   if($objImportUtility->InputFile)
   {
      $arrIncomingFiles = $_ARCHON->getAllIncomingFileLocations();


      if(!empty($arrIncomingFiles))
      {
         $arrInFileOpts = array();

         $arrCompressionExtensions = get_enabled_compression_extensions();

         $arrExtensions = array_merge($objImportUtility->Extensions, $arrCompressionExtensions);

         if(in_array("*", $arrExtensions))
         {
            $AllFiles = true;
         }

         foreach($arrIncomingFiles as $Filename => $Location)
         {
            if($AllFiles || array_search(encoding_strtolower(encoding_substr(strrchr($Filename, "."), 1)), $arrExtensions) !== false)
            {
               $encFilename = encode($Filename, ENCODE_HTML);
               $arrInFileOpts[] = "<option value='$encFilename'>$encFilename</option>";
            }
         }

         if(!empty($arrInFileOpts))
         {
            $html = '<select multiple name="serverfiles[]">';
            $html .= implode('\n', $arrInFileOpts);
            $html .= '</select>';
            $dialogSection->insertRow('selectincoming')->insertHTML($html);
         }
      }


      $dialogSection->insertRow('uploadfile')->insertHTML("<input class='uploadfield' type='file' name='uploadfile' />");
   }





   $_ARCHON->AdministrativeInterface->outputInterface();
}



function database_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->getSection('browse')->disable();
   $_ARCHON->AdministrativeInterface->disableQuickSearch();




   $objModulePhrase = Phrase::getPhrase('header', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strModule = $objModulePhrase ? $objModulePhrase->getPhraseValue(ENCODE_HTML) : 'Archon Module';
   //$strHeaderHelp = $_ARCHON->AdministrativeInterface->createHelpButton('header', $_ARCHON->Package->ID, $_ARCHON->Module->ID);

   $objImportPhrase = Phrase::getPhrase('importdatabase', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strImport = $objImportPhrase ? $objImportPhrase->getPhraseValue(ENCODE_HTML) : 'Import Database';
   //$strImportHelp = $_ARCHON->AdministrativeInterface->createHelpButton('importdatabase', $_ARCHON->Package->ID, $_ARCHON->Module->ID);

   $objExportPhrase = Phrase::getPhrase('export', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strExport = $objExportPhrase ? $objExportPhrase->getPhraseValue(ENCODE_HTML) : 'Export Database';
   //$strExportHelp = $_ARCHON->AdministrativeInterface->createHelpButton('export', $_ARCHON->Package->ID, $_ARCHON->Module->ID);

   $objInstalledPhrase = Phrase::getPhrase('installed', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strInstalled = $objInstalledPhrase ? $objInstalledPhrase->getPhraseValue(ENCODE_HTML) : 'Installed Utilities';
  

   $objDatabaseInfoPhrase = Phrase::getPhrase('databaseinfo', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strDatabaseInfo = $objDatabaseInfoPhrase ? $objDatabaseInfoPhrase->getPhraseValue(ENCODE_HTML) : 'Database Information';

   $objDatabaseServerPhrase = Phrase::getPhrase('databaseserver', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strDatabaseServerPhrase = $objDatabaseServerPhrase ? $objDatabaseServerPhrase->getPhraseValue(ENCODE_HTML) : 'Database Server';
   $strDatabaseServer = encode($_ARCHON->db->ServerAddress, ENCODE_HTML);
   $objDatabaseNamePhrase = Phrase::getPhrase('databasename', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strDatabaseNamePhrase = $objDatabaseNamePhrase ? $objDatabaseNamePhrase->getPhraseValue(ENCODE_HTML) : 'Database Name';
   $strDatabaseName = encode($_ARCHON->db->DatabaseName, ENCODE_HTML);

   $dbStats = $_ARCHON->getDBStats();
   $objDatabaseLoginPhrase = Phrase::getPhrase('databaselogin', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strDatabaseLoginPhrase = $objDatabaseLoginPhrase ? $objDatabaseLoginPhrase->getPhraseValue(ENCODE_HTML) : 'Database Login';
   $strDatabaseLogin = encode($_ARCHON->db->Login, ENCODE_HTML);
   $objDiskUsagePhrase = Phrase::getPhrase('diskusage', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strDiskUsagePhrase = $objDiskUsagePhrase ? $objDiskUsagePhrase->getPhraseValue(ENCODE_HTML) : 'Disk Usage';
   $strDiskUsage = encode($dbStats->DiskUsed, ENCODE_HTML);
   $strDiskFree = encode($dbStats->DiskFree, ENCODE_HTML);


   $objTableInformationPhrase = Phrase::getPhrase('tableinformation', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strTableInformation = $objTableInformationPhrase ? $objTableInformationPhrase->getPhraseValue(ENCODE_HTML) : 'Table Information';

   $objTableNamePhrase = Phrase::getPhrase('tablename', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strTableName = $objTableNamePhrase ? $objTableNamePhrase->getPhraseValue(ENCODE_HTML) : 'Table Name';
   $objRowsPhrase = Phrase::getPhrase('rows', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strRows = $objRowsPhrase ? $objRowsPhrase->getPhraseValue(ENCODE_HTML) : 'Rows';
   $objLastModifiedPhrase = Phrase::getPhrase('lastmodified', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strLastModified = $objLastModifiedPhrase ? $objLastModifiedPhrase->getPhraseValue(ENCODE_HTML) : 'Last Modified';
   $objModifiedByPhrase = Phrase::getPhrase('modifiedby', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strModifiedBy = $objModifiedByPhrase ? $objModifiedByPhrase->getPhraseValue(ENCODE_HTML) : 'Modified By';
   $objExportShortPhrase = Phrase::getPhrase('exportshort', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strExportShort = $objExportShortPhrase ? $objExportShortPhrase->getPhraseValue(ENCODE_HTML) : 'Export';

   $objLaunchPhrase = Phrase::getPhrase('launch', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strLaunch = $objLaunchPhrase ? $objLaunchPhrase->getPhraseValue(ENCODE_HTML) : 'Launch';

   $browseSection = $_ARCHON->AdministrativeInterface->getSection('browse');
   $browseSection->disable();

   $arrPackages = $_ARCHON->getAllPackages();
   $arrImportUtilities = $_ARCHON->getAllDatabaseImportUtilities();
   $arrCompressionExtensions = get_enabled_compression_extensions();
   $arrExportUtilities = $_ARCHON->getAllDatabaseExportUtilities();

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
   $generalSection->insertRow('databaseinfo')->insertHTML("$strDatabaseServerPhrase: $strDatabaseServer<br/>");
   $generalSection->getRow('databaseinfo')->insertHTML("$strDatabaseNamePhrase: $strDatabaseName<br/>");
   $generalSection->getRow('databaseinfo')->insertHTML("$strDatabaseLoginPhrase: $strDatabaseLogin<br/>");
   //    $generalSection->getRow($strDatabaseInfo)->insertHTML("$strDiskUsagePhrase: $strDiskUsage / $strDiskFree<br/>");
   $generalSection->getRow('databaseinfo')->insertHTML("$strDiskUsagePhrase: $strDiskUsage<br/>");


   if(!empty($arrImportUtilities))
   {
      $arrImportOpts = array();
      foreach($arrImportUtilities as $ID => $arrPackageImportUtilities)
      {
         ksort($arrPackageImportUtilities);

         foreach($arrPackageImportUtilities as $UtilityCode => $obj)
         {
            $objNamePhrase = Phrase::getPhrase('import_' . $UtilityCode, $ID, 0, PHRASETYPE_ADMIN);
            $strNamePhrase = $objNamePhrase ? $objNamePhrase->getPhraseValue(ENCODE_HTML) : $UtilityCode;
            $strExtensionList = $obj->Extensions ? ' (' . implode(',', array_merge($obj->Extensions, $arrCompressionExtensions)) . ')' : '';

            $arrImportOpts["{$_ARCHON->Packages[$ID]->APRCode}/{$UtilityCode}"] = $strNamePhrase . $strExtensionList;

         }
      }

      $import_select = $generalSection->insertRow('import')->insertSelect('importutility', $arrImportOpts, array(), NULL, 70);
      $import_select->Watch = false;
      ob_start();
      ?>
<a id="launchimport" href="#"><?php echo($strLaunch); ?></a>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $('#launchimport').button({icons:{primary: 'ui-icon-newwin'}, disabled: true})
      .click(function(){admin_ui_launchimport(); return false});

      $('#importutilityInput').change(function(){
         if($(this).val() != "0"){
            $('#launchimport').button('enable');
         }else{
            $('#launchimport').button('disable');
         }
      })
   });

   function admin_ui_submitimport()
   {
      $('#dialogmodal .relatedselect>*').attr('selected','selected');


      var admindialog = $('#dialogmodal');
      $('#dialogform').ajaxForm({
         dataType: 'html',
         success: function (html) {
            $('#dialogloadingscreen').hide();
            admindialog.dialog('close');
            if(dialogCallback){
               dialogCallback();
            }

            var response = $('#response');
            response.dialog('option', 'width', 600);

            var log = $('<div />')
            .css('padding', '6px')
            .css('height', '400px')
            .css('overflow', 'auto')
            .css('color', 'white')
            .css('background', '#333')
            .css('font-size', '12px')
            .css('font-weight', 'normal')
            .appendTo(response);
            log.html(html);

            response.dialog('open');

         }
      });
      $('#dialogform').submit();

      $('#dialogform .relatedselect>*').removeAttr('selected');
   }




   function admin_ui_launchimport(){
      var importutility = $('#importutilityInput').val();

      if(importutility != "0"){

         var dialog = $('#dialogmodal');
         var orig_buttons = dialog.dialog('option', 'buttons');


         dialog.dialog('option', 'buttons', {
            Import: function(){
               $('#dialogloadingscreen').show();
               admin_ui_submitimport();
               $(this).dialog('option','buttons', orig_buttons);
            },
            Cancel: function(){
               $(this).dialog('close');
               $(this).dialog('option','buttons', orig_buttons);
            }
         });

         admin_ui_opendialog('core','database', 'import', {importutility: importutility});
      }
   }

   /* ]]> */
</script>
      <?php
      $button = ob_get_clean();

      $generalSection->getRow('import')->insertHTML($button);

   }


   if(!empty($arrExportUtilities))
   {
      $arrExportOpts = array();
      foreach($arrExportUtilities as $ID => $arrPackageExportUtilities)
      {
         ksort($arrPackageExportUtilities);

         foreach($arrPackageExportUtilities as $UtilityCode => $obj)
         {
            $objNamePhrase = Phrase::getPhrase('export_' . $UtilityCode, $ID, 0, PHRASETYPE_ADMIN);
            $strNamePhrase = $objNamePhrase ? $objNamePhrase->getPhraseValue(ENCODE_HTML) : $UtilityCode;


            $arrExportOpts["{$_ARCHON->Packages[$ID]->APRCode}/{$UtilityCode}"] = $strNamePhrase;

         }
      }

      $export_select = $generalSection->insertRow('export')->insertSelect('exportutility', $arrExportOpts, array(), NULL, 70);
      $export_select->Watch = false;
      ob_start();
      ?>
<a id="launchexport" href="#"><?php echo($strLaunch); ?></a>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $('#launchexport').button({icons:{primary: 'ui-icon-newwin'}, disabled: true})
      .click(function() {admin_ui_launchexport(); return false});

      $('#exportutilityInput').change(function(){
         if($(this).val() != "0"){
            $('#launchexport').button('enable');
         }else{
            $('#launchexport').button('disable');
         }
      })
   });




   function admin_ui_launchexport(){
      var exportutility = $('#exportutilityInput').val();

      if(exportutility != "0"){

         var dialog = $('#dialogmodal');
         var orig_buttons = dialog.dialog('option', 'buttons');


         dialog.dialog('option', 'buttons', {
            Export: function(){
               $('#dialogloadingscreen').show();
               $('#dialogmodal .relatedselect>*').attr('selected','selected');
               location.href = 'index.php?' + $('#dialogform :input').fieldSerialize();
               $('#dialogform .relatedselect>*').removeAttr('selected');
               $('#dialogloadingscreen').hide();
               $(this).dialog('close');
            },
            Cancel: function(){
               $(this).dialog('close');
               $(this).dialog('option','buttons', orig_buttons);
            }
         });

         admin_ui_opendialog('core','database', 'export', {exportutility: exportutility});
      }
   }

   /* ]]> */
</script>
      <?php
      $button = ob_get_clean();

      $generalSection->getRow('export')->insertHTML($button);

   }



   $tableinformationsection = $_ARCHON->AdministrativeInterface->insertSection('tableinformation', 'custom');
   ob_start();
   ?>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $('#moduletabs').bind('tabsshow', function (event, ui) {
         if($('#moduletabs').tabs('option','selected') == 2)
         {
            $('#sectionloadingscreen').show();


            $('#<?php echo($tableinformationsection->Name); ?>sectionbody').load('index.php #<?php echo($tableinformationsection->Name); ?>sectionbody>*', {
               p: '<?php echo($_REQUEST['p']); ?>',
               adminoverridesection: '<?php echo($tableinformationsection->Name); ?>'
            },
            function() {
               $('#sectionloadingscreen').fadeOut('slow');

            });
         }
         else
         {
            $('#sectionloadingscreen').hide();
         }
      });

      $('#refreshimportutility').button({icons:{primary: 'ui-icon-refresh'}, text: false});
   });
   /* ]]> */
</script>
   <?php
   if($_REQUEST['adminoverridesection'] == 'tableinformation')
   {
      ?>
<div style="max-height:500px; overflow: auto">
   <table id="databasetables">
      <tr>
         <td><b><?php echo($strTableName); ?></b></td>
         <td><b><?php echo($strRows); ?></b></td>
         <td><b><?php echo($strDiskUsagePhrase); ?></b></td>
         <td><b><?php echo($strLastModified); ?></b></td>
         <td><b><?php echo($strModifiedBy); ?></b></td>
      </tr>
            <?php
            $dbStructure = $_ARCHON->getDBStructure();

            $arrPackages = array();
            foreach($_ARCHON->Packages as $key => $objPackage)
            {
               if(!is_natural($key))
               {
                  $arrPackages[$objPackage->ID] = clone $objPackage;
                  $arrPackages[$key] = $arrPackages[$objPackage->ID];
               }
            }

            $arrAllPackages = $_ARCHON->getAllPackages(false);
            // $_ARCHON->Packages is missing the disabled packages. They're information needs filled in.
            foreach($arrAllPackages as $objPackage)
            {
               if(!$objPackage->Enabled)
               {
                  include("packages/{$objPackage->APRCode}/index.php");
               }
            }

            $_ARCHON->Packages = $arrPackages;

            foreach($dbStructure as $tblName => $obj)
            {
               list($prefix, $title) = explode("_", $tblName);

               if(encoding_strpos(encoding_strtoupper($_ARCHON->db->TablePrefixes), encoding_strtoupper($prefix)."_") !== false)
               {
                  $tables[$tblName] = $obj;
               }
            }

            if(CONFIG_CORE_MODIFICATION_LOG_ENABLED)
            {
               $query = "SELECT Timestamp, Login FROM tblCore_ModificationLog WHERE TableName = ? ORDER BY Timestamp DESC";
               $_ARCHON->mdb2->setLimit(1);
               $prep = $_ARCHON->mdb2->prepare($query, 'text', MDB2_PREPARE_RESULT);
            }
            foreach($tables as $tblName => $obj)
            {
               if(CONFIG_CORE_MODIFICATION_LOG_ENABLED)
               {
                  $result = $prep->execute($tblName);
                  if (PEAR::isError($result))
                  {
                     trigger_error($result->getMessage(), E_USER_ERROR);
                  }

                  $row = $result->fetchRow();
                  $result->free();

                  $datestring = $row['Timestamp'] ? date(CONFIG_CORE_DATE_FORMAT, $row['Timestamp']) : $datestring = "N/A";
                  $loginstring = $row['Login'];
               }
               else
               {
                  $datestring = "<span class='admincomment'>N/A</span>";
                  $loginstring =  "<span class='admincomment'>N/A</span>";
               }
               ?>
      <tr>
         <td><?php echo($tblName); ?></td>
         <td><?php echo($dbStats->Tables[$tblName]->Rows); ?></td>
         <td><?php echo($dbStats->Tables[$tblName]->DiskUsed); ?></td>
         <td><?php echo($datestring); ?></td>
         <td><?php echo($loginstring); ?></td>
      </tr>
               <?php
            }
            if(CONFIG_CORE_MODIFICATION_LOG_ENABLED)
            {
               $prep->free();
            }
            ?>
   </table>
</div>
      <?php
   }

   $strTablesTable = ob_get_clean();
   $tableinformationsection->setCustomArguments($strTablesTable);


   $_ARCHON->AdministrativeInterface->outputInterface();

}








function database_importexec()
{
   global $_ARCHON;

   ob_implicit_flush();

   $_ARCHON->AdministrativeInterface->Header->NoControls = true;

   include("header.inc.php");

   $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');

   $objInvalidUtilityPhrase = Phrase::getPhrase('invalidutility', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strInvalidUtility = $objInvalidUtilityPhrase ? $objInvalidUtilityPhrase->getPhraseValue(ENCODE_HTML) : 'Invalid Utility';

   $in_ImportFile = $_REQUEST['f'] ? $_REQUEST['f'] : NULL;


   if(!$in_ImportFile)
   {
      include("footer.inc.php");
      return;
   }
   else
   {
      if(file_exists('database/' . $in_ImportFile . '.inc.php'))
      {
         include('database/' . $in_ImportFile . '.inc.php');
      }
      else
      {
         echo($strInvalidUtility);
         include("footer.inc.php");
         return;
      }
   }

   include("footer.inc.php");
}
?>