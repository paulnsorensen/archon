<?php
$APRCode = 'accessions';
$Version = '3.21';

$_ARCHON->addPackageDependency($APRCode, 'creators', $Version);
$_ARCHON->addPackageDependency($APRCode, 'collections', $Version);
$_ARCHON->addPackageDependency($APRCode, 'subjects', $Version);

$_ARCHON->db->TablePrefixes .= "tblAccessions_";
?>