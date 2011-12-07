<?php
/**
 * Template for output of name information to EAC-CPF format
 *
 * The variable:
 *
 *  $objCreator
 *
 * is an instance of a Creator object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Creator class definition in lib/creator.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Chris Prom
 */

isset($_ARCHON) or die();

$path = preg_replace('/[\w.]+php/u', '', $_SERVER['SCRIPT_NAME']);

echo('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
?>

<eac-cpf 
   xmlns="urn:isbn:1-931666-33-4"
   xmlns:html="http://www.w3.org/1999/xhtml"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="<?php echo("urn:isbn:1-931666-33-4 http://{$_SERVER['HTTP_HOST']}{$path}" . "packages/creators/lib/cpf.xsd"); ?>   " xml:lang="<?php
   echo ($objCreator->Language ? $objCreator->Language->LanguageShort : 'eng');
   echo ('">'. "\n");
   echo ("\t<control>\n");
   echo ("\t\t<recordId>");
   echo ($objCreator->Repository->Code);
   echo ($objCreator->ID);
   echo ("</recordId>\n");
   echo ("\t\t<maintenanceStatus>");       //this is required but Archon does not track, so value is set to revised.
   echo ("revised");
   echo ("</maintenanceStatus>\n");
   echo ("\t\t<maintenanceAgency>\n");
   echo ("\t\t\t<agencyCode>");
   echo ($objCreator->Repository->Code);
   echo ("</agencyCode>\n");
   echo ("\t\t\t<agencyName>");
   echo (bbcode_eac_encode($objCreator->Repository->getString('Name', 0, false, false)));
   echo ("</agencyName>\n");
   echo ("\t\t</maintenanceAgency>\n");
   echo ("\t\t<languageDeclaration>\n");
   echo ("\t\t\t<language languageCode='");
   echo ($objCreator->Language ? $objCreator->Language->LanguageShort : "eng");
   echo ("'>");
   echo ($objCreator->Language ? $objCreator->Language->LanguageLong : "English");
   echo ("</language>\n");
   echo ("\t\t\t<script scriptCode='");
   echo ($objCreator->Script ? $objCreator->Script->ScriptShort : "Latn");
   echo ("'>");
   echo ($objCreator->Script ? $objCreator->Script->ScriptEnglishLong : "Latin");
   echo ("</script>\n");
   echo ("\t\t</languageDeclaration>\n");
   echo ("\t\t<maintenanceHistory>\n\t\t\t<maintenanceEvent>\n");
   echo ("\t\t\t\t<eventType>revised</eventType>\n");
   echo ("\t\t\t\t<eventDateTime>Revision date is not currently tracked in the database.</eventDateTime>\n");
   echo ("\t\t\t\t<agentType>human</agentType>\n");
   echo ($objCreator->BiogHistAuthor ? "\t\t\t\t<agent>". bbcode_eac_encode($objCreator->getString('BiogHistAuthor', 0, false, false)) : "\t\t\t\t<agent>The name of the person who created or revised this record is not recorded.");
   echo ("</agent>\n");

   if ($objCreator->BiogHistAuthor)
   {
      echo ("\t\t\t\t<eventDescription>The ");
      switch ($objCreator->CreatorType)
      {
         case "Personal Name"|| "Unassigned" || "Name":
            echo ("biographical statment");
            break;

         case "Family Name":
            echo ("family history");
            break;

         case "Corporate Name":
            echo ("historical note");
            break;
      }
      echo (" included in this record was written by ". bbcode_eac_encode($objCreator->getString('BiogHistAuthor', 0, false, false)));
      echo ("</eventDescription>\n");
   }
   echo ("\t\t\t</maintenanceEvent>\n\t\t</maintenanceHistory>\n");

   if ($objCreator->Sources)
   {
      $arrSourceParagraphs = explode(NEWLINE, bbcode_eac_encode($objCreator->getString('Sources', 0, false, false)));

      if(!empty($arrSourceParagraphs))
      {
         echo ("\t\t<sources>\n\t\t\t<source>\n\t\t\t\t<descriptiveNote>");
         foreach($arrSourceParagraphs as $paragraph)
         {
            if(trim($paragraph))
            {
               echo("<p>" . preg_replace("/[ \\t]+/u", " ", $paragraph) . "</p>\n");
            }
         }
         echo("</descriptiveNote>\n\t\t\t</source>\n\t\t</sources>\n");
      }
   }

   echo ("\t</control>\n");
   echo ("\t<cpfDescription>\n");
   echo ("\t\t<identity>\n");
   echo ("\t\t\t<entityType>");
   switch ($objCreator->CreatorType)
   {
      case "Personal Name"|| "Unassigned" || "Name":
         echo ("person");
         break;

      case "Family Name":
         echo ("family");
         break;

      case "Corporate Name":
         echo ("corporateBody");
         break;
   }
   echo ("</entityType>\n");
   echo ("\t\t\t<nameEntry localType='nameAuthorized'>\n");
   echo ("\t\t\t\t<part>");

   if ($objCreator->CreatorRelationships && $objCreator->CreatorType == "Corporate Name")
   {
      foreach ($objCreator->CreatorRelationships as $objCreatorRelationship)
      {
         if ($objCreatorRelationship->CreatorRelationshipType =='hierarchical - parent')
         {
            echo (bbcode_eac_encode($objCreatorRelationship->getString('RelatedCreator', 0, false, false)) . ". ");
            break;
         }
      }
   }

   echo (bbcode_eac_encode($objCreator->getString('Name', 0, false, false)));
   echo ("</part>\n");
   echo ("\t\t\t</nameEntry>\n");

   if ($objCreator->NameVariants)
   {
      echo ("\t\t\t<nameEntry localType='nameVariant'>\n");
      echo ("\t\t\t\t<part>");
      echo (bbcode_eac_encode($objCreator->getString('NameVariants', 0, false, false)));
      echo ("</part>\n");
      echo ("\t\t\t</nameEntry>\n");
   }

   if ($objCreator->NameFullerForm)
   {
      echo ("\t\t\t<nameEntry localType='nameFullerForm'>\n");
      echo ("\t\t\t\t<part>");
      echo (bbcode_eac_encode($objCreator->getString('NameFullerForm', 0, false, false)));
      echo ("</part>\n");
      echo ("\t\t\t</nameEntry>\n");
   }
   echo ("\t\t</identity>\n");
   echo ("\t\t<description>\n");

   if ($objCreator->Dates)
   {
      echo ("\t\t\t<existDates>\n");
      $source = $objCreator->CreatorSource ? $objCreator->CreatorSource->getString('SourceAbbreviation') : 'local';
      echo ("\t\t\t\t<date localType='$source'>" . $objCreator->Dates. "</date>\n");
      echo ("\t\t\t</existDates>\n");
   }

   if($objCreator->BiogHist)
   {
      echo ("\t\t\t<biogHist>");

      $arrBiogHistParagraphs = explode(NEWLINE, bbcode_eac_encode($objCreator->getString('BiogHist', 0, false, false)));

      if(!empty($arrBiogHistParagraphs))
      {
         foreach($arrBiogHistParagraphs as $paragraph)
         {
            if(trim($paragraph))
            {
               echo("<p>\n\t\t\t\t" . preg_replace("/[ \\t]+/u", " ", $paragraph) . "</p>\n");
            }
         }
      }

      if ($objCreator->Sources)
      {
         echo ("\t\t\t\t<citation>");
         echo (bbcode_eac_encode($objCreator->getString('Sources', 0, false, false)));
         echo ("</citation>\n");
      }
      echo ("\t\t\t</biogHist>\n");
   }

   echo ("\t\t</description>\n");

   if ($objCreator->Collections || $objCreator->Books || $objCreator->DigitalContent || $objCreator->CreatorRelationships)
   {
      echo ("\t\t<relations>\n");
      foreach ($objCreator->CreatorRelationships as $objCreatorRelationship)
      {
         echo ("\t\t\t<cpfRelation cpfRelationType='" . $objCreatorRelationship->CreatorRelationshipType->toString() . "' xlink:type='simple' xlink:href='http://". $_SERVER['HTTP_HOST'] . $path. "index.php?p=creators/creator&amp;id=". $objCreatorRelationship->RelatedCreatorID."'>");
         echo ("\n\t\t\t\t<relationEntry>");
         echo (bbcode_eac_encode($objCreatorRelationship->getString('RelatedCreator', 0, false, false)));
         echo ("</relationEntry>");
         if ($objCreatorRelationship->Description)
         {
            echo ("\n\t\t\t\t<descriptiveNote>");

            $arrCreatorDescriptionParagraphs = explode(NEWLINE, bbcode_eac_encode($objCreatorRelationship->getString('Description', 0, false, false)));

            if(!empty($arrCreatorDescriptionParagraphs))
            {
               foreach($arrCreatorDescriptionParagraphs as $paragraph)
               {
                  if(trim($paragraph))
                  {
                     echo("<p>" . preg_replace("/[ \\t]+/u", " ", $paragraph) . "</p>\n");
                  }
               }
            }
            echo ("</descriptiveNote>" );
         }
         echo ("\n\t\t\t</cpfRelation>\n");

      }
      foreach ($objCreator->Collections as $objCollection)
      {
         echo ("\t\t\t<resourceRelation resourceRelationType='creatorOf' xlink:type='simple' xlink:href='http://". $_SERVER['HTTP_HOST'] . $path. "index.php?p=collections/controlcard&amp;id=" . $objCollection->ID . "'>\n");
         echo ("\t\t\t\t<relationEntry localType='");
         switch ($objCreator->CreatorType)
         {
            case "Personal Name"|| "Unassigned" || "Name":
               echo ("papers");
               break;

            case "Family Name":
               echo ("familyRecords");
               break;

            case "Corporate Name":
               echo ("archivalRecords");
               break;
         }
         echo  ("'>");
         echo (bbcode_eac_encode($objCollection->getString('Title', 0, false, false)));
         echo ("</relationEntry>\n");
         echo ("\t\t\t</resourceRelation>\n");
      }

      foreach ($objCreator->Accessions as $objAccession)
      {
         echo ("\t\t\t<resourceRelation resourceRelationType='creatorOf' xlink:type='simple' xlink:href='http://". $_SERVER['HTTP_HOST'] . $path. "index.php?p=accessions/accession&amp;id=" . $objAccession->ID . "'>\n");
         echo ("\t\t\t\t<relationEntry localType='unprocessedArchivalRecords'>");
         echo (bbcode_eac_encode($objAccession->getString('Title', 0, false, false)));
         echo ("</relationEntry>\n");
         echo ("\t\t\t</resourceRelation>\n");
      }


      foreach ($objCreator->Books as $objBook)
      {
         $objBook->dbLoadLanguages();
         echo ("\t\t\t<resourceRelation resourceRelationType='creatorOf' xlink:type='simple' xlink:href='http://". $_SERVER['HTTP_HOST'] . $path. "index.php?p=collections/bookcard&amp;id=" . $objBook->ID . "'>\n");
         echo ("\t\t\t\t<relationEntry localType='book'>");
         echo (bbcode_eac_encode($objBook->getString('Title', 0, false, false)));
         echo ("</relationEntry>\n");
         echo ("\t\t\t</resourceRelation>\n");
      }
      foreach ($objCreator->DigitalContent as $objDigitalContent)
      {
         echo ("\t\t\t<resourceRelation resourceRelationType='creatorOf' xlink:type='simple' xlink:href='http://". $_SERVER['HTTP_HOST'] . $path. "index.php?p=digitallibrary/digitalcontent&amp;id=" . $objDigitalContent->ID . "'>\n");
         echo ("\t\t\t\t<relationEntry localType='digitalObject'>");
         echo (bbcode_eac_encode($objDigitalContent->getString('Title', 0, false, false)));
         echo ("</relationEntry>\n");
         echo ("\t\t\t</resourceRelation>\n");
      }
      echo ("\t\t</relations>\n");
   }
   echo ("\t</cpfDescription>\n");
   echo ("</eac-cpf>");