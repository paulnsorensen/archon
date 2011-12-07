<?php
/**
 * Creator list template
 *
 * The variable:
 *
 *  $objCreator
 *
 * is an instance of a Collection object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Collection class definition in lib/collection.inc.php
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

echo("<div class='listitem'>" . $objCreator->toString(LINK_TOTAL) . "</div>\n");

//$validString = htmlspecialchars($objCreator->toString(LINK_TOTAL));

//echo($validString . "<br/>\n");
?>