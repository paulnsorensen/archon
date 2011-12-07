<?php
/**
 * Modification Log Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

modificationlog_ui_initialize();

// Determine what to do based upon user input
function modificationlog_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      // Determine what to do based upon user input
      modificationlog_ui_main();
   }
   else
   {
      modificationlog_ui_exec();
   }

}

// Determine what to do based upon user input
//modificationlog_ui_main();

function modificationlog_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->disableQuickSearch();




   $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');

   $objSearchPhrase = Phrase::getPhrase('search', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strSearch = $objSearchPhrase ? $objSearchPhrase->getPhraseValue(ENCODE_HTML) : 'Search';

   $objDetailsHeaderPhrase = Phrase::getPhrase('requestdata', PACKAGE_CORE, MODULE_MODIFICATIONLOG, PHRASETYPE_ADMIN);
   $strDetailsHeader = $objDetailsHeaderPhrase ? $objDetailsHeaderPhrase->getPhraseValue(ENCODE_HTML) : 'Details';

   $objIDPhrase = Phrase::getPhrase('id', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strID = $objIDPhrase ? $objIDPhrase->getPhraseValue(ENCODE_HTML) : 'Entry #';
   $objTableNamePhrase = Phrase::getPhrase('tablename', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strTableName = $objTableNamePhrase ? $objTableNamePhrase->getPhraseValue(ENCODE_HTML) : 'Table Name';
   $objRowIDPhrase = Phrase::getPhrase('rowid', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strRowID = $objRowIDPhrase ? $objRowIDPhrase->getPhraseValue(ENCODE_HTML) : 'ID';
   $objTimestampPhrase = Phrase::getPhrase('timestamp', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strTimestamp = $objTimestampPhrase ? $objTimestampPhrase->getPhraseValue(ENCODE_HTML) : 'Time';
   $objUserPhrase = Phrase::getPhrase('user', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strUser = $objUserPhrase ? $objUserPhrase->getPhraseValue(ENCODE_HTML) : 'User';
   $objRemoteHostPhrase = Phrase::getPhrase('remotehost', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strRemoteHost = $objRemoteHostPhrase ? $objRemoteHostPhrase->getPhraseValue(ENCODE_HTML) : 'Remote Host';
   $objModulePhrase = Phrase::getPhrase('module', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strModule = $objModulePhrase ? $objModulePhrase->getPhraseValue(ENCODE_HTML) : 'Module';
   $objArchonFunctionPhrase = Phrase::getPhrase('archonfunction', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strArchonFunction = $objArchonFunctionPhrase ? $objArchonFunctionPhrase->getPhraseValue(ENCODE_HTML) : 'Function';

   $objRequestDataPhrase = Phrase::getPhrase('requestdata', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strRequestData = $objRequestDataPhrase ? $objRequestDataPhrase->getPhraseValue(ENCODE_HTML) : 'Details';

   $objNoEntriesPhrase = Phrase::getPhrase('noentries', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strNoEntries = $objNoEntriesPhrase ? $objNoEntriesPhrase->getPhraseValue(ENCODE_HTML) : 'No modification log entries found.';

   $objModLogDisabledPhrase = Phrase::getPhrase('modlogdisabled', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strModLogDisabled = $objModLogDisabledPhrase ? $objModLogDisabledPhrase->getPhraseValue(ENCODE_HTML) : 'modlogdisabled';

   $objPurgeTitlePhrase = Phrase::getPhrase('purgetitle', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strPurgeTitle = $objPurgeTitlePhrase ? $objPurgeTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Purge Modification Log Entries';

   $objPurgeRecordsPhrase = Phrase::getPhrase('purgerecords', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strPurgeRecords = $objPurgeRecordsPhrase ? $objPurgeRecordsPhrase->getPhraseValue(ENCODE_HTML) : 'Delete records older than';

   $obj5YearsPhrase = Phrase::getPhrase('5years', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $str5Years = $obj5YearsPhrase ? $obj5YearsPhrase->getPhraseValue(ENCODE_HTML) : '5 Years';

   $obj2YearsPhrase = Phrase::getPhrase('2years', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $str2Years = $obj2YearsPhrase ? $obj2YearsPhrase->getPhraseValue(ENCODE_HTML) : '2 Years';

   $obj1YearPhrase = Phrase::getPhrase('1year', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $str1Year = $obj1YearPhrase ? $obj1YearPhrase->getPhraseValue(ENCODE_HTML) : '1 Year';

   $obj6MonthsPhrase = Phrase::getPhrase('6months', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $str6Months = $obj6MonthsPhrase ? $obj6MonthsPhrase->getPhraseValue(ENCODE_HTML) : '6 Months';

   $in_q = $_REQUEST['q'] ? $_REQUEST['q'] : '';
   $in_orderbycolumn = $_REQUEST['orderbycolumn'] ? $_REQUEST['orderbycolumn'] : 'Timestamp';
   $in_orderbydirection = $_REQUEST['orderbydirection'] == 'asc' ? ASCENDING : DESCENDING;

   $arrModificationLogEntries = $_ARCHON->searchModificationLogEntries($in_q, $in_orderbycolumn, $in_orderbydirection);

   $browseSection = $_ARCHON->AdministrativeInterface->getSection('browse');
   $browseSection->disable();

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
   $generalSection->Type = 'custom';

   ob_start();

   if(!CONFIG_CORE_MODIFICATION_LOG_ENABLED)
   {
      echo($strModLogDisabled);
   }
   else
   {
      ?>
<script type="text/javascript">
   /* <![CDATA[ */

   var logOrderByColumn = 'Timestamp';
   var logOrderByDirection = '<?php echo(DESCENDING); ?>';

   var logFilterTimeout;
   function useLogFilter(highlight)
   {
      if(highlight)
      {
         $('#modlogfilterfield').effect('highlight');
      }

      $('#modlogtable').load('index.php #modlogtable>*', {
         p: '<?php echo($_REQUEST['p']); ?>',
         q: $('#modlogfilterfield').val(),
         orderbycolumn: logOrderByColumn,
         orderbydirection: logOrderByDirection
      });
   }

   function admin_ui_modlogarrowclick(orderbycolumn, orderbydirection)
   {
      logOrderByColumn = orderbycolumn;
      logOrderByDirection = orderbydirection;

      useLogFilter(false);
   }

   $(function () {
      $('#requestdetailscontainer').dialog({modal:true, autoOpen:false, overlay:{opacity: 0.4, background: "black"}, draggable:false, resizable: true, width: 700});

      admin_ui_delegationbind('click', '.logrequestbutton span', function (e) {
         var requestdata = $(e.target).parent().siblings('.logrequestdata').text();
         var requestdetailscontainer = $('#requestdetailscontainer');
         requestdetailscontainer.text(requestdata);
         requestdetailscontainer.dialog('open');

         return false;
      });

      $('#modlogfilterfield').bind(($.browser.opera ? "keypress" : "keydown") + ".filter", function (event) {
         if(event.keyCode == 13)
         {
            return false;
         }

         clearTimeout(logFilterTimeout);
         logFilterTimeout = setTimeout(function () { useLogFilter(true); }, 400);
      });

      useLogFilter();

   });

   function admin_ui_purgedialog()
   {

      $("#purge").dialog({
         resizable: false,
         height:140,
         width:325,
         modal: true,
         title: '<?php echo($strPurgeTitle); ?>',
         buttons: {
            'Submit': function() {
               $(this).dialog('close');
               var currentF = $('#fInput').val();
               $('#fInput').val('purge');
               $('#PurgeValue').val($('#PurgeSelect').val());
               $('#mainform').submit();
               $('#fInput').val(currentF);
               useLogFilter(false);
            },
            Cancel: function() {
               $(this).dialog('close');
            }
         }
      });
   }


   /* ]]> */
</script>

<div id="modlogfilterline" style="padding:10px">
   <label for="modlogfilterfield"><?php echo($strSearch); ?>:</label>
   <input type="text" id="modlogfilterfield" name="q" value="" maxlength="200" />
</div>
<div id="requestdetailscontainer" title="<?php echo($strDetailsHeader); ?>"></div>
<table id="modlogtable" style="padding:10px">
   <tr>
      <th><?php echo($strID); ?><a href="#" onclick="admin_ui_modlogarrowclick('ID', 'desc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmalldown.gif" alt="small down arrow" /></a><a href="#" onclick="admin_ui_modlogarrowclick('ID', 'asc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmallup.gif" alt="small up arrow" /></a></th>
      <th><?php echo($strTableName); ?><a href="#" onclick="admin_ui_modlogarrowclick('TableName', 'desc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmalldown.gif" alt="small down arrow" /></a><a href="#" onclick="admin_ui_modlogarrowclick('TableName', 'asc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmallup.gif" alt="small up arrow" /></a></th>
      <th><?php echo($strRowID); ?><a href="#" onclick="admin_ui_modlogarrowclick('RowID', 'desc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmalldown.gif" alt="small down arrow" /></a><a href="#" onclick="admin_ui_modlogarrowclick('RowID', 'asc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmallup.gif" alt="small up arrow" /></a></th>
      <th><?php echo($strTimestamp); ?><a href="#" onclick="admin_ui_modlogarrowclick('Timestamp', 'desc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmalldown.gif" alt="small down arrow" /></a><a href="#" onclick="admin_ui_modlogarrowclick('Timestamp', 'asc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmallup.gif" alt="small up arrow" /></a></th>
      <th><?php echo($strUser); ?><a href="#" onclick="admin_ui_modlogarrowclick('Login', 'desc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmalldown.gif" alt="small down arrow" /></a><a href="#" onclick="admin_ui_modlogarrowclick('Login', 'asc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmallup.gif" alt="small up arrow" /></a></th>
      <th><?php echo($strRemoteHost); ?><a href="#" onclick="admin_ui_modlogarrowclick('RemoteHost', 'desc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmalldown.gif" alt="small down arrow" /></a><a href="#" onclick="admin_ui_modlogarrowclick('RemoteHost', 'asc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmallup.gif" alt="small up arrow" /></a></th>
      <th><?php echo($strModule); ?></th>
      <th><?php echo($strArchonFunction); ?><a href="#" onclick="admin_ui_modlogarrowclick('ArchonFunction', 'desc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmalldown.gif" alt="small down arrow" /></a><a href="#" onclick="admin_ui_modlogarrowclick('ArchonFunction', 'asc');"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/arrowsmallup.gif" alt="small up arrow" /></a></th>
      <th>&nbsp;</th>
   </tr>
         <?php
         if(!count($arrModificationLogEntries))
         {
            ?>
   <tr>
      <td colspan="9" class="center"><?php echo($strNoEntries); ?></td>
   </tr>
            <?php
         }
         else
         {
            $arrUsers = $_ARCHON->getAllUsers();

            foreach($arrModificationLogEntries as $objModificationLogEntry)
            {
               $strDetails = $objModificationLogEntry->getString('RequestData', 0, false);
               ?>
   <tr>
      <td><?php echo($objModificationLogEntry->ID); ?></td>
      <td><?php echo($objModificationLogEntry->getString('TableName')); ?></td>
      <td><?php echo($objModificationLogEntry->RowID); ?></td>
      <td><?php echo(date(CONFIG_CORE_DATE_FORMAT, $objModificationLogEntry->Timestamp)); ?></td>
      <td><?php echo($arrUsers[$objModificationLogEntry->UserID] ? $arrUsers[$objModificationLogEntry->UserID]->getString('Login') : $objModificationLogEntry->getString('Login')); ?></td>
      <td><?php echo($objModificationLogEntry->getString('RemoteHost')); ?></td>
      <td><?php echo($_ARCHON->Modules[$objModificationLogEntry->ModuleID] ? $_ARCHON->Modules[$objModificationLogEntry->ModuleID]->toString() : '&nbsp;'); ?></td>
      <td><?php echo($objModificationLogEntry->getString('ArchonFunction')); ?></td>
      <td><a href="#" class="logrequestbutton adminformbutton"><span><?php echo($strRequestData); ?></span></a><span class="logrequestdata"><?php echo($strDetails); ?></span></td>
   </tr>
               <?php
            }
         }
         ?>
</table>

<input type="hidden" id="PurgeValue" name="purgerange" />

<div id="purge" class="hidden">
   <label for="purgefield"><?php echo($strPurgeRecords); ?>:</label>
   <select id='PurgeSelect'>
      <option value="5 years"><?php echo($str5Years); ?></option>
      <option value="2 years"><?php echo($str2Years); ?></option>
      <option value="1 years"><?php echo($str1Year); ?></option>
      <option value="6 months"><?php echo($str6Months); ?></option>
   </select>
</div>
      <?php

   }

   $strGeneralHTML = ob_get_clean();

   $generalSection->setCustomArguments($strGeneralHTML);

   $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_purgedialog()", 'purge', false);

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function modificationlog_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'purge')
   {
      if(isset($_REQUEST['purgerange']))
      {
         $timestamp = strtotime('-' . $_REQUEST['purgerange']);

         if($timestamp)
         {
            $_ARCHON->purgeLog($timestamp);
         }
         else
         {
            $_ARCHON->declareError("Invalid purge range.");
         }
      }
      else
      {
         $_ARCHON->declareError("Invalid purge range.");
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
      $msg = 'Records Purged Successfully.';
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, true);

}