<?php
/**
 * Output file for Encoded Archival Content CPF records
 *
 * @package Archon
 * @author Chris Prom
 */

isset($_ARCHON) or die();

$filename = ($_REQUEST['output']) ? $_REQUEST['output'] : 'cpf_record';


header('Content-type: text/xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '.xml"');

$_REQUEST['templateset'] = "eac";

$_ARCHON->PublicInterface->DisableTheme = true;

include('packages/creators/pub/creator.php');

?>