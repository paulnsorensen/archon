<?php
/**
 * Book List template
 *
 * The variable:
 *
 *  $objBook
 *
 * is an instance of a Book object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Book class definition in lib/book.inc.php
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

//toString(LINK_TOTAL, true)
echo("<div class='listitem'>" . $objBook->toString(LINK_TOTAL) . "</div>\n");

?>