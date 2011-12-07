<?php
/**
 * Footer file for all Administrative scripts.
 *
 * @package Archon
 * @author Kyle Fox, Paul Sorensen
 */
isset($_ARCHON) or die();

if(!$_ARCHON->AdministrativeInterface->Header->NoControls)
{
   $objCopyrightPhrase = Phrase::getPhrase('copyright', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strCopyright = $objCopyrightPhrase ? $objCopyrightPhrase->getPhraseValue(ENCODE_BBCODE) : 'Copyright &copy;$1 <a href="http://www.uiuc.edu/">The University of Illinois at Urbana-Champaign</a>';
   $strCopyright = str_replace('$1', $_ARCHON->CopyrightYear, $strCopyright);
?>
   </div>
   </div>
   </div>
   <div id="footer">
   <?php
   echo($_ARCHON->AdministrativeInterface->FooterHTML);
   ?>
   <p>
      Powered by <a href='<?php echo($_ARCHON->ArchonURL); ?>'>Archon</a> Version <?php
   echo($_ARCHON->Version);
   if($_ARCHON->Revision)
   {
      echo(" rev-" . $_ARCHON->Revision);
   } ?><br/>
      <?php echo($strCopyright); ?>
   </p>
   <p>
      <br />
      Page Generated in: <?php echo(round(microtime(true) - $_ARCHON->StartTime, 3)); ?> seconds (using <?php echo($_ARCHON->QueryLog->QueryCount); ?> queries).<br/>
      <?php
      if(function_exists('memory_get_usage') && function_exists('memory_get_peak_usage'))
      {
      ?>
         Using <?php echo(round(memory_get_usage() / 1048576, 2)); ?>MB of memory. (Peak of <?php echo(round(memory_get_peak_usage() / 1048576, 2)); ?>MB.)<br/>
      <?php
      }
      ?>
   </p>
</div>
</div>
</div>
</div>
</div>
<?php
      if(defined('DEBUG') && DEBUG)
      {
         echo($_ARCHON->Error . "<br /><br />");
         echo($_ARCHON->TestingError . "<br /><br />");
//      $query = "SELECT missing_phrases.PhraseName AS pn FROM missing_phrases NATURAL JOIN accessed_phrases";
//      $result = $_ARCHON->mdb2->query($query);
//      $arrPhrases = array();
//      while($row = $result->fetchRow())
//      {
//         $arrPhrases[] = $row['pn'];
//      }
//      $result->free();
//
//      $query = "DELETE FROM missing_phrases WHERE PhraseName IN ('".implode("','", $arrPhrases)."')";
//
//      $_ARCHON->mdb2->exec($query);
      }
   }
?>


<?php echo($_ARCHON->getJavascriptTags('jquery.form')); ?>
<?php echo($_ARCHON->getJavascriptTags('jquery.cookie')); ?>
<?php echo($_ARCHON->getJavascriptTags('jquery.metadata')); ?>
<?php echo($_ARCHON->getJavascriptTags('jquery.jgrowl.min')); ?>
   <script type="text/javascript" src="packages/core/js/dynatree/jquery.dynatree.min.js"></script>
   <script src="packages/core/js/dynatree/contextmenu/jquery.contextMenu-custom.js" type="text/javascript"></script>   
<?php echo($_ARCHON->getJavascriptTags('jquery.archonwidgets')); ?>

   <!--[if lt IE 7]>
         <script type="text/javascript" src="packages/core/js/jquery.pngfix.js"></script>
         <script type="text/javascript">
             $(function(){
                 $('img[@src$=png]').pngfix();
             });
         </script>
   <![endif]-->
   <script type="text/javascript" src="packages/core/js/ckeditor/ckeditor.js"></script>

<?php echo($_ARCHON->getJavascriptTags('ck-archon')); ?>

<?php echo($_ARCHON->getJavascriptTags('archon')); ?>
<?php echo($_ARCHON->getJavascriptTags('admin')); ?>

</body>
</html>
