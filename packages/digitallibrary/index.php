<?php
$APRCode = 'digitallibrary';
$Version = '3.21';

$_ARCHON->addPackageDependency($APRCode, 'core', $Version);

$_ARCHON->addPackageEnhancement($APRCode, 'collections', $Version);
$_ARCHON->addPackageEnhancement($APRCode, 'creators', $Version);
$_ARCHON->addPackageEnhancement($APRCode, 'subjects', $Version);

$_ARCHON->db->TablePrefixes .= "tblDigitalLibrary_";
?>