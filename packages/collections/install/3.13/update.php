<?php

isset($_ARCHON) or die();

if($_ARCHON->db->ServerType == 'MySQL')
{
   ArchonInstaller::updateDBProgressTable('', "Remove extra LevelContainerID index, if exists");

   $query = "SHOW INDEX FROM tblCollections_Content";

   $result = $_ARCHON->mdb2->query($query);
   if (PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
   while($row = $result->fetchRow())
   {
      if(strtolower($row['Key_name']) == strtolower('LevelContainerID_2'))
      {
         $query = "ALTER TABLE tblCollections_Content DROP INDEX LevelContainerID_2";
         ArchonInstaller::execQuery($query);
      }
   }
   $result->free();
}


?>
