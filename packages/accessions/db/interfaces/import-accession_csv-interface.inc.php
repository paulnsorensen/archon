<?php

$objUser = $_ARCHON->Security->Session->User;

if(!$objUser->RepositoryLimit)
{
   $dialogSection->insertRow('repository')->insertSelect('CurrentRepositoryID', 'getAllRepositories')->required();
}
elseif(count($objUser->Repositories) == 1)
{
   $repositoryID = $objUser->Repositories[key($objUser->Repositories)]->ID;
   $info = $objUser->Repositories[key($objUser->Repositories)]->Name;
   $dialogSection->insertRow('repository')->insertInformation('CurrentRepositoryID', $info);
   $dialogSection->getRow('repository')->insertHTML('<input type="hidden" id="CurrentRepositoryIDInput" name="CurrentRepositoryID" value="'.$repositoryID.'" />');
}
else
{
   $dialogSection->insertRow('repository')->insertSelect('CurrentRepositoryID', $objUser->Repositories)->required();
}

?>
