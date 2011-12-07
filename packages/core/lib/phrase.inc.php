<?php

/**
 * Description of Phrase
 *
 * @author Paul Sorensen
 */
class Phrase extends AObject
{

   public static function loadModulePhrases()
   {
      global $_ARCHON;

      $PhraseTypeID = PhraseType::getPhraseTypeIDFromString('Administrative Phrase');

      $LanguageID = $_ARCHON->Security->Session->getLanguageID();

      static $prep = NULL;

      if(!isset($prep))
      {
         $query = "SELECT * FROM tblCore_Phrases WHERE LanguageID = ? AND PhraseTypeID = ? AND PhraseName = ?";
         $prep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'text'), MDB2_PREPARE_RESULT);
      }

      // Load all phrases for 'module_name'
      $result = $prep->execute(array($LanguageID, $PhraseTypeID, 'module_name'));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         self::$PhraseCache[$row['PackageID']][$row['ModuleID']][$PhraseTypeID][$LanguageID][$row['PhraseName']] = New Phrase($row);
      }
      $result->free();


      // Load all phrases for 'package_name'
      $result = $prep->execute(array($LanguageID, $PhraseTypeID, 'package_name'));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         self::$PhraseCache[$row['PackageID']][$row['ModuleID']][$PhraseTypeID][$LanguageID][$row['PhraseName']] = New Phrase($row);
      }
      $result->free();
   }

   public static function getPhrase($PhraseName, $PackageID, $ModuleID, $PhraseTypeID, $LanguageID = 0)
   {
      global $_ARCHON;

      if(!$PhraseName)
      {
         $_ARCHON->declareError("Could not get Phrase: Phrase Name not defined. {$PhraseName},{$PackageID},{$ModuleID},{$PhraseTypeID},{$LanguageID}");
         return false;
      }

      if(!$PackageID || !is_natural($PackageID))
      {
         $_ARCHON->declareError("Could not get Phrase: Phrase Package not defined. {$PhraseName},{$PackageID},{$ModuleID},{$PhraseTypeID},{$LanguageID}");
         return false;
      }

      if(!is_natural($ModuleID))
      {
         $_ARCHON->declareError("Could not get Phrase: Phrase Module must be numeric. {$PhraseName},{$PackageID},{$ModuleID},{$PhraseTypeID},{$LanguageID}");
         return false;
      }

      if(!$LanguageID)
      {
         $LanguageID = $_ARCHON->Security->Session->getLanguageID();
      }

      if(!$PhraseTypeID || !is_natural($PhraseTypeID))
      {
         $_ARCHON->declareError("Could not get Phrase: PhraseType not defined. {$PhraseName},{$PackageID},{$ModuleID},{$PhraseTypeID},{$LanguageID}");
         return false;
      }

      $objPhrase = self::_getPhrase($PhraseName, $PackageID, $ModuleID, $PhraseTypeID, $LanguageID);

      // Try default language cache.
      if(!$objPhrase && $LanguageID != CONFIG_CORE_DEFAULT_LANGUAGE)
      {
         $objPhrase = self::_getPhrase($PhraseName, $PackageID, $ModuleID, $PhraseTypeID, CONFIG_CORE_DEFAULT_LANGUAGE);
      }

      // Try English
      if(!$objPhrase)
      {
         $englishID = Language::getLanguageIDFromString('eng');
         if($englishID != CONFIG_CORE_DEFAULT_LANGUAGE && $englishID != $LanguageID)
         {
            $objPhrase = self::_getPhrase($PhraseName, $PackageID, $ModuleID, $PhraseTypeID, $englishID);
         }
      }


//      if($objPhrase && defined('DEBUG') && DEBUG)
//      {
//         $_ARCHON->logStats('accessed_phrases', array('PackageID' => $PackageID, 'ModuleID' => $ModuleID, 'PhraseTypeID' => $PhraseTypeID, 'LanguageID' => $LanguageID, 'PhraseName' => $PhraseName), array('Accesses' => 1));
//      }
      // Time to give up.
      if(!$objPhrase)
      {
//         if(defined('DEBUG') && DEBUG)
//         {
//            $_ARCHON->logStats('missing_phrases', array('PackageID' => $PackageID, 'ModuleID' => $ModuleID, 'PhraseTypeID' => $PhraseTypeID, 'LanguageID' => $LanguageID, 'PhraseName' => $PhraseName), array('AccessAttempts' => 1));
//         }
         $DieNote = "Could not load phrase '$PhraseName' with Package ID $PackageID, Module ID $ModuleID, Language ID $LanguageID, and Phrase Type ID $PhraseTypeID! This occured with p={$_REQUEST['p']} and f={$_REQUEST['f']}";
         $_ARCHON->TestingError .= $DieNote . "<br />";
         return false;
      }

      return $objPhrase;
   }

   private static function _getPhrase($PhraseName, $PackageID, $ModuleID, $PhraseTypeID, $LanguageID)
   {
      global $_ARCHON;

      if(!isset(self::$PhraseCache[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID]))
      {
         self::$PhraseCache[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID] = array();
      }

      static $prepAll = NULL;
      static $prepOne = NULL;
      static $loadAllFlag = array();

      $tolerance = 1;

      if(!isset($prepAll))
      {
         $query = "SELECT * FROM tblCore_Phrases WHERE LanguageID = ? AND PhraseTypeID = ? AND PackageID = ? AND (ModuleID = ? OR ModuleID = '0') ORDER BY ModuleID";
         $prepAll = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      if(!isset($prepOne))
      {
         $query = "SELECT * FROM tblCore_Phrases WHERE LanguageID = ? AND PhraseName = ? AND PhraseTypeID = ? AND PackageID = ? AND (ModuleID = ? OR ModuleID = '0') ORDER BY ModuleID DESC";
         $_ARCHON->mdb2->setLimit(1);
         $prepOne = $_ARCHON->mdb2->prepare($query, array('integer', 'text', 'integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }

      // Try to set using language cache.
      $objPhrase = self::$PhraseCache[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName];

      // Maybe cache is too fresh?
      if(!$objPhrase)
      {
         if(sizeof(self::$PhraseCache[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID]) >= $tolerance)
         {
            // Load all phrases with same package id, module id, phrase type id, and language id.
            if(!$loadAllFlag[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID])
            {
               $result = $prepAll->execute(array($LanguageID, $PhraseTypeID, $PackageID, $ModuleID));
               if(PEAR::isError($result))
               {
                  trigger_error($result->getMessage(), E_USER_ERROR);
               }

               while($row = $result->fetchRow())
               {
                  self::$PhraseCache[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$row['PhraseName']] = New Phrase($row);
               }
               $result->free();
               $loadAllFlag[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID] = true;

//               $_ARCHON->TestingError .= "Loaded all: ".$PackageID." ".$ModuleID." ".$PhraseTypeID."<br />";

               $objPhrase = self::$PhraseCache[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName];
            }
         }
         else
         {
//            $_ARCHON->TestingError .= "Loaded one: ".$PhraseName." ".$PackageID." ".$ModuleID." ".$PhraseTypeID."<br />";

            $result = $prepOne->execute(array($LanguageID, $PhraseName, $PhraseTypeID, $PackageID, $ModuleID));
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            if($row = $result->fetchRow())
            {
               $objPhrase = New Phrase($row);
            }
            $result->free();

            self::$PhraseCache[$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName] = $objPhrase;
         }
      }

      return $objPhrase;
   }

   /**
    * Deletes Phrase from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_PHRASES, 'tblCore_Phrases'))
      {
         return false;
      }

      return true;
   }

   /**
    * Loads Phrase from the database
    *
    * @return boolean
    */
   public function dbLoad()
   {
      global $_ARCHON;

      if(!$_ARCHON->loadObject($this, 'tblCore_Phrases'))
      {
         return false;
      }

      if($this->PhraseTypeID)
      {
         $this->PhraseType = New PhraseType($this->PhraseTypeID);
         $this->PhraseType->dbLoad();
      }

      return true;
   }

   /**
    * Stores Phrase to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      // General store function won't know how to handle ModuleIDs
      if(!is_natural($this->ModuleID))
      {
         $_ARCHON->declareError("Could not store Phrase: Module ID must be numeric.");
         $_ARCHON->ProblemFields[] = 'ModuleID';
         return false;
      }

      if($this->ModuleID && (!$_ARCHON->Modules[$this->ModuleID] || $_ARCHON->Modules[$this->ModuleID]->PackageID != $this->PackageID))
      {
         $_ARCHON->declareError("Could not store Phrase: Module ID does not correspond to Package ID.");
         $_ARCHON->ProblemFields[] = 'ModuleID';
         return false;
      }

      $checkquery = "SELECT ID FROM tblCore_Phrases WHERE LanguageID = ? AND PackageID = ? AND ModuleID = ? AND PhraseName = ? AND PhraseTypeID = ? AND ID != ?";
      $checktypes = array('integer', 'integer', 'integer', 'text', 'integer', 'integer');
      $checkvars = array($this->LanguageID, $this->PackageID, $this->ModuleID, $this->PhraseName, $this->PhraseTypeID, $this->ID);
      $checkqueryerror = "A Phrase with the same LanguageAndPackageAndModuleAndNameAndPhraseType already exists in the database";
      $problemfields = array('PhraseName', 'LanguageID', 'PackageID', 'ModuleID', 'PhraseTypeID');
      $requiredfields = array('PackageID', 'LanguageID', 'PhraseName', 'PhraseValue', 'PhraseTypeID');

      if(!$_ARCHON->storeObject($this, MODULE_PHRASES, 'tblCore_Phrases', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
      {
         return false;
      }

      return true;
   }

   /**
    * Generates a formatted string of the Phrase object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE)
   {
      return $this->getString('PhraseName') . ": [" . caplength($this->getPhraseValue(), 50)."]";
   }

   /**
    * Generates a formatted string of the value of the Phrase object
    *
    * @param integer $Encoding[optional]
    * @return string
    */
   public function getPhraseValue($Encoding = ENCODE_NONE)
   {
      if(!$this->ID)
      {
         return false;
      }

      if($Encoding == ENCODE_NONE)
      {
         $String = $this->getString('PhraseValue', 0, true, true);
      }
      else
      {
         $String = $this->PhraseValue;
      }

      $String = encode($String, $Encoding);

      return $String;
   }

   // These variables correspond directly to the fields in the tblCore_Phrases table

   private static $PhraseCache = array();
   /**
    * @var integer
    */
   public $PackageID = 0;
   /**
    * @var integer
    */
   public $ModuleID = 0;
   /**
    * @var integer
    */
   public $PhraseTypeID = 0;
   /**
    * @var integer
    */
   public $LanguageID = 0;
   /**
    * @var string
    */
   public $PhraseName = '';
   /**
    * @var string
    */
   public $PhraseValue = '';
   /**
    * @var string
    */
   public $RegularExpression = '';

   // These variables are loaded from other tables, but relate to the phrase
   /**
    * @var PhraseType
    */
   public $PhraseType = NULL;
}

?>
