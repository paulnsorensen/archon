<?php

/**
 * Description of DescriptiveRules
 *
 * @author Paul Sorensen
 */
class DescriptiveRules extends JSONObject
{

    public static function getAllDescriptiveRules()
    {
        return self::_getAll('packages/collections/lib/descriptiverules.json', __CLASS__);
    }

   /**
    * Loads DescriptiveRules
    *
    * @return boolean
    */
   public function dbLoad()
   {
      return $this->_load('packages/collections/lib/descriptiverules.json', $this);
   }



   /**
    * Outputs DescriptiveRules as string
    *
    * @return string
    */
   public function toString()
   {
      return $this->getString('DescriptiveRulesLong');
   }

   /**
    * @var string
    */
   public $DescriptiveRulesCode = NULL;

   /**
    * @var string
    */
   public $DescriptiveRulesLong = NULL;

}
?>
