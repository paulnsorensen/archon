<?php
/**
 * Header file for all output documents
 *
 * @package Archon
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

if($_ARCHON->AdministrativeInterface)
{
    if(file_exists("adminthemes/{$_ARCHON->AdministrativeInterface->Theme}/header.inc.php"))
    {
        $cwd = getcwd();

        chdir("adminthemes/{$_ARCHON->AdministrativeInterface->Theme}/");

        require_once('header.inc.php');

        chdir($cwd);
    }
}
else
{
    $_ARCHON->QueryString = $_REQUEST['q'];
    //$_ARCHON->QueryStringURL = urlencode(preg_replace("/(.*?)([\s]-[\d\w]*)(.*?)/iu", "\$1\$3", $_ARCHON->QueryString));
    $_ARCHON->QueryStringURL = urlencode($_ARCHON->QueryString);

    if(!$_ARCHON->PublicInterface->DisableTheme)
    {
        $arrWords = $_ARCHON->createSearchWordArray($_ARCHON->QueryString);

        if(file_exists('themes/' . $_ARCHON->PublicInterface->Theme))
        {
            $cwd = getcwd();

            chdir('themes/' . $_ARCHON->PublicInterface->Theme);

            require_once('header.inc.php');

            chdir($cwd);
        }

        // For search highlighting
        ob_start();
        ob_implicit_flush();
    }
}
