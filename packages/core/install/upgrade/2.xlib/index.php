<?php

class LiveSession2x extends ArchonObject {}
class Security2x extends ArchonObject {}
//class Session2x extends ArchonObject {}
class User2x extends ArchonObject {}
class Usergroup2x extends ArchonObject {}

$_ARCHON->registerInclude('LiveSession2x', 'livesession.inc.php');
$_ARCHON->registerInclude('Security2x', 'security.inc.php');
//$_ARCHON->registerInclude('Session2x', 'session.inc.php');
$_ARCHON->registerInclude('User2x', 'user.inc.php');
$_ARCHON->registerInclude('Usergroup2x', 'usergroup.inc.php');

?>