<?php
/**
 * ResearchAppointmentField Manager
 *
 * @author Chris Rishel
 * @package Archon
 * @subpackage AdminUI
 */


isset($_ARCHON) or die();

// Determine what to do based upon researchappointmentfield input
if(!$_REQUEST['f'])
{
    researchappointmentfields_ui_main();
}
else if($_REQUEST['f'] == "search")
{
    researchappointmentfields_ui_search();
}
else
{
    researchappointmentfields_ui_exec();
}

/**
 * Creates the primary researchappointmentfield interface for the ResearchAppointmentField Manager
 *
 */
function researchappointmentfields_ui_main()
{
    global $_ARCHON;

    $_ARCHON->AdministrativeInterface->setClass('ResearchAppointmentField');

    $_ARCHON->AdministrativeInterface->setNameField('ResearchAppointmentField');

    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

    $generalSection->insertRow('displayorder')->insertTextField('DisplayOrder', 4, 10);
    $generalSection->insertRow('name')->insertTextField('Name', 30, 50);
    
    if($_ARCHON->AdministrativeInterface->Object->ID)
    {
	    if($_ARCHON->AdministrativeInterface->Object->InputType == 'radio')
	    {
	        $generalSection->insertRow('defaultvalue')->insertRadioButtons('DefaultValue');
	    }
	    elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'textarea')
	    {
	        $generalSection->insertRow('defaultvalue')->insertTextArea('DefaultValue', $_ARCHON->AdministrativeInterface->Object->Size);
	    }
	    elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'textfield')
	    {
	        $generalSection->insertRow('defaultvalue')->insertTextField('DefaultValue', $_ARCHON->AdministrativeInterface->Object->Size, $_ARCHON->AdministrativeInterface->Object->MaxLength);
	    }
	    elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'select')
	    {
	        $generalSection->insertRow('defaultvalue')->insertSelect('DefaultValue', $_ARCHON->AdministrativeInterface->Object->ListDataSource);
	    }
    }
    
    $generalSection->insertRow('required')->insertRadioButtons('Required');
    $generalSection->insertRow('inputtype')->insertTextField('InputType', 4, 10);
    $generalSection->insertRow('patternid')->insertSelect('PatternID', 'getAllPatterns');
    $generalSection->insertRow('size')->insertTextField('Size', 4, 10);
    $generalSection->insertRow('maxlength')->insertTextField('MaxLength', 4, 10);
    $generalSection->insertRow('listdatasource')->insertSelect('ListDataSource', 'getAllGetAllFunctions');

    $_ARCHON->AdministrativeInterface->outputInterface();

}





/**
 * Creates the list of researchappointmentfields in the list frame of the primary interface
 *
 */
function researchappointmentfields_ui_search()
{
    global $_ARCHON;

    $_ARCHON->AdministrativeInterface->searchResults('searchResearchAppointmentFields', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}






function researchappointmentfields_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');
    
    $objResearchAppointmentField = New ResearchAppointmentField($_REQUEST);

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objResearchAppointmentField->ID = $ID;
            $objResearchAppointmentField->dbStore();
        }
    }
    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objResearchAppointmentField = New ResearchAppointmentField($ID);
            $objResearchAppointmentField->dbDelete();
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
      $msg = "ResearchAppointmentField Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
?>