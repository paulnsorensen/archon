<?php

/*
 * EAD Import Script
 *
 * @package Archon
 * @author Paul Sorensen
 *
 */

isset($_ARCHON) or die();

$currentRepositoryID = $_REQUEST['currentrepositoryid'];

$UtilityCode = 'ead';

$_ARCHON->addDatabaseImportUtility(PACKAGE_COLLECTIONS, $UtilityCode, '3.21', array('xml'), true);

if ($_REQUEST['f'] == 'import-' . $UtilityCode)
{
   if (!$_ARCHON->Security->verifyPermissions(MODULE_DATABASE, FULL_CONTROL))
   {
      die("Permission Denied.");
   }

   if ($currentRepositoryID <= 0)
   {
      die("Repository ID required.");
   }

   @set_time_limit(0);
   ob_implicit_flush(true);

   $arrFiles = $_ARCHON->getAllIncomingFiles();

   $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();
   $arrSubjectSources = $_ARCHON->getAllSubjectSources();

   foreach ($arrSubjectTypes as $objSubjectType)
   {
      $arrSubjectTypeMap[$objSubjectType->EADType] = $objSubjectType->ID;
   }

   foreach ($arrSubjectSources as $objSubjectSource)
   {
      $arrSubjectSourceMap[$objSubjectSource->EADSource] = $objSubjectSource->ID;
   }

   $arrCreatorSources = $_ARCHON->getAllCreatorSources();
   foreach ($arrCreatorSources as $objCreatorSource)
   {
      $arrCreatorSourceMap[$objCreatorSource->SourceAbbreviation] = $objCreatorSource->ID;
   }

   // something might be going on with quotes?
   // need to convert things for MSSQL -- double check encoding_substr


   if (!empty($arrFiles))
   {
      foreach ($arrFiles as $Filename => $strXML)
      {
         echo("<br/><br/>\n");

         echo("Parsing file $Filename...<br/>\n");

         $currEncoding = mb_detect_encoding($strXML, 'UTF-8, ISO-8859-1');
         if ($currEncoding != 'UTF-8')
         {
            echo("File encoded in $currEncoding. Converting to UTF-8...<br/>\n");
            $strXML = encoding_convert_encoding($strXML, 'UTF-8', $currEncoding);
         }

         if (encoding_strpos($strXML, 'ead.xsd') !== false)
         {
            $EADVersion = 'XSD';
         }
         elseif (encoding_strpos($strXML, 'ead.dtd') !== false)
         {
            if (encoding_strpos($strXML, '(EAD) Version 2002') !== false)
            {
               $EADVersion = 'DTD';
            }
            elseif (encoding_strpos($strXML, '(EAD) Version 1.0') !== false)
            {

               echo("The EAD Importer does not support EAD 1.0.    Please use a converter to update your finding aids to EAD 2002.  Skipping file...<br/><br/>\n");
               continue;
            }
            else
            {
               echo("Unable to determine EAD file version.  Skipping file...<br/><br/>\n");
               continue;
            }
         }
         else
         {
            echo("Unable to determine EAD file version.  Skipping file...<br/><b/r>\n");
            continue;
         }


         echo("Importing EAD file in $EADVersion format...<br/><br/>\n");

         $xml = @simplexml_load_string($strXML);

         
         if (!$xml->eadheader)
         {
            echo("The file is not a valid EAD XML file.<br><br>\n");
            continue;
         }

         $ImportRules = array();
         $ImportRules['Title'][] = $xml->archdesc->did->unittitle;
         $ImportRules['Title'][] = $xml->eadheader->filedesc->titlestmt->titleproper;
         $ImportRules['CollectionIdentifier'][] = $xml->archdesc->did->unitid;
         $ImportRules['CollectionIdentifier'][] = $xml->eadheader->eadid;
         $ImportRules['FindingAidAuthor'][] = $xml->eadheader->filedesc->titlestmt->author;
         $ImportRules['FindingAidAuthor'][] = $xml->frontmatter->titlepage->author;

         $ImportRules['Abstract'][] = $xml->archdesc->did->abstract;
         $ImportRules['Scope'][] = $xml->archdesc->scopecontent;
         $ImportRules['Scope'][] = $xml->archdesc->descgrp->scopecontent;
         $ImportRules['Scope'][] = $xml->scopecontent;
         $ImportRules['Arrangement'][] = $xml->archdesc->arrangement;
         $ImportRules['Arrangement'][] = $xml->archdesc->descgrp->arrangement;
         $ImportRules['Arrangement'][] = $xml->arrangement;
         $ImportRules['Arrangement'][] = $xml->archdesc->scopecontent->arrangement;
         $ImportRules['Arrangement'][] = $xml->archdesc->descgrp->scopecontent->arrangement;
         $ImportRules['Arrangement'][] = $xml->scopecontent->arrangement;
         $ImportRules['Arrangement'][] = $xml->archdesc->scopecontent[1]->arrangement;
         $ImportRules['Arrangement'][] = $xml->archdesc->descgrp->scopecontent[1]->arrangement;
         $ImportRules['Arrangement'][] = $xml->scopecontent[1]->arrangement;
         $ImportRules['BiogHist'][] = $xml->archdesc->bioghist;
         $ImportRules['BiogHist'][] = $xml->archdesc->descgrp->bioghist;
         $ImportRules['BiogHist'][] = $xml->bioghist;
         $ImportRules['BiogHist'][] = $xml->archdesc->bioghist->bioghist;
         $ImportRules['BiogHist'][] = $xml->archdesc->descgrp->bioghist->bioghist;
         $ImportRules['BiogHist'][] = $xml->bioghist->bioghist;

         $ImportRules['AccessRestrictions'][] = $xml->archdesc->accessrestrict;
         $ImportRules['AccessRestrictions'][] = $xml->archdesc->descgrp->accessrestrict;
         $ImportRules['AccessRestrictions'][] = $xml->accessrestrict;
         $ImportRules['UseRestrictions'][] = $xml->archdesc->userestrict;
         $ImportRules['UseRestrictions'][] = $xml->archdesc->descgrp->userestrict;
         $ImportRules['UseRestrictions'][] = $xml->userestrict;
         $ImportRules['PhysicalAccess'][] = $xml->archdesc->phystech;
         $ImportRules['PhysicalAccess'][] = $xml->archdesc->descgrp->phystech;
         $ImportRules['PhysicalAccess'][] = $xml->phystech;

         $ImportRules['AcquisitionDate'][] = $xml->acqinfo->p->date;
         $ImportRules['AcquisitionMethod'][] = $xml->archdesc->descgrp->acqinfo;
         $ImportRules['AcquisitionMethod'][] = $xml->archdesc->acqinfo;
         $ImportRules['AcquisitionMethod'][] = $xml->acqinfo;
         $ImportRules['AppraisalInfo'][] = $xml->archdesc->descgrp->appraisal;
         $ImportRules['AppraisalInfo'][] = $xml->archdesc->appraisal;
         $ImportRules['AppraisalInfo'][] = $xml->appraisal;
         $ImportRules['AccrualInfo'][] = $xml->archdesc->descgrp->accruals;
         $ImportRules['AccrualInfo'][] = $xml->archdesc->accruals;
         $ImportRules['AccrualInfo'][] = $xml->accruals;
         $ImportRules['CustodialHistory'][] = $xml->archdesc->descgrp->custodhist;
         $ImportRules['CustodialHistory'][] = $xml->archdesc->custodhist;
         $ImportRules['CustodialHistory'][] = $xml->custodhist;

         $ImportRules['RelatedMaterials'][] = $xml->archdesc->descgrp->relatedmaterial;
         $ImportRules['RelatedMaterials'][] = $xml->archdesc->relatedmaterial;
         $ImportRules['RelatedMaterials'][] = $xml->relatedmaterial;
         $ImportRules['SeparatedMaterials'][] = $xml->archdesc->descgrp->separatedmaterial;
         $ImportRules['SeparatedMaterials'][] = $xml->archdesc->separatedmaterial;
         $ImportRules['SeparatedMaterials'][] = $xml->separatedmaterial;
         $ImportRules['OrigCopiesNote'][] = $xml->archdesc->descgrp->altformavail;
         $ImportRules['OrigCopiesNote'][] = $xml->archdesc->altformavail;
         $ImportRules['OrigCopiesNote'][] = $xml->altformavail;
         $ImportRules['PreferredCitation'][] = $xml->archdesc->descgrp->prefercite;
         $ImportRules['PreferredCitation'][] = $xml->archdesc->prefercite;
         $ImportRules['PreferredCitation'][] = $xml->prefercite;

         $ImportRules['OtherNote'][] = $xml->archdesc->did->note;
         $ImportRules['OtherNote'][] = $xml->archdesc->note;
         $ImportRules['OtherNote'][] = $xml->archdesc->odd;

         $ImportRules['ProcessingInfo'][] = $xml->archdesc->descgrp->processinfo;
         $ImportRules['ProcessingInfo'][] = $xml->archdesc->processinfo;
         $ImportRules['ProcessingInfo'][] = $xml->processinfo;
         $ImportRules['RevisionHistory'][] = $xml->eadheader->revisiondesc;




         $objCollection = New Collection();

         // Enabled
         $objCollection->Enabled = 1;

         // RepositoryID
         $objCollection->RepositoryID = $currentRepositoryID;


         foreach ($ImportRules as $var => $arrXML)
         {
            foreach ($arrXML as $XML)
            {
               if (!$objCollection->$var)
               {
                  $objCollection->$var = import_ead_extracttext($XML);
               }
               else
               {
                  break;
               }
            }
         }


         // CollectionIdentier
         $objCollection->CollectionIdentifier = str_replace('/', '-', $objCollection->CollectionIdentifier);

         // SortTitle
         if ($xml->eadheader->filedesc->titlestmt->titleproper)
         {
            $attr = $xml->eadheader->filedesc->titlestmt->titleproper->attributes();
            if ($attr['type'] && trim($attr['type']) == 'filing')
            {
               $objCollection->SortTitle = import_ead_extracttext($xml->eadheader->filedesc->titlestmt->titleproper);
            }
            else
            {
               $objCollection->SortTitle = encoding_substr($objCollection->Title, 0, 150);
            }
         }

         // Dates
         if (!empty($xml->archdesc->did->unitdate))
         {
            $arrDates = count($xml->archdesc->did->unitdate) > 1 ? $xml->archdesc->did->unitdate : array($xml->archdesc->did->unitdate);

            foreach ($arrDates as $objSimpleXMLElement)
            {
               $arrAttributes = $objSimpleXMLElement->attributes();

               if ($arrAttributes['normal'])
               {
                  $normal = explode('/', $arrAttributes['normal']);
                  if (!empty($normal))
                  {
                     if (count($normal) == 2)
                     {
                        str_replace('-', '', $normal[0]);
                        str_replace('-', '', $normal[1]);
                        $objCollection->NormalDateBegin = encoding_substr($normal[0], 0, 8);
                        $objCollection->NormalDateEnd = encoding_substr($normal[1], 0, 8);
                     }
                     elseif (count($normal == 1))
                     {
                        str_replace('-', '', $normal[0]);
                        $objCollection->NormalDateBegin = encoding_substr($normal[0], 0, 8);
                     }
                  }
               }

               if ($arrAttributes['type'] && $arrAttributes['type'] == 'bulk')
               {
                  $objCollection->PredominantDates = import_ead_extracttext($objSimpleXMLElement);
               }
               else
               {
                  $objCollection->InclusiveDates = import_ead_extracttext($objSimpleXMLElement);

                  if (!$objCollection->NormalDateBegin)
                  {
                     $objCollection->NormalDateBegin = is_natural(encoding_substr($objCollection->InclusiveDates, 0, 4)) ? encoding_substr($objCollection->InclusiveDates, 0, 4) : NULL;
                  }
                  if (!$objCollection->NormalDateEnd)
                  {
                     $objCollection->NormalDateEnd = is_natural(encoding_substr($objCollection->InclusiveDates, encoding_strlen($objCollection->InclusiveDates) - 4, encoding_strlen($objCollection->InclusiveDates))) ? encoding_substr($objCollection->InclusiveDates, encoding_strlen($objCollection->InclusiveDates) - 4, encoding_strlen($objCollection->InclusiveDates)) : NULL;
                  }
               }
            }
         }
         if (!empty($xml->archdesc->did->unittitle->unitdate))
         {
            $arrDates = count($xml->archdesc->did->unittitle->unitdate) > 1 ? $xml->archdesc->did->unittitle->unitdate : array($xml->archdesc->did->unittitle->unitdate);

            foreach ($arrDates as $objSimpleXMLElement)
            {
               $arrAttributes = $objSimpleXMLElement->attributes();

               if ($arrAttributes['normal'])
               {
                  $normal = explode('/', $arrAttributes['normal']);
                  if (!empty($normal))
                  {
                     if (count($normal) == 2)
                     {
                        if (!$objCollection->NormalDateBegin)
                        {
                           str_replace('-', '', $normal[0]);
                           $objCollection->NormalDateBegin = encoding_substr($normal[0], 0, 8);
                        }
                        if (!$objCollection->NormalDateEnd)
                        {
                           str_replace('-', '', $normal[1]);
                           $objCollection->NormalDateEnd = encoding_substr($normal[1], 0, 8);
                        }
                     }
                     elseif (count($normal == 1))
                     {
                        if (!$objCollection->NormalDateBegin)
                        {
                           str_replace('-', '', $normal[0]);
                           $objCollection->NormalDateBegin = encoding_substr($normal[0], 0, 8);
                        }
                     }
                  }
               }

               if ($arrAttributes['type'] && $arrAttributes['type'] == 'bulk')
               {
                  if (!$objCollection->PredominantDates)
                  {
                     $objCollection->PredominantDates = import_ead_extracttext($objSimpleXMLElement);
                  }
               }
               else
               {
                  if (!$objCollection->InclusiveDates)
                  {
                     $objCollection->InclusiveDates = import_ead_extracttext($objSimpleXMLElement);
                  }

                  if (!$objCollection->NormalDateBegin)
                  {
                     $objCollection->NormalDateBegin = is_natural(encoding_substr($objCollection->InclusiveDates, 0, 4)) ? encoding_substr($objCollection->InclusiveDates, 0, 4) : NULL;
                  }
                  if (!$objCollection->NormalDateEnd)
                  {
                     $objCollection->NormalDateEnd = is_natural(encoding_substr($objCollection->InclusiveDates, encoding_strlen($objCollection->InclusiveDates) - 4, encoding_strlen($objCollection->InclusiveDates))) ? encoding_substr($objCollection->InclusiveDates, encoding_strlen($objCollection->InclusiveDates) - 4, encoding_strlen($objCollection->InclusiveDates)) : NULL;
                  }
               }
            }
         }

         if ($xml->eadheader->filedesc->publicationstmt->date)
         {
            $arrAttributes = $xml->eadheader->filedesc->publicationstmt->date->attributes();
            if ($arrAttributes['normal'])
            {
               str_replace('-', '', $arrAttributes['normal']);
               $objCollection->PublicationDate = encoding_substr($arrAttributes['normal'], 0, 8);
            }
            else
            {
               $objCollection->PublicationDate = import_ead_extracttext($xml->eadheader->filedesc->publicationstmt->date);
            }
         }



         $FindingLanguageID = 0;

         if ($xml->eadheader->profiledesc->langusage->language)
         {
            $langAttr = $xml->eadheader->profiledesc->langusage->language->attributes();
            if ($langAttr && $langAttr['langcode'])
            {
               $FindingLanguageID = $_ARCHON->getLanguageIDFromString(trim((string) $langAttr['langcode']));
            }

            if (!$FindingLanguageID)
            {
               $FindingLanguageID = $_ARCHON->getLanguageIDFromString(import_ead_extracttext($xml->eadheader->profiledesc->langusage->language));
            }
         }
         $objCollection->FindingLanguageID = $FindingLanguageID ? $FindingLanguageID : 0;



         // Extent, ExtentUnitID
         if (!empty($xml->archdesc->did->physdesc))
         {
            $arrPhysDesc = count($xml->archdesc->did->physdesc) > 1 ? $xml->archdesc->did->physdesc : array($xml->archdesc->did->physdesc);

            foreach ($arrPhysDesc as $objSimpleXMLElement)
            {
               $arrAttributes = $objSimpleXMLElement->attributes();

               if ($objSimpleXMLElement->extent)
               {
                  $arrAttributes = $objSimpleXMLElement->extent->attributes();

                  if ($arrAttributes['unit'])
                  {
                     $ExtentUnitID = $_ARCHON->getExtentUnitIDFromString($arrAttributes['unit']);
                     $objCollection->ExtentUnitID = $ExtentUnitID ? $ExtentUnitID : 0;
                  }
                  elseif ($arrAttributes['type'])
                  {
                     $ExtentUnitID = $_ARCHON->getExtentUnitIDFromString($arrAttributes['type']);
                     $objCollection->ExtentUnitID = $ExtentUnitID ? $ExtentUnitID : 0;
                  }

                  $strExtent = import_ead_extracttext($objSimpleXMLElement->extent);
               }
               elseif ($arrAttributes['label'] && (encoding_strpos(encoding_strtolower($arrAttributes['label']), 'extent') !== false || encoding_strpos(encoding_strtolower($arrAttributes['label']), 'volume') !== false))
               {
                  $strExtent = import_ead_extracttext($objSimpleXMLElement);
               }
            }

            if ($strExtent)
            {
               preg_match('/([\d\.]+) ([\w ]+)/u', $strExtent, $arrMatches);

               if ($arrMatches[1] && is_numeric($arrMatches[1]))
               {
                  $objCollection->Extent = $arrMatches[1];

                  // If the Extent Units were already defined because of an explicit unit attribute in an extent tag
                  // the pre-existing units should have precedence.
                  if (!$objCollection->ExtentUnitID)
                  {
                     $ExtentUnitID = $_ARCHON->getExtentUnitIDFromString($arrMatches[2]);
                     $objCollection->ExtentUnitID = $ExtentUnitID ? $ExtentUnitID : 0;
                  }
               }
            }
         }


         if (!$objCollection->Title)
         {
            echo("The Collection has no title. <br/>\n");
            continue;
         }
         else
         {
            if (!$objCollection->dbStoreCollection())
            {
               echo($_ARCHON->clearError() . "<br/>\n");
               continue;
            }
            else
            {
               echo("Successfully Stored Collection: {$objCollection->Title}<br/>\n");
            }
         }


         // MaterialTypeID, Creators
         if (!empty($xml->archdesc->did->origination))
         {
            foreach ($xml->archdesc->did->origination->children() as $ElementName => $objSimpleXMLElement)
            {
               if ($ElementName == 'persname')
               {
                  $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Personal Name');

                  $objCollection->MaterialTypeID = $objCollection->MaterialTypeID ? $objCollection->MaterialTypeID : $_ARCHON->getMaterialTypeIDFromString('Personal Papers');
               }
               elseif ($ElementName == 'corpname')
               {
                  $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Corporate Name');

                  $objCollection->MaterialTypeID = $objCollection->MaterialTypeID ? $objCollection->MaterialTypeID : $_ARCHON->getMaterialTypeIDFromString('Offical Records');
               }
               elseif ($ElementName == 'famname')
               {
                  $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Family Name');

                  $objCollection->MaterialTypeID = $objCollection->MaterialTypeID ? $objCollection->MaterialTypeID : $_ARCHON->getMaterialTypeIDFromString('Personal Papers');
               }

               $arrCreators = count($objSimpleXMLElement) > 1 ? $objSimpleXMLElement : array($objSimpleXMLElement);

               foreach ($arrCreators as $objCreatorElement)
               {
                  $CreatorName = import_ead_extracttext($objCreatorElement);

                  $CreatorID = $_ARCHON->getCreatorIDFromString($CreatorName);

                  if (!$CreatorID)
                  {
                     // check for matches of the normal attribute
                     $normal = (string) $arrAttributes['normal'] ? trim((string) $arrAttributes['normal']) : '';
                     if ($normal)
                     {
                        $CreatorID = $_ARCHON->getCreatorIDFromString($normal);
                     }
                  }

                  if (!$CreatorID)
                  {
                     $arrAttributes = $objCreatorElement->attributes();
                     $src = (string) $arrAttributes['source'] ? trim((string) $arrAttributes['source']) : 'local';
                     $CreatorSourceID = $arrCreatorSourceMap[$src];

                     $objCreator = New Creator();
                     $objCreator->Name = $CreatorName;
                     $objCreator->CreatorTypeID = $CreatorTypeID;
                     $objCreator->RepositoryID = $objCollection->RepositoryID;
                     $objCreator->CreatorSourceID = $CreatorSourceID;

                     if (!$objCreator->dbStore())
                     {
                        echo($_ARCHON->clearError() . "<br/>\n");
                     }

                     $CreatorID = $objCreator->ID;
                  }

                  if ($CreatorID)
                  {
                     if (!$objCollection->dbRelateCreator($CreatorID))
                     {
                        echo($_ARCHON->clearError() . "<br/>\n");
                     }
                  }
               }
            }

            if ($objCollection->MaterialTypeID)
            {
               $objCollection->dbStoreCollection();
            }
         }


         // Subjects
         if (!empty($xml->archdesc->controlaccess))
         {
            foreach ($xml->archdesc->controlaccess->children() as $ElementName => $objSimpleXMLElement)
            {
               if ($ElementName == 'controlaccess')
               {
                  foreach ($objSimpleXMLElement->children() as $SubjectElementName => $objSubjectElement)
                  {
                     $SubjectTypeID = $arrSubjectTypeMap[$SubjectElementName] ? $arrSubjectTypeMap[$SubjectElementName] : 0;

                     $arrSubjects = count($objSubjectElement) > 1 ? $objSubjectElement : array($objSubjectElement);

                     if ($SubjectTypeID)
                     {
                        foreach ($arrSubjects as $objSubjectElement)
                        {
                           $SubjectName = import_ead_extracttext($objSubjectElement);

                           $arrAttributes = $objSubjectElement->attributes();

                           $src = (string) $arrAttributes['source'] ? trim((string) $arrAttributes['source']) : 'local';
                           $SubjectSourceID = $arrSubjectSourceMap[$src] ? $arrSubjectSourceMap[$src] : $arrSubjectSourceMap['local'];

                           if (encoding_strpos($SubjectName, '--') !== false)
                           {
                              $arrTraversal = explode('--', $SubjectName);

                              $ParentID = 0;

                              foreach ($arrTraversal as $subject)
                              {
                                 $subject = trim($subject);

                                 $SubjectID = $_ARCHON->getSubjectIDFromString($subject, $ParentID);

                                 if (!$SubjectID)
                                 {
                                    $objSubject = New Subject();
                                    $objSubject->Subject = $subject;
                                    $objSubject->SubjectTypeID = $SubjectTypeID;
                                    $objSubject->SubjectSourceID = $SubjectSourceID;
                                    $objSubject->Parent = $ParentID;

                                    if (!$objSubject->dbStore())
                                    {
                                       echo($_ARCHON->clearError() . "<br/>\n");
                                    }

                                    $SubjectID = $objSubject->ID;
                                 }
                                 $ParentID = $SubjectID;
                              }
                           }
                           else
                           {
                              $SubjectID = $_ARCHON->getSubjectIDFromString($SubjectName);

                              if (!$SubjectID)
                              {
                                 $objSubject = New Subject();
                                 $objSubject->Subject = $SubjectName;
                                 $objSubject->SubjectTypeID = $SubjectTypeID;
                                 $objSubject->SubjectSourceID = $SubjectSourceID;

                                 if (!$objSubject->dbStore())
                                 {
                                    echo($_ARCHON->clearError() . "<br/>\n");
                                 }

                                 $SubjectID = $objSubject->ID;
                              }
                           }

                           if ($SubjectID)
                           {
                              if (!$objCollection->dbRelateSubject($SubjectID))
                              {
                                 echo($_ARCHON->clearError() . "<br/>\n");
                              }
                           }
                        }
                     }
                  }
               }
            }
         }


         // Languages
         if (!empty($xml->archdesc->did->langmaterial))
         {
            foreach ($xml->archdesc->did->langmaterial->children() as $ElementName => $objSimpleXMLElement)
            {
               if ($ElementName == 'language')
               {
                  $langAttr = $objSimpleXMLElement->attributes();
                  $langID = 0;


                  if ($langAttr && $langAttr['langcode'])
                  {
                     $langID = $_ARCHON->getLanguageIDFromString((string) $langAttr['langcode']);
                  }

                  if (!$langID)
                  {
                     $langID = $_ARCHON->getLanguageIDFromString(import_ead_extracttext($objSimpleXMLElement));
                  }

                  if ($langID)
                  {
                     if (!$objCollection->dbRelateLanguage($langID))
                     {
                        echo($_ARCHON->clearError() . "<br/>\n");
                     }
                  }
               }
            }
         }

         if (defined('PACKAGE_DIGITALLIBRARY'))
         {
            // Digital Content
            if ($xml->archdesc->dao)
            {
               $attr = $xml->archdesc->dao->attributes();
               if ($attr['href'])
               {
                  $url = $attr['href'];

                  $title = '';
                  if ($xml->archdesc->dao->daodesc)
                  {
                     $title = import_ead_extracttext($xml->archdesc->dao->daodesc);
                  }

                  $title = $title ? $title : $url;

                  $objDigitalContent = new DigitalContent();
                  $objDigitalContent->Title = $title;
                  $objDigitalContent->ContentURL = $url;
                  $objDigitalContent->CollectionID = $objCollection->ID;

                  if (!$objDigitalContent->dbStore())
                  {
                     echo($_ARCHON->clearError() . "<br/>\n");
                  }
               }
            }
            elseif ($xml->archdesc->daogrp->daoloc)
            {
               foreach ($xml->archdesc->daogrp->children() as $name => $element)
               {
                  if ($name == 'daoloc')
                  {
                     $attr = $element->attributes();
                     if ($attr['href'])
                     {
                        $url = $attr['href'];

                        $title = '';
                        if ($element->daodesc)
                        {
                           $title = import_ead_extracttext($element->daodesc);
                        }

                        $title = $title ? $title : $url;

                        $objDigitalContent = new DigitalContent();
                        $objDigitalContent->Title = $title;
                        $objDigitalContent->ContentURL = $url;
                        $objDigitalContent->CollectionID = $objCollection->ID;

                        if (!$objDigitalContent->dbStore())
                        {
                           echo($_ARCHON->clearError() . "<br/>\n");
                        }
                     }
                  }
               }
            }
         }

         // Content
         if (!empty($xml->archdesc->dsc))
         {
            foreach ($xml->archdesc->dsc->children() as $ElementName => $objSimpleXMLElement)
            {
               import_ead_storecontent($objCollection->ID, 0, $currentRepositoryID, $ElementName, $objSimpleXMLElement);
            }
         }
      }
   }


   echo("<br/>Import Complete!");
}

function import_ead_extracttext($XML)
{
   $text = trim((string) $XML);
   if ($text)
   {
      $c = $XML->children();
      if (!empty($c))
      {
         $str = (string) $XML->asXML();
         $str = bbcode_ead_decode($str);
         $str = str_replace('<lb/>', '\n', $str);
         $text = trim(preg_replace('/<(.+?)>/ismu', '', $str));
      }
      else
      {
         $text = str_replace('<lb/>', '\n', $text);
      }

      $text = preg_replace('/[\s]+/ismu', ' ', $text);
      $text = str_replace('<lb/>', '\n', $text);

      return $text;
   }
   elseif (!empty($XML->p))
   {
      $text = '';

      foreach ($XML->p as $p)
      {
         // if there are elements within paragraphs, the tags will be removed
         // and the string inside will be extracted
         $c = $p->children();
         if (!empty($c))
         {
            $str = (string) $p->asXML();
            $str = bbcode_ead_decode($str);
            $p = trim(preg_replace('/<(.+?)>/ismu', '', $str));
         }
         else
         {
            $p = trim((string) $p);
         }
         $p = preg_replace('/[\s]+/ismu', ' ', $p);
         $text .= $text ? "\n" . $p : $p;
      }
      return $text;
   }

   return '';
}

function import_ead_extractuserfields($elementname, $XMLElement)
{
   global $_ARCHON;
   static $arrEADElementMap = array();
   static $arrEADElements = array();

   if (!CONFIG_COLLECTIONS_ENABLE_USER_DEFINED_FIELDS)
   {
      return NULL;
   }

   if (empty($arrEADElements))
   {
      $arrEADElements = $_ARCHON->getAllEADElements();
   }

   if (empty($arrEADElementMap))
   {
      foreach ($arrEADElements as $objEADElement)
      {
         $arrEADElementMap[$objEADElement->EADTag] = $objEADElement->ID;
      }
   }

   if ($arrEADElementMap[$elementname])
   {
      $objUserField = New UserField();

      $arrAttributes = $XMLElement->attributes();

      if (!empty($arrAttributes) && $arrAttributes['label'])
      {
         $objUserField->Title = import_ead_extracttext($arrAttributes['label']);
      }
      elseif ($XMLElement->head)
      {
         $objUserField->Title = import_ead_extracttext($XMLElement->head);
      }
      else
      {
         $objUserField->Title = $arrEADElements[$arrEADElementMap[$elementname]]->EADElement;
      }

      $objUserField->Value = import_ead_extracttext($XMLElement);
      $objUserField->EADElementID = $arrEADElementMap[$elementname];

      return $objUserField;
   }
   else
   {
      return NULL;
   }
}

function import_ead_storecontent($CollectionID, $ParentID, $RepositoryID, $ElementName, $objSimpleXMLElement)
{
   global $_ARCHON;

   $objContent = NULL;
   $arrUserFields = array();
   $arrCreatorIDs = array();
   $arrSubjectIDs = array();
   $arrDigitalContent = array();

   static $arrEADLevelMap = array();
   static $arrLevelContainers = array();
   static $arrLevelContainerMap = array();
   static $arrCreatorSourceMap = array();
   static $arrSubjectSourceMap = array();
   static $arrSubjectTypeMap = array();
   static $CollectionContentCache = array();

   if (empty($arrLevelContainers))
   {
      $arrLevelContainers = $_ARCHON->getAllLevelContainers();
   }

   if (empty($arrLevelContainerMap))
   {
      foreach ($arrLevelContainers as $objLevelContainer)
      {
         $arrLevelContainerMap[encoding_strtolower($objLevelContainer->LevelContainer)] = $objLevelContainer->ID;
      }
   }

   if (empty($arrEADLevelMap))
   {
      foreach ($arrLevelContainers as $objLevelContainer)
      {
         if ($objLevelContainer->EADLevel && $objLevelContainer->PrimaryEADLevel)
         {
            $arrEADLevelMap[encoding_strtolower($objLevelContainer->EADLevel)] = $objLevelContainer->ID;
         }
      }
   }

   if (empty($arrSubjectTypeMap))
   {
      $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();
      foreach ($arrSubjectTypes as $objSubjectType)
      {
         $arrSubjectTypeMap[$objSubjectType->EADType] = $objSubjectType->ID;
      }
   }

   if (empty($arrSubjectSourceMap))
   {
      $arrSubjectSources = $_ARCHON->getAllSubjectSources();
      foreach ($arrSubjectSources as $objSubjectSource)
      {
         $arrSubjectSourceMap[$objSubjectSource->EADSource] = $objSubjectSource->ID;
      }
   }

   if (empty($arrCreatorSourceMap))
   {
      $arrCreatorSources = $_ARCHON->getAllCreatorSources();
      foreach ($arrCreatorSources as $objCreatorSource)
      {
         $arrCreatorSourceMap[$objCreatorSource->SourceAbbreviation] = $objCreatorSource->ID;
      }
   }

   if (preg_match('/^c([\d]+)?$/u', $ElementName))
   {
      $arrCTagAttributes = $objSimpleXMLElement->attributes();
      $Level = trim(encoding_strtolower((string) $arrCTagAttributes['level']));
      $Level = $arrEADLevelMap[$Level] ? $Level : '';

      if (!$objSimpleXMLElement->did->container)
      {
         $objContent = New CollectionContent();
         $objContent->CollectionID = $CollectionID;
         $objContent->ParentID = $ParentID;
         $objContent->LevelContainerID = $arrEADLevelMap[$Level] ? $arrEADLevelMap[$Level] : $arrEADLevelMap['file'];

         if (!$objContent->dbStore())
         {
            echo($_ARCHON->clearError() . "<br/>\n");
         }
      }
      else
      {
         // this represents the current level in the content hierarchy
         $currentPID = $ParentID;

         foreach ($objSimpleXMLElement->did->container as $containerElement)
         {
            $arrAttributes = $containerElement->attributes();
            $containerType = $arrAttributes['type'] ? $arrAttributes['type'] : $arrAttributes['label'];
            if ($containerType)
            {
               $LevelContainerID = $arrLevelContainerMap[encoding_strtolower(trim((string) $containerType))];

               $LevelContainerIdentifier = import_ead_extracttext($containerElement);
               if (strlen($LevelContainerIdentifier) > 10)
               {
                  $LevelContainerIdentifier = encoding_substr($LevelContainerIdentifier, 0, 10);
               }

               if ($LevelContainerID && $LevelContainerIdentifier)
               {
                  $CollectionContentID = $_ARCHON->getCollectionContentIDFromData($CollectionID, $LevelContainerID, $LevelContainerIdentifier, $currentPID);

                  if ($CollectionContentID)
                  {
                     if ($CollectionContentCache[$CollectionContentID])
                     {
                        $objContent = $CollectionContentCache[$CollectionContentID];
                     }
                     else
                     {
                        $objContent = New CollectionContent($CollectionContentID);
                        $objContent->dbLoad();
                        $CollectionContentCache[$CollectionContentID] = $objContent;
                     }

                     // now any new content (assuming there are more container elements)
                     // will be a child of this already existing content
                     $currentPID = $CollectionContentID;
                  }
                  else
                  {
                     $objContent = New CollectionContent();
                     $objContent->CollectionID = $CollectionID;
                     $objContent->LevelContainerID = $LevelContainerID;
                     $objContent->LevelContainerIdentifier = $LevelContainerIdentifier;
                     $objContent->ParentID = $currentPID;
                     if ($objContent->dbStore())
                     {
                        $currentPID = $objContent->ID;
                     }
                     else
                     {
                        echo($_ARCHON->clearError() . "<br/>\n");
                     }
                  }
               }
               else
               {
                  echo("Error storing content: Unknown container type or identifier. <br/>\n");
               }
            }
            else
            {
               echo("Error storing content: @type attribute must be defined. <br/>\n");
            }
         }

         // check to see if the deepest container element describes the
         // current intellectual level

         $objLevel = $arrLevelContainers[$objContent->LevelContainerID];
         // if the deepest container isn't intellectual, then set the default
         // level to 'file' to invoke the creation of an intellectual content
         if (!$Level && !$objLevel->IntellectualLevel)
         {
            $Level = 'file';
         }
         if ($Level && $Level != $objLevel->EADLevel)
         {
            // add an item below the last container

            $objContent = New CollectionContent();
            $objContent->CollectionID = $CollectionID;
            $objContent->ParentID = $currentPID;
            $objContent->LevelContainerID = $arrEADLevelMap[$Level];

            if (!$objContent->dbStore())
            {
               echo($_ARCHON->clearError() . "<br/>\n");
            }
         }
      }


      if (!empty($objSimpleXMLElement->did))
      {
         foreach ($objSimpleXMLElement->did->children() as $ChildElementName => $objChildSimpleXMLElement)
         {
            $ChildElementName = encoding_strtolower($ChildElementName);

            if ($ChildElementName == 'container')
            {
               // do nothing -- this as already been processed
            }
            elseif ($ChildElementName == 'unitdate')
            {
               $objContent->Date = $objContent->Date ? $objContent->Date . ', ' . (string) $objChildSimpleXMLElement : import_ead_extracttext($objChildSimpleXMLElement);
            }
            elseif ($ChildElementName == 'unittitle')
            {
               $objContent->Title = $objContent->Title ? $objContent->Title . ' ' . (string) $objChildSimpleXMLElement : import_ead_extracttext($objChildSimpleXMLElement);
               $objContent->Title = trim($objContent->Title);

               $arrDates = count($objChildSimpleXMLElement->unitdate) > 1 ? $objChildSimpleXMLElement->unitdate : array($objChildSimpleXMLElement->unitdate);
               foreach ($arrDates as $Date)
               {
                  $objContent->Date = $objContent->Date ? $objContent->Date . ', ' . (string) $Date : import_ead_extracttext($Date);
               }
            }
            elseif ($ChildElementName == 'origination')
            {
               $children = $objChildSimpleXMLElement->children();
               if (!empty($children))
               {
                  // get creators
                  if (CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_CREATORS)
                  {
                     foreach ($objChildSimpleXMLElement->children() as $name => $element)
                     {
                        if ($name == 'persname')
                        {
                           $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Personal Name');
                        }
                        elseif ($name == 'corpname')
                        {
                           $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Corporate Name');
                        }
                        elseif ($name == 'famname')
                        {
                           $CreatorTypeID = $_ARCHON->getCreatorTypeIDFromString('Family Name');
                        }
                        else
                        {
                           continue;
                        }

                        $arrCreators = count($element) > 1 ? $element : array($element);

                        foreach ($arrCreators as $objCreatorElement)
                        {
                           $CreatorName = import_ead_extracttext($objCreatorElement);
                           $arrAttributes = $objCreatorElement->attributes();

                           $CreatorID = $_ARCHON->getCreatorIDFromString($CreatorName);

                           if (!$CreatorID)
                           {
                              // check for matches of the normal attribute
                              $normal = (string) $arrAttributes['normal'] ? trim((string) $arrAttributes['normal']) : '';
                              if ($normal)
                              {
                                 $CreatorID = $_ARCHON->getCreatorIDFromString($normal);
                              }
                           }

                           if (!$CreatorID)
                           {
                              $src = (string) $arrAttributes['source'] ? trim((string) $arrAttributes['source']) : 'local';
                              $CreatorSourceID = $arrCreatorSourceMap[$src];

                              $objCreator = New Creator();
                              $objCreator->Name = $CreatorName;
                              $objCreator->CreatorTypeID = $CreatorTypeID;
                              $objCreator->RepositoryID = $RepositoryID;
                              $objCreator->CreatorSourceID = $CreatorSourceID;
                              if (!$objCreator->dbStore())
                              {
                                 echo($_ARCHON->clearError() . "<br/>\n");
                              }

                              $CreatorID = $objCreator->ID;
                           }

                           if ($CreatorID)
                           {
                              $arrCreatorIDs[] = $CreatorID;
                           }
                        }
                     }
                  }
               }
               else
               {
                  $userfield = import_ead_extractuserfields($ChildElementName, $objChildSimpleXMLElement);
                  if ($userfield)
                  {
                     $arrUserFields[] = $userfield;
                  }
               }
            }
            elseif ($ChildElementName == 'physdesc')
            {
               $arrAttributes = $objChildSimpleXMLElement->attributes();
               if (!empty($arrAttributes) && $arrAttributes['label'])
               {
                  $userfield = import_ead_extractuserfields($ChildElementName, $objChildSimpleXMLElement);
                  if ($userfield)
                  {
                     $arrUserFields[] = $userfield;
                  }
               }

               $children = $objChildSimpleXMLElement->children();
               if (!empty($children))
               {
                  foreach ($objChildSimpleXMLElement->children() as $name => $element)
                  {
                     $userfield = import_ead_extractuserfields($name, $element);
                     if ($userfield)
                     {
                        $arrUserFields[] = $userfield;
                     }
                  }
               }
            }
            elseif ($ChildElementName == 'dao' || $ChildElementName == 'daogrp')
            {
               if (defined('PACKAGE_DIGITALLIBRARY'))
               {
                  // Digital Content
                  if ($ChildElementName == 'dao')
                  {
                     $attr = $objChildSimpleXMLElement->attributes();
                     if ($attr['href'])
                     {
                        $url = $attr['href'];

                        $title = '';
                        if ($objChildSimpleXMLElement->daodesc)
                        {
                           $title = import_ead_extracttext($objChildSimpleXMLElement->daodesc);
                        }

                        $title = $title ? $title : $url;

                        $objDigitalContent = new DigitalContent();
                        $objDigitalContent->Title = $title;
                        $objDigitalContent->ContentURL = $url;
                        $objDigitalContent->CollectionID = $CollectionID;

                        $arrDigitalContent[] = $objDigitalContent;
                     }
                  }
                  else
                  {
                     foreach ($objChildSimpleXMLElement->children() as $name => $element)
                     {
                        if ($name == 'daoloc')
                        {
                           $attr = $element->attributes();
                           if ($attr['href'])
                           {
                              $url = $attr['href'];

                              $title = '';
                              if ($element->daodesc)
                              {
                                 $title = import_ead_extracttext($element->daodesc);
                              }

                              $title = $title ? $title : $url;

                              $objDigitalContent = new DigitalContent();
                              $objDigitalContent->Title = $title;
                              $objDigitalContent->ContentURL = $url;
                              $objDigitalContent->CollectionID = $CollectionID;

                              $arrDigitalContent[] = $objDigitalContent;
                           }
                        }
                     }
                  }
               }
            }
            else
            {
               $userfield = import_ead_extractuserfields($ChildElementName, $objChildSimpleXMLElement);
               if ($userfield)
               {
                  $arrUserFields[] = $userfield;
               }
               else
               {
                  $objContent->Description .= $objContent->Description ? "\n" : '';
                  $objContent->Description .= import_ead_extracttext($objChildSimpleXMLElement);
               }
            }
         } // end c->did->children() loop
      }


      if (isset($objSimpleXMLElement->scopecontent))
      {
         $objScopecontentElement = count($objSimpleXMLElement->scopecontent) > 1 ? current($objSimpleXMLElement->scopecontent) : $objSimpleXMLElement->scopecontent;
         $objContent->Description = import_ead_extracttext($objScopecontentElement);
      }

      foreach ($objSimpleXMLElement->children() as $ChildElementName => $objChildSimpleXMLElement)
      {
         //check for subjects
         if ($ChildElementName == 'controlaccess')
         {
            if (CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_SUBJECTS)
            {
               foreach ($objChildSimpleXMLElement->children() as $name => $element)
               {
                  if ($name == 'controlaccess')
                  {
                     foreach ($element->children() as $SubjectElementName => $objSubjectElement)
                     {
                        $SubjectTypeID = $arrSubjectTypeMap[$SubjectElementName] ? $arrSubjectTypeMap[$SubjectElementName] : 0;

                        $arrSubjects = count($objSubjectElement) > 1 ? $objSubjectElement : array($objSubjectElement);

                        if ($SubjectTypeID)
                        {
                           foreach ($arrSubjects as $objSubjectElement)
                           {
                              $SubjectName = import_ead_extracttext($objSubjectElement);

                              $arrAttributes = $objSubjectElement->attributes();
                              $src = (string) $arrAttributes['source'] ? trim((string) $arrAttributes['source']) : 'local';
                              $SubjectSourceID = $arrSubjectSourceMap[$src] ? $arrSubjectSourceMap[$src] : $arrSubjectSourceMap['local'];

                              if (encoding_strpos($SubjectName, '--') !== false)
                              {
                                 $arrTraversal = explode('--', $SubjectName);

                                 $ParentID = 0;

                                 foreach ($arrTraversal as $subject)
                                 {
                                    $subject = trim($subject);

                                    $SubjectID = $_ARCHON->getSubjectIDFromString($subject, $ParentID);

                                    if (!$SubjectID)
                                    {
                                       $objSubject = New Subject();
                                       $objSubject->Subject = $subject;
                                       $objSubject->SubjectTypeID = $SubjectTypeID;
                                       $objSubject->SubjectSourceID = $SubjectSourceID;
                                       $objSubject->Parent = $ParentID;

                                       if (!$objSubject->dbStore())
                                       {
                                          echo($_ARCHON->clearError() . "<br/>\n");
                                       }

                                       $SubjectID = $objSubject->ID;
                                    }

                                    $ParentID = $SubjectID;
                                 }
                              }
                              else
                              {
                                 $SubjectID = $_ARCHON->getSubjectIDFromString($SubjectName);

                                 if (!$SubjectID)
                                 {
                                    $objSubject = New Subject();
                                    $objSubject->Subject = $SubjectName;
                                    $objSubject->SubjectTypeID = $SubjectTypeID;
                                    $objSubject->SubjectSourceID = $SubjectSourceID;

                                    if (!$objSubject->dbStore())
                                    {
                                       echo($_ARCHON->clearError() . "<br/>\n");
                                    }

                                    $SubjectID = $objSubject->ID;
                                 }
                              }

                              if ($SubjectID)
                              {
                                 $arrSubjectIDs[] = $SubjectID;
                              }
                           }
                        }
                     }
                  }
               }
            }
         }
         else
         {
            $userfield = import_ead_extractuserfields($ChildElementName, $objChildSimpleXMLElement);
            if ($userfield)
            {
               $arrUserFields[] = $userfield;
            }
         }
      }

      if (!empty($objSimpleXMLElement->descgrp))
      {
         foreach ($objSimpleXMLElement->descgrp->children() as $ChildElementName => $objChildSimpleXMLElement)
         {
            $userfield = import_ead_extractuserfields($ChildElementName, $objChildSimpleXMLElement);
            if ($userfield)
            {
               $arrUserFields[] = $userfield;
            }
         }
      }



      if ($objContent->dbStore())
      {
         if (!empty($arrUserFields))
         {
            foreach ($arrUserFields as $objUserField)
            {
               $objUserField->ContentID = $objContent->ID;
               if ($objUserField->Value && !$objUserField->dbStore())
               {
                  echo("Error storing UserField on Content: " . $objContent->Title . " " . $objUserField->Title . ": " . $_ARCHON->clearError() . "<br/>\n");
               }
            }
         }
         if (!empty($arrCreatorIDs))
         {
            if (!$objContent->dbUpdateRelatedCreators($arrCreatorIDs))
            {
               echo($_ARCHON->clearError() . "<br/>\n");
            }
         }
         if (!empty($arrSubjectIDs))
         {
            if (!$objContent->dbUpdateRelatedSubjects($arrSubjectIDs))
            {
               echo($_ARCHON->clearError() . "<br/>\n");
            }
         }
         if (!empty($arrDigitalContent))
         {
            foreach ($arrDigitalContent as $objDigitalContent)
            {
               $objDigitalContent->CollectionContentID = $objContent->ID;
               if ($objDigitalContent->ContentURL && !$objDigitalContent->dbStore())
               {
                  echo($_ARCHON->clearError() . "<br/>\n");
               }
            }
         }

         foreach ($objSimpleXMLElement->children() as $ChildElementName => $objChildSimpleXMLElement)
         {
            if (preg_match('/^c([\d]+)?$/u', $ChildElementName))
            {
               import_ead_storecontent($CollectionID, $objContent->ID, $RepositoryID, $ChildElementName, $objChildSimpleXMLElement);
            }
         }
      }
      else
      {
         echo("Error storing content " . $objContent->Title . ".  Error Message: " . $_ARCHON->clearError() . "<br/>\n");
      }
   }
}

?>
