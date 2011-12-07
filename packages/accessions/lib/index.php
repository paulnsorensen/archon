<?php
/**
 * Accessions Package
 *
 * @package Archon
 * @subpackage API
 * @author Chris Rishel
 */

class Accession extends ArchonObject {}
class AccessionCollectionEntry extends ArchonObject {}
class AccessionLocationEntry extends ArchonObject {}
class ProcessingPriority extends ArchonObject {}

$_ARCHON->registerInclude('Accession', 'accession.inc.php');
$_ARCHON->registerInclude('AccessionCollectionEntry', 'accessioncollectionentry.inc.php');
$_ARCHON->registerInclude('AccessionLocationEntry', 'accessionlocationentry.inc.php');
$_ARCHON->registerInclude('ProcessingPriority', 'processingpriority.inc.php');

$_ARCHON->registerInclude('Creator', 'creators/creator.inc.php');
$_ARCHON->registerInclude('Archon', 'core/archon.inc.php');

/*
require_once('accession.inc.php');
require_once('accessioncollectionentry.inc.php');
require_once('accessionlocationentry.inc.php');
require_once('processingpriority.inc.php');

require_once('core/archon.inc.php');*/

define('SEARCH_ENABLED_ACCESSIONS', nextbitmask('SEARCH'), false);
define('SEARCH_DISABLED_ACCESSIONS', nextbitmask('SEARCH'), false);

define('SEARCH_ACCESSIONS', SEARCH_ENABLED_ACCESSIONS | SEARCH_DISABLED_ACCESSIONS, false);

?>
