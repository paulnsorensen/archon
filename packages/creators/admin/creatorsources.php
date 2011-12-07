<?php
/**
 * Creator Sources Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

creatorsources_ui_initialize();

// Determine what to do based upon user input
function creatorsources_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        creatorsources_ui_main();
    }
    
    elseif($_REQUEST['f'] == 'search')
    {
        creatorsources_ui_search();
    }
    
    else
    {
        creatorsources_ui_exec();
    }
}



// creatorsources_ui_main()
//   - purpose: Creates the primary user interface
//              for the creatorsource Manager.
function creatorsources_ui_main()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->setClass('CreatorSource');
    
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('creatorsource')->insertTextField('CreatorSource', 50, 50)->required();
    $generalSection->insertRow('sourceabbreviation')->insertTextField('SourceAbbreviation', 10, 10)->required();
    $generalSection->insertRow('citation')->insertTextArea('Citation');
    $generalSection->insertRow('description')->insertTextArea('Description');
  

    $_ARCHON->AdministrativeInterface->setNameField('CreatorSource');
 
    $_ARCHON->AdministrativeInterface->outputInterface();
}




function creatorsources_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchCreatorSources', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function creatorsources_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objCreatorSource = New CreatorSource($_REQUEST);
            $objCreatorSource->ID = $ID;
            $objCreatorSource->dbStore();
            $ID = $objCreatorSource->ID;
        }
    }
    elseif($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objCreatorSource = New CreatorSource($ID);
            $objCreatorSource->dbDelete();
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
        $msg = "CreatorSource Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}