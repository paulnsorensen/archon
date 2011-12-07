<?php
/**
 * Collections Package
 *
 * @package Archon
 * @subpackage API
 * @author Chris Rishel
 */

require_once('descriptiverules.inc.php');
require_once('eadelement.inc.php');
require_once('findingaidcache.inc.php');

class Book extends ArchonObject {}
class Classification extends ArchonObject {}
class Collection extends ArchonObject {}
class CollectionContent extends ArchonObject {}
class ExtentUnit extends ArchonObject {}
class LevelContainer extends ArchonObject {}
class Location extends ArchonObject {}
class LocationEntry extends ArchonObject {}
class MaterialType extends ArchonObject {}
class ResearchAppointment extends ArchonObject {}
class ResearchAppointmentField extends ArchonObject {}
class ResearchAppointmentMaterials extends ArchonObject {}
class ResearchAppointmentPurpose extends ArchonObject {}
class ResearchCart extends ArchonObject {}
class ResearcherType extends ArchonObject {}
class UserField extends ArchonObject {}

$_ARCHON->registerInclude('Book', 'book.inc.php');
$_ARCHON->registerInclude('Classification', 'classification.inc.php');
$_ARCHON->registerInclude('Collection', 'collection.inc.php');
$_ARCHON->registerInclude('CollectionContent', 'collectioncontent.inc.php');
$_ARCHON->registerInclude('ExtentUnit', 'extentunit.inc.php');
$_ARCHON->registerInclude('LevelContainer', 'levelcontainer.inc.php');
$_ARCHON->registerInclude('Location', 'location.inc.php');
$_ARCHON->registerInclude('LocationEntry', 'locationentry.inc.php');
$_ARCHON->registerInclude('MaterialType', 'materialtype.inc.php');
$_ARCHON->registerInclude('ResearchAppointment', 'researchappointment.inc.php');
$_ARCHON->registerInclude('ResearchAppointmentField', 'researchappointmentfield.inc.php');
$_ARCHON->registerInclude('ResearchAppointmentMaterials', 'researchappointmentmaterials.inc.php');
$_ARCHON->registerInclude('ResearchAppointmentPurpose', 'researchappointmentpurpose.inc.php');
$_ARCHON->registerInclude('ResearchCart', 'researchcart.inc.php');
$_ARCHON->registerInclude('ResearcherType', 'researchertype.inc.php');
$_ARCHON->registerInclude('UserField', 'userfield.inc.php');

$_ARCHON->registerInclude('Archon', 'core/archon.inc.php');
$_ARCHON->registerInclude('User', 'core/user.inc.php');

$_ARCHON->registerInclude('Creator', 'creators/creator.inc.php');
$_ARCHON->registerInclude('Subject', 'subjects/subject.inc.php');

define('LOADCONTENT_ALL', 0, false);
define('LOADCONTENT_NONE', -1, false);

define('SEARCH_ENABLED_COLLECTIONS', nextbitmask('SEARCH'), false);
define('SEARCH_DISABLED_COLLECTIONS', nextbitmask('SEARCH'), false);
define('SEARCH_ENABLED_COLLECTIONCONTENT', nextbitmask('SEARCH'), false);
define('SEARCH_DISABLED_COLLECTIONCONTENT', nextbitmask('SEARCH'), false);
define('SEARCH_USERFIELDS', nextbitmask('SEARCH'), false);

define('SEARCH_COLLECTIONS', SEARCH_ENABLED_COLLECTIONS | SEARCH_DISABLED_COLLECTIONS, false);
define('SEARCH_COLLECTIONCONTENT', SEARCH_ENABLED_COLLECTIONCONTENT | SEARCH_DISABLED_COLLECTIONCONTENT, false);
?>