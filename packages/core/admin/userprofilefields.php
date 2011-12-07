<?php

/**
 * UserProfileField Manager
 *
 * @author Chris Rishel
 * @package Archon
 * @subpackage AdminUI
 */
isset($_ARCHON) or die();

// Determine what to do based upon userprofilefield input
if(!$_REQUEST['f'])
{
   userprofilefields_ui_main();
}
else if($_REQUEST['f'] == "search")
{
   userprofilefields_ui_search();
}
else
{
   userprofilefields_ui_exec();
}

/**
 * Creates the primary userprofilefield interface for the UserProfileField Manager
 *
 */
function userprofilefields_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('UserProfileField');

   $_ARCHON->AdministrativeInterface->setNameField('UserProfileField');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $generalSection->insertRow('packageid')->insertSelect('PackageID', 'getAllPackages')->required();
   $generalSection->insertRow('userprofilefieldcategoryid')->insertSelect('UserProfileFieldCategoryID', 'getAllUserProfileFieldCategories')->required();
   $generalSection->insertRow('displayorder')->insertTextField('DisplayOrder', 4, 10);
   $generalSection->insertRow('userprofilefield')->insertTextField('UserProfileField', 30, 50)->required();

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
      elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'timestamp')
      {
         $generalSection->insertRow('defaultvalue')->insertTimestampField('DefaultValue', $_ARCHON->AdministrativeInterface->Object->Size, $_ARCHON->AdministrativeInterface->Object->MaxLength);
      }
      elseif($_ARCHON->AdministrativeInterface->Object->InputType == 'select')
      {
         $generalSection->insertRow('defaultvalue')->insertSelect('DefaultValue', $_ARCHON->AdministrativeInterface->Object->ListDataSource);
      }
   }

   $generalSection->insertRow('required')->insertRadioButtons('Required');
   $generalSection->insertRow('usereditable')->insertRadioButtons('UserEditable');
   $generalSection->insertRow('inputtype')->insertSelect('InputType', 'getAllUserProfileFieldInputTypes')->required();
   $generalSection->insertRow('patternid')->insertSelect('PatternID', 'getAllPatterns');
   $generalSection->insertRow('size')->insertTextField('Size', 4, 10);
   $generalSection->insertRow('maxlength')->insertTextField('MaxLength', 4, 10);
   $generalSection->insertRow('listdatasource')->insertSelect('ListDataSource', 'getAllGetAllFunctions');


   $showforcountriesSection = $_ARCHON->AdministrativeInterface->insertSection('showforcountries');
   $showforcountriesSection->insertRow('showforcountries')->insertAdvancedSelect('Countries', array(
       'Class' => 'Country',
       'RelatedArrayName' => 'Countries',
       'RelatedArrayLoadFunction' => 'dbLoadCountries',
       'Multiple' => true,
       'toStringArguments' => array(),
       'params' => array(
           'p' => 'admin/core/ajax',
           'f' => 'searchcountries',
           'searchtype' => 'json'
       )
   ));

   $requireforcountriesSection = $_ARCHON->AdministrativeInterface->insertSection('requireforcountries');
   $requireforcountriesSection->insertRow('requireforcountries')->insertAdvancedSelect('RequiredCountries', array(
       'Class' => 'Country',
       'RelatedArrayName' => 'RequiredCountries',
       'RelatedArrayLoadFunction' => 'dbLoadCountries',
       'Multiple' => true,
       'toStringArguments' => array(),
       'params' => array(
           'p' => 'admin/core/ajax',
           'f' => 'searchcountries',
           'searchtype' => 'json'
       )
   ));

   $_ARCHON->AdministrativeInterface->outputInterface();
}

/**
 * Creates the list of userprofilefields in the list frame of the primary interface
 *
 */
function userprofilefields_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchUserProfileFields', array('excludedisabledpackagefields' => true, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function userprofilefields_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   $objUserProfileField = New UserProfileField($_REQUEST);

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objUserProfileField->ID = $ID;
         $objUserProfileField->dbStore();

         if(is_array($_REQUEST['relatedcountryids']))
         {
            $objUserProfileField->dbUpdateRelatedCountries($_REQUEST['relatedcountryids'], $_REQUEST['relatedrequiredcountryids']);
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objUserProfileField = New UserProfileField($ID);
         $objUserProfileField->dbDelete();
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
      $msg = "UserProfileField Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}

?>