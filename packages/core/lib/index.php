<?php
/**
 * Core Package
 *
 * @package Archon
 * @subpackage API
 * @author Chris Rishel
 */

require_once('archonobject.inc.php');
require_once('aobject.inc.php');

class Archon extends ArchonObject
{
   public function __construct()
   {
      $this->Includes[get_class($this)]->Constructed = true;

      $this->StartTime = microtime(true);
   }

   public function mixClasses($ClassName, $MixinClassName)
   {
      if(!class_exists($ClassName))
      {
         trigger_error("Could not extend class $ClassName: Class $ClassName does not exist", E_USER_ERROR);
      }

      if(!class_exists($MixinClassName))
      {
         trigger_error("Could not extend class $ClassName: Mixin Class $MixinClassName does not exist", E_USER_ERROR);
      }

      // To support older packages but keep includes coming in correct order.
      if(!$this->Includes[$ClassName]->Constructed)
      {
         $ID = count($this->Includes[$ClassName]->FilesAndMixinClassNames);
         $this->Includes[$ClassName]->FilesAndMixinClassNames[$ID]->MixinClassName = $MixinClassName;

         //echo("Delaying mixing of $MixinClassName<br>\n");
         return;
      }

      //echo("Mixing $MixinClassName<br>\n");

      $objReflector = New ReflectionClass($MixinClassName);

      if(!$objReflector->isAbstract())
      {
         trigger_error("Could not extend class $ClassName: Mixin Class $MixinClassName must be declared as abstract", E_USER_ERROR);
      }

      $arrMethods = get_class_methods($MixinClassName);

      if(!empty($arrMethods))
      {
         foreach($arrMethods as $Method)
         {
            if(empty($this->Mixins[$ClassName]->Methods[$Method]->Classes))
            {
               $this->Mixins[$ClassName]->Methods[$Method]->Classes = array($MixinClassName);
            }
            elseif(!isset($this->Mixins[$ClassName]->Methods[$Method]->Parameters[$MixinClassName]))
            {
               trigger_error("Could not extend class $ClassName: Parameters have not been set for $MixinClassName::$Method", E_USER_ERROR);
            }
            /*else

                if($this->Mixins[$ClassName]->Methods[$Method]->Parameters[$MixinClassName]->MixOrder == MIX_BEFORE)
                {
                    array_unshift($this->Mixins[$ClassName]->Methods[$Method]->Classes, $MixinClassName);
                }*/
            else
            {
               array_push($this->Mixins[$ClassName]->Methods[$Method]->Classes, $MixinClassName);
            }
         }
      }

      $arrVariables = get_class_vars($MixinClassName);

      if(!empty($arrVariables))
      {
         foreach($arrVariables as $VariableName => $DefaultValue)
         {
            $this->Mixins[$ClassName]->Variables[$VariableName] = $DefaultValue;

            if($ClassName == get_class($this))
            {
               $this->$VariableName = $DefaultValue;
               $LowerVariableName = strtolower($VariableName);
               $this->$LowerVariableName =& $this->$VariableName;
            }
         }
      }

      return true;
   }

   public function getClassVars($ClassName)
   {
      if(isset($this->Mixins[$ClassName]))
      {
         return $this->Mixins[$ClassName]->Variables;
      }
      else
      {
         return get_class_vars($ClassName);
      }
   }

   public function methodExists($Object, $MethodName)
   {
      if(is_subclass_of($Object, 'ArchonObject'))
      {
         return !empty($this->Mixins[get_class($Object)]->Methods[$MethodName]->Classes);
      }
      else
      {
         return method_exists($Object, $MethodName);
      }
   }

   public function classVarExists($Object, $VarName)
   {
      if(is_subclass_of($Object, 'ArchonObject'))
      {
         return key_exists($VarName, $this->Mixins[get_class($Object)]->Variables);
      }
      else
      {
         return key_exists($VarName, get_class_vars(get_class($Object)));
      }
   }

   public function registerInclude($ClassName, $FileName)
   {
      global $_ARCHON;

//      if(!class_exists($ClassName))
//      {
//         trigger_error("Could not register file for class $ClassName: Class $ClassName does not exist", E_USER_ERROR);
//      }

      if(!file_exists($FileName))
      {
         trigger_error("Could not register file for class $ClassName: File $FileName does not exist", E_USER_ERROR);
      }

      if($ClassName == 'Archon')
      {
         require_once($FileName);
         return;
      }

      $ID = count($this->Includes[$ClassName]->FilesAndMixinClassNames);
      $this->Includes[$ClassName]->FilesAndMixinClassNames[$ID]->FileName = $FileName;
      $this->Includes[$ClassName]->FilesAndMixinClassNames[$ID]->FileDirectory = getcwd();
   }

   public $Mixins = array();

   public $CallbackStack = array();
}

$_ARCHON = New Archon();

require_once('archon.inc.php');

require_once('security.inc.php');
require_once('session.inc.php');
require_once('livesession.inc.php');
require_once('phrase.inc.php');
require_once('jsonobject.inc.php');
require_once('phrasetype.inc.php');
require_once('language.inc.php');
require_once('script.inc.php');
require_once('country.inc.php');


class AdminField extends ArchonObject {}
class AdministrativeInterface extends ArchonObject {}
class AdminRow extends ArchonObject {}
class AdminSection extends ArchonObject {}
class Configuration extends ArchonObject {}
class ModificationLogEntry extends ArchonObject {}
class Module extends ArchonObject {}
class Package extends ArchonObject {}
class Pattern extends ArchonObject {}
class PublicInterface extends ArchonObject {}
class QueryLog extends ArchonObject {}
class Repository extends ArchonObject {}
class StateProvince extends ArchonObject {}
class UnitTest extends ArchonObject {}
class User extends ArchonObject {}
class Usergroup extends ArchonObject {}
class UserProfileField extends ArchonObject {}
class UserProfileFieldCategory extends ArchonObject {}


$_ARCHON->registerInclude('AdminField', 'adminfield.inc.php');
$_ARCHON->registerInclude('AdministrativeInterface', 'administrativeinterface.inc.php');
$_ARCHON->registerInclude('AdminRow', 'adminrow.inc.php');
$_ARCHON->registerInclude('AdminSection', 'adminsection.inc.php');
$_ARCHON->registerInclude('Configuration', 'configuration.inc.php');
$_ARCHON->registerInclude('ModificationLogEntry', 'modificationlogentry.inc.php');
$_ARCHON->registerInclude('Module', 'module.inc.php');
$_ARCHON->registerInclude('Package', 'package.inc.php');
$_ARCHON->registerInclude('Pattern', 'pattern.inc.php');
$_ARCHON->registerInclude('PublicInterface', 'publicinterface.inc.php');
$_ARCHON->registerInclude('QueryLog', 'querylog.inc.php');
$_ARCHON->registerInclude('Repository', 'repository.inc.php');
$_ARCHON->registerInclude('StateProvince', 'stateprovince.inc.php');
$_ARCHON->registerInclude('UnitTest', 'unittest.inc.php');
$_ARCHON->registerInclude('User', 'user.inc.php');
$_ARCHON->registerInclude('Usergroup', 'usergroup.inc.php');
$_ARCHON->registerInclude('UserProfileField', 'userprofilefield.inc.php');
$_ARCHON->registerInclude('UserProfileFieldCategory', 'userprofilefieldcategory.inc.php');

require_once('EmailAddressValidator.php');

// Permissions constants should not use nextbitmask
// because the values are stored in the database
// and therefore must have the same value consistently
define('READ', 1, false);
define('ADD', 2, false);
define('UPDATE', 4, false);
define('DELETE', 8, false);
define('FULL_CONTROL', 16, false);

define('SEARCH_RELATED', nextbitmask('SEARCH'), false);
define('SEARCH_LANGUAGES', nextbitmask('SEARCH'), false);

define('NEWLINE', "\n", false);
define('INDENT', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', false);

define('LINK_NONE', 0, false);
define('LINK_EACH', nextbitmask('LINK'), false);
define('LINK_TOTAL', nextbitmask('LINK'), false);

define('ENCODE_NONE', 0, false);
define('ENCODE_HTML', nextbitmask('ENCODE'), false);
define('ENCODE_JAVASCRIPT', nextbitmask('ENCODE'), false);
define('ENCODE_HTMLTHENJAVASCRIPT', nextbitmask('ENCODE'), false);
define('ENCODE_JAVASCRIPTTHENHTML', nextbitmask('ENCODE'), false);
define('ENCODE_BBCODE', nextbitmask('ENCODE'), false);
define('ENCODE_BBCODEFORJAVASCRIPT', nextbitmask('ENCODE'), false);

define('MIX_OVERRIDE', nextbitmask('MIX'), false);
define('MIX_BEFORE', nextbitmask('MIX'), false);
define('MIX_AFTER', nextbitmask('MIX'), false);

define('ADD_NEW', '(Add New)', false);
define('MULTIPLE_VALUES', '(Multiple Values)', false);

define('COOKIE_EXPIRATION', 2592000, false); // 30 * 24 * 60 * 60
define('SESSION_EXPIRATION', 21600, false); // 6 * 60 * 60

define('ASCENDING', 0, false);
define('DESCENDING', 1, false);

define('UP', 0, false);
define('DOWN', 1, false);

define('MANY_TO_MANY', 1, false);
define('ONE_TO_MANY', 2, false);

define('PHRASETYPE_ADMIN', 5, false);
define('PHRASETYPE_DESC', 3, false);
define('PHRASETYPE_NOUN', 1, false);
define('PHRASETYPE_PUBLIC', 6, false);
define('PHRASETYPE_MESSAGE', 2, false);

define('RESEARCH_NONE', 0, false);
define('RESEARCH_COLLECTIONS', nextbitmask('RESEARCH'), false);
define('RESEARCH_DIGITALLIB', nextbitmask('RESEARCH'), false);
define('RESEARCH_ALL', RESEARCH_COLLECTIONS + RESEARCH_DIGITALLIB, false);

?>
