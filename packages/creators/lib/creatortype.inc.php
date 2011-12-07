<?php
class CreatorType extends JSONObject
{

   public static function getCreatorTypeIDFromString($String)
   {
      return self::_getIDFromString('packages/creators/lib/creatortypes.json', __CLASS__, array('CreatorType'), $String);
   }

   

   public static function getAllCreatorTypes()
   {
      return self::_getAll('packages/creators/lib/creatortypes.json', __CLASS__);
   }

   /**
    * Loads CreatorType
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/creators/lib/creatortypes.json', $this);
   }



   /**
    * Outputs CreatorType as a string
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('CreatorType');
   }



   /**
    * @var string
    */
   public $CreatorType = '';
}

?>