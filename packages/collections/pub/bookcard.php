<?php
/**
 * Output file for book card
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

$objBook = New Book($_REQUEST['id']);
$objBook->dbLoad();
$objBook->dbLoadRelatedObjects();



if($objBook->TemplateSet)
{
    $_ARCHON->PublicInterface->TemplateSet = $objBook->TemplateSet;
    $_ARCHON->PublicInterface->Templates = $_ARCHON->loadTemplates($_ARCHON->PublicInterface->TemplateSet);
}

if(!$_ARCHON->PublicInterface->Templates['collections']['BookCard'])
{
    $_ARCHON->declareError("Could not display BookCard: BookCard template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}

$_ARCHON->PublicInterface->Title = $objBook->toString();



$_ARCHON->PublicInterface->addNavigation($objBook->getString('Title', 30), "p={$_REQUEST['p']}&amp;id=$objBook->ID");

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
    eval($_ARCHON->PublicInterface->Templates['collections']['BookCard']);
}

require_once("footer.inc.php");