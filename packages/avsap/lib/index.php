<?php
/**
 * AVSAP Package
 *
 * @package Archon
 * @subpackage API
 * @author Paul Sorensen
 */

class AVSAPInstitution extends ArchonObject {}
class AVSAPStorageFacility extends ArchonObject {}
class AVSAPAssessment extends ArchonObject {}
class AVSAPFilmAssessment extends ArchonObject {}
class AVSAPAudioCassetteAssessment extends ArchonObject {}
class AVSAPVideoCassetteAssessment extends ArchonObject {}
class AVSAPOpenReelVideoAssessment extends ArchonObject {}
class AVSAPOpenReelAudioAssessment extends ArchonObject {}
class AVSAPOpticalMediaAssessment extends ArchonObject {}
class AVSAPWireAudioAssessment extends ArchonObject {}
class AVSAPGroovedDiscAssessment extends ArchonObject {}
class AVSAPGroovedCylinderAssessment extends ArchonObject {}
class AVSAPScore extends ArchonObject {}

$_ARCHON->registerInclude('AVSAPInstitution', 'avsapinstitution.inc.php');
$_ARCHON->registerInclude('AVSAPStorageFacility', 'avsapstoragefacility.inc.php');
$_ARCHON->registerInclude('AVSAPAssessment', 'avsapassessment.inc.php');
$_ARCHON->registerInclude('AVSAPFilmAssessment', 'avsapfilmassessment.inc.php');
$_ARCHON->registerInclude('AVSAPAudioCassetteAssessment', 'avsapaudiocassetteassessment.inc.php');
$_ARCHON->registerInclude('AVSAPVideoCassetteAssessment', 'avsapvideocassetteassessment.inc.php');
$_ARCHON->registerInclude('AVSAPOpenReelVideoAssessment', 'avsapopenreelvideoassessment.inc.php');
$_ARCHON->registerInclude('AVSAPOpenReelAudioAssessment', 'avsapopenreelaudioassessment.inc.php');
$_ARCHON->registerInclude('AVSAPOpticalMediaAssessment', 'avsapopticalmediaassessment.inc.php');
$_ARCHON->registerInclude('AVSAPWireAudioAssessment', 'avsapwireaudioassessment.inc.php');
$_ARCHON->registerInclude('AVSAPGroovedDiscAssessment', 'avsapgrooveddiscassessment.inc.php');
$_ARCHON->registerInclude('AVSAPGroovedCylinderAssessment', 'avsapgroovedcylinderassessment.inc.php');
$_ARCHON->registerInclude('AVSAPScore', 'avsapscore.inc.php');

$_ARCHON->registerInclude('Archon', 'core/archon.inc.php');

define("SEARCH_AVSAPINSTITUTIONS", nextbitmask('SEARCH'), false);
define("SEARCH_AVSAPSTORAGEFACILITIES", nextbitmask('SEARCH'), false);
define("SEARCH_AVSAPASSESSMENTS", nextbitmask('SEARCH'), false);

define('AVSAP_CLASS_NONE', 0, false);
define('AVSAP_CLASS_INSTITUTION', nextbitmask('AVSAP_CLASS_NONE'), false);
define('AVSAP_CLASS_STORAGEFACILITY', nextbitmask('AVSAP_CLASS_NONE'), false);
define('AVSAP_CLASS_ASSESSMENT', nextbitmask('AVSAP_CLASS_NONE'), false);

define('AVSAP_GENERAL', 0, false);
define('AVSAP_FILM', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_ACASSETTE', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_VCASSETTE', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_VOPENREEL', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_AOPENREEL', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_OPTICAL', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_WIREAUDIO', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_GROOVEDDISC', nextbitmask('AVSAP_GENERAL'), false);
define('AVSAP_GROOVEDCYL', nextbitmask('AVSAP_GENERAL'), false);


define('AVSAPVAL_BAD', '0.0', false);
define('AVSAPVAL_FAIRLYBAD', '0.33', false);
define('AVSAPVAL_FAIR', '0.5', false);
define('AVSAPVAL_FAIRLYGOOD', '0.67', false);
define('AVSAPVAL_GOOD', '1.0', false);
define('AVSAPVAL_UNSURE', '0.4', false);


define('AVSAPTEMP_VERYLOW', '0.2', false);
define('AVSAPTEMP_LOW', '0.6', false);
define('AVSAPTEMP_MEDIUMLOW', '1.0', false);
define('AVSAPTEMP_MEDIUMHIGH','0.8' , false);
define('AVSAPTEMP_HIGH', '0.4', false);
define('AVSAPTEMP_VERYHIGH', '0.0', false);


/* look to phase these out */
//define('AVSAPQUANT_NONE', 0, false);
//define('AVSAPQUANT_SOME', nextbitmask('AVSAPQUANT'), false);
//define('AVSAPQUANT_MANY', nextbitmask('AVSAPQUANT'), false);

//define('AVSAPLEVEL_LOW', '0.0', false);
//define('AVSAPLEVEL_MEDIUM', '0.5', false);
//define('AVSAPLEVEL_HIGH', '1.0', false);

//define('AVSAPANS_DONTKNOW', '0.4', false);
//define('AVSAPANS_NO', '0.0', false);
//define('AVSAPANS_YES', '1.0', false);

?>
