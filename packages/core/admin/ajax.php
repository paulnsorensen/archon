<?php

/**
 * AJAX Interface
 *
 * This file provides the ability to get make ajax calls to get data
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Paul Sorensen
 */
isset($_ARCHON) or die();

ajax_ui_initialize();

// Determine what to do based upon user input
function ajax_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      return; //nothing to do
   }
   elseif($_REQUEST['f'] == 'getphrase')
   {
      ajax_ui_getphrase();
   }
   elseif($_REQUEST['f'] == 'searchlanguages')
   {
      ajax_ui_searchlanguages();
   }
   elseif($_REQUEST['f'] == 'searchcountries')
   {
      ajax_ui_searchcountries();
   }
   elseif($_REQUEST['f'] == 'searchscripts')
   {
      ajax_ui_searchscripts();
   }

//    elseif($_REQUEST['f'] == 'call')
//    {
//        ajax_ui_callfunction();
//    }
//    elseif($_REQUEST['f'] == 'object')
//    {
//        ajax_ui_getobjectdata();
//    }
}

function ajax_ui_getphrase()
{
   global $_ARCHON;

   header('Content-type: text/html; charset=UTF-8');

   $objPhrase = Phrase::getPhrase($_REQUEST['phrasename'], $_REQUEST['packageid'], $_REQUEST['moduleid'], $_REQUEST['phrasetypeid'], $_REQUEST['languageid']);

   if($objPhrase)
   {
      echo($objPhrase->getPhraseValue(ENCODE_HTML));
   }
   else
   {
      echo("Phrase not defined.");
   }
}

function ajax_ui_searchlanguages()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchLanguages', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function ajax_ui_searchcountries()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchCountries', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function ajax_ui_searchscripts()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchScripts', array('limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

//function check_request_variables($arrVarNames = array())
//{
//    foreach($arrVarNames as $name)
//    {
//        if(!isset($_REQUEST[$name]))
//        {
//            return false;
//        }
//    }
//    return true;
//}
//
//function ajax_ui_getobjectdata()
//{
//    global $_ARCHON;
//
//    // @TODO: Find a useful way to call permissions (by passing calling module)
//
//
//    if(!check_request_variables(array('class','id')))
//    {
//        $_ARCHON->declareError("AJAX call failed. Required variables not present.");
//        return false;
//    }
//
//    $obj = new $_REQUEST['class']($_REQUEST['id']);
//    $obj->dbLoad();
//
//    var_dump($obj);
//
//}
//
//function ajax_ui_callfunction()
//{
//    global $_ARCHON;
//
//    //echo("callfunction");
//
//    if(!check_request_variables(array('function','param')))
//    {
//        $_ARCHON->declareError("AJAX call failed. Required variables not present.");
//        return false;
//    }
//
//    $arrObjects = call_user_func_array(array($_ARCHON, $_REQUEST['function']), array($_REQUEST['param']));
//    foreach($arrObjects as $obj)
//    {
//        echo("<option value='{$obj->ID}'>{$obj->toString()}</option>\n");
//    }
//}
?>
