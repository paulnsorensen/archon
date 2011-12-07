<?php
/**
 * Subjects Package
 *
 * @package Archon
 * @subpackage API
 * @author Chris Rishel
 */

require_once('subjecttype.inc.php');

class Subject extends ArchonObject {}
class SubjectSource extends ArchonObject {}

$_ARCHON->registerInclude('Subject', 'subject.inc.php');
$_ARCHON->registerInclude('SubjectSource', 'subjectsource.inc.php');

$_ARCHON->registerInclude('Archon', 'core/archon.inc.php');


define("SEARCH_SUBJECTS", nextbitmask('SEARCH'), false);
?>
