<?php
/**
 * MARC Importer script
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
require_once("packages/collections/lib/php-marc/php-marc.php");

global $_ARCHON;

$currentRepositoryID = $_REQUEST['currentrepositoryid'];

isset($_ARCHON) or die();

$UtilityCode = 'marc';

$_ARCHON->addDatabaseImportUtility(PACKAGE_COLLECTIONS, $UtilityCode, '3.21', array('mrc', 'dat', 'txt'), true);

if($_REQUEST['f'] == 'import-' . $UtilityCode)
{
    if(!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
    {
        die("Permission Denied.");
    }

    if(!is_natural($currentRepositoryID))
    {
       die("Repository ID required.");
    }
    
    ob_implicit_flush();
    @set_time_limit(0);

    $StartTime = microtime(true);

    $arrFiles = $_ARCHON->getAllIncomingFiles();

    $arrMARCMap['099']['a'] = 'CollectionIdentifier';
    //$arrMARCMap['245']['a'] = 'Title';
    $arrMARCMap['245']['f'] = 'InclusiveDates';
    $arrMARCMap['245']['g'] = 'PredominantDates';
    $arrMARCMap['300']['a'] = 'Extent';
    $arrMARCMap['300']['b'] = 'AltExtentStatement';
    //$arrMARCMap['351']['a'] = 'Arrangement';
    //$arrMARCMap['351']['b'] = 'Arrangement';
    $arrMARCMap['500']['a'] = 'OtherNote';
    $arrMARCMap['506']['a'] = 'AccessRestrictions';
    $arrMARCMap['520']['a'] = 'Scope';
    $arrMARCMap['541']['a'] = 'AcquisitionSource';
    $arrMARCMap['541']['c'] = 'AcquisitionMethod';
    $arrMARCMap['555']['a'] = 'PublicationNote';
    $arrMARCMap['561']['a'] = 'CustodialHistory';
    //$arrMARCMap['856']['u'] = 'OtherURL';

    $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();

    if(!empty($arrSubjectTypes))
    {
        foreach($arrSubjectTypes as $objSubjectType)
        {
            $arrSubjectTypeMap[$objSubjectType->EncodingAnalog] = $objSubjectType->ID;
        }
    }
    
    $arrSubjectTypeMap['v'] = $_ARCHON->getSubjectTypeIDFromString('Genre/Form of Material');
    $arrSubjectTypeMap['x'] = $_ARCHON->getSubjectTypeIDFromString('Topical Term');
    $arrSubjectTypeMap['y'] = $_ARCHON->getSubjectTypeIDFromString('Date');
    $arrSubjectTypeMap['z'] = $_ARCHON->getSubjectTypeIDFromString('Geographic Name');

    $PersonalCreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Personal Name');
    $FamilyCreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Family Name');
    $CorporateCreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Corporate Name');

    if(!empty($arrFiles))
    {
        foreach($arrFiles as $Filename => $strMARC)
        {
            echo("Parsing file $Filename...");

            $arrRecords = array();

            $arrLines = explode(NEWLINE, $strMARC);

            if(!empty($arrLines))
            {
                foreach($arrLines as $Line)
                {
                    $arrRecords = array_merge($arrRecords, explode("\x1D", $Line));
                }
            }

            echo("Found " . count($arrRecords) . " possible MARC records.<br><br>\n");

            if(!empty($arrRecords))
            {
                foreach($arrRecords as $strRecord)
                {
                    if($strRecord)
                    {
                        $objMARCFile = New PHP_MARC_File(NULL);
                        $objMARCRecord = $objMARCFile->decode($strRecord);

                        if($objMARCRecord)
                        {
                            $objCollection = New Collection();

                            // Enabled
                            $objCollection->Enabled = 1;

                            // RepositoryID
                            $objCollection->RepositoryID = $currentRepositoryID;

                            if((!$objMARCRecord->field('245') || !$objMARCRecord->field('245')->subfield('a') || !$objMARCRecord->field('245')->subfield('a')->value())
                                && (!$objMARCRecord->field('245') || !$objMARCRecord->field('245')->subfield('b') || !$objMARCRecord->field('245')->subfield('b')->value())
                                && (!$objMARCRecord->field('245') || !$objMARCRecord->field('245')->subfield('k') || !$objMARCRecord->field('245')->subfield('k')->value()))
                            {
                                echo("Could not import collection: Collection has no title (field 245 subfield _a, _b, and _k are not defined).<br>\n");
                                continue;
                            }

                            foreach($arrMARCMap as $field => $arrSubFields)
                            {
                                if($objMARCRecord->field($field))
                                {
                                    foreach($arrSubFields as $subfield => $ArchonField)
                                    {
                                        if($objMARCRecord->field($field)->subfield($subfield))
                                        {
                                            $objCollection->$ArchonField = import_marc_depunctuate($objMARCRecord->field($field)->subfield($subfield)->value());
                                        }
                                    }
                                }
                            }
                            
                            $arrTitleSubFields = array('a', 'b', 'k');
                            foreach($arrTitleSubFields as $subfield)
                            {
                                if($objMARCRecord->field('245') && $objMARCRecord->field('245')->subfield($subfield) && $objMARCRecord->field('245')->subfield($subfield)->value())
                                {
                                    $objCollection->Title = $objCollection->Title ? "$objCollection->Title: {$objMARCRecord->field('245')->subfield($subfield)->value()}" : $objMARCRecord->field('245')->subfield($subfield)->value();
                                }
                            }

                            $objCollection->SortTitle = encoding_substr($objCollection->Title, 0, 50);

                            $objCollection->NormalDateBegin = is_natural(encoding_substr($objCollection->InclusiveDates, 0, 4)) ? encoding_substr($objCollection->InclusiveDates, 0, 4) : NULL;
                            $objCollection->NormalDateEnd = is_natural(encoding_substr($objCollection->InclusiveDates, encoding_strlen($objCollection->InclusiveDates) - 4, encoding_strlen($objCollection->InclusiveDates))) ? encoding_substr($objCollection->InclusiveDates, encoding_strlen($objCollection->InclusiveDates) - 4, encoding_strlen($objCollection->InclusiveDates)) : NULL;

                            if($objCollection->Extent && !is_numeric($objCollection->Extent))
                            {
                                $objCollection->Extent = NULL;
                            }

                            if($objMARCRecord->field('300') && $objMARCRecord->field('300')->subfield('f'))
                            {
                                $objCollection->ExtentUnitID = $_ARCHON->getExtentUnitIDFromString(import_marc_depunctuate($objMARCRecord->field('300')->subfield('f')->value()));
                            }
                            
                            if($objMARCRecord->field('351') && $objMARCRecord->field('351')->subfield('a') && $objMARCRecord->field('351')->subfield('b'))
                            {
                                $objCollection->Arrangement = "Organization: " . import_marc_depunctuate($objMARCRecord->field('351')->subfield('a')->value()) . "\n\n" .
                                                              "Arrangement: " . import_marc_depunctuate($objMARCRecord->field('351')->subfield('b')->value());
                            }
                            elseif($objMARCRecord->field('351') && $objMARCRecord->field('351')->subfield('a'))
                            {
                                $objCollection->Arrangement = import_marc_depunctuate($objMARCRecord->field('351')->subfield('a')->value());
                            }
                            elseif($objMARCRecord->field('351') && $objMARCRecord->field('351')->subfield('b'))
                            {
                                $objCollection->Arrangement = import_marc_depunctuate($objMARCRecord->field('351')->subfield('b')->value());
                            }
                            
                            if($objMARCRecord->field('520') && $objMARCRecord->field('520')->subfield('b'))
                            {
                                $objCollection->Scope = $objCollection->Scope ? $objCollection->Scope . "\n\n" . import_marc_depunctuate($objMARCRecord->field('520')->subfield('b')->value()) : import_marc_depunctuate($objMARCRecord->field('520')->subfield('b')->value());
                            }
                            
                            if($objMARCRecord->field('541') && $objMARCRecord->field('541')->subfield('d'))
                            {
                                $timestamp = strtotime($objMARCRecord->field('541')->subfield('d')->value());
                                $acquisitiondate = getdate($timestamp);
                                
                                $objCollection->AcquisitionDateDay = $acquisitiondate['mday'];
                                $objCollection->AcquisitionDateMonth = $acquisitiondate['mon'];
                                $objCollection->AcquisitionDateYear = $acquisitiondate['year'];
                            }
                            
                            if($objMARCRecord->field('545') && $objMARCRecord->field('545')->subfield('a'))
                            {
                                $objCollection->Scope = $objCollection->Scope ? $objCollection->Scope . "\n\nBiographical Note: " . import_marc_depunctuate($objMARCRecord->field('545')->subfield('a')->value()) : "Biographical Note: " . import_marc_depunctuate($objMARCRecord->field('545')->subfield('a')->value());
                                
                                if($objMARCRecord->field('545')->subfield('b'))
                                {
                                    $objCollection->Scope .= "\n" . import_marc_depunctuate($objMARCRecord->field('545')->subfield('b')->value());
                                }
                            }
                            
                            // For extra URLs
                            $arrFields = $objMARCRecord->fields('856');
                            if(!empty($arrFields))
                            {
                                foreach($arrFields as $objField)
                                {
                                    if($objField->subfield('u'))
                                    {
                                        $URL = import_marc_depunctuate($objField->subfield('u')->value());
                                        
                                        if(!$objCollection->OtherURL)
                                        {
                                            $objCollection->OtherURL = $URL;
                                        }
                                        
                                        if($objField->subfield('y') && $objField->subfield('y')->value())
                                        {
                                            $Label = $objField->subfield('y')->value();
                                        }
                                        else
                                        {
                                            $Label = "Related URL";
                                        }
                                        
                                        $objCollection->OtherNote = $objCollection->OtherNote ? $objCollection->OtherNote . "\n\n$Label: " . $URL : "$Label: " . $URL;
                                    }
                                }
                            }
                            
                            

                            if(!$objCollection->dbStoreCollection())
                            {
                                echo($_ARCHON->clearError() . "<br>\n");
                                continue;
                            }

                            $arrSubjectFields = array('600', '610', '630', '650', '651', '655', '656', '657');

                            $LcshSubjectSourceID = $_ARCHON->getSubjectSourceIDFromString('Library of Congress Subject Heading');
                            $LcshSubjectSourceID = $LcshSubjectSourceID ? $LcshSubjectSourceID : 1;
                            
                            foreach($arrSubjectFields as $field)
                            {
                                $arrFields = $objMARCRecord->fields($field);

                                if(!empty($arrFields))
                                {
                                    foreach($arrFields as $objField)
                                    {
                                        if($objField->subfield('a'))
                                        {
                                            $Subject = import_marc_depunctuate($objField->subfield('a')->value());

                                            if($Subject)
                                            {
                                                $SubjectID = $_ARCHON->getSubjectIDFromString($Subject);

                                                if(!$SubjectID)
                                                {
                                                    $objSubject = New Subject();
                                                    $objSubject->Subject = $Subject;
                                                    $objSubject->SubjectTypeID = $arrSubjectTypeMap[$field];

                                                    if($objField->ind2 == '0')
                                                    {
                                                        $objSubject->SubjectSourceID = $LcshSubjectSourceID;
                                                    }

                                                    if(!$objSubject->dbStore())
                                                    {
                                                        echo($_ARCHON->clearError());
                                                    }

                                                    $SubjectID = $objSubject->ID;
                                                }

                                                $objCollection->dbRelateSubject($SubjectID);
                                            }
                                            
                                            // Now go through and do all applicable subfields
                                            $ParentID = $SubjectID;
                                            
                                            $arrSubFields = $objField->subfields();
                                            foreach($arrSubFields as $objSubField)
                                            {
                                                $tag = $objSubField->tag();
                                                if($arrSubjectTypeMap[$tag])
                                                {
                                                    $Subject = import_marc_depunctuate($objSubField->value());
    
                                                    if($Subject)
                                                    {
                                                        $SubjectID = $_ARCHON->getSubjectIDFromString($Subject, $ParentID);
        
                                                        if(!$SubjectID)
                                                        {
                                                            $objSubject = New Subject();
                                                            $objSubject->Subject = $Subject;
                                                            $objSubject->SubjectTypeID = $arrSubjectTypeMap[$field];
        
                                                            if($objField->ind2 == '0')
                                                            {
                                                                $objSubject->SubjectSourceID = $LcshSubjectSourceID;
                                                            }
                                                            
                                                            $objSubject->ParentID = $ParentID;
        
                                                            if(!$objSubject->dbStore())
                                                            {
                                                                echo($_ARCHON->clearError());
                                                            }
        
                                                            $SubjectID = $objSubject->ID;
                                                        }
        
                                                        $objCollection->dbRelateSubject($SubjectID);
                                                        
                                                        $ParentID = $SubjectID;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $arrCreatorFields = array('100', '110', '700', '710');

                            foreach($arrCreatorFields as $field)
                            {
                                $arrFields = $objMARCRecord->fields($field);

                                if(!empty($arrFields))
                                {
                                    foreach($arrFields as $objField)
                                    {
                                        if($objField->subfield('a'))
                                        {
                                            $Creator = import_marc_depunctuate($objField->subfield('a')->value());

                                            if($Creator)
                                            {
                                                $CreatorID = $_ARCHON->getCreatorIDFromString($Creator);

                                                if(!$CreatorID)
                                                {
                                                    $objCreator = New Creator();
                                                    $objCreator->Name = $Creator;
                                                    $objCreator->RepositoryID = $currentRepositoryID;

                                                    if(encoding_substr($field, 1) == '10')
                                                    {
                                                        $objCreator->CreatorTypeID = $CorporateCreatorTypeID;
                                                    }
                                                    elseif($objField->ind1 == '3')
                                                    {
                                                        $objCreator->CreatorTypeID = $FamilyCreatorTypeID;
                                                    }
                                                    else
                                                    {
                                                        $objCreator->CreatorTypeID = $PersonalCreatorTypeID;
                                                    }

                                                    if($objField->subfield('q'))
                                                    {
                                                        $objCreator->NameFullerForm = import_marc_depunctuate($objField->subfield('q')->value());
                                                    }

                                                    if($objField->subfield('d'))
                                                    {
                                                        $objCreator->Dates = import_marc_depunctuate($objField->subfield('d')->value());
                                                    }

                                                    if(!$objCreator->dbStore())
                                                    {
                                                        echo($_ARCHON->clearError());
                                                    }

                                                    $CreatorID = $objCreator->ID;
                                                }

                                                $objCollection->dbRelateCreator($CreatorID);
                                            }
                                        }
                                    }
                                }
                            }

                            if($objCollection->ID)
                            {
                                echo("Imported " . $objCollection->Title . "<br>\n");
                            }
                        }
                    }
                }
            }
            echo("<br>\n");
        }

        echo("<br>Import Complete!\n");
    }
}





function import_marc_depunctuate($String)
{
    if(encoding_substr($String, -1) == ',' || encoding_substr($String, -1) == ';' || encoding_substr($String, -1) == '.')
    {
        $String = encoding_substr($String, 0, encoding_strlen($String) - 1);
    }

    return $String;
}