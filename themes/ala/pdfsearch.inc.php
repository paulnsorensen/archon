<?php
isset($_ARCHON) or die();
?>


<div id='themeindex' class='bground'>
   <h1><label for="q">PDF/Deep Search</label></h1>
   <div id='pdfinput'>
      <form name="form1" action="http://www.google.com/search" class="search">
         <input type="hidden" name="hq" value="inurl:www.library.illinois.edu/archives/alasfa" />
         <input type="hidden" name="safe" value="off" />
         <input type="hidden" name="filter" value="0" />
         <input id="q" type="text" size="25" name="q" class="searchinput" style='border:solid 1px #ddd'>
         <input type="submit" value="Search" class="button" style='font-size:1em'></form>
   </div>

   <div><br/><span class="bold">Note:</span> This page uses Google to search ALL box/folder listings that exist only in PDF format.  These lists are <span style='color:red;font-weight:bold'>NOT</span> directly searchable through the search box in the left-hand navigation bar.<br/><br/>If you do not find what you are looking for, please <a href='http://www.library.uiuc.edu/archives/email-ahx.php'>contact the Archives</a> for additional assistance.</div>

</div>


