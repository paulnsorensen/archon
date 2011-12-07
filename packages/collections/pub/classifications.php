<?php
/**
 * Output file for browsing by classification
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

if(!$_ARCHON->PublicInterface->Templates['collections']['Classifications'])
{
    $_ARCHON->declareError("Could not display collection: Classifications template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}



$objClassificationsTitlePhrase = Phrase::getPhrase('classifications_title', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
$strClassificationsTitle = $objClassificationsTitlePhrase ? $objClassificationsTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse by Record Group';


$in_ID = $_REQUEST['id'] ? $_REQUEST['id'] : 0;

$objClassification = New Classification($in_ID);

if($in_ID && !$objClassification->dbLoad())
{
    return;
}

$_ARCHON->PublicInterface->Title = $strClassificationsTitle;

$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}");

if($objClassification->Parent)
{
    $arrClassificationTraversal = $_ARCHON->traverseClassification($objClassification->ParentID);

    foreach($arrClassificationTraversal as $objTravClassification)
    {
        $_ARCHON->PublicInterface->addNavigation($objTravClassification->getString('Title', 30), "?p=collections/classifications&amp;id=$objTravClassification->ID");
    }
}

if($objClassification->ID)
{
    $_ARCHON->PublicInterface->addNavigation($objClassification->getString('Title', 30), "?p=collections/classifications&amp;id=$objClassification->ID");

    $objRecordsRelatingTitlePhrase = Phrase::getPhrase('classifications_recordsrelatingtitle', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strRecordsRelatingTitle = $objRecordsRelatingTitlePhrase ? $objRecordsRelatingTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Records Relating to $1';

    $_ARCHON->PublicInterface->Title = str_replace('$1', $objClassification->getString('Title', 30), $strRecordsRelatingTitle);
}



require_once("header.inc.php");
if(!$_ARCHON->Error)
{
	 eval($_ARCHON->PublicInterface->Templates['collections']['Classifications']);
}


require_once("footer.inc.php");
?>