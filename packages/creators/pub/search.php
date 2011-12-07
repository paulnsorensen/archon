<?php
/**
 * Output file for searching
 *
 * @package Archon
 * @subpackage creators
 * @author Chris Rishel
 */

isset($_ARCHON) or die();


$objBiogHistPhrase = Phrase::getPhrase('search_bioghist', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
$strBiogHist = $objBiogHistPhrase ? $objBiogHistPhrase->getPhraseValue(ENCODE_HTML) : 'Biographical/Historical Note';
$objSearchForCreatorPhrase = Phrase::getPhrase('search_searchforcreator', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
$strSearchForCreator = $objSearchForCreatorPhrase ? $objSearchForCreatorPhrase->getPhraseValue(ENCODE_HTML) : 'Searching for Creator: $1';

$in_CreatorID = $_REQUEST['creatorid'] ? $_REQUEST['creatorid'] : 0;


if($in_CreatorID)
{
	$objCreator = New Creator($in_CreatorID);
/*

    if(!$objCreator->dbLoad())
    {
        return;
    }

    if($objCreator->Name)
    {
        echo("<h2>".$objCreator->Name . "</h2>\n");
    }

    
    if($objCreator->BiogHist)
    {
        echo("<div id='CreatorNote'>".$objCreator->getString('BiogHist') . "</div>\n");
    }
*/

    echo("<div id='CreatorResults'>".str_replace('$1', $objCreator->toString(), $strSearchForCreator) . "</div>");
}


function creators_search()
{
    global $_ARCHON, $in_SearchFlags, $ResultCount;
    global $in_CreatorID;

    
    $objCreatorDescriptionsPhrase = Phrase::getPhrase('search_creatordescriptions', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
    $strCreatorDescriptions = $objCreatorDescriptionsPhrase ? $objCreatorDescriptionsPhrase->getPhraseValue(ENCODE_HTML) : 'Creator Descriptions';
    $objMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_CREATORS, 0, PHRASETYPE_PUBLIC);
    $strMatches = $objMatchesPhrase ? $objMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';

    if($_ARCHON->QueryString && !$in_CreatorID && (!$in_SearchFlags || ($in_SearchFlags & SEARCH_CREATOR)))
    {
        $arrCreators = $_ARCHON->searchCreators($_ARCHON->QueryString);

        if(!empty($arrCreators))
        {
?>


<div class='searchTitleAndResults searchlistitem'>
  <span id='CreatorTitle'>
    <a href="#" onclick="toggleDisplay('Creator'); return false;"><img id="CreatorImage" src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif" alt="expand/collapse" /><?php echo(" ".$strCreatorDescriptions); ?></a>
  </span> (<span id='CreatorCount'><?php echo(count($arrCreators)); ?></span> <?php echo($strMatches); ?>)<br/>
  <dl id='CreatorResults' style='display: none;'>
<?php

            foreach($arrCreators as $objCreator)
            {
                echo("<dt>" . $objCreator->toString(LINK_TOTAL) . "</dt>\n");
                $ResultCount++;
            }
?>
  </dl>
</div>
<?php
        }
    }
}

$_ARCHON->addPublicSearchFunction('creators_search', 40);