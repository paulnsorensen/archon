<?php

if($_REQUEST['action'] == 'import')
{
   echo($_REQUEST['archon_id']);
}
elseif($_REQUEST['action'] == 'archive')
{
   
   $arrIDs = explode(',', $_REQUEST['archon_ids']);

   foreach ($arrIDs as $id)
   {
      echo($id ."<br/>");
   }
}

?>
