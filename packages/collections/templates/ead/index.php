<?php
/**
 * Template index for EAD template set
 *
 * Also defines pre and post processing functions
 *
 * @package Archon
 * @author Chris Rishel
 */

$TemplateIndex['Collection'] = "collection.inc.php";
$TemplateIndex['DefaultContent'] = "item.inc.php";


// These functions handle the <cxx> tags that EAD requires

function template_ContentPreProcess($output, $Item)
{
    global $EADCLevel, $EADContainers;

    if($Item['IntellectualLevel'])
    {
        $EADCLevel++;

        if(encoding_strlen($EADCLevel) == 1)
        {
            $EADCLevel = "0".$EADCLevel;
        }

        $output = str_replace("#EADCLevel#", $EADCLevel, $output);
    }

    return $output;
}


function template_ContentPostProcess($output, $Item)
{
    global $EADCLevel, $EADContainers;

    if($Item['IntellectualLevel'])
    {
        $output = str_replace("#EADCLevel#", $EADCLevel, $output);
        $EADCLevel--;
        if(encoding_strlen($EADCLevel) == 1)
        {
            $EADCLevel = "0".$EADCLevel;
        }
    }

    if($Item['PhysicalContainer'])
    {
        array_pop($EADContainers);
    }

    return $output;
}

?>