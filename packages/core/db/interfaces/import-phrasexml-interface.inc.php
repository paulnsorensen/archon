<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
$dialogSection->insertRow('allpackages')->insertCheckBox('allpackages');
$arrAPRCodes = array();
foreach($_ARCHON->getAllPackages() as $objPackage)
{
   $arrAPRCodes[$objPackage->APRCode] = $objPackage->toString();
}
$dialogSection->insertRow('packageid')->insertSelect('aprcode', $arrAPRCodes);
$dialogSection->getRow('packageid')->setEnableConditions('allpackages', false);
$dialogSection->insertRow('languageid')->insertSelect('LanguageID', 'getAllLanguages');




?>
