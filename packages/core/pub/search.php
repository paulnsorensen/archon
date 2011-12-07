<?php
/**
 * Output file for all searching
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();



$objSearchTitlePhrase = Phrase::getPhrase('search_title', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
$strSearchTitle = $objSearchTitlePhrase ? $objSearchTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Search Results';
$objDetailedSearchTitlePhrase = Phrase::getPhrase('search_detailedtitle', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
$strDetailedSearchTitle = $objDetailedSearchTitlePhrase ? $objDetailedSearchTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Search Results for "$1"';

// We need to add a navigation entry if q is NOT set.
// If q is set, it will be automatically added by the header.
if(!$_REQUEST['q'])
{
    $_ARCHON->PublicInterface->Title = $strSearchTitle;
    $_ARCHON->PublicInterface->addNavigation($strSearchTitle);
}
else
{
    //$_ARCHON->PublicInterface->Title .= ' for "' . encode($_REQUEST['q'], ENCODE_HTML) . '"';
    $_ARCHON->PublicInterface->Title = str_replace('$1', encode($_REQUEST['q'], ENCODE_HTML), $strDetailedSearchTitle);
}

require_once("header.inc.php");
echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

$in_LanguageID = $_REQUEST['languageid'] ? $_REQUEST['languageid'] : 0;

$in_SearchFlags = $_REQUEST['flags'] ? $_REQUEST['flags'] : 0;

$ResultCount = 0;


if($in_LanguageID)
{
    $objLanguage = New Language($in_LanguageID);

    if(!$objLanguage->dbLoad())
    {
        return;
    }

    $objResultsForLanguagePhrase = Phrase::getPhrase('search_resultsforlanguage', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strResultsForLanguage = $objResultsForLanguagePhrase ? $objResultsForLanguagePhrase->getPhraseValue(ENCODE_HTML) : 'Search Results for Language: $1';

    echo(str_replace('$1', $objLanguage->toString(LINK_NONE, true), $strResultsForLanguage) . "<br/><br/>\n");
}
elseif($_ARCHON->QueryString)
{
    $objSearchedForPhrase = Phrase::getPhrase('search_searchedfor', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strSearchedFor = $objSearchedForPhrase ? $objSearchedForPhrase->getPhraseValue(ENCODE_HTML) : 'You searched for "$1".';

    echo("<div class='listitemhead bold'>". str_replace('$1', encode($_ARCHON->QueryString, ENCODE_HTML), $strSearchedFor) . "</div><br/><br/>\n");
    
}

$arrPackages = $_ARCHON->Packages;

echo ("<div id='listitemwrapper' class='bground'>");
foreach($arrPackages as $ID => $objPackage)
{
    if(is_natural($ID) && $objPackage->APRCode != 'core' && file_exists("packages/$objPackage->APRCode/pub/search.php"))
    {
        require_once("packages/$objPackage->APRCode/pub/search.php");
    }
}

uasort($_ARCHON->PublicInterface->PublicSearchFunctions, create_function('$a,$b', 'return strnatcasecmp($a->DisplayOrder, $b->DisplayOrder);'));
foreach($_ARCHON->PublicInterface->PublicSearchFunctions as $FunctionName => $obj)
{
    call_user_func($FunctionName);
}
echo ("</div>");


if($ResultCount)
{
    $objSingularHitPhrase = Phrase::getPhrase('search_singularhit', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strSingularHit = $objSingularHitPhrase ? $objSingularHitPhrase->getPhraseValue(ENCODE_HTML) : "$1 Hit!  Click the links to show each category's results.";
    $objPluralHitPhrase = Phrase::getPhrase('search_pluralhit', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strPluralHit = $objPluralHitPhrase ? $objPluralHitPhrase->getPhraseValue(ENCODE_HTML) : "$1 Hits!  Click the links to show each category's results.";

    $strHit = $ResultCount != 1 ? $strPluralHit : $strSingularHit;

    echo("<br/><div class='listitemhead bold'>" . str_replace('$1', $ResultCount, $strHit) . "</div>\n");

    if($ResultCount >= CONFIG_CORE_SEARCH_RESULTS_LIMIT)
    {
        $objExcessResultsPhrase = Phrase::getPhrase('search_excessresults', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
        $strExcessResults = $objExcessResultsPhrase ? $objExcessResultsPhrase->getPhraseValue(ENCODE_HTML) : 'Your search yielded too many results.  Try adding more search terms!';

        echo("<br/><div class='listitemhead bold'>$strExcessResults</div>\n");
    }
}
else
{
    $objNoResultsPhrase = Phrase::getPhrase('search_noresults', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strNoResults = $objNoResultsPhrase ? $objNoResultsPhrase->getPhraseValue(ENCODE_HTML) : 'Your search did not return any results.  Please revise your query and try again.';

    echo("<br/><div class='listitemhead bold'>$strNoResults</div>\n");
}


echo ("<br/>");
require_once("footer.inc.php");