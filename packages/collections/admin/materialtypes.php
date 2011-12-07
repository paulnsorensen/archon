<?php
/**
 * Material Types Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Prom
 */

isset($_ARCHON) or die();

materialtypes_ui_initialize();

// Determine what to do based upon user input

function materialtypes_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        materialtypes_ui_main();
    }
    
      else if($_REQUEST['f'] == 'search')
    {
        materialtypes_ui_search();
    }
    
    else
    {
        materialtypes_ui_exec(); // No interface needed, include an execution file.
    }
}

//   materialtypes_ui_main()
//   - purpose: Creates the primary user interface
//     for the materialtype Manager.

function materialtypes_ui_main()
{
    global $_ARCHON;
    $_ARCHON->AdministrativeInterface->setClass('MaterialType');
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('materialtype')->insertTextField('MaterialType', 50, 50)->required();
    $_ARCHON->AdministrativeInterface->setNameField('MaterialType');
    $_ARCHON->AdministrativeInterface->outputInterface();
}


function materialtypes_ui_search()
{
    global $_ARCHON;    
    $_ARCHON->AdministrativeInterface->searchResults('searchMaterialTypes', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

// languages_ui_main()
//   - purpose: Executes data storage and deletion
//     when the user submits information

function materialtypes_ui_exec()
{
    global $_ARCHON;
    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');
    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objMaterialType = New MaterialType($_REQUEST);
            $objMaterialType->ID = $ID;
            $objMaterialType->dbStore();
            $ID = $objMaterialType->ID;
        }
        $msg = "MaterialType Database Updated.";
    }

    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objMaterialType = New MaterialType($ID);
            $objMaterialType->dbDelete();
        }
        $msg = "Material Type Deleted from Database.";
    }
    else
    {
        $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
            
    }

    if($_ARCHON->Error)
    {
        $msg = $_ARCHON->Error;
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}