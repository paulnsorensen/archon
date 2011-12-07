<?php
/**
 * Output file for MARC Records
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

require_once("packages/collections/lib/php-marc/php-marc.php");

$objBook = New Book($_REQUEST['id']);
$objBook->dbLoad();
$objBook->dbLoadRelatedObjects();
$objBook = map_recursive('strip_tags', $objBook);
//comment out the line above if you want the html tags from database fields passed through verbatim to the MARC record
$_ARCHON->PublicInterface->EscapeXML = false;

/*
if(isset($_REQUEST['raw']))
{
    $objCollection = map_recursive(create_function('$string', 'return encoding_convert_encoding($string, "iso-8859-1");'), $objCollection);
}
*/

if($_REQUEST['marc'])
{
    $objFile = New PHP_MARC_File(NULL);
    $objMARCRecord = $objFile->decode($_REQUEST['marc']);
    print $objMARCRecord->formatted();
    return;
}

if(!$objBook->ID)
{
    echo("Invalid Book!");
    return;
}

$objMARCRecord = New PHP_MARC();

$objMARCRecord->leader('00000npcaa        a 4500');

$fields['005']->Value = date('Y').date('m').date('d').date('h').date('i').date('s').".0";

$field8 = "010101i";


// Adriana says we don't need to specify the country code for
// archival records.
$field8 .= "xx";    //$field8 .= "ilu";

if(!empty($objBook->Languages))
{
    $field8 .= current($objBook->Languages)->LanguageShort;
}

$field8 .= "##";
$fields['008']->Value = $field8;


if($objBook->Creators)
{
    foreach($objBook->Creators as $objCreator)
    {
        if($objCreator->CreatorType->CreatorType == 'Personal Name' || $objCreator->CreatorType->CreatorType == 'Family Name')
        {
            if($objCreator->ID == $objBook->PrimaryCreator->ID)
            {
                $fieldnumber = '100';
            }
            else
            {
                $fieldnumber = '700';
            }

            $count[$fieldnumber]++;
            $fieldnumber .= ".".$count[$fieldnumber];

            if($objCreator->CreatorType == 'Personal Name')
            {
                $fields[$fieldnumber]->Indicator1 = 1;
            }
            else
            {
                $fields[$fieldnumber]->Indicator1 = 3;
            }

            if($objCreator->Name)
            {
                $fields[$fieldnumber]->SubFields['a'] = $objCreator->Name;
            }

            if($objCreator->NameFullerForm)
            {
                $fields[$fieldnumber]->SubFields['q'] = "({$objCreator->NameFullerForm})";
            }
            
            if($objCreator->Dates)
            {
                $fields[$fieldnumber]->SubFields['d'] = $objCreator->Dates;
            }

            if($fields[$fieldnumber]->SubFields['a'] && ($objCreator->ID == $objBook->PrimaryCreator->ID))
            {
                $creatorset = 1;
            }

            if($objCreator->BiogHist && ($objCreator->ID == $objBook->PrimaryCreator->ID))
            {
                $fields['545']->Indicator1 = 0;
                list($fields['545']->SubFields['a'], $fields['545']->SubFields['b']) = explode("\r\n\r\n", $objCreator->BiogHist);

                if(!$fields['545']->SubFields['b'])
                {
                    unset($fields['545']->SubFields['b']);
                }

                @ksort($fields['545']->SubFields);
            }
        }
        else if($objCreator->CreatorType->CreatorType == 'Corporate Name')
        {
            if($objCreator->ID == $objBook->PrimaryCreator->ID)
            {
                $fieldnumber = '110';
            }
            else
            {
                $fieldnumber = '710';
            }

            $count[$fieldnumber]++;
            $fieldnumber .= ".".$count[$fieldnumber];

            $fields[$fieldnumber]->Indicator1 = 2;

            if($objCreator->ParentBody)
            {
                $fields[$fieldnumber]->SubFields['a'] = $objCreator->ParentBody;

                if($objCreator->Name)
                {
                    $fields[$fieldnumber]->SubFields['b'] = $objCreator->Name;
                }
            }
            else if($objCreator->Name)
            {
                $fields[$fieldnumber]->SubFields['a'] = $objCreator->Name;
            }

            if($fields[$fieldnumber]->SubFields['a'] && ($objCreator->ID == $objBook->PrimaryCreator->ID))
            $creatorset = 1;

            if($objCreator->BiogHist && ($objCreator->ID == $objBook->PrimaryCreator->ID))
            {
                $fields['545']->Indicator1 = 1;
                list($fields['545']->SubFields['a'], $fields['545']->SubFields['b']) = explode("\r\n\r\n", $objCreator->BiogHist);

                if(!$fields['545']->SubFields['b'])
                {
                    unset($fields['545']->SubFields['b']);
                }
            }
        }
    }
}




if($objBook->Title)
{
    if($creatorset)
    {
        $fields['245']->Indicator1 = 0;
    }
    else
    {
        $fields['245']->Indicator1 = 1;
    }

    $fields['245']->Indicator2 = 0;

    $fields['245']->SubFields['a'] = $objBook->Title;


}

if($objBook->Edition)
{
    $fields['250']->SubFields['a'] = $objBook->Edition;
    
}



if($objBook->PublicationDate)
{
	$fields['362']->Indicator1 = 1;
    $fields['362']->Indicator2 = 0;
    $fields['362']->SubFields['a'] = $objBook->PublicationDate;
}



if($objBook->Notes)
{
    $fields['500']->SubFields['a'] = $objBook->Notes;
}

if($objBook->Subjects)
{
    foreach($objBook->Subjects as $objSubject)
    {
        $arrTraversal = $_ARCHON->traverseSubject($objSubject->ID);

        $topsubject = current($arrTraversal);

        $fieldnumber = $topsubject->SubjectType->EncodingAnalog;

        $count[$fieldnumber]++;
        $fieldnumber .= ".".$count[$fieldnumber];

        $fields[$fieldnumber]->SubFields['a'] = $topsubject->Subject;

        $fields[$fieldnumber]->Indicator1 = '#';

        if($topsubject->SubjectSourceID)
        {
            $fields[$fieldnumber]->Indicator2 = 0;
        }
        else
        {
            $fields[$fieldnumber]->Indicator2 = 7;
            $fields[$fieldnumber]->SubFields['2'] = "local";
        }

        array_shift($arrTraversal);

        foreach($arrTraversal as $id => $obj)
        {
            if($obj->SubjectType->SubjectType == "Genre/Form of Material")
            {
                $subfield = 'v';
            }
            else if($obj->SubjectType->SubjectType == "Date")
            {
                $subfield = 'y';
            }
            else if($obj->SubjectType->SubjectType == "Geographic Name")
            {
                $subfield = 'z';
            }
            else
            {
                $subfield = 'x';
            }

            $fields[$fieldnumber]->SubFields[$subfield][] = $obj->Subject;
        }
    }
}

$path = encoding_substr($_SERVER['SCRIPT_NAME'], 1, encoding_strrpos($_SERVER['SCRIPT_NAME'], "/"));


$fields['856']->Indicator1 = 4;
$fields['856']->Indicator2 = 2;
$fields['856']->SubFields['3'] = "Book Card";
$fields['856']->SubFields['u'] = "http://{$_SERVER['HTTP_HOST']}/{$path}?p=collections/bookcard&amp;id=$objBook->ID";

if($fields)
{
    ksort($fields);

    foreach($fields as $number => $obj)
    {
        if($obj->SubFields || $obj->Value)
        {
            if($obj->Value)
            {
                $field = new PHP_MARC_Field(encoding_substr($number, 0, 3), $obj->Value);
                $objMARCRecord->append_fields($field);
            }
            else
            {
                $field = new PHP_MARC_Field(encoding_substr($number, 0, 3), "{$obj->Indicator1}", "{$obj->Indicator2}", $obj->SubFields);
                $objMARCRecord->append_fields($field);
            }
        }
    }
}

if(isset($_REQUEST['raw']))
{
    $raw = $objMARCRecord->raw();

    $raw = str_replace("\r", '', $raw);
    
    header('Content-Disposition: attachment; filename="' . $objBook->Title . '.mrc"');
    echo(str_replace("\n", ' ', $raw));
    //echo($raw);
}
else
{
    

    $objMarcTitlePhrase = Phrase::getPhrase('marc_title', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strMarcTitle = $objMarcTitlePhrase ? $objMarcTitlePhrase->getPhraseValue(ENCODE_HTML) : '$1: MARC Record';
    $objMarcRecordPhrase = Phrase::getPhrase('marc_marcrecord', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strMarcRecord = $objMarcRecordPhrase ? $objMarcRecordPhrase->getPhraseValue(ENCODE_HTML) : 'MARC Record';
    $objRecordForBookPhrase = Phrase::getPhrase('marc_recordforBook', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strRecordForBook = $objRecordForBookPhrase ? $objRecordForBookPhrase->getPhraseValue(ENCODE_HTML) : 'MARC Record For Book: $1';
    $objRawMarcPhrase = Phrase::getPhrase('marc_rawmarc', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
    $strRawMarc = $objRawMarcPhrase ? $objRawMarcPhrase->getPhraseValue(ENCODE_HTML) : 'Raw MARC Output';
    
    $_ARCHON->PublicInterface->EscapeXML = CONFIG_ESCAPE_XML;
    
    $_ARCHON->PublicInterface->Title = str_replace('$1', $objBook->toString(), $strMarcTitle);
    $_ARCHON->PublicInterface->Title = $objBook->toString() . ": MARC Record";

  
    $_ARCHON->PublicInterface->addNavigation($objBook->getString('Title', 30), "?p=collections/bookcard&amp;id=$objBook->ID");
    $_ARCHON->PublicInterface->addNavigation($strMarcRecord, "?p={$_REQUEST['p']}&amp;id=$objBook->ID");

    require("header.inc.php");
    echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

    echo(str_replace('$1', $objBook->toString(), $strRecordForBook) . "<br/><br/>\n");
    echo("<pre>");
    echo(nl2br(encode($objMARCRecord->formatted(), ENCODE_HTML)));
    echo("</pre>");
    echo("<br/><a href='?p={$_REQUEST['p']}&amp;id=$objBook->ID&amp;raw=1'>$strRawMarc</a>");

    require("footer.inc.php");
}
?>