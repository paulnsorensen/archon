<?php
/**
 * Output file for browsing by collection
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();


   //
    
    if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['CollectionList'])
    {
        $_ARCHON->declareError("Could not list Collections: CollectionList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
    }

    echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

  //  if(!$_ARCHON->Error)
  //  {
        $arrCollections = $_ARCHON->getCollectionsForChar($in_Char, true, $_SESSION['Archon_RepositoryID'], array('ID', 'Title', 'SortTitle', 'ClassificationID', 'InclusiveDates', 'CollectionIdentifier'));
        echo($_ARCHON->clearError());
        
        if(!empty($arrCollections))
        {
            $objHoldingsBeginningWithPhrase = Phrase::getPhrase('collections_holdingsbeginningwithlist', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
            $strHoldingsBeginningWith = $objHoldingsBeginningWithPhrase ? $objHoldingsBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Holdings Beginning With "$1"';
            $strHoldingsBeginningWith = str_replace('$1', encoding_strtoupper($in_Char), $strHoldingsBeginningWith);
            
        	echo("<div class='listitemhead bold'>$strHoldingsBeginningWith</div><br/><br/>\n<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>\n");
            
            foreach($arrCollections as $objCollection)
            {
                eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['CollectionList']);
            }
            
            echo("</div>\n");
    //    }
    }
