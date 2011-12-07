<?php
isset($_ARCHON) or die();

require_once("header.inc.php");

if(!$_ARCHON->Error)
{
    if(file_exists('themes/' . $_ARCHON->PublicInterface->Theme . '/index.php'))
    {
        $cwd = getcwd();

        chdir('themes/' . $_ARCHON->PublicInterface->Theme);

        require_once('index.php');

        chdir($cwd);
    }
}

require_once("footer.inc.php");
?>
