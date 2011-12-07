<?php

/**
 * Description of Country
 *
 * @author Paul Sorensen
 */
class Country extends JSONObject
{

   public static function searchCountries($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return self::_search('packages/core/lib/countries.json', __CLASS__, array('CountryName', 'ISOAlpha2', 'ISOAlpha3', 'ISONumeric3'), $SearchQuery, $Limit, $Offset);
   }



   public static function getAllCountries($ReturnList = false)
   {
      if(!$ReturnList)
      {
         return self::_getAll('packages/core/lib/countries.json', __CLASS__);
      }
      else
      {
         return self::_getArray('packages/core/lib/countries.json', 'CountryName');
      }
   }


   
   /**
    * Loads Country from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/core/lib/countries.json', $this);
   }




   /**
    * Returns the Country Name as a string
    *
    * @param string $Delimiter[optional]
    * @return string
    */
   public function toString()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not convert Country to string: Country ID not defined.");
         return false;
      }

      if(!$this->CountryName)
      {
         $this->dbLoad();
      }

      $String = $this->CountryName;

      return $String;
   }



   /**
    * @var string
    */
   public $CountryName = '';

   /**
    * @var string
    */
   public $ISOAlpha2 = '';

   /**
    * @var string
    */
   public $ISOAlpha3 = '';

   /**
    * @var string
    */
   public $ISONumeric3 = '';
}
?>
