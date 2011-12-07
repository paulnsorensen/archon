<?php
/**
 * Subject Sources Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

subjectsources_ui_initialize();

// Determine what to do based upon user input
function subjectsources_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        subjectsources_ui_main();
    }
    
      else if($_REQUEST['f'] == 'search')
    {
        subjectsources_ui_search();
    }
    
    else
    {
        subjectsources_ui_exec(); // No interface needed, include an execution file.
    }
}



// subjectsources_ui_main()
//   - purpose: Creates the primary user interface
//              for the subjectsource Manager.
function subjectsources_ui_main()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->setClass('SubjectSource');
    
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('subjectsource')->insertTextField('SubjectSource', 50, 50)->required();
    $generalSection->insertRow('eadsource')->insertTextField('EADSource', 10, 10)->required();
  

    $_ARCHON->AdministrativeInterface->setNameField('SubjectSource');
 
    $_ARCHON->AdministrativeInterface->outputInterface();
}




function subjectsources_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchSubjectSources', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function subjectsources_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objSubjectSource = New SubjectSource($_REQUEST);
            $objSubjectSource->ID = $ID;
            $objSubjectSource->dbStore();
            $ID = $objSubjectSource->ID;
        }
    }
    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objSubjectSource = New SubjectSource($ID);
            $objSubjectSource->dbDelete();
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
        $msg = "SubjectSource Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}