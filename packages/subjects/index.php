<?php
$APRCode = 'subjects';
$Version = '3.21';

$_ARCHON->addPackageDependency($APRCode, 'core', $Version);

$_ARCHON->db->TablePrefixes .= "tblSubjects_";
?>