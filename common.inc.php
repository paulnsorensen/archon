<?php

/**
 * Common file containing functions which extend PHP's functionality
 *
 * These functions are not involved with the
 * database or user interaction in any way.
 * They serve only to build upon the function base of PHP.
 *
 * @package Archon
 * @authors Chris Rishel, Kyle Fox, Paul Sorensen
 */
// fix for PHP versions before 5.2
if(!function_exists('array_fill_keys'))
{

   function array_fill_keys($target, $value = '')
   {
      if(is_array($target))
      {
         foreach($target as $key => $val)
         {
            $filledArray[$val] = is_array($value) ? $value[$key] : $value;
         }
      }
      return $filledArray;
   }

}

if(!function_exists('json_encode') || !function_exists('json_decode'))
{
   require_once('includes/JSON/JSON.php');

   if(!function_exists('json_encode'))
   {

      function json_encode($data, $options = 0)
      {
         $json = new Services_JSON();
         return( $json->encode($data) );
      }

   }

   if(!function_exists('json_decode'))
   {

      function json_decode($data, $bool)
      {
         if($bool)
         {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
         }
         else
         {
            $json = new Services_JSON();
         }
         return($json->decode($data));
      }

   }
}

/**
 * Recursively changes the case of all array keys
 *
 * @param array $arr
 * @param integer $case
 * @return array
 */
function array_change_key_case_recursive($arr, $case = CASE_LOWER)
{
   $arr = array_change_key_case($arr, $case);

   foreach($arr as $key => $value)
   {
      if(is_array($value))
      {
         $arr[$key] = array_change_key_case_recursive($value, $case);
      }
   }

   return $arr;
}

function bb_decode($String)
{
//convert new lines
//$String = nl2br($String);
//$String = preg_replace('/\[<\]/u', '&lt;', $String ) ;
//$String = preg_replace('/\[>\]/u', '&gt;', $String ) ;
//convert bold tags
   $String = preg_replace('/\[b\](.+?)\[\/b]/u', '<strong>$1</strong>', $String);

   //convert italics tags
   $String = preg_replace('/\[i\](.+?)\[\/i]/u', '<em>$1</em>', $String);

   //convert underline
   $String = preg_replace('/\[u\](.+?)\[\/u]/u', '<span style="text-decoration:underline">$1</span>', $String);

   // [sup]
   $String = preg_replace('/\[sup\](.+?)\[\/sup\]/u', '<sup>$1</sup>', $String);

   // [sub]
   $String = preg_replace('/\[sub\](.+?)\[\/sub\]/u', '<sub>$1</sub>', $String);

   //convert links
   $String = preg_replace('/\[url=(.+?)\](.+?)\[\/url]/u', '<a href="$1" rel="external">$2</a>', $String);
   $String = preg_replace('/\[url\](.+?)\[\/url]/u', '<a href="$1" rel="external">$1</a>', $String);


   //    //convert list items
   //    $String = preg_replace('/\[\*\](.+?)(?=\[\*\]|\[\/list\])/u','<li>$1</li>$2',$String);
   //    $String = preg_replace('(\[\*\](.+?)(\[\/list\]))','<li>$1</li>$2',$String);
   //
   //    //convert unordered lists
   //    $String = preg_replace('/\[list\](.+?)\[\/list]/u','<ul>$1</ul>',$String);
   //
   //    //convert ordered lists
   //    $String = preg_replace('/\[list=1](.+?)\[\/list]/u','<ol>$1</ol>',$String);
   /*
     $String = preg_replace('(\[list=a](.+?)\[\/list])','<ol style="list-style-type:lower-alpha">$1</ol>',$String);
     $String = preg_replace('(\[list=A](.+?)\[\/list])','<ol style="list-style-type:upper-alpha">$1</ol>',$String);
     $String = preg_replace('(\[list=i](.+?)\[\/list])','<ol style="list-style-type:lower-roman">$1</ol>',$String);
     $String = preg_replace('(\[list=I](.+?)\[\/list])','<ol style="list-style-type:upper-roman">$1</ol>',$String);
    */

   return $String;
}

/**
 * Returns a string representation of the result of a boolean
 * evaluation of a variable.
 *
 * @param mixed $in
 * @return string
 */
function bool($in)
{
   if($in)
   {
      return "true";
   }
   else
   {
      return "false";
   }
}

/**
 * Returns the logical AND of two booleans
 *
 * @param boolean $b1
 * @param boolean $b2
 * @return string
 */
function boolean_and($b1, $b2)
{
   return $b1 && $b2;
}

/**
 * Returns the logical OR of two booleans
 *
 * @param boolean $b1
 * @param boolean $b2
 * @return string
 */
function boolean_or($b1, $b2)
{
   return $b1 || $b2;
}



function concatinate($str1, $str2)
{
   return $str1 . $str2;
}



/**
 * Fixes a string to a certain maximum length.
 *
 * @param string $string
 * @param integer $length
 * @return string
 */
function caplength($string, $length)
{
   $string = bbcode_striptags($string);

   $oldlen = encoding_strlen($string);

   if($oldlen > $length)
   {
      $string = encoding_substr($string, 0, $length - 3) . "...";
   }

   return $string;
}

/**
 * Strips BBCode tags from a string provided in the $bbtags array.
 * --$bbtags may contain regular expressions
 *
 * -- an alternative idea may be to bbcode decode the string and run strip_tags()
 *
 * @param string $string
 * @param string[] $bbtags
 * @return string
 */
function bbcode_striptags($string, $bbtags = array('i', 'u', 'b'))
{
   foreach($bbtags as $tag)
   {
      $string = preg_replace('/\[' . $tag . '\](.+?)\[\/' . $tag . ']/u', '$1', $string);
   }
   return $string;
}

/**
 * Returns the string length ignoring the BBCode tags
 *
 * @param string $string
 * @param string[] $bbtags
 * @return integer
 */
function bbcode_strlen($string, $bbtags = array('i', 'u', 'b'))
{
   return strlen(bbcode_striptags($string, $bbtags));
}

function bbcode_ead_decode($string)
{
   $string = preg_replace('/<emph .*?render=(["\'])italic\1.*?>[\s]*(.+?)[\s]*<\/emph>/ismu', '[i]$2[/i]', $string);
   $string = preg_replace('/<emph .*?render=(["\'])bold\1.*?>[\s]*(.+?)[\s]*<\/emph>/ismu', '[b]$2[/b]', $string);
   $string = preg_replace('/<emph .*?render=(["\'])underline\1.*?>[\s]*(.+?)[\s]*<\/emph>/ismu', '[u]$2[/u]', $string);
   $string = preg_replace('/<emph .*?render=(["\'])super\1.*?>[\s]*(.+?)[\s]*<\/emph>/ismu', '[sup]$2[/sup]', $string);
   $string = preg_replace('/<emph .*?render=(["\'])sub\1.*?>[\s]*(.+?)[\s]*<\/emph>/ismu', '[sub]$2[/sub]', $string);

   $string = preg_replace('/<extref .*?href=(["\'])(.+?)\1.*?>[\s]*(.+?)[\s]*<\/extref>/ismu', '[url=$2]$3[/url]', $string);

   $string = preg_replace('/<emph .*?render=(["\'])doublequote\1.*?>[\s]*(.+?)[\s]*<\/emph>/ismu', '"$2"', $string);
   $string = preg_replace('/<emph .*?render=(["\'])singlequote\1.*?>[\s]*(.+?)[\s]*<\/emph>/ismu', "'$2'", $string);

//   $string = str_replace('<lb/>', '\n', $string);

   return $string;
}

function bbcode_ead_encode($string)
{
   $string = str_replace('&nbsp;', ' ', $string);

   //convert bold tags
   $string = preg_replace('/\[b\](.+?)\[\/b]/u', '<emph render="bold">$1</emph>', $string);

   //convert italics tags
   $string = preg_replace('/\[i\](.+?)\[\/i]/u', '<emph render="italic">$1</emph>', $string);

   //convert underline
   $string = preg_replace('/\[u\](.+?)\[\/u]/u', '<emph render="underline">$1</emph>', $string);

   // [sup]
   $string = preg_replace('/\[sup\](.+?)\[\/sup\]/u', '<emph render="super">$1</emph>', $string);

   // [sub]
   $string = preg_replace('/\[sub\](.+?)\[\/sub\]/u', '<emph render="sub">$1</emph>', $string);

   //convert links
   $string = preg_replace('/\[url=(.+?)\](.+?)\[\/url]/u', '<extref linktype="simple" audience="external" show="embed" actuate="onrequest" href="$1">$2</extref>', $string);
   $string = preg_replace('/\[url\](.+?)\[\/url]/u', '<extref linktype="simple" audience="external" show="embed" actuate="onrequest" href="$1">$1</extref>', $string);

   return $string;
}

function bbcode_eac_encode($string)
{
   $string = str_replace('&nbsp;', ' ', $string);

   //convert bold tags
   $string = preg_replace('/\[b\](.+?)\[\/b]/u', '<span style="font-style:bold">$1</span>', $string);

   //convert italics tags
   $string = preg_replace('/\[i\](.+?)\[\/i]/u', '<span style="font-style:italic">$1</span>', $string);

   //convert underline
   $string = preg_replace('/\[u\](.+?)\[\/u]/u', '<span style="text-decoration:underline">$1</span>', $string);

   // [sup]
   $string = preg_replace('/\[sup\](.+?)\[\/sup\]/u', '<span style="font-size:xx-small;vertical-align:top">$1</span>', $string);

   // [sub]
   $string = preg_replace('/\[sub\](.+?)\[\/sub\]/u', '<span style="font-size:xx-small;vertical-align:bottom">$1</span>', $string);

   //convert links
//   $string = preg_replace('/\[url=(.+?)\](.+?)\[\/url]/u','<citation xlink:type="simple" xlink:href="$1">$2</citation>', $string);
//   $string = preg_replace('/\[url\](.+?)\[\/url]/u','<citation xlink:type="simple" xlink:href="$1">$1</citation>', $string);
   $string = preg_replace('/\[url=(.+?)\](.+?)\[\/url]/u', '', $string);
   $string = preg_replace('/\[url\](.+?)\[\/url]/u', ' ', $string);

   return $string;
}

/**
 * Encodes a string as specified by $Encoding
 *
 * @param string $String
 * @param integer $Encoding
 */
function encode($String, $Encoding)
{
   if($Encoding == ENCODE_NONE)
   {
      return $String;
   }

   if($Encoding == ENCODE_HTML || $Encoding == ENCODE_HTMLTHENJAVASCRIPT)
   {
      //ENT_NOQUOTES?
      $String = htmlspecialchars($String, ENT_QUOTES, 'UTF-8');
      $String = str_replace('  ', '&nbsp; ', $String);
   }

   if($Encoding == ENCODE_JAVASCRIPT || $Encoding == ENCODE_JAVASCRIPTTHENHTML || $Encoding == ENCODE_HTMLTHENJAVASCRIPT || $Encoding == ENCODE_BBCODEFORJAVASCRIPT)
   {
      $String = str_replace("\r", '', $String);
      $String = str_replace("\n", '\n', $String);
      $String = str_replace("'", "\'", $String);
      $String = str_replace("\"", '\"', $String);

      $String = str_replace("\\", '\\', $String);
   }

   if($Encoding == ENCODE_JAVASCRIPTTHENHTML || $Encoding == ENCODE_BBCODE)
   {
      $String = htmlspecialchars($String, ENT_QUOTES, 'UTF-8');
      $String = str_replace('  ', '&nbsp; ', $String);
   }

   if($Encoding == ENCODE_BBCODE || $Encoding == ENCODE_BBCODEFORJAVASCRIPT)
   {
      $String = ptag($String);
      $String = bb_decode($String);
   }


   return $String;
}

/**
 * Converts string from one encoding to another
 *
 * @param string $str
 * @param string $to_encoding
 * @param string $from_encoding
 * @return string
 */
function encoding_convert_encoding($str, $to_encoding, $from_encoding = 'UTF-8')
{
   global $_mbstringLoaded, $_iconvLoaded;

   if(strtolower($to_encoding) == strtolower($from_encoding))
   {
      return $str;
   }

   if($_mbstringLoaded)
   {
      return mb_convert_encoding($str, $to_encoding, $from_encoding);
   }
   elseif($_iconvLoaded)
   {
      return iconv($from_encoding, $to_encoding . '//TRANSLIT', $str);
   }
   elseif(strtolower($to_encoding) == 'iso-8859-1')
   {
      return utf8_decode($str);
   }
   elseif(strtolower($from_encoding) == 'iso-8859-1' && strtolower($to_encoding) == 'utf-8')
   {
      return utf8_encode($str);
   }
   else
   {
      return $str;
   }
}

/**
 * Finds length of multi-byte strings
 *
 * @param string $str
 * @param string $encoding
 * @return integer
 */
function encoding_strlen($str, $encoding = 'UTF-8')
{
   global $_mbstringLoaded, $_iconvLoaded;

   if($_mbstringLoaded)
   {
      return mb_strlen($str, $encoding);
   }
   elseif($_iconvLoaded)
   {
      return iconv_strlen($str, $encoding);
   }
   elseif(!$encoding || strtolower($encoding) == 'utf-8')
   {
      return strlen(utf8_decode($str));
   }
   else
   {
      return strlen($str);
   }
}

/**
 * Returns position of $needle as integer or false if $needle is not
 * present in $haystack
 *
 * @param string $haystack
 * @param string $needle
 * @param integer $offset
 * @param string $encoding
 * @return mixed
 */
function encoding_strpos($haystack, $needle, $offset = 0, $encoding = 'UTF-8')
{
   global $_mbstringLoaded, $_iconvLoaded;

   if($_mbstringLoaded)
   {
      return mb_strpos($haystack, $needle, $offset, $encoding);
   }
   elseif($_iconvLoaded)
   {
      return iconv_strpos($haystack, $needle, $offset, $encoding);
   }
   elseif(!$encoding || strtolower($encoding) == 'utf-8')
   {
      return strpos(utf8_decode($haystack), utf8_decode($needle), $offset);
   }
   else
   {
      return strpos($haystack, $needle, $offset);
   }
}

/**
 * Same as strpos, except finds position from end of $haystack
 *
 * @param string $haystack
 * @param string $needle
 * @param integer $offset
 * @param string $encoding
 * @return mixed
 */
function encoding_strrpos($haystack, $needle, $offset = 0, $encoding = 'UTF-8')
{
   global $_mbstringLoaded, $_iconvLoaded;

   if($_mbstringLoaded)
   {
      return mb_strrpos($haystack, $needle, $offset, $encoding);
   }
   elseif($_iconvLoaded)
   {
      return iconv_strrpos($haystack, $needle, $encoding);
   }
   elseif(!$encoding || strtolower($encoding) == 'utf-8')
   {
      return strrpos(utf8_decode($haystack), utf8_decode($needle), $offset);
   }
   else
   {
      return strrpos($haystack, $needle, $offset);
   }
}

/**
 * Returns substring based on $start position and $length
 *
 * @param string $string
 * @param integer $start
 * @param integer $length
 * @param string $encoding
 * @return mixed
 */
function encoding_substr($string, $start, $length = NULL, $encoding = 'UTF-8')
{
   global $_mbstringLoaded, $_iconvLoaded;

   if($_mbstringLoaded)
   {
      return $length ? mb_substr($string, $start, $length, $encoding) : mb_substr($string, $start);
   }
   elseif($_iconvLoaded)
   {
      return iconv_substr($string, $start, $length, $encoding);
   }
   elseif(!$encoding || strtolower($encoding) == 'utf-8')
   {
      return utf8_encode(substr(utf8_decode($string), $start, $length));
   }
   else
   {
      return substr($string, $start, $length);
   }
}

/**
 * Returns lowercase representation of string
 *
 * @param string $str
 * @param string $encoding
 * @return string
 */
function encoding_strtolower($str, $encoding = 'UTF-8')
{
   global $_mbstringLoaded;

   if($_mbstringLoaded)
   {
      return mb_strtolower($str, $encoding);
   }
   elseif(!$encoding || strtolower($encoding) == 'utf-8')
   {
      return utf8_encode(strtolower(utf8_decode($str)));
   }
   else
   {
      return strtolower($str);
   }
}

/**
 * Returns uppercase representation of string
 *
 * @param string $str
 * @param string $encoding
 * @return string
 */
function encoding_strtoupper($str, $encoding = 'UTF-8')
{
   global $_mbstringLoaded;

   if($_mbstringLoaded)
   {
      return mb_strtoupper($str, $encoding);
   }
   elseif(!$encoding || strtolower($encoding) == 'utf-8')
   {
      return utf8_encode(strtoupper(utf8_decode($str)));
   }
   else
   {
      return strtoupper($str);
   }
}

/**
 * Returns number of times $needle appears in $haystack
 *
 * @param string $haystack
 * @param string $needle
 * @param string $encoding
 * @return integer
 */
function encoding_substr_count($haystack, $needle, $encoding = 'UTF-8')
{
   global $_mbstringLoaded;

   if($_mbstringLoaded)
   {
      return mb_substr_count($haystack, $needle, $encoding);
   }
   elseif(!$encoding || strtolower($encoding) == 'utf-8')
   {
      return substr_count(utf8_decode($haystack), utf8_decode($needle));
   }
   else
   {
      return substr_count($haystack, $needle);
   }
}

/**
 * Returns $string with substring replaced with $replacement
 *
 * @param string $string
 * @param string $replacement
 * @param integer $start
 * @param integer $length
 * @param string $encoding
 * @return string
 */
function encoding_substr_replace($string, $replacement, $start, $length = NULL, $encoding = 'UTF-8')
{
   if(isset($length))
   {
      return encoding_substr($string, 0, $start, $encoding) . $replacement . encoding_substr($string, $start + $length, NULL, $encoding);
   }
   else
   {
      return encoding_substr($string, 0, $start, $encoding) . $replacement;
   }
}

/**
 * Loads an array of files from one file.
 *
 * This function adds a layer of abstraction when opening
 * files in PHP.  If the file passed is a supported multi-file
 * archive, every file in that archive will be loaded into the array.
 * If the file is a compressed file, the file will be decompressed
 * and loaded into the array.
 *
 * Even if only one file is loaded, it will be put into an array,
 * so any code calling this function should expect an array on success.
 *
 * Currently supported formats:
 *
 *  - zip
 *  - gz
 *  - bz2
 *
 * @param string $Filename
 * @return array
 */
function file_get_contents_array($Filename)
{
   $Filename = realpath($Filename);

   if(!file_exists($Filename) || !is_readable($Filename))
   {
      return false;
   }

   $arrFileContents = array();

   if(function_exists('zip_open') && is_resource($zip = zip_open($Filename)))
   {
      while($zip_entry = zip_read($zip))
      {
         if(zip_entry_open($zip, $zip_entry, "r"))
         {
            $arrFileContents[zip_entry_name($zip_entry)] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
         }
      }
   }
   elseif(function_exists('bzopen') && $bz = bzopen($Filename, 'r') && is_resource($bz))
   {
      while(!feof($bz))
      {
         $FileContents .= bzread($bz);
      }

      $arrFileContents[str_replace('.bz2', '', basename($Filename))] = $FileContents;
   }
   elseif(function_exists('gzfile'))
   {
      $arrFileContents[str_replace('.gz', '', basename($Filename))] = implode("", gzfile($Filename));
   }
   else
   {
      $arrFileContents[$Filename] = file_get_contents($Filename);
   }

   return $arrFileContents;
}

/**
 * Creates a filename ready string
 *
 * @param string $string
 * @return string
 */
function formatFileName($string)
{
   $string = trim($string);

   $string = preg_replace("/&(.+?);/", "", $string);
   $string = preg_replace("/[ ]+/", "_", $string);
   $string = bbcode_striptags($string);
   $string = strip_tags($string);
   $string = preg_replace("/[^A-Za-z0-9_]/iu", "", $string);
   $string = strtolower($string);

   return $string;
}

/**
 * Casts to a float
 *
 * @param float $number
 * @return float
 */
function formatNumber($number)
{
   // just cast to float
   $number += 0;
   return $number;
}

/**
 * Returns a nicely formatted string representation of a filesize.
 *
 * @param integer $size
 * @return string
 */
function formatsize($size)
{
   if(($size / 1024) > 1)
   {
      $size = $size / 1024;
      $extension = "KB";
      if(($size / 1024) > 1)
      {
         $size = $size / 1024;
         $extension = "MB";
         if(($size / 1024) > 1)
         {
            $size = $size / 1024;
            $extension = "GB";
            if(($size / 1024) > 1)
            {
               $size = $size / 1024;
               $extension = "TB";
            }
         }
      }
   }
   else
   {
      $extension = "Bytes";
   }

   $size = round($size, 2);

   return($size . " " . $extension);
}

/**
 * Parses CSV into two-dimensional array
 *
 * @param string $string
 * @return string[][]
 */
function getCSVFromString($string)
{
   $string = str_replace("\r\n", "\n", $string);
   $string = str_replace("\r", "\n", $string);

   if($string[strlen($string) - 1] != "\n")
   {
      $string .= "\n";
   }

   $data = array();
   $row = array('');
   $idx = 0;
   $quoted = false;
   for($i = 0; $i < strlen($string); $i++)
   {
      $ch = $string[$i];
      if($ch == '"')
      {
         $quoted = !$quoted;
      }

      // End of line
      if($ch == "\n" && !$quoted)
      {
         // Remove enclosure delimiters
         for($k = 0; $k < count($row); $k++)
         {
            if($row[$k] != '' && $row[$k][0] == '"')
            {
               $row[$k] = substr($row[$k], 1, strlen($row[$k]) - 2);
            }
            $row[$k] = str_replace('""', '"', $row[$k]);
         }

         // Append row into table
         $data[] = $row;
         $row = array('');
         $idx = 0;
      }

      // End of field
      elseif($ch == ',' && !$quoted)
      {
         $row[$idx] = trim($row[$idx]);
         $row[++$idx] = "";
      }

      // Inside the field
      else
      {
         $row[$idx] .= $ch;
      }
   }

   reset($data);
   return $data;
}

/**
 * Returns an array of enabled compression file extensions
 *
 * @return string[]
 */
function get_enabled_compression_extensions()
{
   $arrExtensions = array();

   if(function_exists('zip_open'))
   {
      $arrExtensions[] = 'zip';
   }

   if(function_exists('rar_open'))
   {
      $arrExtensions[] = 'rar';
   }

   if(function_exists('gzopen'))
   {
      $arrExtensions[] = 'gz';
   }

   if(function_exists('bzopen'))
   {
      $arrExtensions[] = 'bz2';
   }

   return $arrExtensions;
}

/**
 * Checks to see if $var is a natural number.
 *
 * This can be used to make sure non-negative integers
 * are being used as function arguments.
 *
 * @param mixed $var
 * @return boolean
 */
function is_natural($var)
{
   return (strval(intval($var)) == strval($var)) && ($var >= 0);
}

function js_array($array, $quotes = true)
{
   if(empty($array))
   {
      return '[]';
   }
   elseif($quotes)
   {
      return "['" . implode("', '", $array) . "']";
   }
   else
   {
      return "[" . implode(", ", $array) . "]";
   }
}

/**
 * Functions like array_map, but works also on objects and
 * all child objects.
 *
 * Note: In the case of a recursive objects (i.e. a doubly-linked list)
 * you must pass the name of the recursive property to $ignoredproperties
 * or your webserver will crash!  $ignoredproperties should contain a comma-delimited
 * list of object properties to ignore.
 *
 * @param callback $function
 * @param mixed $var
 * @param string $ignoredproperties
 * @return mixed
 */
function map_recursive($function, $var, $ignoredproperties = 'Collection,Collections,Parent,PrimaryCreator')
{
   if(func_num_args() > 3)
   {
      $funcargs = func_get_args();
      array_shift($funcargs);
      array_shift($funcargs);
      array_shift($funcargs);
   }
   else
   {
      $funcargs = array();
   }

   if(!function_exists($function))
   {
      return $var;
   }

   $lowerignoredproperties = strtolower($ignoredproperties);

   if(is_object($var) || is_array($var))
   {
      foreach($var as $key => &$val)
      {
         $lowerkey = strtolower($key);

         if(is_object($val) || is_array($val))
         {
            if(is_object($var) && ($key == $lowerkey || $var->$key !== $var->$lowerkey) && (encoding_strpos($lowerignoredproperties, $lowerkey) === false))
            {
               $val = call_user_func_array('map_recursive', array_merge(array($function, $val, $ignoredproperties), $funcargs));
            }
            elseif(is_array($var) && ($key == $lowerkey || $var[$key] !== $var[$lowerkey]))
            {
               $val = call_user_func_array('map_recursive', array_merge(array($function, $val, $ignoredproperties), $funcargs));
            }
         }
         else
         {
            if(is_object($var) && ($key == $lowerkey || $var->$key !== $var->$lowerkey))
            {
               $val = call_user_func_array($function, array_merge(array($val), $funcargs));
            }
            elseif(is_array($var) && ($key == $lowerkey || $var[$key] !== $var[$lowerkey]))
            {
               $val = call_user_func_array($function, array_merge(array($val), $funcargs));
            }
         }
      }
   }
   else
   {
      $var = call_user_func_array($function, array_merge(array($var), $funcargs));
   }

   return $var;
}

/**
 * Functions exactly as natcasesort, but sorts by key value
 *
 * @param array &$array
 * @return boolean
 */
function natcaseksort(&$array)
{
   if(empty($array) || !is_array($array))
   {
      return false;
   }

   $arrKeys = array_keys($array);

   natcasesort($arrKeys);
   $arrTmp = array();

   foreach($arrKeys as $Key)
   {
      $arrTmp[$Key] = $array[$Key];
   }

   $array = $arrTmp;

   return true;
}

/**
 * Returns next bitmask of type $strBitmask. Useful when total number of required bitmasks
 * is unknown
 *
 * @param string $strBitmask
 * @return integer
 */
function nextbitmask($strBitmask)
{
   global $arrBitmasks;

   if(!$strBitmask)
   {
      return false;
   }
   else
   {
      $strBitmask = strtolower($strBitmask);
      return $arrBitmasks[$strBitmask] = isset($arrBitmasks[$strBitmask]) ? $arrBitmasks[$strBitmask] <<= 1 : 1;
   }
}

/**
 * Attempts to pluralize a string
 *
 * @param string $word
 * @return string
 */
function pluralize($word)
{
   $lword = encoding_strtolower($word);

   if(encoding_substr($lword, -1) == "s")
   {
      return $word;
   }
   elseif(encoding_substr($lword, -1) == "x")
   {
      return $word . "es";
   }
   elseif(encoding_substr($lword, -1) == "y" && $lword != 'day')
   {
      return encoding_substr($word, 0, encoding_strlen($word) - 1) . "ies";
   }
   else
   {
      return $word . "s";
   }
}

/**
 * Converts line breaks to <p></p>
 *
 * @param string $string
 * @return string
 */
function ptag($string)
{
   return (encoding_strpos($string, NEWLINE) !== false) ? '<p>' . str_replace(NEWLINE, '</p><p>', $string) . '</p>' : $string;
}

if(extension_loaded('mbstring'))
{
   $_mbstringLoaded = true;

   mb_internal_encoding('UTF-8');
   mb_http_output('UTF-8');
}

if(extension_loaded('iconv'))
{
   $_iconvLoaded = true;

   iconv_set_encoding('input_encoding', 'UTF-8');
   iconv_set_encoding('output_encoding', 'UTF-8');
   iconv_set_encoding('internal_encoding', 'UTF-8');
}

