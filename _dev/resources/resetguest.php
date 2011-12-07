<?php
// This is necessary to fool import scripts into thinking
// we have authenticated

require_once('includes.inc.php');

$ID = $_ARCHON->getUserIDFromLogin("guest");

$ID = $ID ? $ID : 0;

$objUser = New User($ID);
$objUser->Login = "guest";
$objUser->Password = "guest";
$objUser->DisplayName = "Guest User";
$objUser->RepositoryID = 1;
$objUser->Locked = 0;
$objUser->RepositoryLimit = 0;
$objUser->LanguageID = 0;

$objUser->dbUpdateRelatedUsergroups(array(6));
$UserID = $objUser->ID;
$objUser->IsAdminUser = 1;

// Have to use a database query to avoid security checks.
$objUser->PasswordHashy = crypt($objUser->Password, crypt($objUser->Password));

if($ID)
{
    $query = "UPDATE tblCore_Users
    SET
      Login = '" . $_ARCHON->mdb2->escape($objUser->Login) . "',
      PasswordHash = '" . $_ARCHON->mdb2->escape($objUser->PasswordHashy) . "',
      DisplayName = '" . $_ARCHON->mdb2->escape($objUser->DisplayName) . "',
      Locked = '" . $_ARCHON->mdb2->escape($objUser->Locked) . "'
    WHERE
       ID = '$UserID'";

$_ARCHON->mdb2->exec($query);

}
else
{
    $query = "INSERT INTO tblCore_Users (
      Login,
      PasswordHash,
      DisplayName,
      LanguageID,
      Locked
   ) VALUES (
      '" . $_ARCHON->mdb2->escape($objUser->Login) . "',
      '" . $_ARCHON->mdb2->escape($objUser->PasswordHashy) . "',
      '" . $_ARCHON->mdb2->escape($objUser->DisplayName) . "',
      '" . $_ARCHON->mdb2->escape($objUser->LanguageID) . "',
      '" . $_ARCHON->mdb2->escape($objUser->Locked) . "'
   )";

   $query1 = "INSERT INTO tblCore_UserUsergroupIndex (UserID, UsergroupID) VALUES ($UserID, 6)"; 

$_ARCHON->mdb2->exec($query);
$_ARCHON->mdb2->exec($query1);
}



header("Location: index.php");