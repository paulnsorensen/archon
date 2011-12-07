<?php
/**
 * Collection List template
 *
 * The variable:
 *
 *  $objCollection
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

echo("<div class='listitem'>");
if($objCollection->ClassificationID)
{
    $objCollection->Classification = New Classification($objCollection->ClassificationID);
    //$objCollection->Classification->dbLoad(true);
    echo($_ARCHON->Error);
    echo($objCollection->Classification->toString(LINK_NONE, true, false, true, false) . '/');
}
echo($objCollection->toString(LINK_TOTAL, true, false) . "</div>\n");

?>