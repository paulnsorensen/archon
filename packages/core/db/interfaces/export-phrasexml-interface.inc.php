<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
   $dialogSection->insertRow('packageid')->insertSelect('PackageID', 'getAllPackages');
   $dialogSection->insertRow('moduleid')->insertSelect('ModuleID', 'getAllModules');
   $dialogSection->insertRow('phrasetypeid')->insertSelect('PhraseTypeID', 'getAllPhraseTypes');
   $dialogSection->insertRow('languageid')->insertSelect('LanguageID', 'getAllLanguages');
   $dialogSection->insertRow('notlanguageid')->insertSelect('NotLanguageID', 'getAllLanguages');


?>
