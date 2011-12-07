<?php
/**
 * Subject list template
 *
 * The variable:
 *
 *  $objSubject
 *
 * is an instance of a Subject object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Subject class definition in lib/subject.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

echo("<div class='listitem'>" . $objSubject->toString(LINK_TOTAL, true) . "</div>\n");
?>