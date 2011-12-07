<?php
/**
 * Item template for finding aid output
 *
 * The variable:
 *
 *  $objContent
 *
 * is an instance of a CollectionContent object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the CollectionContent class definition in lib/collection.inc.php
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

if($depthChange == 1)
{
   echo("<dd><dl class='faitem'>");
}
elseif($depthChange < 0)
{
   for($i = 0; $i > $depthChange; $i--)
      echo("</dl></dd>");
}

if($readPermissions || $Content['Enabled'])
{

?>

<dt class='faitem'><a name="id<?php echo($ID); ?>"></a><?php echo($Content['String']); ?></dt>

<?php
if($Content['Description'])
{
   echo("<dd class='faitemcontent'>" . $Content['Description'] . "</dd>\n");
}

if($Content['Userfields'])
{
//      echo("<dd class='faitemcontent'>" . $_ARCHON->createStringFromUserFieldArray($objContent->UserFields, "</dd>\n<dd class='faitemcontent'>\n") . "</dd>\n");

   $string = '';
   $objLast = end($Content['Userfields']);

   foreach($Content['Userfields'] as $ID => $String)
   {
      $string .= $String;

      if($ID != $objLast['ID'])
      {
         $string .= "</dd>\n<dd class='faitemcontent'>\n";
      }
   }
   echo("<dd class='faitemcontent'>" . $string . "</dd>\n");

}

//   if(!empty($objContent->Content))
//   {
//      echo("<dd><dl class='faitem'>#CONTENT#</dl></dd>");
//   }
}
else
{
   $objInfoRestrictedPhrase = Phrase::getPhrase('informationrestricted', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strInfoRestricted = $objInfoRestrictedPhrase ? $objInfoRestrictedPhrase->getPhraseValue(ENCODE_HTML) : 'Information restricted, please contact us for additional information.';
   ?>

<dt class='faitem'><?php echo($strInfoRestricted); ?></dt>

   <?php
}


?>
