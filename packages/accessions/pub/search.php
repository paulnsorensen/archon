<?php
/**
 * Output file for searching
 *
 * @package Archon
 * @subpackage accessions
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

$in_ClassificationID = $_REQUEST['classificationid'] ? $_REQUEST['classificationid'] : 0;
$in_CollectionID = $_REQUEST['collectionid'] ? $_REQUEST['collectionid'] : 0;
$in_SubjectID = $_REQUEST['subjectid'] ? $_REQUEST['subjectid'] : 0;
$in_CreatorID = $_REQUEST['creatorid'] ? $_REQUEST['creatorid'] : 0;

function accessions_search()
{
    global $_ARCHON, $in_SearchFlags, $ResultCount;
    global $in_ClassificationID, $in_CollectionID, $in_SubjectID, $in_CreatorID;
    
    

    $objUnprocessedMaterialsPhrase = Phrase::getPhrase('search_unprocessedmaterials', PACKAGE_ACCESSIONS, 0, PHRASETYPE_PUBLIC);
    $strUnprocessedMaterials = $objUnprocessedMaterialsPhrase ? $objUnprocessedMaterialsPhrase->getPhraseValue(ENCODE_HTML) : 'Unprocessed Materials';
    $objMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_ACCESSIONS, 0, PHRASETYPE_PUBLIC);
    $strMatches = $objMatchesPhrase ? $objMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';

    if(($_ARCHON->QueryString || $in_SubjectID || $in_CreatorID || $in_ClassificationID || $in_CollectionID) && (!$in_SearchFlags || ($in_SearchFlags & SEARCH_ACCESSIONS)))
    {
        $SearchFlags = $in_SearchFlags ? $in_SearchFlags : SEARCH_ACCESSIONS;
        
        $arrAccessions = $_ARCHON->searchAccessions($_ARCHON->QueryString, $SearchFlags,$in_ClassificationID, $in_CollectionID, $in_SubjectID, $in_CreatorID, CONFIG_CORE_SEARCH_RESULTS_LIMIT, 0);

        $arrDisplayAccessions = array();
        
        if (!$_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ))  //show only unprocessed accessions to public
        {
            foreach($arrAccessions as $ID => $objAccession)
            {
                if($objAccession->UnprocessedExtent > 0)
                {
                    $arrDisplayAccessions[$ID] = $objAccession;
                }        
            }   
        }
        else  //show all accessions for authenticated users
        { 
            foreach($arrAccessions as $ID => $objAccession)
            { 
                  $arrDisplayAccessions[$ID] = $objAccession;
            }   
        }
           
        if(!empty($arrDisplayAccessions))
        {
?>
<div class='searchTitleAndResults searchlistitem'>
  <span id='AccessionTitle'>
    <a href="#" onclick="toggleDisplay('Accession'); return false;"><img id="AccessionImage" src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif" alt="expand/collapse" /><?php echo(" ".$strUnprocessedMaterials); if ($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONS, READ)){echo (" and Processed Accessions");}?></a>
  </span> (<span id='AccessionCount'><?php echo(count($arrDisplayAccessions)); ?></span> <?php echo($strMatches); ?>)<br/>
  <dl id='AccessionResults' style='display: none;'>
<?php

            foreach($arrDisplayAccessions as $objAccession)
            {

                echo("<dt>" . $objAccession->toString(LINK_EACH) . "</dt>\n");
                $ResultCount++;
            }
?>
  </dl>
</div>
<?php
        }
    }
}

$_ARCHON->addPublicSearchFunction('accessions_search', 30);
