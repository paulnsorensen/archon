<?php

isset($_ARCHON) or die();


ArchonInstaller::updateDBProgressTable('', "Insert data into tblCollections_ResearcherTypes if it is empty");

$query = "SELECT ID FROM tblCollections_ResearcherTypes";

$result = $_ARCHON->mdb2->query($query);
if (PEAR::isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}
$row = $result->fetchRow();
if(!$row['ID'])
{
   $queries = array();
   if($_ARCHON->db->ServerType == 'MSSQL')
   {
      $queries[] = "SET IDENTITY_INSERT tblCollections_ResearcherTypes ON";
   }
   $queries[] = "INSERT INTO tblCollections_ResearcherTypes (ID, ResearcherType) VALUES (1, 'Public')";
   $queries[] = "INSERT INTO tblCollections_ResearcherTypes (ID, ResearcherType) VALUES (2, 'Faculty')";
   $queries[] = "INSERT INTO tblCollections_ResearcherTypes (ID, ResearcherType) VALUES (3, 'Student')";
   $queries[] = "INSERT INTO tblCollections_ResearcherTypes (ID, ResearcherType) VALUES (4, 'Administrative Staff')";
   $queries[] = "INSERT INTO tblCollections_ResearcherTypes (ID, ResearcherType) VALUES (5, 'Graduate Student')";
   if($_ARCHON->db->ServerType == 'MSSQL')
   {
      $queries[] = "SET IDENTITY_INSERT tblCollections_ResearcherTypes OFF";
   }
   ArchonInstaller::execQueries($queries);
   unset($queries);
}
$result->free();


ArchonInstaller::updateDBProgressTable('', "Insert data into tblCollections_ResearchAppointmentPurposes if it is empty");

$query = "SELECT ID FROM tblCollections_ResearchAppointmentPurposes";

$result = $_ARCHON->mdb2->query($query);
if (PEAR::isError($result))
{
   trigger_error($result->getMessage(), E_USER_ERROR);
}
$row = $result->fetchRow();
if(!$row['ID'])
{
   $queries = array();
   if($_ARCHON->db->ServerType == 'MSSQL')
   {
      $queries[] = "SET IDENTITY_INSERT tblCollections_ResearchAppointmentPurposes ON";
   }
   $queries[] = "INSERT INTO tblCollections_ResearchAppointmentPurposes (ID, ResearchAppointmentPurpose) VALUES (1, 'Administrative')";
   $queries[] = "INSERT INTO tblCollections_ResearchAppointmentPurposes (ID, ResearchAppointmentPurpose) VALUES (2, 'Classroom')";
   $queries[] = "INSERT INTO tblCollections_ResearchAppointmentPurposes (ID, ResearchAppointmentPurpose) VALUES (3, 'Dissertation')";
   $queries[] = "INSERT INTO tblCollections_ResearchAppointmentPurposes (ID, ResearchAppointmentPurpose) VALUES (4, 'Historical Research')";
   $queries[] = "INSERT INTO tblCollections_ResearchAppointmentPurposes (ID, ResearchAppointmentPurpose) VALUES (5, 'Personal')";
   if($_ARCHON->db->ServerType == 'MSSQL')
   {
      $queries[] = "SET IDENTITY_INSERT tblCollections_ResearchAppointmentPurposes OFF";
   }
   
   ArchonInstaller::execQueries($queries);
   unset($queries);
}
$result->free();

?>
