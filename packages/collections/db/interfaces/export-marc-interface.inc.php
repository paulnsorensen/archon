<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

$objUser = $_ARCHON->Security->Session->User;

if(!$objUser->RepositoryLimit)
{
   $dialogSection->insertRow('repositoryid')->insertSelect('repositoryid', 'getAllRepositories')->required();
}
elseif(count($objUser->Repositories) == 1)
{
   $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
   $info = $objUser->Repositories[key($objUser->Repositories)]->Name;
   $dialogSection->insertRow('repositoryid')->insertInformation('repositoryid', $info);
   $dialogSection->getRow('repositoryid')->insertHTML('<input type="hidden" id="repositoryidInput" name="repositoryid" value="'.$repositoryID.'" />');
}
else
{
   $dialogSection->insertRow('repositoryid')->insertSelect('repositoryid', $objUser->Repositories)->required();
}

$dialogSection->insertRow('classificationid')->insertHierarchicalSelect('classificationid', 'traverseClassification', 'getChildClassifications', 'Classification');



?>
