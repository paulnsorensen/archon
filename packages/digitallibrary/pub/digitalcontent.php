<?php
/**
 * Output file for digital content
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

$objDigitalContent = New DigitalContent($_REQUEST['id']);

if(!$objDigitalContent->ID)
{
    $_ARCHON->declareError("Could not load DigitalContent: DigitalContent ID not defined.");
}

if($objDigitalContent->dbLoad())
{
    $objDigitalContent->dbLoadRelatedObjects();

    if($objDigitalContent->Collection)
    {
        $objDigitalContent->Collection->dbLoadRelatedObjects();
    }

    $objDigitalContent->Repository = $objDigitalContent->getRepository();

    $_ARCHON->PublicInterface->Title = $objDigitalContent->getString('Title');

    

    $objDigitalArchivesPhrase = Phrase::getPhrase('digitalcontent_digitalarchives', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
    $strDigitalArchives = $objDigitalArchivesPhrase ? $objDigitalArchivesPhrase->getPhraseValue(ENCODE_HTML) : 'Digital Archives';

    $_ARCHON->PublicInterface->addNavigation($strDigitalArchives, "?p=digitallibrary/digitallibrary");
    $_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "index.php?p=digitallibrary/digitalcontent&amp;id=$objDigitalContent->ID");

    if(!$_ARCHON->PublicInterface->Templates['digitallibrary']['DigitalContent'])
    {
        $_ARCHON->declareError("Could not display DigitalContent: DigitalContent template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
    }
}


require_once("header.inc.php");

if(!$_ARCHON->Error)
{
    eval($_ARCHON->PublicInterface->Templates['digitallibrary']['DigitalContent']);
}

require_once("footer.inc.php");