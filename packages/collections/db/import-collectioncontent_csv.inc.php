<?php
/**
 * Collection Content importer script
 *
 * This script takes .csv files and associates each row with a specified collection record.
 * A sample csv/excel file is provided in the archon/incoming folder, to show the necessary format.
 * For user defined fields, the label/head is set directly in the script--see lines 260 and following.
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox
 * 
 */

isset($_ARCHON) or die();

$UtilityCode = 'collectioncontent_csv';

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
        $arrEADElements = $_ARCHON->getAllEADElements();
        foreach($arrEADElements as $objEADElement)
        {
            $arrEADElementMap[$objEADElement->EADTag] = $objEADElement->ID;
        }
        
        if(!($SeriesLevelContainerID = $_ARCHON->getLevelContainerIDFromString('Series')))
        {
            echo('Series Level/Container ID not found!');
            return;
        }
        if(!($SubSeriesLevelContainerID = $_ARCHON->getLevelContainerIDFromString('Sub-Series')))
        {
            echo('Sub-Series Level/Container ID not found!');
            return;
        }
        if(!($BoxLevelContainerID = $_ARCHON->getLevelContainerIDFromString('Box')))
        {
            echo('Box Level/Container ID not found!');
            return;
        }
        if(!($FolderLevelContainerID = $_ARCHON->getLevelContainerIDFromString('Folder')))
        {
            echo('Folder Level/Container ID not found!');
            return;
        }
        if(!($ItemLevelContainerID = $_ARCHON->getLevelContainerIDFromString('Item')))
        {
            echo('Item Level/Container ID not found!');
            return;
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
                    $RecordSeriesNumber = trim(reset($arrData));
                    $CollectionID = $_ARCHON->getCollectionIDForNumber($RecordSeriesNumber);
                    
                    if(!$CollectionID)
                    {
                        echo("Collection " . $RecordSeriesNumber . " not found!<br>\n");
                        continue;
                    }
                    
                    $CurrentContentID = 0;
                    
                    unset($objCurrentContent);
                    unset($objCollectionContent);
                    
                    $SeriesLevelContainerIdentifier = next($arrData);
                    
                    if ($SeriesLevelContainerIdentifier)
                    {
                        $TempContentID = $_ARCHON->getCollectionContentIDFromData($CollectionID, $SeriesLevelContainerID, $SeriesLevelContainerIdentifier, $CurrentContentID);
                        if(!$TempContentID)
                        {
                            $objCurrentContent = new CollectionContent();
                            $objCurrentContent->CollectionID = $CollectionID;
                            
                            $objCurrentContent->LevelContainerID = $SeriesLevelContainerID;
                            $objCurrentContent->LevelContainerIdentifier = $SeriesLevelContainerIdentifier;
                            
                            $objCurrentContent->ParentID = $CurrentContentID;
                            
                            $objCurrentContent->dbStore();
                            $CurrentContentID = $objCurrentContent->ID;
                        }
                        else
                        {
                            $CurrentContentID = $TempContentID;
                        }
                    }
                    
                    $SubSeriesLevelContainerIdentifier = next($arrData);
                    
                    if ($SubSeriesLevelContainerIdentifier)
                    {
                        $TempContentID = $_ARCHON->getCollectionContentIDFromData($CollectionID, $SubSeriesLevelContainerID, $SubSeriesLevelContainerIdentifier, $CurrentContentID);
                        if(!$TempContentID)
                        {
                            $objCurrentContent = new CollectionContent();
                            $objCurrentContent->CollectionID = $CollectionID;
                            
                            $objCurrentContent->LevelContainerID = $SubSeriesLevelContainerID;
                            $objCurrentContent->LevelContainerIdentifier = $SubSeriesLevelContainerIdentifier;
                            
                            $objCurrentContent->ParentID = $CurrentContentID;
                            
                            $objCurrentContent->dbStore();
                            $CurrentContentID = $objCurrentContent->ID;
                        }
                        else
                        {
                            $CurrentContentID = $TempContentID;
                        }
                    }
                    
                    $BoxLevelContainerIdentifier = next($arrData);
                    
                    if ($BoxLevelContainerIdentifier)
                    {
                        $TempContentID = $_ARCHON->getCollectionContentIDFromData($CollectionID, $BoxLevelContainerID, $BoxLevelContainerIdentifier, $CurrentContentID);
                        if(!$TempContentID)
                        {
                            $objCurrentContent = new CollectionContent();
                            $objCurrentContent->CollectionID = $CollectionID;
                            
                            $objCurrentContent->LevelContainerID = $BoxLevelContainerID;
                            $objCurrentContent->LevelContainerIdentifier = $BoxLevelContainerIdentifier;
                            
                            $objCurrentContent->ParentID = $CurrentContentID;
                            
                            $objCurrentContent->dbStore();
                            $CurrentContentID = $objCurrentContent->ID;
                        }
                        else
                        {
                            $CurrentContentID = $TempContentID;
                        }
                    }
                    
                    $FolderLevelContainerIdentifier = next($arrData);
                    
                    if ($FolderLevelContainerIdentifier)
                    {
                        $TempContentID = $_ARCHON->getCollectionContentIDFromData($CollectionID, $FolderLevelContainerID, $FolderLevelContainerIdentifier, $CurrentContentID);
                        if(!$TempContentID)
                        {
                            $objCurrentContent = new CollectionContent();
                            $objCurrentContent->CollectionID = $CollectionID;
                            
                            $objCurrentContent->LevelContainerID = $FolderLevelContainerID;
                            $objCurrentContent->LevelContainerIdentifier = $FolderLevelContainerIdentifier;
                            
                            $objCurrentContent->ParentID = $CurrentContentID;
                            
                            $objCurrentContent->dbStore();
                            $CurrentContentID = $objCurrentContent->ID;
                        }
                        else
                        {
                            $CurrentContentID = $TempContentID;
                        }
                    }
                    
                    $ItemLevelContainerIdentifier = next($arrData);
                    
                    if ($ItemLevelContainerIdentifier)
                    {
                        $TempContentID = $_ARCHON->getCollectionContentIDFromData($CollectionID, $ItemLevelContainerID, $ItemLevelContainerIdentifier, $CurrentContentID);
                        if(!$TempContentID)
                        {
                            $objCurrentContent = new CollectionContent();
                            $objCurrentContent->CollectionID = $CollectionID;
                            
                            $objCurrentContent->LevelContainerID = $ItemLevelContainerID;
                            $objCurrentContent->LevelContainerIdentifier = $ItemLevelContainerIdentifier;
                            
                            $objCurrentContent->ParentID = $CurrentContentID;
                            
                            $objCurrentContent->dbStore();
                            $CurrentContentID = $objCurrentContent->ID;
                        }
                        else
                        {
                            $CurrentContentID = $TempContentID;
                        }
                    }
                    
                    $objCollectionContent = $objCurrentContent;
                    
                    if(!$objCollectionContent)
                    {
                        echo("Failed to create new content!<br>\n");
                        continue;
                    }
                    
                    
                    $objCollectionContent->Title = trim(next($arrData));                    
                    
                    $objCollectionContent->Date = trim(next($arrData));
                    
                    
                    $objCollectionContent->Description = trim(next($arrData));
                    
                    
                    $objCollectionContent->dbStore();
                    if(!$objCollectionContent->ID)
                    {
                        echo("Error importing!<br>\n");
                        continue;
                    }
                    
                    // Lots of User-Defined Fields
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['accessrestrict'];
                        $objUserField->Title = 'Access Restriction';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['accruals'];
                        $objUserField->Title = 'Accruals';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['acqinfo'];
                        $objUserField->Title = 'Acquisition Information';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['altformavail'];
                        $objUserField->Title = 'Alternate Format';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['appraisal'];
                        $objUserField->Title = 'Appraisal Information';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['arrangement'];
                        $objUserField->Title = 'Arrangement';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['bioghist'];
                        $objUserField->Title = 'Biographical/Historical Note';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['origination'];
                        $objUserField->Title = 'Creator';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['custodhist'];
                        $objUserField->Title = 'Custodial History';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['originalsloc'];
                        $objUserField->Title = 'Originals or Copies Note';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['odd'];
                        $objUserField->Title = 'Other Information';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['physdesc'];
                        $objUserField->Title = 'Physical Description';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['prefercite'];
                        $objUserField->Title = 'Preferred Citation';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['processinfo'];
                        $objUserField->Title = 'Processing Information';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['relatedmaterial'];
                        $objUserField->Title = 'Related Material';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['unitid'];
                        $objUserField->Title = 'UnitID';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    if (trim(next($arrData)))
                    {
                        $objUserField = new UserField();
                        $objUserField->ContentID = $objCollectionContent->ID;
                        $objUserField->Value = trim(current($arrData));
                        $objUserField->EADElementID = $arrEADElementMap['userestrict'];
                        $objUserField->Title = 'Use Restrictions';
                        if($objUserField->Value)
                        $objUserField->dbStore();
                    }
                    
                    if($objCollectionContent->ID)
                    {
                        echo("Imported {$objCollectionContent->Title}.<br>\n");
                    }
                    
                    flush();
                }
            }
        }
    }
}

?>