<?php

/**
 * Description of Script
 *
 * @author Paul Sorensen
 */
class Script extends JSONObject
{

   public static function searchScripts($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return self::_search('packages/core/lib/scripts.json', __CLASS__, array('ScriptEnglishLong'), $SearchQuery, $Limit, $Offset);
   }


   public static function getAllScripts($ReturnList = false)
   {
      if(!$ReturnList)
      {
         return self::_getAll('packages/core/lib/scripts.json', __CLASS__);
      }
      else
      {
         return self::_getArray('packages/core/lib/scripts.json', 'ScriptEnglishLong');
      }
   }


   /**
    * Loads Script from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/core/lib/scripts.json', $this);
   }


   /**
    * Outputs Script if Script is cast to string
    *
    * @magic
    * @return string
    */
   public function toString()
   {
      return $this->getString('ScriptEnglishLong');
   }

   
   /**
    * @var string
    */
   public $ScriptEnglishLong = '';

   /**
    * @var string
    */
   public $ScriptShort = '';

   /**
    * @var string
    */
   public $ScriptFrenchLong = '';

   /** @var integer */
   public $ScriptCode = 0;

}
?>
