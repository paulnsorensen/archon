<?php
/**
 * About Archon
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

if(!$_REQUEST['f'])
{
    about_ui_main();
}
else
{
	about_ui_exec();
}

// about_ui_main()
//   - purpose: Creates the About Archon Page

function about_ui_main()
{
    global $_ARCHON;
    
    $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');
    $objArchonPhrase = Phrase::getPhrase('archon', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
    $strArchon = $objArchonPhrase ? $objArchonPhrase->getPhraseValue(ENCODE_HTML) : 'Archon';
    $objReleaseDatePhrase = Phrase::getPhrase('releasedate', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
    $strReleaseDate = $objReleaseDatePhrase ? $objReleaseDatePhrase->getPhraseValue(ENCODE_HTML) : 'Release Date';
    $objInstallIDPhrase = Phrase::getPhrase('installid', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
    $strInstallID = $objInstallIDPhrase ? $objInstallIDPhrase->getPhraseValue(ENCODE_HTML) : 'Installation ID';
    $objCopyrightPhrase = Phrase::getPhrase('copyright', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
    $strCopyright = $objCopyrightPhrase ? $objCopyrightPhrase->getPhraseValue(ENCODE_BBCODE) : 'Copyright &copy;$1 <a href="http://www.uiuc.edu/">The University of Illinois at Urbana-Champaign</a>';
    $strCopyright = str_replace('$1', $_ARCHON->CopyrightYear, $strCopyright);
    $objHistoryPhrase = Phrase::getPhrase('history', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
    $strHistory = $objHistoryPhrase ? $objHistoryPhrase->getPhraseValue(ENCODE_BBCODE) : "Please visit <a href=$_ARCHON->ArchonURL>$_ARCHON->ArchonURL</a> for more information.";
    $objStaffPhrase = Phrase::getPhrase('staff', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
    $strStaff = $objStaffPhrase ? $objStaffPhrase->getPhraseValue() : 'Thank you to everybody involved in this project.';
    
    $browseSection = $_ARCHON->AdministrativeInterface->getSection('browse');
    $browseSection->disable();

    $_ARCHON->AdministrativeInterface->disableQuickSearch();
    
    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    $generalSection->insertRow('about')->insertHTML("$strArchon {$_ARCHON->getString('Version')}<br/>$strReleaseDate: {$_ARCHON->getString('ReleaseDate')}<br/>$strInstallID: ". CONFIG_CORE_INSTALLATION_ID);
    $generalSection->getRow('about')->insertHTML("<br/><a href='{$_ARCHON->getString('ArchonURL')}'>{$_ARCHON->getString('ArchonURL')}</a><br/>$strCopyright");
    $generalSection->insertRow('project_history')->insertHTML("$strHistory");
    $generalSection->insertRow("diagnostic_information")->insertHTML("<a class='adminformbutton' href='?p=admin/core/about&amp;f=phpinfo' target='_blank'>PHPInfo</a>");
    $generalSection->insertRow("credits")->insertHTML("$strStaff");
    
    $_ARCHON->AdministrativeInterface->outputInterface();
}





function about_ui_exec()
{
    global $_ARCHON;
    
    if($_REQUEST['f'] == 'phpinfo')
    {
        if($_ARCHON->Security->verifyPermissions(MODULE_ABOUT, FULL_CONTROL))
        {
        	phpinfo();
        	die();
        }
        else
        {
        	$_ARCHON->declareError("Could not generate phpinfo(): Permission denied.");
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
    
    $_ARCHON->AdministrativeInterface->sendResponse($msg, array(), $_ARCHON->Error);
}