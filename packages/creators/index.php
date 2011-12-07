<?php
$APRCode = 'creators';
$Version = '3.21';

$_ARCHON->addPackageDependency($APRCode, 'core', $Version);

$_ARCHON->db->TablePrefixes .= "tblCreators_";
?>