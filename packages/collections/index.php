<?php
$APRCode = 'collections';
$Version = '3.21';

$_ARCHON->addPackageDependency($APRCode, 'creators', $Version);
$_ARCHON->addPackageDependency($APRCode, 'subjects', $Version);

$_ARCHON->db->TablePrefixes .= "tblCollections_";
?>