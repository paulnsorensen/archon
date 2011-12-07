<?php
/**
 * Pattern Manager
 *
 * @author Chris Rishel
 * @package Archon
 * @subpackage AdminUI
 */


isset($_ARCHON) or die();

// Determine what to do based upon pattern input
if(!$_REQUEST['f'])
{
   patterns_ui_main();
}
else if($_REQUEST['f'] == "search")
{
   patterns_ui_search();
}
else
{
   patterns_ui_exec();
}

/**
 * Creates the primary pattern interface for the Pattern Manager
 *
 */
function patterns_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Pattern');

   // $_ARCHON->AdministrativeInterface->Object->ScratchPad = NULL;

   $_ARCHON->AdministrativeInterface->setNameField('Name');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('packageid')->insertSelect('PackageID', 'getAllPackages');
   $generalSection->insertRow('name')->insertTextField('Name', 30, 50);
   $generalSection->insertRow('pattern')->insertTextField('Pattern', 85, 100);

   if($_ARCHON->AdministrativeInterface->Object->Pattern)
   {
      ob_start();
      $IDName = 'unittestresults';
      if($_ARCHON->AdministrativeInterface->Object->dbLoadUnitTests() && !empty($_ARCHON->AdministrativeInterface->Object->UnitTests))
      {




         echo("<table id='{$IDName}' class='infotable'>");
         echo("<tr><th>Input</th><th>Expected To</th><th>Result</th></tr>");
         $count = 0;
         foreach($_ARCHON->AdministrativeInterface->Object->UnitTests as $objUnitTest)
         {
            $strResult = $_ARCHON->AdministrativeInterface->Object->match($objUnitTest->Value) == $objUnitTest->ExpectedResult ? "<span style='color:lime; font-weight:bold'>PASS</span>" : "<span style='color:red; font-weight:bold'>FAIL</span>";
            $strExpected = $objUnitTest->ExpectedResult ? "Match" : "Not Match";

            $ListItem = "<td>".$objUnitTest->Value."</td><td>".$strExpected."</td><td>".$strResult."</td>";
            if ($count % 2 == 0)
            {
               echo("<tr class='evenrow'>{$ListItem}</tr>");
            }
            else
            {
               echo("<tr>{$ListItem}</tr>");
            }
            $count++;
         }

         echo("</table>");


      }
      $UnitTestResults = ob_get_clean();

      $_ARCHON->AdministrativeInterface->addReloadField(
              $generalSection->insertRow('unittests')->insertHTML($UnitTestResults));

      $generalSection->insertRow('testsubject')->insertTextField('TestSubject', 30, 255);

   }


   $unitTestSection = $_ARCHON->AdministrativeInterface->insertSection('unittests', 'multiple');
   $unitTestSection->setMultipleArguments('UnitTest', 'UnitTests', 'dbLoadUnitTests');
   $unitTestSection->insertRow('value')->insertTextField('Value', 100);
   $unitTestSection->insertRow('expectedresult')->insertRadioButtons('ExpectedResult', array(1 => 'match', 0 => 'notmatch'));


   $_ARCHON->AdministrativeInterface->outputInterface();

}





/**
 * Creates the list of patterns in the list frame of the primary interface
 *
 */
function patterns_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('getAllPatterns');
}






function patterns_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['testsubject'] != '')
   {
      ob_start();

      echo("Result: " . preg_match($_REQUEST['pattern'], $_REQUEST['testsubject'], $arrMatches) . " Matches: ");

      $msg = ob_get_clean();
   }
   else
   {
      if($_REQUEST['f'] == 'store')
      {
         foreach($arrIDs as &$ID)
         {
            $objPattern = New Pattern($_REQUEST);
            $objPattern->ID = $ID;
            $stored = $objPattern->dbStore();


            if($stored && is_array($_REQUEST['unittests']) && !empty($_REQUEST['unittests']))
            {
               foreach($_REQUEST['unittests'] as $UnitTestID => $array)
               {
                  $array['id'] = $UnitTestID;
                  $array['patternid'] = $ID;

                  $objUnitTest = New UnitTest($array);

                  if($array['_fdelete'])
                  {
                     $objUnitTest->dbDelete();
                  }
                  elseif($objUnitTest->Value != NULL)
                  {
                     $objUnitTest->dbStore();
                  }
               }
            }


         }
      }
      else if($_REQUEST['f'] == 'delete')
      {
         foreach($arrIDs as $ID)
         {
            $objPattern = New Pattern($ID);
            $objPattern->dbDelete();
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
         $msg = "Pattern Database Updated Successfully.";
      }
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
?>