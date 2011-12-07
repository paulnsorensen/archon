<?php
/**
 * Main page for default template
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

if($_REQUEST['f'] == 'pdfsearch')
{
    require("pdfsearch.inc.php");
    return;
}
//$_ARCHON->PublicInterface->Title.=": Holdings Database";

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>



<div id='themeindex' class='bground'>


<div style="width:75%; margin:1em auto; padding:1em; background-color:#F9F9FA; border:#ddd 1px solid; font-size:small" class="bground"><h2 style='margin-top:.1em'>About this Database</h2>This database contains descriptions of all materials held by the Illinois History and Lincoln Collections at the University of Illinois Library.</a></div>

<!--
<div style='border:red 1px solid; text-align:center; float:left; font-size:small'>
<img src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/memstad.gif" alt="Photo of Memorial Stadium, circa 1925"><br/>Memorial Stadium, circa 1925
</div>
-->


</div>