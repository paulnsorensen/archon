<?php
/**
 * StateProvince Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

// Determine what to do based upon user input
if(!$_REQUEST['f'])
{
    stateprovinces_ui_main();
}
else if($_REQUEST['f'] == "search")
{
    stateprovinces_ui_search();
}
else
{
    stateprovinces_ui_exec();
}





// stateprovinces_ui_main()
//   - purpose: Creates the primary user interface
//              for the StateProvince Manager.
function stateprovinces_ui_main()
{
    global $_ARCHON;

    $_ARCHON->AdministrativeInterface->setClass('StateProvince');

    $_ARCHON->AdministrativeInterface->setNameField('StateProvince');

    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

    $generalSection->insertRow('countryid')->insertSelect('CountryID', 'getAllCountries');
    $generalSection->insertRow('stateprovincename')->insertTextField('StateProvinceName', 30, 100);
    $generalSection->insertRow('isoalpha2')->insertTextField('ISOAlpha2', 2, 2);
    
    $_ARCHON->AdministrativeInterface->outputInterface();
}




// stateprovinces_ui_list()
//   - purpose: Generates the listbox in a separate
//              frame for the main stateprovinces UI.
function stateprovinces_ui_search()
{
    global $_ARCHON;

    //$_ARCHON->AdministrativeInterface->setClass('StateProvince');

    $_ARCHON->AdministrativeInterface->searchResults('searchStateProvinces', array('countryid' => NULL, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}





function stateprovinces_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        if(is_array($_REQUEST['ids']) && !empty($_REQUEST['ids']))
        {
            foreach($_REQUEST['ids'] as &$ID)
            {
                $objStateProvince = New StateProvince($_REQUEST);
                $objStateProvince->ID = $ID;
                $objStateProvince->dbStore();
            }
        }
    }
    else if($_REQUEST['f'] == 'delete')
    {
        if(is_array($_REQUEST['ids']) && !empty($_REQUEST['ids']))
        {
            foreach($_REQUEST['ids'] as $ID)
            {
                $objStateProvince = New StateProvince($ID);
                $objStateProvince->dbDelete();
            }
        }
    }
    else
    {
        $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
       // $location = "window.top.frames['main'].location='?p={$_REQUEST['p']}&f=';";
    }

    if($_ARCHON->Error)
    {
        $msg = $_ARCHON->Error;
    }
    else
    {
        $msg = 'Region Database Updated Successfully.';
    }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}