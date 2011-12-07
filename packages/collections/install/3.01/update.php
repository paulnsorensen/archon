<?php

isset($_ARCHON) or die();

ArchonInstaller::updateDBProgressTable('', "Ensure collection content module is installed");

$query = "SELECT * FROM tblCore_Modules WHERE Script = 'collectioncontent'";
$result = $_ARCHON->mdb2->query($query);
Archoninstaller::handleError($result, $query);

if($result->numRows() == 0)
{
   $package_collections = ArchonInstaller::getPackageID('collections');

   $query = "INSERT INTO tblCore_Modules (PackageID, Script) VALUES ('{$package_collections}','collectioncontent')";

   ArchonInstaller::execQuery($query);
}


ArchonInstaller::updateDBProgressTable('', 'Ensure LevelContainerID index is created on collection content table');

if($_ARCHON->db->ServerType == 'MSSQL')
{
   $query = "IF NOT EXISTS (SELECT * FROM sysindexes WHERE id=object_id('tblCollections_Content') AND name='LevelContainerID') CREATE INDEX LevelContainerID ON tblCollections_Content(LevelContainerID)";
   ArchonInstaller::execQuery($query);
}
elseif($_ARCHON->db->ServerType == 'MySQL')
{
   $found = false;
   $query = "SHOW INDEXES FROM tblCollections_Content";
   $result = $_ARCHON->mdb2->query($query);
   Archoninstaller::handleError($result, $query);

   while($row = $result->fetchRow() && !$found)
   {
      if(strtolower($row['Key_name']) == 'levelcontainerid')
      {
         $found = true;
      }
   }

   if(!$found)
   {
      $query = "ALTER TABLE tblCollections_Content ADD INDEX (LevelContainerID)";
      ArchonInstaller::execQuery($query);
   }
}



?>
