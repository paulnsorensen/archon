<?php
/**
 * LevelContainers Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Prom
 */

isset($_ARCHON) or die();

levelcontainers_ui_initialize();

// Determine what to do based upon user input

function levelcontainers_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        levelcontainers_ui_main();
    }
    else if($_REQUEST['f'] == 'search')
    {
        levelcontainers_ui_search();
    }
    
    else
    {
        levelcontainers_ui_exec(); // No interface needed, include an execution file.
    }
}

// levelcontainers_ui_main()
//   - purpose: Creates the primary user interface
//              for the levelcontainer Manager.
function levelcontainers_ui_main()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->setClass('LevelContainer');
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('levelcontainer')->insertTextField('LevelContainer', 50, 50)->required();
    $generalSection->insertRow('intellectuallevel')->insertCheckBox('IntellectualLevel');
    $generalSection->insertRow('physicalcontainer')->insertCheckBox('PhysicalContainer');
    $generalSection->insertRow('eadlevel')->insertTextField('EADLevel', 10, 10);
    $generalSection->getRow('eadlevel')->setEnableConditions('IntellectualLevel', true);
    $generalSection->insertRow('primaryeadlevel')->insertRadioButtons('PrimaryEADLevel');
    $generalSection->getRow('primaryeadlevel')->setEnableConditions('IntellectualLevel', true);
    $generalSection->insertRow('globalnumbering')->insertRadioButtons('GlobalNumbering');

    
    $_ARCHON->AdministrativeInterface->setNameField('LevelContainer');
    $_ARCHON->AdministrativeInterface->outputInterface();   
}

function levelcontainers_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchLevelContainers', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function levelcontainers_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objLevelContainer = New LevelContainer($_REQUEST);
            $objLevelContainer->ID = $ID;
            $objLevelContainer->dbStore();
            $ID = $objLevelContainer->ID;
        }
    }

    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objLevelContainer = New LevelContainer($ID);
            $objLevelContainer->dbDelete();
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
        $msg = "LevelContainer Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}