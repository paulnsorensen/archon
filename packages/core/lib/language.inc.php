<?php

/**
 * Description of newlanguageinc
 *
 * @author psorens2
 */
class Language extends JSONObject
{

   public static function getLanguageIDFromString($String)
   {
      return self::_getIDFromString('packages/core/lib/languages.json', __CLASS__, array('LanguageLong', 'LanguageShort'), $String);
   }



   public static function searchLanguages($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return self::_search('packages/core/lib/languages.json', __CLASS__, array('LanguageLong'), $SearchQuery, $Limit, $Offset);
   }



   public static function getAllLanguages($ReturnList = false)
   {
      if(!$ReturnList)
      {
         return self::_getAll('packages/core/lib/languages.json', __CLASS__);
      }
      else
      {
         return self::_getArray('packages/core/lib/languages.json', 'LanguageLong');
      }

   }


   /**
    * Loads Language from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/core/lib/languages.json', $this);
   }

   /**
    * Generates a formatted string of the Language object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Language to string: Language ID not defined.");
         return false;
      }

      if(!$this->LanguageLong)
      {
         $this->dbLoad();
      }

      if($MakeIntoLink == LINK_NONE)
      {
         $String .= $this->getString('LanguageLong'). " [".$this->getString('LanguageShort'). "]";
      }
      else
      {
         if($_ARCHON->QueryStringURL)
         {
            $q = '&amp;q=' . $_ARCHON->QueryStringURL;
         }

         $String .= "<a href='?p=core/search&amp;languageid={$this->ID}$q'>{$this->getString('LanguageLong')}</a>";
      }
      
      return $String;
   }


   /** @var string */
   public $LanguageShort = '';

   /** @var string */
   public $LanguageLong = '';

   /** @var integer */
   public $DisplayOrder = 0;

}
?>
