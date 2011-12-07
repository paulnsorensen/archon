<?php
isset($_ARCHON) or die();

	echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>
	

<div id='themeindex' class='bground'>

<div style="width:40%; margin:1em auto; padding:1em; background-color:#F9F9FA; border:#ddd 1px solid; font-size:small" class="bground">
<h2 style='margin-top:.1em'><label for="q">PDF/Deep Search</label></h2>
<div style='text-align:center'>				
    <form name="form1" action="http://www.google.com/search" class="search">
    <input type="hidden" name="hq" value="inurl:www.library.illinois.edu/archives/uasfa" />
    <input type="hidden" name="safe" value="off" />
    <input type="hidden" name="filter" value="0" />
	<input id="q" type="text" size="25" name="q" class="searchinput" style='border:solid 1px #ddd'>
	<input type="submit" value="Search" class="button" style='font-size:1em'></form>
</div>
</div>					
<div style="text-align:center; width:60%;margin:1em auto; padding:1em; background-color:#F9F9FA; border:#ddd 1px solid; font-size:small" class="bground">This page uses Google to search over 17,000 pages of box/folder listings that exist only in PDF format.  These lists are <span style='color:red;font-weight:bold'>NOT</span> directly searchable through the search box in the top navigation bar. If you do not find what you are looking for, please <a href='http://www.library.uiuc.edu/archives/email-ahx.php'>contact the Archives</a> for additional assistance.</p>

</div>


