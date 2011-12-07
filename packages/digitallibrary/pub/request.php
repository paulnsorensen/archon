<?php
/**
 * Output file for digital content
 *
 * @package Archon
 * @author Chris Prom 3/19/2008
 */

isset($_ARCHON) or die();

if(!defined('PACKAGE_DIGITALLIBRARY'))
{
    return;
}

$objDigitalContent = New DigitalContent($_REQUEST['id']);

if(!$objDigitalContent->ID)
{
    $_ARCHON->declareError("Could not load DigitalContent: DigitalContent ID not defined.");
}

$objDigitalContent->dbLoad();
$objDigitalContent->dbLoadRelatedObjects();

if($objDigitalContent->Collection)
{
    $objDigitalContent->Collection->dbLoadRelatedObjects();
}

if(!$objDigitalContent->Browsable && !$_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
{
    $DisableTheme = $_ARCHON->PublicInterface->DisableTheme;
    $_ARCHON->PublicInterface->DisableTheme = true;
    
    $_ARCHON->declareError("Could not access DigitalContent \"" . $objDigitalContent->toString() . "\": Public access disallowed.");
    
    $_ARCHON->PublicInterface->DisableTheme = $DisableTheme;
}



$objDigitalArchivesPhrase = Phrase::getPhrase('digitalcontent_digitalarchives', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
$strDigitalArchives = $objDigitalArchivesPhrase ? $objDigitalArchivesPhrase->getPhraseValue(ENCODE_HTML) : 'Digital Archives';
$objRequestHighPhrase = Phrase::getPhrase('digitalcontent_digitalarchives', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strRequestHigh = $objRequestHighPhrase ? $objRequestHighPhrase->getPhraseValue(ENCODE_HTML) : "Request Hi-Resolution Image";

$_ARCHON->PublicInterface->Title = $strRequestHigh;

$_ARCHON->PublicInterface->addNavigation($strDigitalArchives, "?p=digitallibrary/digitallibrary");
$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "index.php?p=digitallibrary/digitalcontent&amp;id=$objDigitalContent->ID");

if(!$_ARCHON->PublicInterface->Templates['digitallibrary']['Request'])
{
    $_ARCHON->declareError("Could not display DigitalContent: Request template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}


require_once("header.inc.php");

if(!$_ARCHON->Error)
{
    eval($_ARCHON->PublicInterface->Templates['digitallibrary']['DigitalContent']);
    eval($_ARCHON->PublicInterface->Templates['digitallibrary']['Request']);
}

require_once("footer.inc.php");