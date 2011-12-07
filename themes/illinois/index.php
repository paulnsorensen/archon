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
//$_ARCHON->PublicInterface->Title.=": Holdings and Image/Document Database";

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>



<div id='themeindex' class='bground'>


<div style="width:75%; margin:1em auto; padding:1em; background-color:#F9F9FA; border:#ddd 1px solid; font-size:small" class="bground"><h2 style='margin-top:.1em'>About this Database</h2>This database contains descriptions of all materials held by the University of Illinois Archives, incuding non-current University records, faculty and alumni papers, and materials from the Sousa Archives and Center for American Music, the Advertising Council Archives, the Amercian Society of Quality, and others institutions whose archives we hold on contract for their research value.  The American Library Association Archives are described in a <a href="../ala/holdings">separate database.</a><br/><br/>Materials are arranged according to the principle of provenance (files from one creating office or person are not mixed with those from another) and they are also indexed by subject.  The Archives are available for use during the University Archives' normal business hours.  We can be contacted at <a href="mailto:illarch@uiuc.edu">illiarch@uiuc.edu</a> or (217) 333-0798.

</div>

<!--
<div style='border:red 1px solid; text-align:center; float:left; font-size:small'>
<img src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/memstad.gif" alt="Photo of Memorial Stadium, circa 1925"><br/>Memorial Stadium, circa 1925
</div>
-->






<div style="margin:1em auto; padding:1em; background-color:#F9F9FA; border:#ddd 1px solid; width:90%; margin:1em auto; clear:left; font-size:small" class="bground">
<h2 style='margin-top:.1em'>Search Tips</h2>
<ul>
  <li style='margin-bottom:4px'>The search engine looks for every term you submit, no matter how small.</li>
  <li style='margin-bottom:4px'>The search engine finds descriptions of our paper-based holdings <span class='bold'>and</span> items in our online archives.</li>
  <li style='margin-bottom:4px'>To look for phrases, use double quotes, e.g "Festival of Contemporary Arts"</li>
  <li style='margin-bottom:4px'>Record Series shortcut: e.g. '26/4/1' finds our <a href='?p=collections/controlcard&amp;id=2563'>Alumni File</a>.</li>
  <li style='margin-bottom:4px'>To search deep into box/folders lists that are not in the database, follow the <a href='?p=core/index&amp;f=pdfsearch'>Deep Search</a> link.</li>
    <li style='margin-bottom:4px'>Limiting 'Hits':
    <ul>
      <li class='back'>Use a minus sign, e.g. 'bass -fish' finds bass guitars but not bass fishing.</li>
      <li class='back'>Browse by subject, name, or campus unit.</li>
      <li class='back'>Call or <a href='http://web.library.uiuc.edu/ahx/email-ahx.asp'>email</a> the archives.  We're here to help!</li>
    </ul>
  </li>
</ul>
</div>


</div>