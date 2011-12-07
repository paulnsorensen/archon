<?php

/**
 * Public User Manager
 *
 * @author Chris Rishel
 * @package Archon
 * @subpackage AdminUI
 */
isset($_ARCHON) or die();

// Determine what to do based upon user input
if(!$_REQUEST['f'])
{
   publicusers_ui_main();
}
else if($_REQUEST['f'] == "search")
{
   publicusers_ui_search();
}
else
{
   publicusers_ui_exec();
}

/**
 * Creates the primary user interface for the User Manager
 *
 */
function publicusers_ui_main()
{
   global $_ARCHON;


   $objNoPhrase = Phrase::getPhrase('no', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strNo = $objNoPhrase ? $objNoPhrase->getPhraseValue(ENCODE_HTML) : 'No';

   $_ARCHON->AdministrativeInterface->setClass('User');

   $_ARCHON->AdministrativeInterface->setNameField('Login');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   if($_ARCHON->AdministrativeInterface->Object->ID != 0 && $_ARCHON->AdministrativeInterface->Object->IsAdminUser)
   {
      $_ARCHON->AdministrativeInterface->Redirect = "admin/core/adminusers&id={$_ARCHON->AdministrativeInterface->Object->ID}";
      $_ARCHON->AdministrativeInterface->outputInterface();
   }



   if($_ARCHON->AdministrativeInterface->Object->ID != -1)
   {
//	    $generalSection->insertRow('login')->insertTextField('Login',15, 50)->required();
      $generalSection->insertRow('email')->insertTextField('Email', 30, 50)->required();

      $password = $generalSection->insertRow('password')->insertPasswordField('Password');
      $confirmpassword = $generalSection->insertRow('confirmpassword')->insertPasswordField('ConfirmPassword');
      if(!$_ARCHON->AdministrativeInterface->Object->ID)
      {
         $password->required();
         $confirmpassword->required();
      }

      $generalSection->insertRow('firstname')->insertTextField('FirstName', 30, 50)->required();
      $generalSection->insertRow('lastname')->insertTextField('LastName', 30, 50)->required();
      $generalSection->insertRow('displayname')->insertTextField('DisplayName', 30, 100)->required();
      $generalSection->insertRow('languageid')->insertAdvancedSelect('LanguageID',
              array(
                  'Class' => 'Language',
                  'RelatedArrayName' => 'Languages',
                  'RelatedArrayLoadFunction' => 'dbLoadLanguages',
                  'Multiple' => false,
                  'toStringArguments' => array(),
                  'params' => array(
                      'p' => 'admin/core/ajax',
                      'f' => 'searchlanguages',
                      'searchtype' => 'json'
                  )
      ));
      $generalSection->insertRow('countryid')->insertSelect('CountryID', 'getAllCountries');

      if($_ARCHON->AdministrativeInterface->Object->Pending || !$_ARCHON->AdministrativeInterface->Object->ID)
      {
         $generalSection->insertRow('pending')->insertRadioButtons('Pending');
      }
      else
      {
         $generalSection->insertRow('pending')->insertInformation('Pendingtext', $strNo);
      }

      $generalSection->insertRow('locked')->insertRadioButtons('Locked');

      $additionalSection = $_ARCHON->AdministrativeInterface->insertSection('additional');

      if($_ARCHON->AdministrativeInterface->Object->ID)
      {
         call_user_func(array($_ARCHON->AdministrativeInterface->Object, 'dbLoadUserProfileFields'));
         $prevUserProfileFieldCategoryID = 0;

         foreach($_ARCHON->AdministrativeInterface->Object->UserProfileFields as $Key => $objUserProfileField)
         {
            if(is_natural($Key))
            {
               if($objUserProfileField->InputType == 'radio')
               {
                  $additionalSection->insertRow("userprofilefields{$objUserProfileField->ID}", $objUserProfileField->UserProfileField)->insertRadioButtons("UserProfileFields[$objUserProfileField->ID][Value]");
               }
               elseif($objUserProfileField->InputType == 'textarea')
               {
                  $additionalSection->insertRow("userprofilefields{$objUserProfileField->ID}", $objUserProfileField->UserProfileField)->insertTextArea("UserProfileFields[$objUserProfileField->ID][Value]", $objUserProfileField->Size);
               }
               elseif($objUserProfileField->InputType == 'textfield')
               {
                  $additionalSection->insertRow("userprofilefields{$objUserProfileField->ID}", $objUserProfileField->UserProfileField)->insertTextField("UserProfileFields[$objUserProfileField->ID][Value]", $objUserProfileField->Size, $objUserProfileField->MaxLength);
               }
               elseif($objUserProfileField->InputType == 'timestamp')
               {
                  $additionalSection->insertRow("userprofilefields{$objUserProfileField->ID}", $objUserProfileField->UserProfileField)->insertTimestampField("UserProfileFields[$objUserProfileField->ID][Value]", $objUserProfileField->Size, $objUserProfileField->MaxLength);
               }
               elseif($objUserProfileField->InputType == 'select')
               {
                  $additionalSection->insertRow("userprofilefields{$objUserProfileField->ID}", $objUserProfileField->UserProfileField)->insertSelect("UserProfileFields[$objUserProfileField->ID][Value]", $objUserProfileField->ListDataSource);
               }
            }
         }
      }
   }

   $_ARCHON->AdministrativeInterface->outputInterface();
}

/**
 * Creates the list of users in the list frame of the primary interface
 *
 */
function publicusers_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchUsers', array('isadminuser' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function publicusers_ui_exec()
{
   global $_ARCHON;

//   $objUser = New User($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      if($_REQUEST['password'] && $_REQUEST['password'] != $_REQUEST['confirmpassword'])
      {
         $_ARCHON->declareError("Could not store User: Passwords do not match.");
      }
      else
      {
         $arrModules = $_ARCHON->getAllModules();
         $arrUserProfileFields = $_ARCHON->getAllUserProfileFields();

         foreach($arrIDs as &$ID)
         {
            $objUser = New User($_REQUEST);
            $objUser->ID = $ID;
            $objUser->IsAdminUser = 0;
            $objUser->Login = $objUser->Email;

            if($objUser->dbStore())
            {
               $ID = $objUser->ID;

//               if(is_array($_REQUEST['relatedrepositoryids']))
//               {
//                  $objUser->dbUpdateRelatedRepositories($_REQUEST['relatedrepositoryids']);
//               }

               if(isset($_REQUEST['userprofilefields']))
               {
                  foreach($_REQUEST['userprofilefields'] as $UserProfileFieldID => $arr)
                  {
                     $objUser->dbSetUserProfileField($UserProfileFieldID, $arr['value']);
                  }
               }
            }
         }
      }
   }
   else if($_REQUEST['f'] == 'delete')
   {
      foreach($arrIDs as $ID)
      {
         $objUser = New User($ID);
         $objUser->IsAdminUser = 0;
         $objUser->dbDelete();
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
      $msg = "User Database Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}

?>