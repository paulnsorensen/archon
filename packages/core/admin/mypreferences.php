<?php
/**
 * My Preferences manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Prom 1/28/2009
 */


isset($_ARCHON) or die();

// Determine what to do based upon user input
if(!$_REQUEST['f'])
{
   mypreferences_ui_main();
}
elseif($_REQUEST['f'] == 'dialog_changelanguage')
{
   mypreferences_ui_dialog_changelanguage();
}
elseif($_REQUEST['f'] == 'dialog_changepassword')
{
   mypreferences_ui_dialog_changepassword();
}
else
{
   mypreferences_ui_exec();
}

// mypreferences_ui_main()
//   - purpose: Creates the primary user interface
//              for the mypreferences Manager.
function mypreferences_ui_main()
{
   global $_ARCHON;

   $browseSection = $_ARCHON->AdministrativeInterface->getSection('browse');
   $browseSection->disable();

   $_ARCHON->AdministrativeInterface->disableQuickSearch();


   $objChangeLanguagePhrase = Phrase::getPhrase('changelanguage', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strChangeLanguage = $objChangeLanguagePhrase ? $objChangeLanguagePhrase->getPhraseValue(ENCODE_HTML) : 'Change Language_';

   $objChangePasswordPhrase = Phrase::getPhrase('changepassword', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strChangePassword = $objChangePasswordPhrase ? $objChangePasswordPhrase->getPhraseValue(ENCODE_HTML) : 'Change Password_';



   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');


   $generalSection->insertRow()->insertHTML("<button type='button' class='adminformbutton' onclick='admin_ui_opendialog(\"core\", \"mypreferences\", \"changelanguage\")'>{$strChangeLanguage}</button>");

   $generalSection->insertRow()->insertHTML("<button type='button' class='adminformbutton' onclick='admin_ui_opendialog(\"core\", \"mypreferences\", \"changepassword\")'>{$strChangePassword}</button>");

   $_ARCHON->AdministrativeInterface->outputInterface();
}




function mypreferences_ui_dialog_changelanguage()
{
   global $_ARCHON;
   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/core/mypreferences', 'changelanguage');

   $objLanguage = new Language($_ARCHON->Security->Session->User->LanguageID);
   $strLanguage = $objLanguage->toString();

   $dialogSection->insertRow('currentlanguage')->insertInformation('CurrentLanguage', $strLanguage);
   $dialogSection->insertRow('languageid')->insertSelect('LanguageID', 'getAllLanguages');

   $_ARCHON->AdministrativeInterface->outputInterface();
}




function mypreferences_ui_dialog_changepassword()
{
   global $_ARCHON;
   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/core/mypreferences', 'changepassword');

   $dialogSection->insertRow('currentpassword')->insertPasswordField('CurrentPassword', 50, 100);
   $dialogSection->insertRow('newpassword')->insertPasswordField('NewPassword', 50, 100);
   $dialogSection->insertRow('confirmnewpassword')->insertPasswordField('ConfirmNewPassword', 50, 100);

   $_ARCHON->AdministrativeInterface->outputInterface();

}


function mypreferences_ui_exec()
{
   global $_ARCHON;

   //@set_time_limit(0);

   if($_REQUEST['f'] == 'changepassword')
   {
      if($_ARCHON->Security->Session->User->verifyPassword($_REQUEST['currentpassword']))
      {
         if($_REQUEST['newpassword'] == $_REQUEST['confirmnewpassword'])
         {
            $_ARCHON->Security->Session->User->dbSetPassword($_REQUEST['newpassword']);
         }
         else
         {
            $_ARCHON->declareError("Could not change password: New passwords do not match.");
         }
      }
      else
      {
         $_ARCHON->declareError("Could not change password: Current password is not valid.");
      }
   }
   elseif($_REQUEST['f'] == 'changelanguage')
   {
      $_ARCHON->Security->Session->User->dbSetLanguageID($_REQUEST['languageid']);

   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
   }

   $location = "?p=admin/core/mypreferences";

   if($_ARCHON->Error)
   {
      $msg = "<font color=red>$_ARCHON->Error</font>";
   }
   else
   {
      $msg = "Preferences Updated Successfully.";
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
