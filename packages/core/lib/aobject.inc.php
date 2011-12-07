<?php

/**
 * Abstract class for non-mixin objects (ArchonObject is the abstract mixin object)
 *
 * @author Paul Sorensen
 */
abstract class AObject
{

   function __construct($ID_or_Row = 0)
   {
      if(is_array($ID_or_Row))
      {
         $ID_or_Row = array_change_key_case($ID_or_Row);
         $vars = get_object_vars($this);
         if(!empty($vars))
         {
            foreach($vars as $VariableName => $DefaultValue)
            {
               $val = $ID_or_Row[strtolower($VariableName)];
               if(isset($val))
               {
                  $this->$VariableName = $val;
               }
            }
         }
      }
      else
      {
         $this->ID = $ID_or_Row;
      }
   }





   /**
    * getString escapes any special XML characters unless EscapeXML is set to false.
    *
    * @param string $Variable
    * @return string
    */
   public function getString($Variable, $MaxLength = 0, $HTMLLineBreaks = true, $DecodeBB = NULL)
   {
      global $_ARCHON;

      if(!isset($DecodeBB))
      {
         $DecodeBB = $_ARCHON->PublicInterface ? true : false;
      }

      $String = $this->$Variable;

      if($_ARCHON->db->ServerType == 'MSSQL')
      {
         $String = encoding_convert_encoding($String, 'UTF-8', 'ISO-8859-1');
      }

      $String = trim($String);

      if ($MaxLength != 0)
      {
         $String = caplength($String, $MaxLength);
      }


      if(CONFIG_CORE_ESCAPE_XML)
      {
         if($DecodeBB)
         {
            $String = encode($String, ENCODE_BBCODE);
         }
         else
         {
            $String = $HTMLLineBreaks ? ptag(encode($String, ENCODE_HTML)) : encode($String, ENCODE_HTML);
         }
      }

      return $String;
   }

   function __toString()
   {
      return get_class($this);
   }

   public function toString()
   {
      return get_class($this);
   }

   public $ID = 0;
}
?>
