<?php

/**
 * Description of CreatorRelationshipType
 *
 * @author Paul Sorensen
 */
class CreatorRelationshipType extends JSONObject
{
   public static function getAllCreatorRelationshipTypes()
   {
      return self::_getAll('packages/creators/lib/creatorrelationshiptypes.json', __CLASS__);
   }

   /**
    * Loads CreatorRelationshipType
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/creators/lib/creatorrelationshiptypes.json', $this);
   }



   /**
    * Outputs CreatorRelationshipType as a string
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('CreatorRelationshipType');
   }
   

   public $CreatorRelationshipType;
}



?>
