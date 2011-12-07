<?php
/**
 * Description of EADElement
 *
 * @author Paul Sorensen
 */
class EADElement extends JSONObject
{

   public static function getEADElementIDFromString($String)
   {
      return self::_getIDFromString('packages/collections/lib/eadelements.json', __CLASS__, array('EADElement'), $String);
   }


   public static function getAllEADElements()
   {
      return self::_getAll('packages/collections/lib/eadelements.json', __CLASS__);
   }


   /**
    * Loads EADElement
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/collections/lib/eadelements.json', $this);

   }


   /**
    * Outputs EADElement as a string
    *
    * @magic
    * @return string
    */
   public function toString()
   {
      return $this->getString('EADElement');
   }


   /**
    * @var string
    */
   public $EADElement = NULL;

   /**
    * @var string
    */
   public $EADTag = NULL;

   /**
    * @string
    */
   public $TitleLocation = NULL;

   /**
    * @string
    */
   public $LineBreakTag = NULL;
}
?>
