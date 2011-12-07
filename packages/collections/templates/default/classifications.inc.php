<?php
/**
 * Output file for browsing by classification
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();


echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

$arrClassifications = $_ARCHON->getChildClassifications($objClassification->ID);
$arrCollections = $_ARCHON->getCollectionsForClassification($objClassification->ID, true);


if ($objClassification->Description)
{
   echo("<div id='classificationdesc' class='mdround'>{$objClassification->getString('Description')}</div>");
    
}

if(!empty($arrClassifications))
{
	 if($objClassification->ID)
	{
	    $objSubgroupsUnderPhrase = Phrase::getPhrase('classifications_subgroupsunder', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
        $strSubgroupsUnder = $objSubgroupsUnderPhrase ? $objSubgroupsUnderPhrase->getPhraseValue(ENCODE_HTML) : 'Subgroups under $1';

		echo('<div class="listitemhead bold">' . str_replace('$1', $objClassification->toString(LINK_NONE, false, true, false, true, $_ARCHON->PublicInterface->Delimiter), $strSubgroupsUnder) . "</div><br/><br/>\n");
	}
	else
	{
	    $objBrowseRecordGroupsPhrase = Phrase::getPhrase('classifications_browserecordgroups', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
        $strBrowseRecordGroups = $objBrowseRecordGroupsPhrase ? $objBrowseRecordGroupsPhrase->getPhraseValue(ENCODE_HTML) : 'Browse Record Groups';

	    echo ("<div class='listitemhead bold'>$strBrowseRecordGroups</div><br/><br/>");
	}

	echo("<div id='classificationlist' class='bground'><div class='listitemcover'></div>");
    foreach($arrClassifications as $objChildClassification)
    {
        echo("<div class='listitem'>");
        echo($objChildClassification->toString(LINK_NONE, true, false, true, false, ' ') . ' ');
        echo($objChildClassification->toString(LINK_TOTAL, false, true) . "</div>\n");
    }
    echo('</div>');
}


if(!empty($arrCollections))
{
	$objRecordsFiledUnderPhrase = Phrase::getPhrase('classifications_recordsfiledunder', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strRecordsFiledUnder = $objRecordsFiledUnderPhrase ? $objRecordsFiledUnderPhrase->getPhraseValue(ENCODE_HTML) : 'Records filed under "$1"';
    $strRecordsFiledUnder = str_replace('$1', $objClassification->toString(LINK_NONE, false, true, false, false), $strRecordsFiledUnder);

	echo("<br/><div class='listitemhead bold'>$strRecordsFiledUnder</div><br/><br/>");
	echo("<div id='recordsunderlist' class='bground'><div class='listitemcover'></div>");
    foreach($arrCollections as $objCollection)
    {
        $output = "<div class='listitem'>";
        $output .= $objClassification->toString(LINK_NONE, true, false, true, false, " ") . ' ';
        $output .= $objCollection->toString(LINK_TOTAL, true);
        $output .= "</div>\n";

        echo($output);
    }
    echo('</div>');
}
elseif (empty($arrClassifications) && empty($arrCollections))
{
    $objGoBackPhrase = Phrase::getPhrase('classifications_goback', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strGoBack = $objGoBackPhrase ? $objGoBackPhrase->getPhraseValue(ENCODE_HTML) : 'Go Back';
    $objNoMaterialsPhrase = Phrase::getPhrase('classifications_nomaterials', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strNoMaterials = $objNoMaterialsPhrase ? $objNoMaterialsPhrase->getPhraseValue(ENCODE_HTML) : 'No materials are classified under $1';

	echo ("<span class='bold'><a href='?p=collections/classifications&amp;id=" . $objClassification->ParentID . "'>$strGoBack</a>");
	echo("<br/><br/>" . str_replace('$1', $objClassification->toString(LINK_NONE, false, true, false, false), $strNoMaterials));
}

