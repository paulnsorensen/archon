<?php
/**
 * Processing Priority Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

processingpriorities_ui_initialize();

// Determine what to do based upon user input
function processingpriorities_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
       processingpriorities_ui_main();
    }
    else if($_REQUEST['f'] == 'search')
    {
        processingpriorities_ui_search();
    }
    else
    {
       processingpriorities_ui_exec();
    }
}

// processingpriorities_ui_main()
//   - purpose: Creates the primary user interface
//              for the Processing Priorities Manager.
function processingpriorities_ui_main()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->setClass('ProcessingPriority');
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('processingpriority')->insertTextField('ProcessingPriority', 50, 50)->required();
    $generalSection->insertRow('description')->insertTextArea('Description');
    $generalSection->insertRow('displayorder')->insertTextField('DisplayOrder', 3, 5)->required();

    
    $_ARCHON->AdministrativeInterface->setNameField('ProcessingPriority');
    $_ARCHON->AdministrativeInterface->outputInterface();
}


function processingpriorities_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchProcessingPriorities', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}




function processingpriorities_ui_exec()
{
    global $_ARCHON;
    
    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objProcessingPriority = New ProcessingPriority($_REQUEST);
            $objProcessingPriority->ID = $ID;
            $objProcessingPriority->dbStore();
            $ID = $objProcessingPriority->ID;
        }
    }
    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
        	$objProcessingPriority = New ProcessingPriority($ID);
            $objProcessingPriority->dbDelete();
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
        $msg = "ProcessingPriority Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}