<?php
/**
 * Appointments Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Prom
 */

isset($_ARCHON) or die();

appointments_ui_initialize();

// Determine what to do based upon user input
function appointments_ui_initialize()
{
    if(!$_REQUEST['f'])
    {
        appointments_ui_main();
    }
    
      else if($_REQUEST['f'] == 'search')
    {
        appointments_ui_search();
    }
    
    else
    {
        appointments_ui_exec(); // No interface needed, include an execution file.
    }
}



// appointments_ui_main()
//   - purpose: Creates the primary user interface
//              for the appointment Manager.
function appointments_ui_main()
{
    global $_ARCHON;

    $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');

    $objAllMaterialsPhrase = Phrase::getPhrase('allmaterials', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
    $strAllMaterials = $objAllMaterialsPhrase ? $objAllMaterialsPhrase->getPhraseValue(ENCODE_HTML) : 'Return/Retrieve All Materials';

    $objReturnPhrase = Phrase::getPhrase('widget_appointments_return', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
    $strReturn = $objReturnPhrase ? $objReturnPhrase->getPhraseValue(ENCODE_HTML) : 'Return';
    $objRetrievePhrase = Phrase::getPhrase('widget_appointments_retrieve', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
    $strRetrieve = $objRetrievePhrase ? $objRetrievePhrase->getPhraseValue(ENCODE_HTML) : 'Retrieve';

    $objLocationsPhrase = Phrase::getPhrase('widget_appointments_locations', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
    $strLocations = $objLocationsPhrase ? $objLocationsPhrase->getPhraseValue(ENCODE_JAVASCRIPTTHENHTML) : 'Locations for collection';
    $objNoLocationsPhrase = Phrase::getPhrase('widget_appointments_nolocations', PACKAGE_COLLECTIONS, 0, PHRASETYPE_ADMIN);
    $strNoLocations = $objNoLocationsPhrase ? $objNoLocationsPhrase->getPhraseValue(ENCODE_JAVASCRIPTTHENHTML) : 'No location entries found for collection.';
    
    $_ARCHON->AdministrativeInterface->setClass('ResearchAppointment');


    $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');


    $generalSection->insertRow('arrivaltime')->insertTimestampField('ArrivalTime', 50, 66)->required();    //convert to standard time format
    $generalSection->insertRow('departuretime')->insertTimestampField('DepartureTime', 50, 66);
    $generalSection->insertRow('researcherid')->insertSelect('ResearcherID', 'getAllPublicUsers')->required();
    $generalSection->insertRow('appointmentpurposeid')->insertSelect('AppointmentPurposeID', 'getAllResearchAppointmentPurposes');
    $generalSection->insertRow('topic')->insertTextField('Topic', 66, 66);
    $generalSection->insertRow('researchercomments')->insertTextArea('ResearcherComments', 5, 50);
    $generalSection->insertRow('archivistcomments')->insertTextArea('ArchivistComments', 5, 50);
    
    $SubmitTimeValue = $_ARCHON->AdministrativeInterface->Object->SubmitTime ? date(CONFIG_CORE_DATE_FORMAT, $_ARCHON->AdministrativeInterface->Object->SubmitTime) : '';
    
    $generalSection->insertRow('submittime')->insertInformation($SubmitTimeValue);
    
    
   
    $objAppointment = $_ARCHON->AdministrativeInterface->Object;
    
    if($objAppointment->ID)
    {
         ob_start();
         
         $jsIDs = js_array($_ARCHON->AdministrativeInterface->IDs);
?>
<script type="text/javascript">
/* <![CDATA[ */
function admin_ui_returnmaterials(collectionid, appointmentmaterialsid)
{
    $('#fInput').val('return');
    $('#CollectionIDInput').val(collectionid);
    $('#AppointmentMaterialsIDInput').val(appointmentmaterialsid);

    $('#mainform').ajaxSubmit(function (xml) {
        admin_ui_displayresponse(xml);

        $('#materialscontainer').load('index.php #materialscontainer>*', {
            p: '<?php echo($_REQUEST['p']); ?>',
            'IDs[]': <?php echo($jsIDs); ?>,
            adminoverridesection: '<?php echo('materials'); ?>'
        });
    });

    $('#fInput').val('store');
}

function admin_ui_retrievematerials(collectionid, appointmentmaterialsid)
{
    $('#fInput').val('retrieve');
    $('#CollectionIDInput').val(collectionid);
    $('#AppointmentMaterialsIDInput').val(appointmentmaterialsid);

    $('#mainform').ajaxSubmit(function (xml) {
        admin_ui_displayresponse(xml);

        $('#materialscontainer').load('index.php #materialscontainer>*', {
            p: '<?php echo($_REQUEST['p']); ?>',
            'IDs[]': <?php echo($jsIDs); ?>,
            adminoverridesection: '<?php echo('materials'); ?>'
        });
    });

    $('#fInput').val('store');
}
/* ]]> */
</script>
<input type="hidden" name="CollectionID" id="CollectionIDInput" value="" />
<input type="hidden" name="AppointmentMaterialsID" id="AppointmentMaterialsIDInput" value="" />
<?php

         echo("<div id='affectallmaterialscontainer'>\n");
         echo($strAllMaterials);
         echo("<a href='#' onclick='admin_ui_returnmaterials(0, 0);'><img src='{$_ARCHON->AdministrativeInterface->ImagePath}/return.gif' title='$strReturn' alt='$strReturn' /></a><a href='#' onclick='admin_ui_retrievematerials(0, 0);'><img src='{$_ARCHON->AdministrativeInterface->ImagePath}/retrieve.gif' title='$strRetrieve' alt='$strRetrieve' /></a><br />");
         echo("</div>\n");

         $objAppointment->dbLoadMaterials();
         $objAppointment->dbLoadResearcher();

         $PrevClassificationID = 0;
         $PrevCollectionID = 0;

         echo("<div id='materialscontainer'>\n");
         echo("<div id='innermaterialscontainer'>\n");

         echo("Materials:<br />\n");

         foreach($objAppointment->Materials->Collections as $CollectionID => $arrContent)
        {
            foreach($arrContent->Content as $ContentID => $objMaterials)
            {
                if(CONFIG_COLLECTIONS_SEARCH_BY_CLASSIFICATION && $objMaterials->Collection->ClassificationID && $objMaterials->Collection->ClassificationID != $PrevClassificationID)
                {
                    if($PrevClassificationID)
                    {
                        echo("</dl></dd></dl>\n");
                    }

                    echo($objMaterials->Collection->Classification->toString(LINK_NONE, false, true, false, true, $_ARCHON->AdministrativeInterface->Delimiter) . "<br />\n");
                    echo("<dl class='appointmentmanagermaterialslist'>\n");

                    $ClassificationID = $objMaterials->Collection->ClassificationID;
                    $PrevCollectionID = 0;
                }

                if($CollectionID != $PrevCollectionID)
                {
                    if($PrevCollectionID)
                    {
                        echo("</dl></dd>\n");
                    }

                    $objMaterials->Collection->dbLoadLocationEntries();

                    if(!empty($objMaterials->Collection->LocationEntries))
                    {
                        $CollectionLinkAnchor = '<a href="#" onclick="alert(\'' . $strLocations . ':\n\n';
                        foreach($objMaterials->Collection->LocationEntries as $objLocationEntry)
                        {
                            $_ARCHON->AdministrativeInterface->EscapeXML = false;
                            $CollectionLinkAnchor .= encode($objLocationEntry->toString(), ENCODE_JAVASCRIPTTHENHTML);
                            $_ARCHON->AdministrativeInterface->EscapeXML = true;
                            $CollectionLinkAnchor .= '\n';
                        }

                        $CollectionLinkAnchor .= '\'); return false;">';
                    }
                    else
                    {
                        $CollectionLinkAnchor = '<a href="#" onclick="alert(\'' . $strNoLocations . '\'); return false;">';
                    }

                    echo("<dd>" . $CollectionLinkAnchor . $objMaterials->Collection->toString() . "</a>");

                    if($arrContent->ReturnTime == -1 || ($arrContent->RetrievalTime && !$arrContent->ReturnTime))
                    {
                        echo("<a href='#' onclick='admin_ui_returnmaterials($CollectionID, 0);'><img src='{$_ARCHON->AdministrativeInterface->ImagePath}/return.gif' title='$strReturn' alt='$strReturn' /></a>");
                    }

                    if($arrContent->RetrievalTime <= 0 || ($arrContent->RetrievalTime && $arrContent->ReturnTime))
                    {
                        echo("<a href='#' onclick='admin_ui_retrievematerials($CollectionID, 0);'><img src='{$_ARCHON->AdministrativeInterface->ImagePath}/retrieve.gif' title='$strRetrieve' alt='$strRetrieve' /></a>");
                    }

                    echo("<dl class='appointmentmanagermaterialslist'>\n");
                }
                     echo("<dd>");


                if($objMaterials->CollectionContent)
                {
                    echo($objMaterials->CollectionContent->toString(LINK_NONE, true, true, true, true, $_ARCHON->AdministrativeInterface->Delimiter));

                    if(!$objMaterials->ReturnTime && $objMaterials->RetrievalTime)
                    {
                        echo("<a href='#' onclick='admin_ui_returnmaterials(0, {$objMaterials->ID});'><img src='{$_ARCHON->AdministrativeInterface->ImagePath}/return.gif' title='$strReturn' alt='$strReturn' /></a>");
                    }
                    else
                    {
                        echo("<a href='#' onclick='admin_ui_retrievematerials(0, {$objMaterials->ID});'><img src='{$_ARCHON->AdministrativeInterface->ImagePath}/retrieve.gif' title='$strRetrieve' alt='$strRetrieve' /></a>");
                    }

                }
                      echo("</dd>\n");


                $PrevCollectionID = $CollectionID;
                $PrevClassificationID = $ClassificationID;
            }
        }

        echo("</dl></dd></dl><br /><br />\n");

        echo("</div>\n");
         echo("</div>\n");

         $materialsHTML = ob_get_clean();
    }

    $materialsSection = $_ARCHON->AdministrativeInterface->insertSection('materials', 'custom');
    $materialsSection->setCustomArguments($materialsHTML);
    
    $_ARCHON->AdministrativeInterface->setNameField('ArrivalTime');
 
    $_ARCHON->AdministrativeInterface->outputInterface();
}




function appointments_ui_search()
{
    global $_ARCHON;
    
    $_ARCHON->AdministrativeInterface->searchResults('searchResearchAppointments');
}


function appointments_ui_exec()
{
    global $_ARCHON;

    $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

    if($_REQUEST['f'] == 'store')
    {
        if(!$_REQUEST['arrivaltime'] = strtotime($_REQUEST['arrivaltime']))
        {
            $_ARCHON->declareError("Could not store appointment: Unable to parse ArrivalTime.");
        }
        else if($_REQUEST['departuretime'] && (!$_REQUEST['departuretime'] = strtotime($_REQUEST['departuretime'])))
        {
            $_ARCHON->declareError("Could not store appointment: Unable to parse DepartureTime.");
        }
        else
        {
            foreach($arrIDs as &$ID)
            {
                $objAppointment = New ResearchAppointment($_REQUEST);
                $objAppointment->ID = $ID;
                $objAppointment->dbStore();
                $ID = $objAppointment->ID;
            }
        }
    }
    else if($_REQUEST['f'] == 'delete')
    {
        foreach($arrIDs as $ID)
        {
            $objAppointment = New ResearchAppointment($ID);
            $objAppointment->dbDelete();
        }
    }
    else if($_REQUEST['f'] == 'retrieve')
    {
        if($_REQUEST['appointmentmaterialsid'])
        {
            $objAppointmentMaterials = New ResearchAppointmentMaterials($_REQUEST['appointmentmaterialsid']);
            $objAppointmentMaterials->dbRetrieve();
        }
        else if($_REQUEST['id'])
        {
            $CollectionID = $_REQUEST['collectionid'] ? $_REQUEST['collectionid'] : 0;

            $objAppointment = New ResearchAppointment($_REQUEST['id']);
            $objAppointment->dbRetrieveMaterials($CollectionID);
        }
    }
    else if($_REQUEST['f'] == 'return')
    {
        if($_REQUEST['appointmentmaterialsid'])
        {
            $objAppointmentMaterials = New ResearchAppointmentMaterials($_REQUEST['appointmentmaterialsid']);
            $objAppointmentMaterials->dbReturn();
        }
        else if($_REQUEST['id'])
        {
            $CollectionID = $_REQUEST['collectionid'] ? $_REQUEST['collectionid'] : 0;

            $objAppointment = New ResearchAppointment($_REQUEST['id']);
            $objAppointment->dbReturnMaterials($CollectionID);
        }
    }
    else
    {
        $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
    }

    if($_ARCHON->Error)
    {
        $msg = $_ARCHON->Error;
    }
    else
    {
        $msg = "Appointment Database Updated Successfully.";
    }

    $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error);
}
