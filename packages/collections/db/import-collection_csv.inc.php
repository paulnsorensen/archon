<?php
/**
 * Collection importer script.
 *
 * This script takes .csv files in a defined format and creates a new collection record for each row in the database.
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

$UtilityCode = 'collection_csv';

$_ARCHON->addDatabaseImportUtility(PACKAGE_COLLECTIONS, $UtilityCode, '3.21', array('csv'), true);

if($_REQUEST['f'] == 'import-' . $UtilityCode)
{
    if(!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
    {
        die("Permission Denied.");
    }
    
    @set_time_limit(0);
    
    ob_implicit_flush();
    
    $arrFiles = $_ARCHON->getAllIncomingFiles();
    
    if(!empty($arrFiles))
    {
        $arrRepositories = $_ARCHON->getAllRepositories();
        foreach($arrRepositories as $objRepository)
        {
            $arrRepositoryMap[encoding_strtolower($objRepository->Name)] = $objRepository->ID;
        }
        
        $arrMaterialTypes = $_ARCHON->getAllMaterialTypes();
        foreach($arrMaterialTypes as $objMaterialType)
        {
            $arrMaterialTypesMap[encoding_strtolower($objMaterialType->MaterialType)] = $objMaterialType->ID;
        }
        
        $arrExtentUnits = $_ARCHON->getAllExtentUnits();
        foreach($arrExtentUnits as $objExtentUnit)
        {
            $arrExtentUnitsMap[encoding_strtolower($objExtentUnit->ExtentUnit)] = $objExtentUnit->ID;
        }
        
        $arrTemplateList = $_ARCHON->getAllTemplates();
        foreach($arrTemplateList as $template)
        {
            $arrTemplatesMap[encoding_strtolower($template)] = $template;
        }
        
        $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Personal Name');
        
        $arrDescriptiveRules = $_ARCHON->getAllDescriptiveRules();
        foreach($arrDescriptiveRules as $objDescriptiveRules)
        {
            $arrDescriptiveRulesMap[encoding_strtolower($objDescriptiveRules->DescriptiveRulesCode)] = $objDescriptiveRules->ID;
        }
        
        $arrLanguages = $_ARCHON->getAllLanguages();
        foreach($arrLanguages as $objLanguage)
        {
            $arrLanguagesMap[encoding_strtolower($objLanguage->LanguageShort)] = $objLanguage->ID;
        }
        
        foreach($arrFiles as $Filename => $strCSV)
        {
            echo("Parsing file $Filename...<br /><br />\n\n");
            
            // Remove byte order mark if it exists.
            $strCSV = ltrim($strCSV, "\xEF\xBB\xBF");
            
            $arrAllData = getCSVFromString($strCSV);
            // ignore first line?
            foreach($arrAllData as $arrData)
            {
                if(!empty($arrData))
                {
                    $objCollection = new Collection();
                    
                    $objCollection->Title = reset($arrData);
                    
                    $RepositoryName = next($arrData);
                    $objCollection->RepositoryID = $arrRepositoryMap[encoding_strtolower($RepositoryName)] ? $arrRepositoryMap[encoding_strtolower($RepositoryName)] : CONFIG_CORE_DEFAULT_REPOSITORY;
                    if($objCollection->RepositoryID == CONFIG_CORE_DEFAULT_REPOSITORY && encoding_strtolower($RepositoryName) != encoding_strtolower($arrRepositories[CONFIG_CORE_DEFAULT_REPOSITORY]->Name) && $RepositoryName)
                    {
                        echo("Repository $RepositoryName not recognized!<br />\n");
                    }
                    
                    $RecordGroupNumber = trim(next($arrData));
                    if($RecordGroupNumber)
                    {
                        $objCollection->ClassificationID = $_ARCHON->getClassificationIDForNumber($RecordGroupNumber);
                        if(!$objCollection->ClassificationID)
                        {
                            echo("Classification $RecordGroupNumber not found!<br />\n");
                        }
                    }
                    else
                    {
                        $objCollection->ClassificationID = 0;
                    }
                    
                    $objCollection->CollectionIdentifier = next($arrData);
                    
                    $SortTitle = next($arrData);
                    $objCollection->SortTitle = $SortTitle ? $SortTitle : $objCollection->Title;
                    
                    $objCollection->NormalDateBegin = next($arrData);
                    $objCollection->NormalDateEnd = next($arrData);
                    
                    $objCollection->InclusiveDates = next($arrData);
                    $objCollection->PredominantDates = next($arrData);
                    
                    $MaterialType = next($arrData);
                    $objCollection->MaterialTypeID = $arrMaterialTypesMap[encoding_strtolower($MaterialType)] ? $arrMaterialTypesMap[encoding_strtolower($MaterialType)] : 0;
                    if(!$objCollection->MaterialTypeID && $MaterialType)
                    {
                        echo("Material Type $MaterialType not found!<br />\n");
                    }
                    
                    $objCollection->Extent = next($arrData);
                    $ExtentUnit = next($arrData);
                    $objCollection->ExtentUnitID = $arrExtentUnitsMap[encoding_strtolower($ExtentUnit)] ? $arrExtentUnitsMap[encoding_strtolower($ExtentUnit)] : 0;
                    if(!$objCollection->ExtentUnitID && $ExtentUnit)
                    {
                        echo("Extent Unit $ExtentUnit not found!<br />\n");
                    }
                    
                    $objCollection->FindingAidAuthor = next($arrData);
                    
                    $objCollection->TemplateSet = next($arrData);
                    if(!$arrTemplatesMap[encoding_strtolower($objCollection->TemplateSet)] && $objCollection->TemplateSet)
                    {
                        echo("Template set $objCollection->TemplateSet not found!<br />\n");
                        $objCollection->TemplateSet = '';
                    }
                    else
                    {
                        $objCollection->TemplateSet = $arrTemplatesMap[encoding_strtolower($objCollection->TemplateSet)];
                    }
                    
                    $CreatorName = next($arrData);
                    $CreatorID = $_ARCHON->getCreatorIDFromString($CreatorName);
                    if(!$CreatorID && $CreatorName)
                    {
                        $objCreator = new Creator();
                        $objCreator->Name = $CreatorName;
                        $objCreator->CreatorTypeID = $CreatorTypeID;
                        $objCreator->RepositoryID = $objCollection->RepositoryID;
                        $objCreator->dbStore();
                        $CreatorID = $objCreator->ID;
                    }
                    
                    $objCollection->Scope = next($arrData);
                    $objCollection->Arrangement = next($arrData);
                    $objCollection->AltExtentStatement = next($arrData);
                    
                    $objCollection->AccessRestrictions = next($arrData);
                    $objCollection->UseRestrictions = next($arrData);
                    $objCollection->PhysicalAccess = next($arrData);
                    $objCollection->TechnicalAccess = next($arrData);
                    
                    $objCollection->AcquisitionDateMonth = next($arrData);
                    $objCollection->AcquisitionDateDay = next($arrData);
                    $objCollection->AcquisitionDateYear = next($arrData);
                    $objCollection->AcquisitionSource = next($arrData);
                    $objCollection->AcquisitionMethod = next($arrData);
                    $objCollection->AppraisalInfo = next($arrData);
                    $objCollection->AccrualInfo = next($arrData);
                    $objCollection->CustodialHistory = next($arrData);
                    
                    $objCollection->RelatedMaterials = next($arrData);
                    $objCollection->RelatedMaterialsURL = next($arrData);
                    $objCollection->RelatedPublications = next($arrData);
                    $objCollection->SeparatedMaterials = next($arrData);
                    $objCollection->OrigCopiesNote = next($arrData);
                    $objCollection->OrigCopiesURL = next($arrData);
                    $objCollection->PreferredCitation = next($arrData);
                    
                    $objCollection->OtherURL = next($arrData);
                    $objCollection->OtherNote = next($arrData);
                    
                    $DescriptiveRulesCode = next($arrData);
                    $objCollection->DescriptiveRulesID = $arrDescriptiveRulesMap[encoding_strtolower($DescriptiveRulesCode)] ? $arrDescriptiveRulesMap[encoding_strtolower($DescriptiveRulesCode)] : 0;
                    if(!$objCollection->DescriptiveRulesID && $DescriptiveRulesCode)
                    {
                        echo("Descriptive Rules $DescriptiveRulesCode not found!<br />\n");
                    }
                    
                    $objCollection->ProcessingInfo = next($arrData);
                    $objCollection->RevisionHistory = next($arrData);
                    $objCollection->PublicationDateMonth = next($arrData);
                    $objCollection->PublicationDateDay = next($arrData);
                    $objCollection->PublicationDateYear = next($arrData);
                    $objCollection->PublicationNote = next($arrData);
                    
                    $LanguageShort = next($arrData);
                    $objCollection->FindingLanguageID = $arrLanguagesMap[encoding_strtolower($LanguageShort)] ? $arrLanguagesMap[encoding_strtolower($LanguageShort)] : 0;                 
                    if(!$objCollection->FindingLanguageID && $LanguageShort)
                    {
                        echo("Language $LanguageShort not found!<br />\n");
                    }
                    
                    $objCollection->dbStoreCollection();
                    if(!$objCollection->ID)
                    {
                        echo("Error storing collection $objCollection->Title: {$_ARCHON->clearError()}<br />\n");
                        continue;
                    }
                    
                    if($CreatorID)
                    {
                        if(!$objCollection->dbRelateCreator($CreatorID))
                        {
                            echo("Error relating creator $CreatorName to collection: {$_ARCHON->clearError()}<br />\n");
                        }
                    }
                    
                    if($objCollection->ID)
                    {
                        echo("Imported {$objCollection->Title}.<br /><br />\n\n");
                    }
                    
                    flush();
                }
            }
        }
        
        echo("All files imported!");
    }
}

?>