<?php
/**
 * UserProfileFieldCategory Manager
 *
 * @author Chris Rishel
 * @package Archon
 * @subpackage AdminUI
 */


isset($_ARCHON) or die();

// Determine what to do based upon userprofilefield input
if(!$_REQUEST['f'])
{
    userprofilefieldcategories_ui_main();
}
else if($_REQUEST['f'] == "search")
{
    userprofilefieldcategories_ui_search();
}
else
{
    userprofilefieldcategories_ui_exec();
}

/**
 * Creates the primary userprofilefield interface for the UserProfileFieldCategory Manager
 *
 */
function userprofilefieldcategories_ui_main()
{
    global $_ARCHON;

    $_ARCHON->AdministrativeInterface->setClass('UserProfileFieldCategory');

    $_ARCHON->AdministrativeInterface->setNameField('UserProfileFieldCategory');

    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    
    $generalSection->insertRow('userprofilefieldcategory')->insertTextField('UserProfileFieldCategory', 30, 50)->required();
    $generalSection->insertRow('displayorder')->insertTextField('DisplayOrder', 4, 10);

    $_ARCHON->AdministrativeInterface->outputInterface();

}





/**
 * Creates the list of userprofilefieldcategories in the list frame of the primary interface
 *
 */
function userprofilefieldcategories_ui_search()
{
    global $_ARCHON;

    $_ARCHON->AdministrativeInterface->searchResults('searchUserProfileFieldCategories', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}






function userprofilefieldcategories_ui_exec()
{
    global $_ARCHON;


    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        foreach($arrIDs as &$ID)
        {
            $objUserProfileFieldCategory = New UserProfileFieldCategory($_REQUEST);
            $objUserProfileFieldCategory->ID = $ID;
            $objUserProfileFieldCategory->dbStore();
        }
   }
    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objUserProfileFieldCategory = New UserProfileFieldCategory($ID);
            $objUserProfileFieldCategory->dbDelete();
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
      $msg = "UserProfileFieldCategory Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
?>