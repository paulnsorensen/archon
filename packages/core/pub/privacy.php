<?php
isset($_ARCHON) or die();



$objPrivacyTitlePhrase = Phrase::getPhrase('privacy_title', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
$strPrivacyTitle = $objPrivacyTitlePhrase ? $objPrivacyTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Privacy Note';

$_ARCHON->PublicInterface->Title = $strPrivacyTitle;

if(!$_ARCHON->PublicInterface->Templates['core']['Privacy'])
{
    $_ARCHON->declareError("Privacy template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
}

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
    eval($_ARCHON->PublicInterface->Templates['core']['Privacy']);
}

require_once("footer.inc.php");
?>