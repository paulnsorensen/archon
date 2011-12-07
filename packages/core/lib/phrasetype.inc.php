<?php
class PhraseType extends JSONObject
{

   public static function getPhraseTypeIDFromString($String)
   {
      return self::_getIDFromString('packages/core/lib/phrasetypes.json', __CLASS__, array('PhraseType'), $String);
   }


   
   public static function getAllPhraseTypes()
   {
      return self::_getAll('packages/core/lib/phrasetypes.json', __CLASS__);
   }
   

   /**
    * Loads PhraseType
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/core/lib/phrasetypes.json', $this);
   }



   /**
    * Outputs Phrasetype as a string
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('PhraseType');
   }


   /** @var string */
   public $PhraseType = NULL;
}
?>