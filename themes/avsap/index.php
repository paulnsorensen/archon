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
<p>Welcome to the Audiovisual Self-Assessment Program (AvSAP), a project created at the University of Illinois at Urbana-Champaign with generous grant support by the <a href="http://www.imls.gov">Institute for Museum and Library Services</a>.</p>

You must first complete a web form to obtain a username and password before entering the AvSAP.  To do so, please <a href="https://illinois.edu/fb/sec/269260">click here</a>. You will receive a confirmation within two business days.

<p>Please <a href="http://www.library.illinois.edu/prescons/avsap/PDFs/AvSAP_Manual.pdf">refer to the user manual</a> for full directions on how to utilize this tool.</p>
<p>To report problems, please e-mail Jennifer Hain Teper at <a href="mailto:jhain@illinois.edu">jhain@illinois.edu<a/>.</p>


<div class="themeindexcenter">
   
 <a href="http://www.illinois.edu"><img src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/logoUIUC.gif" alt="University of Illinois I-Mark Logo"/></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <a href="http://www.imls.gov"><img src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/logoIMLS.gif" alt="IMLS Logo"/></a>


</div>

   </div>