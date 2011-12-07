<?php
class SubjectType extends JSONObject
{

   public static function getSubjectTypeIDFromString($String)
   {
      return self::_getIDFromString('packages/subjects/lib/subjecttypes.json', __CLASS__, array('SubjectType'), $String);
   }



   public static function searchSubjectTypes($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return self::_search('packages/subjects/lib/subjecttypes.json', __CLASS__, array('SubjectType'), $SearchQuery, $Limit, $Offset);
   }



   public static function getAllSubjectTypes()
   {
      return self::_getAll('packages/subjects/lib/subjecttypes.json', __CLASS__);
   }



   /**
    * Loads SubjectType from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {

      return $this->_load('packages/subjects/lib/subjecttypes.json', $this);

   }

   

   /**
    * Outputs SubjectType if SubjectType is cast to string
    *
    * @magic
    * @return string
    */
   public function toString()
   {
      return $this->getString('SubjectType');
   }

   /**
    * @var string
    */
   public $SubjectType = '';

   /**
    * @var string
    */
   public $EADType = '';

   /**
    * @var string
    */
   public $EncodingAnalog = '';
}

?>