<?php

/**
 * Description of JSONObject
 *
 * @author Paul Sorensen
 */
abstract class JSONObject extends AObject
{

   private static function _getJSONArray($Filename)
   {
      static $JSONCache = array();

      if(!isset($JSONCache[$Filename]))
      {
         $JSONCache[$Filename] = self::_loadJSON($Filename);
      }

      return $JSONCache[$Filename];
   }

   private static function _loadJSON($Filename)
   {
      global $_ARCHON;

      $Filename = $_ARCHON->RootDirectory . "/" . $Filename;

      if(file_exists($Filename))
      {
         $json = json_decode(file_get_contents($Filename), true);

         if(!$json)
         {
            $_ARCHON->declareError('JSON file invalid');
         }

         return $json;
      }
      $_ARCHON->declareError("JSON file $Filename does not exist");

      return array(); //return empty array if file doesn't exist
   }

   //todo make this look for phrases somehow too
   protected function _load($Filename, $Object)
   {
      global $_ARCHON;

      $arrJSON = self::_getJSONArray($Filename);

      if(empty($arrJSON))
      {
         $_ARCHON->declareError('failed to load JSON file');
         return false;
      }

      $vars = get_object_vars($Object);

      if(!empty($vars))
      {
         foreach($vars as $VariableName => $DefaultValue)
         {
            if(isset($arrJSON[$this->ID][$VariableName]))
            {
               $this->$VariableName = $arrJSON[$this->ID][$VariableName];
            }
         }
      }

      return true;
   }

   protected static function _getAll($Filename, $Classname)
   {
      $arrJSON = self::_getJSONArray($Filename);

      if(empty($arrJSON))
      {
         return false;
      }

      $arrObjects = array();

      foreach($arrJSON as $ID => $objJSON)
      {
         $arrObjects[$ID] = New $Classname($objJSON);
      }

      return $arrObjects;
   }

   protected static function _getArray($Filename, $KeyName)
   {
      $arrJSON = self::_getJSONArray($Filename);

      if(empty($arrJSON))
      {
         return false;
      }

      // check first element for the value before iteration through whole array
      if(!array_key_exists($KeyName, reset($arrJSON)))
      {
         return false;
      }

      $arrValues = array();

      foreach($arrJSON as $ID => $objJSON)
      {
         $arrValues[$ID] = $objJSON[$KeyName];
      }

      return $arrValues;
   }

   protected static function _search($Filename, $Classname, $KeyNames, $SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      global $_ARCHON;

      if(!is_array($KeyNames))
      {
         $KeyNames = array($KeyNames);
      }

      $arrJSON = self::_getJSONArray($Filename);

      if(empty($arrJSON))
      {
         $_ARCHON->declareError('failed to load JSON file');
         return false;
      }

      // check first element for the value before iteration through whole array
      foreach($KeyNames as $keyName)
      {
         if(!array_key_exists($keyName, reset($arrJSON)))
         {
            $_ARCHON->declareError('Field does not exist');
            return false;
         }
      }

      if(!$SearchQuery)
      {
         $arrResults = $arrJSON;

         if($Limit || $Offset)
         {
            $arrResults = array_slice($arrResults, $Offset, $Limit, true);
         }
      }
      else
      {
         $arrResults = array();

         $count = 0;
         $limit = ($Limit == 0) ? 0 : $Limit + $Offset;

         foreach($arrJSON as $ID => $objJSON)
         {
            foreach($KeyNames as $keyName)
            {
               if(stripos($objJSON[$keyName], $SearchQuery) !== false)
               {
                  if(!isset($arrResults[$ID]))
                  {
                     $arrResults[$ID] = $objJSON;
                     if($limit != 0 && $count == $limit - 1)
                     {
                        break 2;
                     }
                     $count++;
                  }
               }
            }
         }

         if($Offset)
         {
            $arrResults = array_slice($arrResults, $Offset, NULL, true);
         }
      }



      $arrObjects = array();

      foreach($arrResults as $ID => $objJSON)
      {
         $arrObjects[$ID] = New $Classname($objJSON);
      }

      return $arrObjects;
   }

   protected static function _getIDFromString($Filename, $Classname, $KeyNames, $SearchQuery, $CaseSensitive = false)
   {
      global $_ARCHON;

      $cmpFunction = ($CaseSensitive) ? 'strcmp' : 'strcasecmp';

      if(!is_array($KeyNames))
      {
         $KeyNames = array($KeyNames);
      }

      if($SearchQuery && !is_string($SearchQuery))
      {
         $SearchQuery = (string) $SearchQuery; //invokes toString on objects
      }

      static $IDCache = array();
      if(isset($IDCache[$Classname][$SearchQuery]))
      {
         return $IDCache[$Classname][$SearchQuery];
      }


      $arrJSON = self::_getJSONArray($Filename);

      if(empty($arrJSON))
      {
         $_ARCHON->declareError('failed to load JSON file');
         return false;
      }

      // check first element for the value before iteration through whole array
      foreach($KeyNames as $keyName)
      {
         if(!array_key_exists($keyName, reset($arrJSON)))
         {
            $_ARCHON->declareError('Field does not exist');
            return false;
         }
      }

      foreach($arrJSON as $ID => $objJSON)
      {
         foreach($KeyNames as $keyName)
         {
            if($cmpFunction($objJSON[$keyName], $SearchQuery) == 0)
            {
               $IDCache[$Classname][$SearchQuery] = $ID;
               return $ID;
            }
         }
      }

      return 0;
   }

}

?>
