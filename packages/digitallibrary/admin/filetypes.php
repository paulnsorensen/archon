<?php
/**
 * File Types Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

filetypes_ui_initialize();

// Determine what to do based upon user input

function filetypes_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        filetypes_ui_main();
    }
    
      else if($_REQUEST['f'] == 'search')
    {
        filetypes_ui_search();
    }
    
    else
    {
        filetypes_ui_exec(); // No interface needed, include an execution file.
    }
}


// filetypes_ui_main()
//   - purpose: Creates the primary user interface
//              for the filetype Manager.

function filetypes_ui_main()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->setClass('FileType');
    
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('filetype')->insertTextField('FileType', 50, 50)->required();
    $generalSection->insertRow('fileextensions')->insertTextField('FileExtensions', 50, 50)->required();
    $generalSection->insertRow('contenttype')->insertTextField('ContentType', 50, 50)->required();
    $generalSection->insertRow('mediatypeid')->insertSelect('MediaTypeID', 'getAllMediaTypes');

    
    $_ARCHON->AdministrativeInterface->setNameField('FileType');
 
    $_ARCHON->AdministrativeInterface->outputInterface();


}


function filetypes_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchFileTypes', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}


function filetypes_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objFileType = New FileType($_REQUEST);
            $objFileType->ID = $ID;
            $objFileType->dbStore();
            $ID = $objFileType->ID;
        }
    }

    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objFileType = New FileType($ID);
            $objFileType->dbDelete();
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
        $msg = "FileType Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}