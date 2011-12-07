<?php
/**
 * Accession importer script.
 *
 * This script takes .csv files in a defined format and creates a new accession record for each row in the database.
 * A sample csv/excel file is provided in the archon/incoming folder, to show the necessary format.
 *
 * If a creator is defined in the CSV file, the script checks to see if an authority entry exists for the creator,
 * then links to the authority entry.  If no authority entry exists, it makes a new creator authority,
 * then links it to the record.
 *
 * this script does not currently support the import and linking of controlled subject or genre terms.
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

$currentRepositoryID = $_REQUEST['currentrepositoryid'];

$UtilityCode = 'accession_csv';

$_ARCHON->addDatabaseImportUtility(PACKAGE_ACCESSIONS, $UtilityCode, '3.21', array('csv'), true);

if($_REQUEST['f'] == 'import-' . $UtilityCode)
{
    if(!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
    {
        die("Permission Denied.");
    }

    if($currentRepositoryID <= 0)
   {
      die("Repository ID required.");
   }

    @set_time_limit(0);

    ob_implicit_flush();

    $arrFiles = $_ARCHON->getAllIncomingFiles();

    if(!empty($arrFiles))
    {
        $arrLocations = $_ARCHON->getAllLocations();
        foreach($arrLocations as $objLocation)
        {
            $arrLocationsMap[encoding_strtolower($objLocation->Location)] = $objLocation->ID;
        }

        $arrMaterialTypes = $_ARCHON->getAllMaterialTypes();
        foreach($arrMaterialTypes as $objMaterialType)
        {
            $arrMaterialTypesMap[encoding_strtolower($objMaterialType->MaterialType)] = $objMaterialType->ID;
        }

        $arrProcessingPriorities = $_ARCHON->getAllProcessingPriorities();
        foreach($arrProcessingPriorities as $objProcessingPriority)
        {
            $arrProcessingPrioritiesMap[encoding_strtolower($objProcessingPriority->ProcessingPriority)] = $objProcessingPriority->ID;
        }
        
        $arrExtentUnits = $_ARCHON->getAllExtentUnits();
        foreach($arrExtentUnits as $objExtentUnit)
        {
            $arrExtentUnitsMap[encoding_strtolower($objExtentUnit->ExtentUnit)] = $objExtentUnit->ID;
        }

        $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Personal Name');

        $arrLanguages = $_ARCHON->getAllLanguages();
        foreach($arrLanguages as $objLanguage)
        {
            $arrLanguagesMap[encoding_strtolower($objLanguage->LanguageShort)] = $objLanguage->ID;
        }

        foreach($arrFiles as $Filename => $strCSV)
        {
            echo("Parsing file $Filename...<br><br>\n\n");

            // Remove byte order mark if it exists.
            $strCSV = ltrim($strCSV, "\xEF\xBB\xBF");

            $arrAllData = getCSVFromString($strCSV);
            // ignore first line?
            foreach($arrAllData as $arrData)
            {
                if(!empty($arrData))
                {
                    $objAccession = new Accession();

                    $objAccession->AccessionDateMonth = reset($arrData);
                    $objAccession->AccessionDateDay = next($arrData);
                    $objAccession->AccessionDateYear = next($arrData);

                    $objAccession->Title = next($arrData);

                    $objAccession->Identifier = next($arrData);

                    $objAccession->InclusiveDates = next($arrData);

                    $objAccession->ReceivedExtent = next($arrData);
                    $ReceivedExtentUnit = next($arrData);
                    $objAccession->ReceivedExtentUnitID = $arrExtentUnitsMap[encoding_strtolower($ReceivedExtentUnit)] ? $arrExtentUnitsMap[encoding_strtolower($ReceivedExtentUnit)] : 0;
                    if(!$objAccession->ReceivedExtentUnitID && $ReceivedExtentUnit)
                    {
                        echo("Extent Unit $ReceivedExtentUnit not found!<br>\n");
                    }

                    $objAccession->UnprocessedExtent = next($arrData);
                    $UnprocessedExtentUnit = next($arrData);
                    $objAccession->UnprocessedExtentUnitID = $arrExtentUnitsMap[encoding_strtolower($UnprocessedExtentUnit)] ? $arrExtentUnitsMap[encoding_strtolower($UnprocessedExtentUnit)] : 0;
                    if(!$objAccession->UnprocessedExtentUnitID && $UnprocessedExtentUnit)
                    {
                        echo("Extent Unit $UnprocessedExtentUnit not found!<br>\n");
                    }

                    $MaterialType = next($arrData);
                    $objAccession->MaterialTypeID = $arrMaterialTypesMap[encoding_strtolower($MaterialType)] ? $arrMaterialTypesMap[encoding_strtolower($MaterialType)] : 0;
                    if(!$objAccession->MaterialTypeID && $MaterialType)
                    {
                        echo("Material Type $MaterialType not found!<br>\n");
                    }

                    $ProcessingPriority = next($arrData);
                    $objAccession->ProcessingPriorityID = $arrProcessingPrioritiesMap[encoding_strtolower($ProcessingPriority)] ? $arrProcessingPrioritiesMap[encoding_strtolower($ProcessingPriority)] : 0;
                    if(!$objAccession->ProcessingPriorityID && $ProcessingPriority)
                    {
                        echo("Processing Priority $ProcessingPriority not found!<br>\n");
                    }

                    $objAccession->ExpectedCompletionDateMonth = next($arrData);
                    $objAccession->ExpectedCompletionDateDay = next($arrData);
                    $objAccession->ExpectedCompletionDateYear = next($arrData);



                    // Try for collection first, and go back to classification if fails
                    $RecordSeriesNumber = next($arrData);
                    $objCollectionEntry = NULL;
                    if($RecordSeriesNumber)
                    {
                        $objCollectionEntry = New AccessionCollectionEntry();

                        $objCollectionEntry->CollectionID = $_ARCHON->getCollectionIDForNumber($RecordSeriesNumber);
                        if($objCollectionEntry->CollectionID)
                        {
                            // Assign ClassificationID based on CollectionID
                            $objCollectionEntry->Collection = New Collection($objCollectionEntry->CollectionID);
                            $objCollectionEntry->Collection->dbLoad();
                            $objCollectionEntry->ClassificationID = $objCollectionEntry->Collection->ClassificationID;
                        }
                        else
                        {
                            $objCollectionEntry->ClassificationID = $_ARCHON->getClassificationIDForNumber($RecordSeriesNumber);

                            if(!$objCollectionEntry->ClassificationID)
                            {
                                $objCollectionEntry = NULL;
                                echo("Collection/Classification " . $RecordSeriesNumber . " not found!<br>\n");
                            }
                        }
                    }

                    $LocationContent = next($arrData);
                    $objLocationEntry = NULL;
                    if ($LocationContent)
                    {
                        $objLocationEntry = New AccessionLocationEntry();

                        $Location = next($arrData);

                        $objLocationEntry->LocationID = $arrLocationsMap[encoding_strtolower($Location)] ? $arrLocationsMap[encoding_strtolower($Location)] : 0;

                        if($objLocationEntry->LocationID != 0)
                        {
                            $objLocationEntry->Content = $LocationContent;
                            $objLocationEntry->RangeValue = next($arrData);
                            $objLocationEntry->Section = next($arrData);
                            $objLocationEntry->Shelf = next($arrData);
                            $objLocationEntry->Extent = next($arrData);
                            $LocationEntryExtentUnit = next($arrData);
                            $objLocationEntry->ExtentUnitID = $arrExtentUnitsMap[encoding_strtolower($LocationEntryExtentUnit)] ? $arrExtentUnitsMap[encoding_strtolower($LocationEntryExtentUnit)] : 0;
                            if(!$objLocationEntry->ExtentUnitID && $LocationEntryExtentUnit)
                            {
                                echo("Extent Unit $LocationEntryExtentUnit not found!<br>\n");
                            }
                        }
                        else
                        {
                            echo("Location $Location not found!<br>\n");
                            $objLocationEntry = NULL;
                            for ($i = 0; $i<5; $i++)
                            {
                                next($arrData);
                            }

                        }
                    }
                    else //no location entry is specified
                    {
                        for ($i = 0; $i<6; $i++)
                            {
                                next($arrData);
                            }
                    }

                    $CreatorName = next($arrData);
                    $CreatorID = $_ARCHON->getCreatorIDFromString($CreatorName);
                    if(!$CreatorID && $CreatorName)
                    {
                        $objCreator = new Creator();
                        $objCreator->Name = $CreatorName;
                        $objCreator->CreatorTypeID = $CreatorTypeID;
                        $objCreator->RepositoryID = $currentRepositoryID;
                        $objCreator->dbStore();
                        $CreatorID = $objCreator->ID;
                    }

                    $objAccession->Donor = next($arrData);
                    $objAccession->DonorContactInformation = next($arrData);
                    $objAccession->DonorNotes = next($arrData);


                    $objAccession->PhysicalDescription = next($arrData);
                    $objAccession->ScopeContent = next($arrData);
                    $objAccession->Comments = next($arrData);


                    $objAccession->dbStore();
                    if(!$objAccession->ID)
                    {
                        echo("Error storing accession $objAccession->Title: {$_ARCHON->clearError()}<br>\n");
                        continue;
                    }


                    if($objLocationEntry)
                    {
                        $objLocationEntry->AccessionID = $objAccession->ID;

                        if(!$objLocationEntry->dbStore())
                        {
                            echo("Error relating LocationEntry to accession: {$_ARCHON->clearError()}<br>\n");
                        }
                    }


                    if($objCollectionEntry)
                    {
                        $objCollectionEntry->AccessionID = $objAccession->ID;

                        if(!$objCollectionEntry->dbStore())
                        {
                            echo("Error relating Collection/Classification to accession: {$_ARCHON->clearError()}<br>\n");
                        }
                    }

                    if($CreatorID)
                    {
                        if(!$objAccession->dbRelateCreator($CreatorID))
                        {
                            echo("Error relating creator $CreatorName to accession: {$_ARCHON->clearError()}<br>\n");
                        }
                    }



                    if($objAccession->ID)
                    {
                        echo("Imported {$objAccession->Title}.<br><br>\n\n");
                    }

                    flush();
                }
            }
        }

        echo("All files imported!");
    }
}

?>