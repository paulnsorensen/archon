<?php
/**
 * Main page for default template
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();
echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>

<div id='themeindex' class='bground'>
<dl>
  <dt class='index'>Default Behaviors</dt>
  <dd class='index'>
    <ul>
      <li>The search engine looks for records containing every term you submit.</li>
    </ul>
  </dd>
  <dt class='index'>Search By Phrase</dt>
  <dd class='index'>
    <ul>
      <li>Use double quotes around your search query.  (e.g "Festival of Contemporary Arts")</li>
    </ul>
  </dd>
  <dt class='index'>Narrow Your Search Results</dt>
  <dd class='index'>
    <ul>
      <li>Use a minus sign before a term you want to omit from your results.  (e.g. 'bass -fish' finds bass guitars but not bass fishing.)</li>
      <li>Browse by collection title, subject, name, or classification.</li>
    </ul>
  </dd>
</dl>
</div>