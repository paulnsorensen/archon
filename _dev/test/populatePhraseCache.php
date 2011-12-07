<?php
 public function populatePhraseCache()
   {
      global $_ARCHON;

      $APRCode = $_ARCHON->Package->APRCode;
      $PackageID = $_ARCHON->Package->ID;
      $Script = $_ARCHON->Module->Script;
      $ModuleID = $_ARCHON->Module->ID;
      $LanguageID = $_ARCHON->Security->Session->getLanguageID();
      $LanguageShort = $_ARCHON->getLanguageShortFromID($LanguageID);

//      var_dump($_ARCHON->MemoryCache['Phrases'][$PackageID][$ModuleID][$PhraseTypeID][$LanguageID]);

      $APRCode = preg_replace('/[\\/\\\\]/u', '', $APRCode);

      $arrFiles = array();

      if(file_exists("packages/{$APRCode}/install/phrasexml/"))
      {
         if($handle = opendir("packages/{$APRCode}/install/phrasexml/"))
         {
            while(false !== ($file = readdir($handle)))
            {
               if(preg_match("/([\\w]+)-$APRCode\\.xml/ui", $file, $arrMatch) && $arrMatch[1] == $LanguageShort)
               {
                  $arrFiles = array_merge($arrFiles, file_get_contents_array("packages/{$APRCode}/install/phrasexml/$file"));
               }
            }
         }
      }

      if(!empty($arrFiles))
      {
         $arrModules = $_ARCHON->getAllModules(false);
         foreach($arrModules as $ID => $objModule)
         {
            $arrModules[$objModule->Script] =& $arrModules[$ID];
         }

         foreach($arrFiles as $Filename => $strXML)
         {


            $objXML = simplexml_load_string($strXML);


            if(!$objXML)
            {
               echo("The file is not a valid Phrases XML file.<br><br>\n");
               continue;
            }




            $package = NULL;
            $arrPackageElements = count($objXML->package) > 1 ? $objXML->package : array($objXML->package);
            foreach($arrPackageElements as $packagelement)
            {
               if($APRCode == (string) $packagelement['aprcode'])
               {
                  $package = $packagelement;
               }
               if($package)
               {
                  break;
               }
            }

            $module = NULL;
            $module_none = NULL;
            $arrModuleElements = count($package->module) > 1 ? $package->module : array($package->module);
            foreach($arrModuleElements as $moduleelement)
            {
               if($Script == (string) $moduleelement['script'])
               {
                  $module = $moduleelement;
               }
               elseif((string) $moduleelement['script'] == MODULE_NONE)
               {
                  $module_none = $moduleelement;
               }
               if($module && $module_none)
               {
                  break;
               }
            }


            $phrasetype = NULL;
            $phrasetype_none = NULL;
            $arrPhraseTypeElements = count($module->phrasetype) > 1 ? $module->phrasetype : array($module->phrasetype);
            foreach($arrPhraseTypeElements as $phrasetypeelement)
            {
               $PhraseTypeName = (string) $phrasetypeelement['name'];
               if($PhraseTypeName == 'Administrative Phrase')
               {
                  $phrasetype = $phrasetypeelement;
                  break;
               }
            }
            $arrPhraseTypeElements = count($module_none->phrasetype) > 1 ? $module_none->phrasetype : array($module_none->phrasetype);
            foreach($arrPhraseTypeElements as $phrasetypeelement)
            {
               $PhraseTypeName = (string) $phrasetypeelement['name'];
               if($PhraseTypeName == 'Administrative Phrase')
               {
                  $phrasetype_none = $phrasetypeelement;
                  break;
               }
            }

            $PhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Administrative Phrase');


            $arrPhrase = count($phrasetype_none->phrase) > 1 ? $phrasetype_none->phrase : array($phrasetype_none->phrase);
            foreach($arrPhrase as $phraseelement)
            {
               $PhraseName = (string) $phraseelement['name'];
               if(!$PhraseName)
               {
                  continue;
               }

               $objPhrase = new Phrase();
               $objPhrase->LanguageID = $LanguageID;
               $objPhrase->PackageID = $PackageID;
               $objPhrase->ModuleID = 0;
               $objPhrase->PhraseName = $PhraseName;
               $objPhrase->PhraseTypeID = $PhraseTypeID;
               $objPhrase->RegularExpression = (string) $phraseelement['regularexpression'];

               $PhraseValue = trim((string) $phraseelement);
               $PhraseValue = str_replace("\n\t\t\t\t\t", "\n", $PhraseValue);

               $objPhrase->PhraseValue = $PhraseValue;
               $_ARCHON->MemoryCache['Phrases'][$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName] = $objPhrase;

            }

              $arrPhrase = count($phrasetype->phrase) > 1 ? $phrasetype->phrase : array($phrasetype->phrase);
            foreach($arrPhrase as $phraseelement)
            {
               $PhraseName = (string) $phraseelement['name'];
               if(!$PhraseName)
               {
                  continue;
               }

               $objPhrase = new Phrase();
               $objPhrase->LanguageID = $LanguageID;
               $objPhrase->PackageID = $PackageID;
               $objPhrase->ModuleID = $ModuleID;
               $objPhrase->PhraseName = $PhraseName;
               $objPhrase->PhraseTypeID = $PhraseTypeID;
               $objPhrase->RegularExpression = (string) $phraseelement['regularexpression'];

               $PhraseValue = trim((string) $phraseelement);
               $PhraseValue = str_replace("\n\t\t\t\t\t", "\n", $PhraseValue);

               $objPhrase->PhraseValue = $PhraseValue;
               $_ARCHON->MemoryCache['Phrases'][$PackageID][$ModuleID][$PhraseTypeID][$LanguageID][$PhraseName] = $objPhrase;

            }
         }




      }
//            var_dump($_ARCHON->MemoryCache['Phrases']);


   }
?>
