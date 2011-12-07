<?php
/**
 * Output file for browsing by subject
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();



$in_ID = isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL;
$in_Char = isset($_REQUEST['char']) ? $_REQUEST['char'] : NULL;
$in_SubjectTypeID = isset($_REQUEST['subjecttypeid']) ? $_REQUEST['subjecttypeid'] : 0;
$in_Browse = isset($_REQUEST['browse']) ? true : false;



$objSubjectsTitlePhrase = Phrase::getPhrase('subjects_title', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
$strSubjectsTitle = $objSubjectsTitlePhrase ? $objSubjectsTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Browse by Subject';

$_ARCHON->PublicInterface->Title = $strSubjectsTitle;
$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}");


if($in_SubjectTypeID)
{
   $objSubjectType = New SubjectType($in_SubjectTypeID);
   $objSubjectType->dbLoad();
   $_ARCHON->PublicInterface->addNavigation(str_replace('$1', $objSubjectType->toString(), $strOfType), "?p={$_REQUEST['p']}&amp;subjecttypeid=$in_SubjectTypeID");
}

if($in_ID)
{
   subjects_listChildSubjects($in_ID);
}
elseif($in_Char)
{
   subjects_listSubjectsForChar($in_Char, $in_SubjectTypeID);
}
elseif($in_Browse)
{
   $in_Page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

   subjects_listAllSubjects($in_Page, $in_SubjectTypeID);

}
else
{
   subjects_main($in_SubjectTypeID);
}


function subjects_main($SubjectTypeID)
{
   global $_ARCHON;


   

   $objOfTypePhrase = Phrase::getPhrase('subjects_oftype', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
   $strOfType = $objOfTypePhrase ? $objOfTypePhrase->getPhraseValue(ENCODE_HTML) : 'Of Type "$1"';


   $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';


   $arrSubjectCount = $_ARCHON->countSubjects(true, $SubjectTypeID);
   $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();

   require_once("header.inc.php");

   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

   if($arrSubjectTypes[$SubjectTypeID])
   {
      $objTypedShowBeginningWithPhrase = Phrase::getPhrase('subjects_typedshowbeginningwith', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
      $strTypedShowBeginningWith = $objTypedShowBeginningWithPhrase ? $objTypedShowBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Show "$1" Subjects Beginning with';
      $strTypedShowBeginningWith = str_replace('$1', $arrSubjectTypes[$SubjectTypeID]->toString(), $strTypedShowBeginningWith);

      echo("<div class='center'><span class='bold'>$strTypedShowBeginningWith:</span><br/><br/><div class='bground beginningwith'>");
   }
   else
   {
      $objShowBeginningWithPhrase = Phrase::getPhrase('subjects_showbeginningwith', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
      $strShowBeginningWith = $objShowBeginningWithPhrase ? $objShowBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Show Subjects Beginning with';

      echo("<div class='center'><span class='bold'>$strShowBeginningWith:</span><br/><br/><div class='bground beginningwith'>");
   }

   if(!empty($arrSubjectCount['#']))
   {
      $href = "?p={$_REQUEST['p']}&amp;char=" . urlencode('#');
      if($SubjectTypeID)
      {
         $href .= "&amp;subjecttypeid=$SubjectTypeID";
      }
      echo("<a href='$href'>-#-</a>" . INDENT);
   }
   else
   {
      echo("-#-" . INDENT);
   }

   for($i = 65; $i < 91; $i++)
   {
      $char = chr($i);

      if(!empty($arrSubjectCount[encoding_strtolower($char)]))
      {
         $href = "?p={$_REQUEST['p']}&amp;char=$char";
         if($SubjectTypeID)
         {
            $href .= "&amp;subjecttypeid=$SubjectTypeID";
         }
         echo("<a href='$href'>-$char-</a>" . INDENT);
      }
      else
      {
         echo("-$char-" . INDENT);
      }

      if($char == 'M')
      {
         echo("<br/><br/>\n");
      }
   }
   echo("<br/><br/><a href='?p={$_REQUEST['p']}&amp;browse&amp;subjecttypeid={$SubjectTypeID}'>{$strViewAll}</a>");

   $objFilterByPhrase = Phrase::getPhrase('subjects_filterby', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
   $strFilterBy = $objFilterByPhrase ? $objFilterByPhrase->getPhraseValue(ENCODE_HTML) : 'Filter Subjects by';

   ?>
</div><br/>
<span class='bold'><?php echo($strFilterBy); ?>:</span>
<br/><br/>
   <?php
   if(!empty($arrSubjectTypes))
   {
      foreach($arrSubjectTypes as $objSubjectType)
      {
         if($objSubjectType->ID != $SubjectTypeID)
         {
            echo("<a href='?p={$_REQUEST['p']}&amp;subjecttypeid=$objSubjectType->ID'>" . $objSubjectType->toString() . "</a><br/>");
         }
         else
         {
            echo("{$objSubjectType->toString()}<br/>");
         }
      }
   }
   ?>
</div>
   <?php
}


function subjects_listChildSubjects($ID)
{
   global $_ARCHON;

   
 
   $objSubject = New Subject($ID);
   $objSubject->dbLoad();

   $objSubTermHeaderPhrase = Phrase::getPhrase('subjects_subtermheader', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
   $strSubTermHeader = $objSubTermHeaderPhrase ? $objSubTermHeaderPhrase->getPhraseValue(ENCODE_HTML) : 'Sub-Terms Under $1';
   $strSubTermHeader = str_replace('$1', $objSubject->toString(LINK_NONE, true, $_ARCHON->PublicInterface->Delimiter), $strSubTermHeader);
   $objRelatedRecordsPhrase = Phrase::getPhrase('subjects_relatedrecords', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
   $strRelatedRecords = $objRelatedRecordsPhrase ? $objRelatedRecordsPhrase->getPhraseValue(ENCODE_HTML) : 'Related Records';


   $_ARCHON->PublicInterface->addNavigation($objSubject->toString(LINK_EACH, true, $_ARCHON->PublicInterface->Delimiter), "?p={$_REQUEST['p']}&amp;id=$ID");
   $arrSubjects = $_ARCHON->getChildSubjects($ID);


   if(empty($arrSubjects))
   {
      header("Location: index.php?p=core/search&subjectid=$ID");
   }else
   {
      require_once("header.inc.php");

      if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['SubjectList'])
      {
         $_ARCHON->declareError("Could not list Subjects: SubjectList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
      }

      echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");
      echo("<div class='listitemhead bold'>$strSubTermHeader:</div><br/><br/>\n");
      echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");
      echo("<span class='small' style='margin-left:.5em'>(<a href='?p=core/search&amp;subjectid=$ID'>$strRelatedRecords</a>)</span>");
      echo("<br/><br/>\n");


      foreach($arrSubjects as $objSubject)
      {
         eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['SubjectList']);
      }
      echo ("</div>");
   }

}


function subjects_listSubjectsForChar($Char, $SubjectTypeID)
{
   global $_ARCHON;


   

   $objTypedBeginningWithPhrase = Phrase::getPhrase('subjects_typedbeginningwith', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
   $strTypedBeginningWith = $objTypedBeginningWithPhrase ? $objTypedBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Of Type "$1" Beginning with "$2"';
   $objSubjectsBeginningWithPhrase = Phrase::getPhrase('subjects_subjectsbeginningwith', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
   $strSubjectsBeginningWith = $objSubjectsBeginningWithPhrase ? $objSubjectsBeginningWithPhrase->getPhraseValue(ENCODE_HTML) : 'Beginning with "$1"';


   $arrSubjectTypes = $_ARCHON->getAllSubjectTypes();


   if($SubjectTypeID)
   {
      $_ARCHON->PublicInterface->addNavigation(str_replace(array('$1', '$2'), array($arrSubjectTypes[$SubjectTypeID]->toString(), encoding_strtoupper($Char)), $strTypedBeginningWith), "?p={$_REQUEST['p']}&amp;subjecttypeid=$SubjectTypeID&amp;char=$Char");
   }
   else
   {
      $_ARCHON->PublicInterface->addNavigation(str_replace('$1', encoding_strtoupper($Char), $strSubjectsBeginningWith), "?p={$_REQUEST['p']}&amp;char=$Char");
   }


   require_once("header.inc.php");

   if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['SubjectList'])
   {
      $_ARCHON->declareError("Could not list Subjects: SubjectList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }


   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");


   $arrSubjects = $_ARCHON->getSubjectsForChar($Char, $SubjectTypeID);

   if(!empty($arrSubjects))
   {
      if($arrSubjectTypes[$SubjectTypeID])
      {
         $objTypedBeginningWithHeaderPhrase = Phrase::getPhrase('subjects_typedbeginningwithheader', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
         $strTypedBeginningWithHeader = $objTypedBeginningWithHeaderPhrase ? $objTypedBeginningWithHeaderPhrase->getPhraseValue(ENCODE_HTML) : '"$1" Subjects Beginning with "$2"';
         $strTypedBeginningWithHeader = str_replace(array('$1', '$2'), array($arrSubjectTypes[$SubjectTypeID]->toString(), encoding_strtoupper($Char)), $strTypedBeginningWithHeader);

         echo("<div class='listitemhead bold'>$strTypedBeginningWithHeader</div><br/><br/>\n");
      }
      else
      {
         $objSubjectsBeginningWithHeaderPhrase = Phrase::getPhrase('subjects_subjectsbeginningwithheader', PACKAGE_SUBJECTS, 0, PHRASETYPE_PUBLIC);
         $strSubjectsBeginningWithHeader = $objSubjectsBeginningWithHeaderPhrase ? $objSubjectsBeginningWithHeaderPhrase->getPhraseValue(ENCODE_HTML) : 'Subjects Beginning with "$1"';
         $strSubjectsBeginningWithHeader = str_replace('$1', encoding_strtoupper($Char), $strSubjectsBeginningWithHeader);

         echo("<div class='listitemhead bold'>$strSubjectsBeginningWithHeader</div><br/><br/>\n");
      }

      echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");

      foreach($arrSubjects as $objSubject)
      {
         eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['SubjectList']);
      }

      echo("</div>");
   }

}

function subjects_listAllSubjects($Page, $SubjectTypeID)
{
   global $_ARCHON;

   $arrSubjects = $_ARCHON->searchSubjects($_REQUEST['q'], NULL, $SubjectTypeID, CONFIG_CORE_PAGINATION_LIMIT + 1, ($Page-1)*CONFIG_CORE_PAGINATION_LIMIT);

   if(count($arrSubjects) > CONFIG_CORE_PAGINATION_LIMIT)
   {
      $morePages = true;
      array_pop($arrSubjects);
   }

// Set up a URL for any prev/next buttons or in case $Page
// is too high
   $paginationURL = 'index.php?p=' . $_REQUEST['p'].'&browse&subjecttypeid='.$SubjectTypeID;

   if(empty($arrSubjects) && $Page != 1)
   {
      header("Location: $paginationURL");
   }

   

   $objViewAllPhrase = Phrase::getPhrase('viewall', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
   $strViewAll = $objViewAllPhrase ? $objViewAllPhrase->getPhraseValue(ENCODE_HTML) : 'View All';

   $_ARCHON->PublicInterface->addNavigation($strViewAll);

   require_once("header.inc.php");

   if(!$_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['SubjectList'])
   {
      $_ARCHON->declareError("Could not list Subjects: SubjectList template not defined for template set {$_ARCHON->PublicInterface->TemplateSet}.");
   }

   echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");


   if(!$_ARCHON->Error)
   {
      if(!empty($arrSubjects))
      {
         echo("<div class='listitemhead bold'>$strViewAll</div><br/><br/>\n");
         echo("<div id='listitemwrapper' class='bground'><div class='listitemcover'></div>");

         foreach($arrSubjects as $objSubject)
         {
            eval($_ARCHON->PublicInterface->Templates[$_ARCHON->Package->APRCode]['SubjectList']);
         }

         echo("</div>");
      }

      if($Page > 1 || $morePages)
      {
         echo("<div class='paginationnav'>");

         if($Page > 1)
         {
            $prevPage = $Page - 1;
            $prevURL = encode($paginationURL . "&page=$prevPage", ENCODE_HTML);
            echo("<span class='paginationprevlink'><a href='$prevURL'>Prev</a></span>");
         }
         if($morePages)
         {
            $nextPage = $Page + 1;
            $nextURL = encode($paginationURL . "&page=$nextPage", ENCODE_HTML);
            echo("<span class='paginationnextlink'><a href='$nextURL'>Next</a></span>");
         }
         echo("</div>");
      }
   }
}


require_once("footer.inc.php");

?>
