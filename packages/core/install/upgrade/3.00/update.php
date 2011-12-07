<?php

isset($_ARCHON) or die();

$package_core = ArchonInstaller::getPackageID('core');


ArchonInstaller::updateDBProgressTable('', "Update Configuration table to use new listdatasource format");
if($_ARCHON->db->ServerType == 'MySQL')
{
   $query = "UPDATE tblCore_Configuration SET ListDataSource = CONCAT(\"get\", ListDataSource) WHERE ListDataSource IS NOT NULL";
}
elseif($_ARCHON->db->ServerType == 'MSSQL')
{
   $query = "UPDATE tblCore_Configuration SET ListDataSource = 'get' + ListDataSource WHERE ListDataSource IS NOT NULL";
}
else
{
   ArchonInstaller::updateDBProgressTable('ERROR', "SQL Type is not defined");
   trigger_error("SQL Type is not defined", E_USER_ERROR);

}
ArchonInstaller::execQuery($query);



$package_collections = ArchonInstaller::getPackageID('collections');

$package_research = ArchonInstaller::getPackageID('research');


ArchonInstaller::updateDBProgressTable('', "Add 'Verify Public Accounts' configuration directive");

if($package_research != -1)
{
   $query = "UPDATE tblCore_Configuration SET PackageID = ".$package_core.", Directive = 'Verify Public Accounts' WHERE tblCore_Configuration.Directive = 'Confirm Researcher Accounts'";
}
else
{
   $query = "INSERT INTO tblCore_Configuration (PackageID, Directive, Value, InputType, ReadOnly) VALUES (".$package_core.", 'Verify Public Accounts', '1', 'radio', '0')";
}
ArchonInstaller::execQuery($query);



ArchonInstaller::updateDBProgressTable('', "Add usergroups to tblCore_UserUsergroupIndex");

$query = "SELECT ID,UsergroupID FROM tblCore_Users";
$result = $_ARCHON->mdb2->query($query);
if (PEAR::isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}

while($row = $result->fetchRow())
{
   $query = "INSERT INTO tblCore_UserUsergroupIndex (UserID, UsergroupID) VALUES (".$row['ID'].", ".$row['UsergroupID'].")";
   ArchonInstaller::execQuery($query);

}
$result->free();




if($package_collections != -1)
{
ArchonInstaller::updateDBProgressTable('', "Add repositories to tblCore_UserRepositoryIndex");


   $query = "SELECT ID,RepositoryID FROM tblCore_Users";
   $result = $_ARCHON->mdb2->query($query);
   if (PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }

   while($row = $result->fetchRow())
   {
      $query = "INSERT INTO tblCore_UserRepositoryIndex (UserID, RepositoryID) VALUES (".$row['ID'].", ".$row['RepositoryID'].")";
      ArchonInstaller::execQuery($query);

   }
   $result->free();

}
else
{
ArchonInstaller::updateDBProgressTable('', "Add RepositoryLimit Field");


   if($_ARCHON->db->ServerType == 'MSSQL')
   {
      $query = "ALTER TABLE tblCore_Users ADD RepositoryLimit BIT NOT NULL DEFAULT '0'";
   }
   elseif($_ARCHON->db->ServerType == 'MySQL')
   {
      $query = "ALTER TABLE tblCore_Users ADD RepositoryLimit tinyint(1) NOT NULL DEFAULT '0'";
   }
   else
   {
      trigger_error("SQL Type is not defined", E_USER_ERROR);
   }
   ArchonInstaller::execQuery($query);

}



if($package_research != -1)
{
ArchonInstaller::updateDBProgressTable('', "Add researchers to tblCore_Users ");

   $query = "SELECT * FROM tblResearch_Researchers";
   $result = $_ARCHON->mdb2->query($query);
   if (PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }

   while($row = $result->fetchRow())
   {
      $login = $row['Email'];

      $displayname = $_ARCHON->mdb2->escape($row['FirstName']." ".$row['LastName']);

      $query = "INSERT INTO tblCore_Users (Login, Email, PasswordHash, FirstName, LastName, DisplayName, IsAdminUser, RegisterTime, Pending, PendingHash, LanguageID, RepositoryID, UsergroupID, RepositoryLimit, Locked) ";
      $query .= "VALUES ('".$login."', '".$login."', '".$row['PasswordHash']."', '".$_ARCHON->mdb2->escape($row['FirstName'])."', '".$_ARCHON->mdb2->escape($row['LastName'])."', '".$displayname."', 0, '".$row['RegisterTime']."', ".$row['Pending'].", '".$row['PendingHash']."', '0', '0', '0', '0', '0')";
      ArchonInstaller::execQuery($query);


      $query = "SELECT ID FROM tblCore_Users WHERE Login = '".$login."'";
      $idresult = $_ARCHON->mdb2->query($query);
      if (PEAR::isError($idresult))
      {
         trigger_error($idresult->getMessage(), E_USER_ERROR);
      }
      $idrow = $idresult->fetchRow();
      $id = $idrow['ID'];
      $idresult->free();

      if($id)
      {
         $arrUserProfileFields = array();


         $query = "SELECT ID FROM tblCore_StateProvinces WHERE ISOAlpha2 = '".$row['State']."'";
         $stateresult = $_ARCHON->mdb2->query($query);
         $staterow = $stateresult->fetchRow();
         $stateID = $staterow['ID'];
         $stateresult->free();

         if($stateID)
         {
            $arrUserProfileFields['StateProvinceID'] = $stateID;
            $query = "SELECT CountryID FROM tblCore_StateProvinces WHERE ID = ". $stateID;
            $countryresult = $_ARCHON->mdb2->query($query);
            if (PEAR::isError($countryresult))
            {
               trigger_error($countryresult->getMessage(), E_USER_ERROR);
            }
            $countryrow = $countryresult->fetchRow();
            $countryID = $countryrow['CountryID'];
            $countryresult->free();

            $query = "UPDATE tblCore_Users SET CountryID = ".$countryID." WHERE ID = ".$id;
            ArchonInstaller::execQuery($query);

         }

         $arrUserProfileFields['Address'] = $row['Address'];
         $arrUserProfileFields['Address2'] = $row['Address2'];
         $arrUserProfileFields['City'] = $row['City'];
         $arrUserProfileFields['ZIPCode'] = $row['ZIPCode'];
         $arrUserProfileFields['ZIPPlusFour'] = $row['ZIPPlusFour'];
         $arrUserProfileFields['Phone'] = $row['Phone'];
         $arrUserProfileFields['PhoneExtension'] = $row['PhoneExtension'];
         $arrUserProfileFields['ResearcherTypeID'] = $row['ResearcherTypeID'];


         foreach ($arrUserProfileFields as $FieldName => $Value)
         {
            if($Value && $Value != '')
            {
               $query = "SELECT ID FROM tblCore_UserProfileFields WHERE UserProfileField = '".$FieldName."'";
               $upfresult = $_ARCHON->mdb2->query($query);
               if (PEAR::isError($upfresult))
               {
                  trigger_error($upfresult->getMessage(), E_USER_ERROR);
               }
               $upfrow = $upfresult->fetchRow();
               $userprofilefieldID = $upfrow['ID'];
               $upfresult->free();

               if($userprofilefieldID)
               {

                  $query = "INSERT INTO tblCore_UserUserProfileFieldIndex (UserID, UserProfileFieldID, Value) VALUES ('".$id."', '".$userprofilefieldID."', '".$_ARCHON->mdb2->escape($Value)."')";
                  ArchonInstaller::execQuery($query);
               }
            }
         }
      }
   }
   $result->free();



ArchonInstaller::updateDBProgressTable('', "Drop unneccesary Researcher field");

   if($_ARCHON->db->ServerType == 'MSSQL')
   {
      $queries[] = "DELETE FROM tblCore_Sessions WHERE Researcher = 1";
      $queries[] = "DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblCore_Sessions' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblCore_Sessions') AND name = 'Researcher')); SET @cmd = 'ALTER TABLE tblCore_Sessions DROP CONSTRAINT ' + @defname; EXEC(@cmd)";
      $queries[] = "ALTER TABLE tblCore_Sessions DROP COLUMN Researcher";
   }
   elseif($_ARCHON->db->ServerType == 'MySQL')
   {
      $queries[] = "DELETE FROM tblCore_Sessions WHERE Researcher = 1";
      $queries[] = "ALTER TABLE tblCore_Sessions DROP Researcher";
   }
   else
   {
      trigger_error("SQL Type is not defined", E_USER_ERROR);
   }
   ArchonInstaller::execQueries($queries);
   unset($queries);
}


if($package_collections != -1)
{
   echo("Remove RepositoryID column from user taCble");
   ArchonInstaller::updateDBProgressTable('', "Remove RepositoryID column from user table");

   if($_ARCHON->db->ServerType == 'MSSQL')
   {

      //@TODO Figure out what the deal is with these constraints  -- I think it's fixed now but we'll keep both queries in here just in case
      //ALTER TABLE tblCore_Users DROP CONSTRAINT RepositoryID_Default;
      $queries[] = "DECLARE @defname VARCHAR(100), @cmd VARCHAR(1000); SET @defname = (SELECT name FROM sysobjects so JOIN sysconstraints sc ON so.id = sc.constid WHERE object_name(so.parent_obj) = 'tblCore_Users' AND so.xtype = 'D' AND sc.colid = (SELECT colid FROM syscolumns WHERE id = object_id('tblCore_Users') AND name = 'RepositoryID')); SET @cmd = 'ALTER TABLE tblCore_Users DROP CONSTRAINT ' + @defname; EXEC(@cmd)";
      $queries[] = "ALTER TABLE tblCore_Users DROP COLUMN RepositoryID";
   }
   elseif($_ARCHON->db->ServerType == 'MySQL')
   {
      $queries[] = "ALTER TABLE tblCore_Users DROP RepositoryID";

   }
   else
   {
      trigger_error("SQL Type is not defined", E_USER_ERROR);
   }
   ArchonInstaller::execQueries($queries);
   unset($queries);

}


echo($_ARCHON->mdb2->getDebugOutput());
?>
