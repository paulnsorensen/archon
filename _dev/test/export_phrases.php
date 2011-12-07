<?php

$arrLangs = array('eng','spa');
$arrAPRCodes = array('avsap','accessions','collections','core','creators','digitallibrary','subjects');

foreach($arrLangs as $lang)
{
   foreach($arrAPRCodes as $apr)
   {
      $filepath = "../packages/{$apr}/install/phrasexml/{$lang}-{$apr}.xml";

      if(file_exists($filepath))
      {
         $fh = fopen($filepath, 'w') or die("can't open file");
         fclose($fh);
         unlink($filepath);
      }

      $_REQUEST['language'] = $lang;
      $_REQUEST['aprcode'] = $apr;

//      ob_start();
      include("../packages/core/db/export-phrasexml.inc.php");
//      $file = ob_get_clean();


//      $fh = fopen($filepath, 'w') or die("can't open file");
//      fwrite($fh, $file);
//      fclose($fh);
   }
}


?>
