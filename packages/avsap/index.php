<?php
$APRCode = 'avsap';
$Version = '3.21';

$_ARCHON->addPackageDependency($APRCode, 'collections', $Version);

$_ARCHON->db->TablePrefixes .= "tblAVSAP_";
?>
