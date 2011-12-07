<?php

isset($_ARCHON) or die();

if(!$_REQUEST['f'])
{
    home_ui_main();
}
else
{
    home_exec();
}


function home_ui_main()
{
    global $_ARCHON;
    

   $in_Message = CONFIG_CORE_ADMINISTRATIVE_WELCOME_MESSAGE;

    $LatestVersion = $_ARCHON->getLatestArchonVersion();
    $LatestRevision = $_ARCHON->getLatestArchonRevision();

    if(file_exists("packages/core/install/install.php"))
    {
        $in_Message = "<span style='color:red'>NOTICE: Archon will not function until packages/core/install/install.php has been deleted.</span>";
        $in_Message = $_ARCHON->processPhrase($in_Message);
    }
    elseif(version_compare($_ARCHON->Version, $LatestVersion) < 0)
    {
        $in_Message = 'A newer version of Archon has been released, visit <a href="http://www.archon.org/">www.archon.org</a> to upgrade.';
        $in_Message = $_ARCHON->processPhrase($in_Message);
    }
    elseif(version_compare($_ARCHON->Revision, $LatestRevision) < 0 && version_compare($_ARCHON->Version, $LatestVersion) == 0)
    {
        $in_Message = 'A newer revision of your version of Archon has been released, visit <a href="http://www.archon.org/">www.archon.org</a> to update.';
        $in_Message = $_ARCHON->processPhrase($in_Message);
    }
    else
    {
        foreach($_ARCHON->Packages as $ID => $objPackage)
        {
            if(!is_natural($ID) && version_compare($objPackage->Version, $_ARCHON->getLatestPackageVersionFromAPRCode($objPackage->APRCode)) < 0)
            {
                $in_Message = "A newer version of the {$objPackage->APRCode} package has been released, visit <a href='http://www.archon.org/'>www.archon.org</a> to upgrade.";
                $in_Message = $_ARCHON->processPhrase($in_Message);
                break;
            }
        }
    }

    if($in_Message == CONFIG_CORE_ADMINISTRATIVE_WELCOME_MESSAGE)
    {
        $arrPackages = $_ARCHON->getAllPackages(0, false);

        foreach($arrPackages as $ID => $objPackage)
        {
            if(file_exists("packages/$objPackage->APRCode/index.php"))
            {
                include("packages/$objPackage->APRCode/index.php");
                $objPackage->Version = $Version;

                if(version_compare($objPackage->Version, $objPackage->DBVersion) > 0)
                {
                    $in_Message = "<span style='color:red'>NOTICE: Package {$objPackage->APRCode} is out of date. Please run the upgrade script in the Package Manager.</span>";
                    $in_Message = $_ARCHON->processPhrase($in_Message);
                    break;
                }
            }
        }
    }
    
    $_ARCHON->AdministrativeInterface->getSection('browse')->disable();

    $_ARCHON->AdministrativeInterface->disableQuickSearch();

    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
    
    $generalSection->insertRow()->insertHTML("<h2>".$in_Message."</h2>")->disableHelp();
   

    $_ARCHON->AdministrativeInterface->outputInterface();
}




function home_exec()
{
    global $_ARCHON;
    
    if(true)
    {
        $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
    }

    if($_ARCHON->Error)
    {
        $msg = "<font color=red>$_ARCHON->Error</font>";
    }
    else
    {
        $msg = "Database Updated Successfully.";
    }
    
    $_ARCHON->sendMessageAndRedirect($msg, $location);
}
?>