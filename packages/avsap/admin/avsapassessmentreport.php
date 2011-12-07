<?php
/**
 * AVSAP Report Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Paul Sorensen, Mamta Singh
 */
isset($_ARCHON) or die();

avsapreports_ui_initialize();

// Determine what to do based upon user input
function avsapreports_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      avsapreports_ui_main();
   }
   else if($_REQUEST['f'] == 'print')
   {
      avsapreports_ui_print();
   }
   else if($_REQUEST['f'] == 'csv')
   {
      avsapreports_ui_csv();
   }
}

// avsapreports_ui_main()
//   - purpose: Creates the primary user interface
//              for the AVSAP Reports Manager.
function avsapreports_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('AVSAPReport', false, false, false);

   $browseSection = $_ARCHON->AdministrativeInterface->getSection('browse');
   $browseSection->disable();

   $_ARCHON->AdministrativeInterface->disableQuickSearch();

   $generalSection = $_ARCHON->AdministrativeInterface->insertSection('general', 'custom');
   ob_start();

   $objCollectionNamePhrase = Phrase::getPhrase('collectionname', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strCollectionName = $objCollectionNamePhrase ? $objCollectionNamePhrase->getPhraseValue(ENCODE_HTML) : 'Collections';
   $objItemNamePhrase = Phrase::getPhrase('itemname', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strItemName = $objItemNamePhrase ? $objItemNamePhrase->getPhraseValue(ENCODE_HTML) : 'Items';
   $objLocationPhrase = Phrase::getPhrase('location', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strLocation = $objLocationPhrase ? $objLocationPhrase->getPhraseValue(ENCODE_HTML) : 'Location';
   $objAvsapScorePhrase = Phrase::getPhrase('avsapscore', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strAvsapScore = $objAvsapScorePhrase ? $objAvsapScorePhrase->getPhraseValue(ENCODE_HTML) : 'Score';
   $objFormatPhrase = Phrase::getPhrase('format', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strFormat = $objFormatPhrase ? $objFormatPhrase->getPhraseValue(ENCODE_HTML) : 'Format';
   $objSignificancePhrase = Phrase::getPhrase('significance', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strSignificance = $objSignificancePhrase ? $objSignificancePhrase->getPhraseValue(ENCODE_HTML) : 'Significance';
   $objNotePhrase = Phrase::getPhrase('notes', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strNote = $objNotePhrase ? $objNotePhrase->getPhraseValue(ENCODE_HTML) : 'Notes';
   $objRepositoryPhrase = Phrase::getPhrase('repository', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strRepositiory = $objRepositoryPhrase ? $objRepositoryPhrase->getPhraseValue(ENCODE_HTML) : 'Repository';
?>
   <script type="text/javascript" src="packages/core/js/jquery.dataTables.min.js"></script>
   <script type="text/javascript">

      function admin_ui_print(getValue)
      {
         var printdata = '';
         var printorder='';
         if($('#reporttable > thead > tr').children('th.sorting_asc').text() != '')
         {
            printdata = $('#reporttable > thead > tr').children('.sorting_asc').text();
            printorder = 'asc';
         }
         else if($('#reporttable > thead > tr').children('.sorting_desc') != '')
         {
            printdata = $('#reporttable > thead > tr').children('.sorting_desc').text();
            printorder = 'desc';
         }
         if(getValue == 'print')
         {
            window.open('index.php?p=admin/avsap/avsapassessmentreport&f=print&printdata='+printdata+'&printorder='+printorder+'&repository='+$('#repositoryid').val(),'print','width=700,height=600,resizable=yes,scrollbars=1,left='+((screen.width - 700)/2)+',top='+((screen.height - 600) / 2));
         }
         else if(getValue == 'csv')
         {
            window.open('index.php?p=admin/avsap/avsapassessmentreport&f=csv&printdata='+printdata+'&printorder='+printorder+'&repository='+$('#repositoryid').val(), '_self');
         }
      }


      $(function () {

         $('#reporttable').dataTable({
            "bAutoWidth": false,
            "aoColumns": [null,null,null,null,null,null,{
                  "bSortable": false
               }]

         });

         $("#repositoryid").change(function()
         {
            var selectvalue;
            selectvalue = $("#repositoryid").val();
            window.open('index.php?p=admin/avsap/avsapassessmentreport&repositoryid='+selectvalue, '_self');

         });
      });
   </script>
<?php
   $repositoryID = 0;

   $objUser = $_ARCHON->Security->Session->User;

   if($objUser->RepositoryLimit)
   {
      $arrRepositories = $objUser->Repositories;
   }
   else
   {
      $arrRepositories = $_ARCHON->getAllRepositories();
   }


   if(!empty($objUser->Repositories))
   {
      if(count($objUser->Repositories) == 1)
      {
         $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
      }
   }
   if($_GET['repositoryid'] != NULL)
   {
      $repositoryID = $_GET['repositoryid'];
   }

   $objAvsap = $_ARCHON->AdministrativeInterface->searchResults('searchAVSAPAssessments', array('repositoryid' => $repositoryID));
?>
   <div id="reporttablepadding">
      <label for="repositorylabel"><?php echo ($strRepositiory); ?> </label>
      <select id="repositoryid">
      <?php
      if(count($arrRepositories) != 1)
      {
         echo("<option value='0'> (Select One) </option>");
      }
      foreach($arrRepositories as $ID => $Repository)
      {
         if($repositoryID != NULL && $ID == $repositoryID)
            echo("<option value='$ID' selected='selected'>{$Repository->toString()}</option>");
         else
            echo("<option value='$ID'>{$Repository->toString()}</option>");
      }
      ?>
   </select>
   <table id='reporttable' class='display'>
      <thead>
         <tr>
            <th><?php echo ($strCollectionName); ?></th>
            <th><?php echo ($strItemName); ?></th>
            <th><?php echo ($strLocation); ?></th>
            <th><?php echo ($strAvsapScore); ?></th>
            <th><?php echo ($strFormat); ?></th>
            <th><?php echo ($strSignificance); ?></th>
            <th><?php echo ($strNote); ?></th>
         </tr>
      </thead>
      <tbody>
         <?php
         if(!empty($objAvsap))
         {

            foreach($objAvsap as $obj)
            {
               $objStorageAvsap = New AVSAPStorageFacility($obj->StorageFacilityID);
               $objStorageAvsap->dbLoad();

               if($obj->CollectionID)
               {
                  $objCollection = New Collection($obj->CollectionID);
                  $collection = $objCollection->toString();
               }
               else
               {
                  $collection = '';
               }
         ?>
               <tr>
                  <td><?php echo ($collection); ?></td>
                  <td><?php echo ($obj->Name); ?></td>
                  <td><?php echo ($objStorageAvsap->Name); ?></td>
                  <td><?php echo ($obj->Score); ?></td>
                  <td><?php echo ($obj->getFormatPhrase($obj->Format)); ?></td>
                  <td> <?php $significancePhrase = $obj->getSignificancePhrase($obj->Significance); ?><input type="hidden" value="<?php echo($significancePhrase[1]); ?>" /><?php echo ($significancePhrase[0]); ?></td>
                  <td><?php echo ($obj->Notes); ?></td>
               </tr>
         <?php
            }
         }
         ?>
      </tbody>
   </table>
</div>
<div id='glossary'></div>

<?php
         $strReportTable = ob_get_clean();
         $generalSection->setCustomArguments($strReportTable);

         $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_print('print')", 'print', false);
         $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_print('csv')", 'csv', false);


         $_ARCHON->AdministrativeInterface->setNameField('name');



         $_ARCHON->AdministrativeInterface->outputInterface();
      }

      function avsapreports_ui_print()
      {
         global $_ARCHON;

         $_ARCHON->AdministrativeInterface->setClass('AVSAPReport', false, false, false);

         $objCollectionNamePhrase = Phrase::getPhrase('collectionname', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strCollectionName = $objCollectionNamePhrase ? $objCollectionNamePhrase->getPhraseValue(ENCODE_HTML) : 'Collection Name';
         $objItemNamePhrase = Phrase::getPhrase('itemname', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strItemName = $objItemNamePhrase ? $objItemNamePhrase->getPhraseValue(ENCODE_HTML) : 'Item Name';
         $objLocationPhrase = Phrase::getPhrase('location', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strLocation = $objLocationPhrase ? $objLocationPhrase->getPhraseValue(ENCODE_HTML) : 'Location';
         $objAvsapScorePhrase = Phrase::getPhrase('avsapscore', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strAvsapScore = $objAvsapScorePhrase ? $objAvsapScorePhrase->getPhraseValue(ENCODE_HTML) : 'AvSAP Score';
         $objFormatPhrase = Phrase::getPhrase('format', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strFormat = $objFormatPhrase ? $objFormatPhrase->getPhraseValue(ENCODE_HTML) : 'Format';
         $objSignificancePhrase = Phrase::getPhrase('significance', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strSignificance = $objSignificancePhrase ? $objSignificancePhrase->getPhraseValue(ENCODE_HTML) : 'Significance Flag';
         $objNotePhrase = Phrase::getPhrase('note', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strNote = $objNotePhrase ? $objNotePhrase->getPhraseValue(ENCODE_HTML) : 'Note';

         $printdata = $_GET['printdata'];
         $printorder = $_GET['printorder'];
         $repositoryID = $_GET['repository'];

         $objArr = $_ARCHON->searchAVSAPAssessmentsForPrint($printdata, $printorder, $repositoryID);
         $objAvsap = New AVSAPAssessment($_REQUEST);
?>

         <img src="adminthemes/default/images/AvSAPlogo1.png"><br><hr/>
         <input type='button' value='Print' onclick="window.print();" style="float:right; margin-bottom: 10px; margin-right: 20px;" >
         <br /><br />
         <table id='report' style="border-spacing:10px;">

            <thead>
               <tr>
                  <th><?php echo ($strCollectionName); ?></th>
                  <th><?php echo ($strItemName); ?></th>
                  <th><?php echo ($strLocation); ?></th>
                  <th><?php echo ($strAvsapScore); ?></th>
                  <th><?php echo ($strFormat); ?></th>
                  <th><?php echo ($strSignificance); ?></th>
                  <th><?php echo ($strNote); ?></th>
               </tr>
            </thead>
            <tbody>
      <?php
         if($printdata == 'Format')
         {

            function sort_desc($a, $b)
            {
               return -1 * (strcmp(strtolower($a['4']), strtolower($b['4'])));
            }

            function sort_asc($a, $b)
            {
               return (strcmp(strtolower($a['4']), strtolower($b['4'])));
            }

            if($printorder == 'asc')
            {
               uasort($objArr, "sort_asc");
            }
            else if($printorder == 'desc')
            {
               uasort($objArr, "sort_desc");
            }
         }
         foreach($objArr as $obj)
         {
            $significancePhrase = $objAvsap->getSignificancePhrase($obj[5]);
            $subAssessmentPhrase = $_ARCHON->getAVSAPFormatList($obj[7]);
            $formatPhrase = $subAssessmentPhrase[$obj[4]];
            echo "<tr>";
            echo "<td> $obj[0] </td>";
            echo "<td> $obj[1] </td>";
            echo "<td> $obj[2] </td>";
            echo "<td> $obj[3] </td>";
            echo "<td> $formatPhrase </td>";
            echo "<td> $significancePhrase[0]  </td>";
            echo "<td> $obj[6] </td>";

            echo "</tr>";
         }
      ?>
      </tbody>
   </table>
<?php
      }

      function avsapreports_ui_csv()
      {

         global $_ARCHON;

         $_ARCHON->AdministrativeInterface->setClass('AVSAPReport', false, false, false);

         $objCollectionNamePhrase = Phrase::getPhrase('collectionname', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strCollectionName = $objCollectionNamePhrase ? $objCollectionNamePhrase->getPhraseValue(ENCODE_NONE) : 'Collection Name';
         $objItemNamePhrase = Phrase::getPhrase('itemname', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strItemName = $objItemNamePhrase ? $objItemNamePhrase->getPhraseValue(ENCODE_NONE) : 'Item Name';
         $objLocationPhrase = Phrase::getPhrase('location', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strLocation = $objLocationPhrase ? $objLocationPhrase->getPhraseValue(ENCODE_NONE) : 'Location';
         $objAvsapScorePhrase = Phrase::getPhrase('avsapscore', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strAvsapScore = $objAvsapScorePhrase ? $objAvsapScorePhrase->getPhraseValue(ENCODE_NONE) : 'AvSAP Score';
         $objFormatPhrase = Phrase::getPhrase('format', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strFormat = $objFormatPhrase ? $objFormatPhrase->getPhraseValue(ENCODE_NONE) : 'Format';
         $objSignificancePhrase = Phrase::getPhrase('significance', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strSignificance = $objSignificancePhrase ? $objSignificancePhrase->getPhraseValue(ENCODE_NONE) : 'Significance Flag';
         $objNotePhrase = Phrase::getPhrase('note', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
         $strNote = $objNotePhrase ? $objNotePhrase->getPhraseValue(ENCODE_NONE) : 'Note';

         $printdata = $_GET['printdata'];
         $printorder = $_GET['printorder'];
         $repositoryID = $_GET['repository'];

         $headings = array($strCollectionName, $strItemName, $strLocation, $strAvsapScore, $strFormat, $strSignificance, $strNote);

         $objUser = $_ARCHON->Security->Session->User;

         $objArr = $_ARCHON->searchAVSAPAssessmentsForPrint($printdata, $printorder, $repositoryID);
         $objAvsap = New AVSAPAssessment($_REQUEST);


         if($printdata == 'Format')
         {

            function sort_desc($a, $b)
            {
               return -1 * (strcmp(strtolower($a['4']), strtolower($b['4'])));
            }

            function sort_asc($a, $b)
            {
               return (strcmp(strtolower($a['4']), strtolower($b['4'])));
            }

            if($printorder == 'asc')
            {
               uasort($objArr, "sort_asc");
            }
            else if($printorder == 'desc')
            {
               uasort($objArr, "sort_desc");
            }
         }

         $values = array();

         foreach($objArr as $obj)
         {
            $significancePhrase = $objAvsap->getSignificancePhrase($obj[5]);
            $subAssessmentPhrase = $_ARCHON->getAVSAPFormatList($obj[7]);
            $formatPhrase = html_entity_decode($subAssessmentPhrase[$obj[4]], ENT_QUOTES, 'UTF-8');

            $items = array($obj[0], $obj[1], $obj[2], $obj[3], $formatPhrase, $significancePhrase[0], html_entity_decode($obj[6], ENT_QUOTES, 'UTF-8'));

            $values[] = $items;
         }


         $fp = fopen('php://output', 'w');
         if($fp)
         {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="AvSAPAssessmentRepot.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            fputcsv($fp, $headings);
            foreach($values as $row)
            {
               fputcsv($fp, $row);
            }
            exit();
         }
      }

