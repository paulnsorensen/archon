<?php
/**
 * Output file for searching
 *
 * @package Archon
 * @subpackage subjects
 * @author Chris Rishel
 */

isset($_ARCHON) or die();


    
$objSearchForSubjectPhrase = Phrase::getPhrase('search_searchforsubject', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
$strSearchForSubject = $objSearchForSubjectPhrase ? $objSearchForSubjectPhrase->getPhraseValue(ENCODE_HTML) : 'Searching for Subject: $1';

$in_SubjectID = $_REQUEST['subjectid'] ? $_REQUEST['subjectid'] : NULL;

if($in_SubjectID)
{
    $objSubject = New Subject($in_SubjectID);

    if(!$objSubject->dbLoad())
    {
        return;
    }

    echo("<div class='listitemhead bold'>" . str_replace('$1', $objSubject->toString(LINK_NONE, true), $strSearchForSubject) . "</div><br/><br/>\n");
}


function subjects_search()
{
    global $_ARCHON, $in_SearchFlags, $ResultCount;
    global $in_SubjectID;
    
    
    
    $objSubjectHeadingsPhrase = Phrase::getPhrase('search_subjectheadings', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
    $strSubjectHeadings = $objSubjectHeadingsPhrase ? $objSubjectHeadingsPhrase->getPhraseValue(ENCODE_HTML) : 'Subject Headings';
    $objMatchesPhrase = Phrase::getPhrase('search_matches', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
    $strMatches = $objMatchesPhrase ? $objMatchesPhrase->getPhraseValue(ENCODE_HTML) : 'Matches';
    
    if(($_ARCHON->QueryString || $in_SubjectID) && (!$in_SearchFlags || ($in_SearchFlags & SEARCH_SUBJECTS)))
    {
        $arrSubjects = $_ARCHON->searchSubjects($_ARCHON->QueryString, $in_SubjectID, 0, true);
    
        if(!empty($arrSubjects))
        {
?>
<div class='searchTitleAndResults searchlistitem'>
  <span id='SubjectTitle'>
    <a href="#" onclick="toggleDisplay('Subject'); return false;"><img id="SubjectImage" src="<?php echo($_ARCHON->PublicInterface->ImagePath); ?>/plus.gif" alt="expand/collapse" /><?php echo(" ".$strSubjectHeadings); ?></a>
  </span> (<span id='SubjectCount'><?php echo(count($arrSubjects)); ?></span> <?php echo($strMatches); ?>)<br/>
<dl id='SubjectResults' style='display: none;'>
<?php
    
            foreach($arrSubjects as $objSubject)
            {
                echo("<dt>" . $objSubject->toString(LINK_TOTAL, true) . "</dt>\n");
                $ResultCount++;
            }
?>
</dl>
</div>
<?php
        }
    }
}

$_ARCHON->addPublicSearchFunction('subjects_search', 50);
?>