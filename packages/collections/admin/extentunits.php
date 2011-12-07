<?php
/**
 * Appointment Purposes Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Prom
 */

isset($_ARCHON) or die();

extentunits_ui_initialize();

// Determine what to do based upon user input
function extentunits_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        extentunits_ui_main();
    }
    
      else if($_REQUEST['f'] == 'search')
    {
        extentunits_ui_search();
    }
    
    else
    {
        extentunits_ui_exec(); // No interface needed, include an execution file.
    }
}



// extentunits_ui_main()
//   - purpose: Creates the primary user interface
//              for the extentunit Manager.
function extentunits_ui_main()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->setClass('ExtentUnit');
    
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('extentunit')->insertTextField('ExtentUnit', 50, 50)->required();

    $_ARCHON->AdministrativeInterface->setNameField('ExtentUnit');
 
    $_ARCHON->AdministrativeInterface->outputInterface();
}




function extentunits_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchExtentUnits', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function extentunits_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objExtentUnit = New ExtentUnit($_REQUEST);
            $objExtentUnit->ID = $ID;
            $objExtentUnit->dbStore();
            $ID = $objExtentUnit->ID;
        }
    }
    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objExtentUnit = New ExtentUnit($ID);
            $objExtentUnit->dbDelete();
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
        $msg = "ExtentUnit Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}