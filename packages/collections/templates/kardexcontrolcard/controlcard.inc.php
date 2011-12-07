<?php
/**
 * Control Card template
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

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head><title><?php echo(strip_tags($_ARCHON->PublicInterface->Title)); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<table cellpadding="5" cellspacing="0" style="border: black dashed 1px; width: 198mm; height: 128mm">
  <tr>
    <td style="border-bottom: solid; width: 143mm; height: 10mm; border-right: solid;" valign="top">
      <div style="float:right;"><span style="font-size: xx-small">University of Illinois Archives Control Card</span></div>
      <span class='bold' style="font: small, 'arial narrow';">Record Group</span><br/>
      <span style="font: small, 'times';"><?php if($objCollection->Classification->Parent) { echo($objCollection->Classification->Parent->toString(LINK_NONE, false, true)); } ?></span>
    </td>
    <td colspan="2" rowspan="2" valign="top" style="border-bottom: solid; width: 55mm;">
      <span class='bold' style="font: small, 'arial narrow';">Date Received</span> <span style="font: small, 'times';"><?php echo('&nbsp;' . ($objCollection->getString('AcquisitionDate') ? $objCollection->getString('AcquisitionDateMonth') . '/' . $objCollection->getString('AcquisitionDateDay') . '/' . $objCollection->getString('AcquisitionDateYear') . ';&nbsp;' : '') . $objCollection->getString('AccrualInfo')) ?></span>
    </td>
  </tr>
  <tr>
    <td style="border-bottom: solid; border-right: solid; width: 143mm; height: 10mm;" valign="top">
      <span class='bold' style="font: small, 'arial narrow';">Sub-group</span><br/>
      <span style="font: small, 'times';"><?php if($objCollection->Classification) { echo($objCollection->Classification->toString(LINK_NONE, false, true)); } ?></span>
    </td>
  </tr>
  <tr>
    <td style="border-bottom: solid; border-right: solid; width: 143mm; height: 10mm;" valign="top">
      <span class='bold' style="font: small, 'arial narrow';">Arranged</span><br/>
      <span style="font: small, 'times';"><?php echo($objCollection->getString('Arrangement')); ?></span>
    </td>
    <td style="border-bottom: solid; border-right: solid; width: 29mm; height: 10mm;" valign="top">
      <span class='bold' style="font: small, 'arial narrow';">Volume</span><br/>
      <span style="font: small, 'times';"><?php echo($objCollection->getString('Extent')); ?></span>
    </td>
    <td style="border-bottom: solid; width: 26mm; height: 10mm;" valign="top">
      <span class='bold' style="font: small, 'arial narrow';">SFA</span><br/>
      <span style="font: small, 'times';"><?php echo(preg_replace("/[^\d]*?/u", "", $objCollection->getString('OtherNote'))); ?></span>
    </td>
  </tr>
  <tr>
    <td colspan="3" valign="top">
      <span class='bold' style="font: small, 'arial narrow';">Description:</span> <span style="font: small, 'times';"><?php echo($objCollection->getString('Scope')); ?></span>
    </td>
  </tr>
  <tr>
    <td colspan="3" valign="bottom">
      <span style="font: small, 'times';"><?php echo(($objCollection->Classification ? $objCollection->Classification->toString(LINK_NONE, true, false, true, false) . '/' : '') . $objCollection->getString('CollectionIdentifier') . ' ' . $objCollection->toString()); ?></span>
    </td>
  </tr>
</table>
</body>
</html>