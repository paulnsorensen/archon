<?php
isset($_ARCHON) or die();

if(!$_REQUEST['f'])
{
    $_ARCHON->declareError("Could not load JavaScriptLibrary: No library filename specified.");
}

if($_ARCHON->Error)
{
    die();
}

header('Content-Type: application/x-javascript, charset=UTF-8');

$Filename = preg_replace('/[^\w^\d^-^_]/u', '', encoding_strtolower($_REQUEST['f']));

$arrPackages = $_ARCHON->getAllPackages();

foreach($arrPackages as $objPackage)
{
    if(file_exists("packages/$objPackage->APRCode/js/$Filename.js"))
    {
        echo(file_get_contents("packages/$objPackage->APRCode/js/$Filename.js") . NEWLINE);
    }
}
?>