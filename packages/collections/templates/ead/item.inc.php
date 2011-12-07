<?php
/**
 * Item template for finding aid output
 *
 * The variable:
 *
 *  $objContent
 *
 * is an instance of a CollectionContent object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the CollectionContent class definition in lib/collection.inc.php
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
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

global $EADContainers;

// Define some flags for User Defined Fields
// The 1st array value indicates where the User Defined Field should be included in EAD export

$tab_map['c'] = "\t\t";
$tab_map['did'] = "\t\t\t";
$tab_map['descgrp'] = "\t\t\t";
$tab_map['physdesc'] = "\t\t\t\t";

$udf['accessrestrict'] = 'descgrp';
$udf['accruals'] = 'descgrp';
$udf['acqinfo'] = 'descgrp';
$udf['altformavail'] = 'descgrp';
$udf['appraisal'] = 'descgrp';
$udf['custodhist'] = 'descgrp';
$udf['prefercite'] = 'descgrp';
$udf['processinfo'] = 'descgrp';
$udf['relatedmaterial'] = 'descgrp';
$udf['separatedmaterial'] = 'descgrp';
$udf['userestrict'] = 'descgrp';
$udf['originalsloc'] = 'descgrp';
$udf['odd'] = 'descgrp';

$udf['origination'] = 'did';
$udf['physdesc'] = 'did';
$udf['langmaterial'] = 'did';
$udf['materialspec'] = 'did';
$udf['note'] = 'did';
$udf['unitid'] = 'did';

$udf['dimensions'] = 'physdesc';
$udf['extent'] = 'physdesc';
$udf['physfacet'] = 'physdesc';

$udf['bioghist'] = 'c';
$udf['arrangement'] = 'c';


if(!empty($Content['UserFields']))
{   
   // Indicate where user fields are to be included in EAD and if they are expressed with head and paragraph tags or as label attributes

   foreach($Content['UserFields'] as $UserField)
   {

      if($UserField['Value'] && $UserField['EADElement'])
      {
         $tag = $UserField['EADTag'];
         $title = $UserField['Title'] ? bbcode_ead_encode($UserField['Title']) : $UserField['EADElement'];
         $wrapper=$udf[$tag];
         $indent = $tab_map[$wrapper];


         // Does title go in a title or a head?
         if($UserField['TitleLocation'] == 'label')
         {
            $label = "label=\"{$title}\"";
            $frag = $indent . "<" . $tag . " " . $label . ">";
         }
         elseif ($UserField['TitleLocation'] == 'head')
         {
            $frag = $indent . '<' . $tag . ">\n";
            $frag .= $indent. "\t" . '<head>' . $title . "</head>";
         }

         // Does the field contain paragraphs?
         if($UserField['LineBreakTag'] == 'p')
         { // paragraphs
            $arrValueParagraphs = explode(NEWLINE, bbcode_ead_encode($UserField['Value']));
            if(!empty($arrValueParagraphs))
            {
               $frag .= "\n";
               foreach($arrValueParagraphs as $paragraph)
               {
                  if(trim($paragraph))
                  {
                     $frag .= $indent. "\t" . '<p>' . preg_replace("/[ \\t]+/u", " ", $paragraph) . "</p>\n";
                  }
               }
            }
         }
         elseif($UserField['LineBreakTag'] == 'lb')
         {
            $frag .= str_replace(NEWLINE, '<lb/>', (preg_replace("/[ \\t]+/u", " ", bbcode_ead_encode($UserField['Value']))));
         }

         // Close element
         $frag .= $indent."</" . $tag . ">";

         $udf_contents[$wrapper][] = $frag;
      }
   }
}

if(isset($Content['DigitalContent']) && !empty($Content['DigitalContent']))
{
   $digContent = $Content['DigitalContent'];
   if(count($digContent) == 1)
   {
      $dc = reset($digContent);
      $dc_links = "\t\t<dao xlink:type=\"simple\" xlink:href=\"".$dc['ContentURL']."\" xlink:actuate=\"onRequest\" xlink:show=\"new\">\n";
      $dc_links .= "\t\t\t<daodesc>\n";
      $dc_links .= "\t\t\t<head>Title:</head>\n";
      $dc_links .= "\t\t\t\t<p>".$dc['Title']."</p>\n";
      $dc_links .= "\t\t\t</daodesc>\n";
      $dc_links .= "\t\t</dao>\n";
   }
   else
   {
      $dc_links = "\t\t<daogrp>\n";
      foreach($digContent as $arrDC)
      {
         $dc_links .= "\t\t\t<daoloc xlink:type=\"simple\" xlink:href=\"".$arrDC['ContentURL']."\" xlink:actuate=\"onRequest\" xlink:show=\"new\">\n";
         $dc_links .= "\t\t\t\t<daodesc>\n";
         $dc_links .= "\t\t\t\t<head>Title:</head>";
         $dc_links .= "\t\t\t\t\t<p>".$arrDC['Title']."</p>\n";
         $dc_links .= "\t\t\t\t</daodesc>\n";
         $dc_links .= "\t\t\t</daoloc>\n";
      }
      $dc_links .= "\t\t</daogrp>\n";

   }
}

if(!empty($Content['Creators']))
{
   $creators = "\t\t<origination label=\"Creator\" encodinganalog=\"245\$c\">\n";


   foreach($Content['Creators'] as $objCreator)
   {
      if($objCreator->CreatorType->CreatorType == 'Corporate Name')
      {
         $type = 'corp';
         $encodinganalog = '110';
         $string = bbcode_ead_encode($objCreator->getString('Name', 0, false, false));
         $normal = $string;
      }
      else
      {
         $encodinganalog = '100';
         $string = bbcode_ead_encode($objCreator->getString('Name', 0, false, false));

         if($objCreator->CreatorType->CreatorType == 'Personal Name')
         {
            $type = 'pers';
         }
         else
         {
            $type = 'fam';
         }

         $normal = $string;

         if($objCreator->Dates)
         {
            $string .= ", ".bbcode_ead_encode($objCreator->getString('Dates', 0, false, false));
         }
      }
      $source =  $objCreator->CreatorSource->getString('SourceAbbreviation');

      $creators .= "\t\t\t<{$type}name normal=\"{$normal}\" encodinganalog=\"{$encodinganalog}\" source=\"{$source}\" role=\"Collector\">{$string}</{$type}name>\n";

   }

   $creators .= "\t\t</origination>\n";

}




if(isset($Content['Subjects']) && !empty($Content['Subjects']))
{
   foreach ($Content['Subjects'] as $objSubject)
   {
      $arrTraversal = $_ARCHON->traverseSubject($objSubject->ID);
      $objParent = reset($arrTraversal);

      $arrEADSubjects[$objParent->SubjectType->ID][$objSubject->ID] = $objSubject->toString(LINK_NONE, true, ' -- ');
   }
   if(!empty($arrEADSubjects))
   {
      $subjects_str = "\t\t<controlaccess>\n";
      $subjects_str .= "\t\t\t<head>Access Terms</head>\n";

      $subjects_str .= "\t\t\t<p>This content is indexed under the following controlled access subject terms.</p>\n";
      $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();
      $arrSubjectSources = $_ARCHON->getAllSubjectSources();

      foreach($arrSubjectTypes as $objSubjectType)

         if(!empty($arrEADSubjects[$objSubjectType->ID]))
         {
            $subjects = $arrEADSubjects[$objSubjectType->ID];

            $subjects_str .= "\t\t\t<controlaccess>\n";
            $subjects_str .= "\t\t\t\t<head>".bbcode_ead_encode($objSubjectType->getString('SubjectType', 0, false, false))."</head>\n";

            @asort($subjects);
            @reset($subjects);

            foreach($subjects as $id => $subject)
            {
               $encodinganalog = $objSubjectType->EncodingAnalog ? " encodinganalog=\"".bbcode_ead_encode($objSubjectType->getString('EncodingAnalog', 0, false, false))."\"" : '';
               $source = $arrSubjectSources[$objCollection->Subjects[$id]->SubjectSourceID]->EADSource ? bbcode_ead_encode($arrSubjectSources[$objCollection->Subjects[$id]->SubjectSourceID]->getString('EADSource', 0, false, false)) : 'local';
               $tag=bbcode_ead_encode($objSubjectType->getString('EADType', 0, false, false));
               if(strpos($tag,'name'))
               {
                  $role=' role="subject"';
               } else
               {
                  $role='';
               }

               $subjects_str .= "\t\t\t\t<".bbcode_ead_encode($objSubjectType->getString('EADType', 0, false, false)) . $encodinganalog ." source=\"{$source}\" {$role}>{$subject}</".bbcode_ead_encode($objSubjectType->getString('EADType', 0, false, false)).">\n";
            }

            $subjects_str .= "\t\t\t</controlaccess>\n";
         }
      $subjects_str .= "\t\t</controlaccess>\n";

   }
}


if($Content['PhysicalContainer'])
{
   $EADContainers[] = "\t<container type=\"{$Content['LevelContainer']}\">" . $Content['LevelContainerIdentifier'] . "</container>\n";
}

if(!$Content['IntellectualLevel'])
{
   ?>
#CONTENT#
   <?php
}
else
{
   ?>
<c#EADCLevel# level="<?php echo($Content['EADLevel']); ?>">
   <did>
      <unitid label="ArchonID" audience="internal">id<?php echo($Content['ID']); ?></unitid>
         <?php


         if(!empty($EADContainers))
         {
            foreach($EADContainers as $container)
            {
               echo($container);
            }
         }


         if($Content['Title'])
         {
            ?>
      <unittitle><?php echo(bbcode_ead_encode($Content['Title'])); ?></unittitle>
            <?php
         }
         if($Content['PrivateTitle'])
         {
            ?>
      <unittitle label="Private" audience="internal"><?php echo(bbcode_ead_encode($Content['PrivateTitle'])); ?></unittitle>
            <?php
         }

         if($Content['Date'])
         {
            ?>
      <unitdate><?php echo($Content['Date']); ?></unitdate>
            <?php
         }

         if(isset($dc_links))
         {
            echo($dc_links);
            unset($dc_links);
         }

         if(isset($creators))
         {
            echo($creators);
            unset($creators);
         }

         if(isset($udf_contents['did']))
         {
            echo(implode("\n", $udf_contents['did']). "\n");
            unset($udf_contents['did']);
         }

         if(isset($udf_contents['physdesc']))
         {
            echo("\t\t" . "<physdesc>\n");
            echo(implode("\n", $udf_contents['physdesc']) . "\n");
            echo("\t\t" . "</physdesc>\n");
            unset($udf_contents['physdesc']);
         }

         ?>
   </did>
      <?php

      if(isset($subjects_str))
      {
         echo($subjects_str);
         unset($subjects_str);
      }

      if(isset($udf_contents['descgrp']))
      {
         echo("\t" . "<descgrp>\n");
         echo(implode("\n", $udf_contents['descgrp']). "\n");
         echo("\t" . "</descgrp>\n");
         unset($udf_contents['descgrp']);
      }

      if(isset($udf_contents['c']))
      {
         echo(implode("\n", $udf_contents['c']) ."\n");
         unset($udf_contents['c']);
      }

      if($Content['Description'])
      {
         ?>
   <scopecontent>
      <head>Scope and Contents</head>
            <?php
            $arrScopeParagraphs = explode(NEWLINE, bbcode_ead_encode($Content['Description']));
            foreach($arrScopeParagraphs as $paragraph)
            {
               if(trim($paragraph))
               {
                  ?>
      <p><?php echo(trim(strip_tags($paragraph))); ?></p>
                  <?php
               }
            }
            ?>
   </scopecontent>
         <?php
      }


      ?>
   #CONTENT#
</c#EADCLevel#>
   <?php
}

?>
