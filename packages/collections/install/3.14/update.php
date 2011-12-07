<?php

isset($_ARCHON) or die();

ArchonInstaller::updateDBProgressTable('', "Fix sort order conflicts");


$query = "SELECT ParentID,CollectionID,COUNT(DISTINCT(LevelContainerID)) FROM tblCollections_Content WHERE CollectionID != 0
GROUP BY CollectionID,ParentID HAVING COUNT(DISTINCT(LevelContainerID))>1";
$result = $_ARCHON->mdb2->query($query);
ArchonInstaller::handleError($result, $query);

$arrContent = array();

while ($row = $result->fetchRow())
{
   $arrContent[$row['CollectionID']][] = $row['ParentID'];
}
$result->free();


foreach ($arrContent as $cid => $arr_pid)
{
   foreach ($arr_pid as $pid)
   {
      $query = "SELECT DISTINCT(LevelContainerID) FROM tblCollections_Content WHERE CollectionID = $cid
  AND ParentID = $pid ORDER BY LevelContainerID";
      $result = $_ARCHON->mdb2->query($query);
      ArchonInstaller::handleError($result, $query);

      $first_row = true;
      while ($row = $result->fetchRow())
      {
         if (!$first_row)
         {
            $query = "(SELECT MAX(SortOrder) AS shift FROM tblCollections_Content WHERE
         CollectionID = $cid AND ParentID = $pid AND LevelContainerID = $last)";
            $res = $_ARCHON->mdb2->query($query);
            ArchonInstaller::handleError($res, $query);
            $r = $res->fetchRow();
            $shift = $r['shift'];
            $res->free();

            $query = "UPDATE tblCollections_Content SET SortOrder = SortOrder + $shift
 WHERE CollectionID = $cid AND ParentID = $pid AND LevelContainerID =" . $row['LevelContainerID'];
            ArchonInstaller::execQuery($query);
            $last = $row['LevelContainerID'];
         } else
         {
            $last = $row['LevelContainerID'];
            $first_row = false;
         }
      }
      $result->free();
   }
}
?>
