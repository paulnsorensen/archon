<?php
/**
 * DigitalContent list template
 *
 * The variable:
 *
 *  $objDigitalContent
 *
 * is an instance of a DigitalContent object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the DigitalContent class definition in lib/digitallibrary.inc.php
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

echo("<div class='listitem'>" . $objDigitalContent->toString(LINK_TOTAL));
if($objDigitalContent->Date)
{
echo(", ". $objDigitalContent->getString('Date'));
}
echo("</div>\n");
?>