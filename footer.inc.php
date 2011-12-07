<?php
/**
 * Footer for all output documents
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

if($_ARCHON->AdministrativeInterface)
{
   if(file_exists("adminthemes/{$_ARCHON->AdministrativeInterface->Theme}/footer.inc.php"))
   {
      $cwd = getcwd();

      chdir("adminthemes/{$_ARCHON->AdministrativeInterface->Theme}/");

      require_once('footer.inc.php');

      chdir($cwd);
   }
}
else
{
   if($_ARCHON->PublicInterface->DisableTheme)
   {
      return;
   }

   $output = '';
   if(ob_get_level() > $_ARCHON->DefaultOBLevel)
   {
      $output = ob_get_clean();

      $arrWords = $_ARCHON->createSearchWordArray($_ARCHON->QueryString);

      $count = 0;
      if(!empty($arrWords))
      {
         foreach($arrWords as $word)
         {
            if($word && $word{0} != "-")
            {
               $output = preg_replace("/(\A|\>)([^\<]*[^\w^=^\<^\+^\/]|)(" . preg_quote($word, '/') . ")(|[^\w^=^\>\+][^\>]*)(\<|\z)/ui", "$1$2<span class='highlight$count bold'>$3</span>$4$5", $output);
               $count++;
            }
         }
      }
   }
   echo($output);

   if(file_exists('themes/' . $_ARCHON->PublicInterface->Theme))
   {
      $cwd = getcwd();

      chdir('themes/' . $_ARCHON->PublicInterface->Theme);

      require_once('footer.inc.php');

      chdir($cwd);
   }


   $objCopyrightPhrase = Phrase::getPhrase('copyright', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strCopyright = $objCopyrightPhrase ? $objCopyrightPhrase->getPhraseValue() : 'Copyright &copy;$1 <a href="http://www.uiuc.edu/">The University of Illinois at Urbana-Champaign</a>';
   $strCopyright = str_replace('$1', $_ARCHON->CopyrightYear, $strCopyright);
   ?>
<div id="archoninfo">
   Page Generated in: <?php echo(round(microtime(true) - $_ARCHON->StartTime, 3)); ?> seconds (using <?php echo($_ARCHON->QueryLog->QueryCount); ?> queries).<br/>

      <?php
      if(function_exists('memory_get_usage') && function_exists('memory_get_peak_usage'))
      {
         ?>
   Using <?php echo(round(memory_get_usage()/1048576,2)); ?>MB of memory. (Peak of <?php echo(round(memory_get_peak_usage()/1048576,2)); ?>MB.)<br/>
         <?php
      }
      ?>
   <br/>
   Powered by <a href='<?php echo($_ARCHON->ArchonURL); ?>'>Archon</a> Version <?php echo($_ARCHON->Version); if($_ARCHON->Revision){echo(" rev-".$_ARCHON->Revision);} ?><br/>
      <?php echo($strCopyright); ?>
   <br/>
      <?php

      ?>


</div>
</body>
</html>
   <?php
}
$_ARCHON->Security->Session->close();
$_ARCHON->mdb2->disconnect();
?>