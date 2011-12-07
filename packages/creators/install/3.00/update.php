<?php

isset($_ARCHON) or die();

ArchonInstaller::updateDBProgressTable('', "Set RepositoryID to default repository on existing creator records");

$query = "SELECT Value FROM tblCore_Configuration WHERE Directive = 'Default Repository'";

$result = $_ARCHON->mdb2->query($query);
if (PEAR::isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}
$row = $result->fetchRow();
$DefaultRepositoryID = $row['Value'];
$result->free();

$query = "UPDATE tblCreators_Creators SET RepositoryID = '{$DefaultRepositoryID}'";

ArchonInstaller::execQuery($query);


ArchonInstaller::updateDBProgressTable('', "Copy any ParentBody fields into new creator relationship table");

$query = "SELECT ID FROM tblCreators_CreatorTypes WHERE CreatorType = 'Unassigned'";

$result = $_ARCHON->mdb2->query($query);
if (PEAR::isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}
$row = $result->fetchRow();
$UnassignedID = $row['ID'];
$result->free();

$query = "SELECT ID,ParentBody FROM tblCreators_Creators WHERE ParentBody IS NOT NULL AND ParentBody != ''";
$result = $_ARCHON->mdb2->query($query);
if (PEAR::isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}
while($row = $result->fetchRow())
{
   $CreatorID = $row['ID'];
   $strParentBody = $_ARCHON->mdb2->escape($row['ParentBody']);
   $query = "SELECT ID FROM tblCreators_Creators WHERE Name ='".$strParentBody."' OR NameFullerForm ='".$strParentBody."' OR NameVariants ='".$strParentBody."'";
   $_ARCHON->mdb2->setLimit(1);
   $parentbodyresult = $_ARCHON->mdb2->query($query);
   if (PEAR::isError($parentbodyresult))
   {
      trigger_error($parentbodyresult->getMessage(), E_USER_ERROR);
   }
   $parentbodyrow = $parentbodyresult->fetchRow();
   if($parentbodyrow['ID'])
   {
      $RelatedCreatorID = $parentbodyrow['ID'];
      $parentbodyresult->free();
   }
   else
   {
      $parentbodyresult->free();
      $query = "INSERT INTO tblCreators_Creators (Name, CreatorTypeID) VALUES('".$strParentBody."','".$UnassignedID."')";
      ArchonInstaller::execQuery($query);

      $query = "SELECT ID FROM tblCreators_Creators WHERE Name = '".$strParentBody."'";
      $parentbodyresult = $_ARCHON->mdb2->query($query);
      if (PEAR::isError($parentbodyresult))
      {
         trigger_error($parentbodyresult->getMessage(), E_USER_ERROR);
      }
      $parentbodyrow = $parentbodyresult->fetchRow();
      if($parentbodyrow['ID'])
      {
         $RelatedCreatorID = $parentbodyrow['ID'];
         $parentbodyresult->free();
      }
   }

   $query = "INSERT INTO tblCreators_CreatorCreatorIndex (CreatorID, RelatedCreatorID, CreatorRelationshipTypeID) VALUES ('".$CreatorID."', '".$RelatedCreatorID."', '2')";
   ArchonInstaller::execQuery($query);

}
$result->free();



ArchonInstaller::updateDBProgressTable('', "Copy any RelatedCreators fields into new creator relationship table");

$query = "SELECT ID,RelatedCreators FROM tblCreators_Creators WHERE RelatedCreators IS NOT NULL AND RelatedCreators != ''";
$result = $_ARCHON->mdb2->query($query);
if (PEAR::isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}
while($row = $result->fetchRow())
{
   $CreatorID = $row['ID'];
   $strRelatedCreators = $_ARCHON->mdb2->escape($row['RelatedCreators']);
   $query = "SELECT ID FROM tblCreators_Creators WHERE Name ='".$strRelatedCreators."' OR NameFullerForm ='".$strRelatedCreators."' OR NameVariants ='".$strRelatedCreators."'";
   $_ARCHON->mdb2->setLimit(1);
   $relatedcreatorsresult = $_ARCHON->mdb2->query($query);
   if (PEAR::isError($relatedcreatorsresult))
   {
      trigger_error($relatedcreatorsresult->getMessage(), E_USER_ERROR);
   }
   $relatedcreatorsrow = $relatedcreatorsresult->fetchRow();
   if($relatedcreatorsrow['ID'])
   {
      $RelatedCreatorID = $relatedcreatorsrow['ID'];
      $relatedcreatorsresult->free();
   }
   else
   {
      $relatedcreatorsresult->free();
      $query = "INSERT INTO tblCreators_Creators (Name, CreatorTypeID) VALUES('".$strRelatedCreators."','".$UnassignedID."')";
      ArchonInstaller::execQuery($query);


      $query = "SELECT ID FROM tblCreators_Creators WHERE Name = '".$strRelatedCreators."'";
      $relatedcreatorsresult = $_ARCHON->mdb2->query($query);
      if (PEAR::isError($relatedcreatorsresult))
      {
         trigger_error($relatedcreatorsresult->getMessage(), E_USER_ERROR);
      }
      $relatedcreatorsrow = $relatedcreatorsresult->fetchRow();
      if($relatedcreatorsrow['ID'])
      {
         $RelatedCreatorID = $parentbodyrow['ID'];
         $relatedcreatorsresult->free();
      }
   }

   $query = "INSERT INTO tblCreators_CreatorCreatorIndex (CreatorID, RelatedCreatorID, CreatorRelationshipTypeID) VALUES ('".$CreatorID."', '".$RelatedCreatorID."', '7')";
   ArchonInstaller::execQuery($query);

}
$result->free();

?>
