<?php
/**
 * Creators Package
 *
 * @package Archon
 * @subpackage API
 * @author Chris Rishel
 */

class Creator extends ArchonObject {}
class CreatorRelationship extends ArchonObject {}
class CreatorSource extends ArchonObject {}



$_ARCHON->registerInclude('Creator', 'creator.inc.php');
require_once('creatortype.inc.php');
$_ARCHON->registerInclude('CreatorRelationship', 'creatorrelationship.inc.php');
require_once('creatorrelationshiptype.inc.php');
$_ARCHON->registerInclude('CreatorSource', 'creatorsource.inc.php');

$_ARCHON->registerInclude('Archon', 'core/archon.inc.php');
$_ARCHON->registerInclude('AdminSection', 'core/adminsection.inc.php');


define("SEARCH_CREATORS", nextbitmask('SEARCH'), false);
?>
