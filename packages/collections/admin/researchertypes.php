<?php
/**
 * Researcher Types Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

researchertypes_ui_initialize();

// Determine what to do based upon user input
function researchertypes_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        researchertypes_ui_main();
    }
    
      else if($_REQUEST['f'] == 'search')
    {
        researchertypes_ui_search();
    }
    
    else
    {
        researchertypes_ui_exec(); // No interface needed, include an execution file.
    }
}



// researchertypes_ui_main()
//   - purpose: Creates the primary user interface
//              for the researchertype Manager.
function researchertypes_ui_main()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->setClass('ResearcherType');
    
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('researchertype')->insertTextField('ResearcherType', 50, 50)->required();

    $_ARCHON->AdministrativeInterface->setNameField('ResearcherType');
 
    $_ARCHON->AdministrativeInterface->outputInterface();
}




function researchertypes_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchResearcherTypes', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function researchertypes_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objResearcherType = New ResearcherType($_REQUEST);
            $objResearcherType->ID = $ID;
            $objResearcherType->dbStore();
            $ID = $objResearcherType->ID;
        }
    }
    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objResearcherType = New ResearcherType($ID);
            $objResearcherType->dbDelete();
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
        $msg = "ResearcherType Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}