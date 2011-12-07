<?php


isset($_ARCHON) or die();

$objAccession = New Accession($_REQUEST['id']);
$objAccession->dbLoadAll();


//if(!$objAccession->Enabled && !$_ARCHON->Security->verifyPermissions(MODULE_ACCESSIONS, READ)) //|| ($objAccession->RepositoryID != $_ARCHON->Security->Session->User->RepositoryID && $_ARCHON->Security->Session->User->RepositoryLimit))
if(!$objAccession->enabled())
{
    $_ARCHON->AdministrativeInterface = true;
    $_ARCHON->declareError("Could not access Accession \"" . $objAccession->toString() . "\": Public access disallowed.");
    $_ARCHON->AdministrativeInterface = false;
}

if(!$_ARCHON->PublicInterface->Templates['accessions']['Accession'])
{

    $_ARCHON->declareError("Could not display Accession: Accession template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}

$_ARCHON->PublicInterface->Title = $objAccession->toString();

$_ARCHON->PublicInterface->addNavigation($objAccession->getString('Title', 30), "p={$_REQUEST['p']}&amp;id=$objAccession->ID");

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
    eval($_ARCHON->PublicInterface->Templates['accessions']['Accession']);
}

require_once("footer.inc.php");

?>