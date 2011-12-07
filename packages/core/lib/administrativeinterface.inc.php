<?php

abstract class Core_AdministrativeInterface
{

   public function addScript($script)
   {
      if(!$this->Script)
      {
         $this->Script = '';
      }
      $this->Script .= $script . "\n\n";
   }

   public function disableQuickSearch()
   {
      $this->DisableQuickSearch = true;
   }

   public function addReloadSection($objAdminSection)
   {
      $this->ReloadSections[] = $objAdminSection;
   }

   public function addReloadRow($objAdminRow)
   {
      $this->ReloadRows[] = $objAdminRow;
   }

   public function addReloadField($objAdminField)
   {
      $this->ReloadFields[] = $objAdminField;
   }

   public function getHelpLink($PhraseName, $PackageID, $ModuleID)
   {
      global $_ARCHON;

      if(!$PhraseName || !$PackageID || $ModuleID == NULL)
      {
         return '';
      }

      $objClickForHelpPhrase = Phrase::getPhrase('clickforhelp', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strClickForHelp = $objClickForHelpPhrase ? $objClickForHelpPhrase->getPhraseValue(ENCODE_HTML) : 'Click for help.';

      $strHelp = "<a class='helplink {phrasename: \"{$PhraseName}\", packageid: {$PackageID}, moduleid: {$ModuleID}}' title='{$strClickForHelp}' id='{$PhraseName}helplink'>?</a>";

      return $strHelp;
   }

   /**
    * Returns a section of the module administrative interface.
    * Returns NULL if section is not found.
    *
    * @param string $Section
    * @return mixed
    */
   public function getSection($Section)
   {
      return $this->AdminSections[$Section];
   }

   public function getSectionsByType($Type)
   {
      $arrAdminSections = array();
      foreach($this->AdminSections as $objAdminSection)
      {
         if($objAdminSection->Type == $Type)
         {
            $arrAdminSections[$objAdminSection->Name] = $objAdminSection;
         }
      }

      return $arrAdminSections;
   }

   /**
    * Initializes the administrative interface.
    *
    * @param string $Theme
    * @return string HTML to be echoed
    */
   public function initialize($Theme = CONFIG_CORE_DEFAULT_THEME)
   {
      global $_ARCHON;

      if(preg_match('/[\\/\\\\]/u', $Theme))
      {
         $_ARCHON->declareError("Could not load Theme: Invalid Theme $Theme.");
         return false;
      }

      $this->Theme = $Theme ? $Theme : CONFIG_CORE_DEFAULT_THEME;

      $this->ImagePath = "adminthemes/$Theme/images";
      if(is_dir("adminthemes/$Theme/js"))
      {
         $this->ThemeJavascriptPath = "adminthemes/$Theme/js";
      }

      if(file_exists('adminthemes/' . $this->Theme . '/init.inc.php'))
      {
         $cwd = getcwd();

         chdir('adminthemes/' . $this->Theme);

         require_once('init.inc.php');

         chdir($cwd);
      }

      // Prepare default sections.
      $this->insertSection('browse', 'browse');
      $this->insertSection('general', 'general');
      $this->insertSection('dialog', 'dialog', true);
   }

   public function insertHeaderControl($OnClick, $ImageOrPhraseName, $Disabled = false, $UIButtonClass = NULL)
   {
      $HeaderControl->OnClick = $OnClick;
      $HeaderControl->ImageOrPhraseName = $ImageOrPhraseName;
      $HeaderControl->UIButtonClass = $UIButtonClass;
      $HeaderControl->Disabled = $Disabled;

      $this->AdditionalHeaderControls[] = $HeaderControl;
   }

   public function insertFooterHTML($HTML)
   {
      $this->FooterHTML .= "\n$HTML";
   }

   public function insertHeaderHTML($HTML)
   {
      $this->HeaderHTML .= "\n$HTML";
   }

   public function insertSearchOption($FieldNames, $DataTraversalSources = NULL, $LabelPhraseName = '', $ChildrenSources = NULL, $ClassNames = NULL, $DefaultValues = 0)
   {
      $FieldNames = is_array($FieldNames) ? $FieldNames : array($FieldNames);
      $FieldName = end($FieldNames);

      $this->SearchOptions[$FieldName]->FieldNames = $FieldNames;
      $this->SearchOptions[$FieldName]->DataTraversalSources = is_array($DataTraversalSources) ? $DataTraversalSources : array($DataTraversalSources);
      $this->SearchOptions[$FieldName]->ChildrenSources = is_array($ChildrenSources) ? $ChildrenSources : array($ChildrenSources);
      $this->SearchOptions[$FieldName]->ClassNames = is_array($ClassNames) ? $ClassNames : array($ClassNames);
      $this->SearchOptions[$FieldName]->LabelPhraseName = $LabelPhraseName;
      $this->SearchOptions[$FieldName]->DefaultValues = is_array($DefaultValues) ? $DefaultValues : array($DefaultValues);

      if(reset($this->SearchOptions[$FieldName]->DataTraversalSources))
      {
         $optionRow = $this->AdminSections['browse']->insertRow();
         $selectField = $optionRow->insertHierarchicalSelect(
                         $this->SearchOptions[$FieldName]->FieldNames, $this->SearchOptions[$FieldName]->DataTraversalSources, $this->SearchOptions[$FieldName]->ChildrenSources, $this->SearchOptions[$FieldName]->ClassNames
         );

         $this->SearchOptions[$FieldName]->SelectField = $selectField;

         if($_REQUEST['adminoverridefield'] == "{$FieldName}SearchOption")
         {
            $_ARCHON->AdministrativeInterface->OverrideField = $searchField;
         }
      }
   }

   public function insertSection($Name, $Type = 'general', $Modal = false)
   {
      global $_ARCHON;

      $this->AdminSections[$Name] = New AdminSection();
      $this->AdminSections[$Name]->Name = $Name;
      $this->AdminSections[$Name]->Type = $Type;

      $this->AdminSections[$Name]->Modal = $Modal;
      $this->AdminSections[$Name]->ModuleID = $this->LoadingPackageID ? 0 : $_ARCHON->Module->ID;
      $this->AdminSections[$Name]->PackageID = $this->LoadingPackageID ? $this->LoadingPackageID : $_ARCHON->Package->ID;

      if($_REQUEST['adminoverridesection'] == $Name)
      {
         $this->OverrideSection = $this->AdminSections[$Name];
      }

      if($Type == 'multiple')
      {
         $this->addReloadSection($this->AdminSections[$Name]);
      }

      return $this->AdminSections[$Name];
   }

   public function getJSCarryOverFields()
   {
      $CarryOverFieldStrings = array();
      foreach($this->CarryOverFields as $CarryOverField)
      {
         $CarryOverFieldStrings[] = "'$CarryOverField'";
      }
      return '[' . implode(', ', $CarryOverFieldStrings) . ']';
   }

   public function outputInterface()
   {
      global $_ARCHON;


      if($this->OverrideSection || $this->OverrideRow || $this->OverrideField)
      {
         $this->Header->NoControls = true;
      }

      require_once('header.inc.php');


      if($this->OverrideSection)
      {
         echo("<div id='{$this->OverrideSection->Name}sectionbody'>\n");
         $this->OverrideSection->outputInterface();
         echo("</div>\n");
      }
      elseif($this->OverrideRow)
      {
         echo("<div id='{$this->OverrideRow->Name}rowwrapper'><table>\n");
         $this->OverrideRow->outputInterface();
         echo("</table></div>\n");
      }
      elseif($this->OverrideField)
      {
         $divID = str_replace(array('[', ']'), '', "{$this->OverrideField->getFieldName()}Field");
         if($this->OverrideField->IDPrefix)
         {
            $divID = $this->OverrideField->IDPrefix . $divID;
         }
         echo("<div id='{$divID}'>\n");
         $this->OverrideField->outputInterface();
         echo("</div>\n");
      }

      if($this->OverrideSection || $this->OverrideRow || $this->OverrideField)
      {
         require_once('footer.inc.php');
         return;
      }

      ob_start();


      $objModulePhrase = Phrase::getPhrase('header', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strModule = $objModulePhrase ? $objModulePhrase->getPhraseValue(ENCODE_HTML) : 'Archon Module';

      $strHelp = $_ARCHON->AdministrativeInterface->getHelpLink('header', $_ARCHON->Package->ID, $_ARCHON->Module->ID);

      $objSavePhrase = Phrase::getPhrase('save', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strSave = $objSavePhrase ? $objSavePhrase->getPhraseValue(ENCODE_HTML) : 'save';
      $objNewPhrase = Phrase::getPhrase('new', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strNew = $objNewPhrase ? $objNewPhrase->getPhraseValue(ENCODE_HTML) : 'new';
      $objDeletePhrase = Phrase::getPhrase('delete', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strDelete = $objDeletePhrase ? $objDeletePhrase->getPhraseValue(ENCODE_HTML) : 'delete';
      $objCancelPhrase = Phrase::getPhrase('cancel', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strCancel = $objCancelPhrase ? $objCancelPhrase->getPhraseValue(ENCODE_HTML) : 'cancel';

      $objFindPhrase = Phrase::getPhrase('find', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strFind = $objFindPhrase ? $objFindPhrase->getPhraseValue(ENCODE_HTML) : 'find:';

      $objDefaultHelpPhrase = Phrase::getPhrase('defaulthelp', PACKAGE_CORE, 0, PHRASETYPE_DESC);
      $strDefaultHelp = $objDefaultHelpPhrase ? $objDefaultHelpPhrase->getPhraseValue(ENCODE_HTML) : 'Click on the question mark icons or select fields to receive help.';

      $phrase = ($this->Object && $_ARCHON->classVarExists($this->Object, 'ParentID')) ? 'deletemessage_children' : 'deletemessage';
      $objDeleteMessagePhrase = Phrase::getPhrase($phrase, PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strDeleteMessage = $objDeleteMessagePhrase ? $objDeleteMessagePhrase->getPhraseValue(ENCODE_HTML) : 'Are you sure you want to delete this record AND all of its children (if applicable)?';


      if(isset($this->Object))
      {
         $curObjectName = $this->Object->ID ? bb_decode($this->Object->toString()) . " (ID: {$this->Object->ID})" : 'Add New';
      }


      $jsCarryOverFields = $this->getJSCarryOverFields();

      if(!$this->Object || $this->CanAdd == false || !$_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, ADD))
      {
         $disableAddClass = ' disabled';
      }

      if(!$this->Object || $this->CanDelete == false || !$_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, DELETE) || !reset($this->IDs))
      {
         $disableDeleteClass = ' disabled';
      }

      if(!$this->Object || $this->CanUpdate == false || !$_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, UPDATE))
      {
         $disableCancelClass = ' disabled';
         $disableSaveClass = ' disabled';
      }

      $helppinsource = $_ARCHON->AdministrativeInterface->ImagePath;
      $helppinsource .= $_ARCHON->Security->Session->getRemoteVariable('pinhelp') ? '/locked.gif' : '/unlocked.gif';



      //Set each section's position and generate the html links for each correlated tab
      $currentPosition = -1;
      $arrTabs = array();
      foreach($this->AdminSections as $objAdminSection)
      {
         if($objAdminSection->Name == 'browse' || $objAdminSection->Name == 'general')
         {
            $PackageID = PACKAGE_CORE;
            $ModuleID = MODULE_NONE;
         }
         else
         {
            $PackageID = $_ARCHON->Package->ID;
            $ModuleID = $_ARCHON->Module->ID;
         }

         if(!$objAdminSection->Modal)
         {
            $strTabHelpLink = $this->getHelpLink($objAdminSection->Name, $PackageID, $ModuleID);

            $currentPosition++;
            $objAdminSection->Position = $currentPosition;
            $arrTabs[$currentPosition] = "<li id='{$objAdminSection->Name}tab' class='ui-tabs-nav-item'><a tabindex='1' href='#{$objAdminSection->Name}fragment'>{$objAdminSection->toString()}</a>$strTabHelpLink</li>";

            // Disable all special sections if in a browsable module.
            if(!$this->AdminSections['browse']->Disabled)
            {
               if((!$this->Object || !$this->Object->ID) && (!$objAdminSection->RequiredForNewObjects && $objAdminSection->Name != 'browse' && $objAdminSection->Name != 'general'))
               {
                  $objAdminSection->Disabled = true;
               }
            }
            if($objAdminSection->Disabled)
            {
               $disabledTabs[$objAdminSection->Position] = $objAdminSection->Position;
            }
            else
            {
               $enabledTabs[$objAdminSection->Position] = $objAdminSection->Position;
            }
         }
      }

      $disabledTabs = is_array($disabledTabs) ? $disabledTabs : array();
      $enabledTabs = is_array($enabledTabs) ? $enabledTabs : array();

      $jsDisabledTabs = js_array($disabledTabs, false);

      // Automatically pick the browse tab if the object is new, otherwise pick the general tab
      $selectedTab = ($_REQUEST['selectedtab']) ? $_REQUEST['selectedtab'] : (($this->Object && $this->Object->ID) ? 1 : 0);

      if(array_search($selectedTab, $disabledTabs) !== false && !empty($enabledTabs))
      {
         $selectedTab = reset($enabledTabs);
      }
      ?>
      <script type="text/javascript">
         /* <![CDATA[ */

         $(function () {
      <?php
      if($_ARCHON->Module->ID != MODULE_PACKAGES)
      {
         ?>
                  $('#mainform').ajaxForm(function (xml) {
                     var success = admin_ui_displayresponse(xml);

                     if(success)
                     {
                        admin_ui_updateformchangearray();

                        admin_ui_processxml(xml);

                        admin_ui_updatenamefield('<?php echo($this->NameFieldName); ?>');

                        var boundElements = admin_ui_getboundelements();


         <?php
         foreach($this->ReloadSections as $objAdminSection)
         {
            echo("admin_ui_reloadsection('{$objAdminSection->Name}', boundElements);\n");
         }

         foreach($this->ReloadRows as $objAdminRow)
         {
            echo("admin_ui_reloadrow('{$objAdminRow->Name}');\n");
         }

         foreach($this->ReloadFields as $objAdminField)
         {
            echo("admin_ui_reloadfield('{$objAdminField->Name}');\n");
         }
         ?>
                     }
                     $('#savecontrol').removeClass('submitting');
                     $('#savecontrol').removeClass('disabled');         
                  });
         <?php
         if($this->Script)
         {
            echo("\n" . $this->Script . "\n");
         }
      }
      ?>

            $('#moduletabs').tabs({
               selected: <?php echo($selectedTab); ?>,
               disabled: <?php echo($jsDisabledTabs); ?>
            });

            $('#<?php echo($this->NameFieldName); ?>Input').focus();      
         });
         /* ]]> */
      </script>
      <div id="modulewrapper">
         <div id="moduletitle">
            <?php $moduleTitle = $curObjectName ? "{$strModule} - <span id='curobjectname'>{$curObjectName}</span> {$strHelp}" : "{$strModule} {$strHelp}";
            echo($moduleTitle); ?>
         </div>
         <div id="modulemain">
            <div id="successbox"></div>
            <div id="modulecontrols">
               <ul id="controlbuttons">                  
                  <li><a id="addcontrol" class="control icon<?php echo($disableAddClass); ?>" onclick="if($(this).hasClass('disabled')) return false; admin_ui_addnew(<?php echo($jsCarryOverFields); ?>);">
                        <span></span><?php echo($strNew); ?>
                     </a></li>
                  <li><a id="savecontrol" class="control icon<?php echo($disableSaveClass); ?>" onclick="if($(this).hasClass('disabled')) return false; admin_ui_submit();">
                        <span></span><?php echo($strSave); ?>
                     </a></li>
                  <li><a id="deletecontrol" class="control icon<?php echo($disableDeleteClass); ?>" onclick="if($(this).hasClass('disabled')) return false; admin_ui_confirm('<?php echo($strDeleteMessage); ?>', function() {admin_ui_delete(); return false});">
                        <span></span><?php echo($strDelete); ?>
                     </a></li>
                  <li><a id="cancelcontrol" class="control icon<?php echo($disableCancelClass); ?>" onclick="if($(this).hasClass('disabled')) return false; window.onbeforeunload= null; location.href = '<?php echo(htmlspecialchars($_SERVER['REQUEST_URI'])); ?>';">
                        <span></span><?php echo($strCancel); ?>
                     </a></li>

                  <?php
                  foreach($this->AdditionalHeaderControls as $HeaderControl)
                  {
                     $disableControl = $HeaderControl->Disabled ? ' disabled' : '';

                     $encodedOnclick = encode($HeaderControl->OnClick, ENCODE_HTML);
                     echo("<li><a class='control{$disableControl}' onclick='if($(this).hasClass(\"disabled\")) return false; $encodedOnclick'>\n");

                     if(file_exists("{$_ARCHON->AdministrativeInterface->ImagePath}/{$HeaderControl->ImageOrPhraseName}"))
                     {
                        echo("<img src='{$_ARCHON->AdministrativeInterface->ImagePath}/{$HeaderControl->ImageOrPhraseName}' />\n");
                     }
                     else
                     {
                        $objAdditionalControlPhrase = Phrase::getPhrase($HeaderControl->ImageOrPhraseName, $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
                        $strAdditionalControl = $objAdditionalControlPhrase ? $objAdditionalControlPhrase->getPhraseValue(ENCODE_HTML) : $HeaderControl->ImageOrPhraseName;

                        if($HeaderControl->UIButtonClass)
                        {
                           $buttonClass = "ui-icon-" . $HeaderControl->UIButtonClass;
                           echo("<span class='ui-icon {$buttonClass}' />\n");
                        }

                        echo("$strAdditionalControl");
                     }

                     echo("</a></li>\n");
                  }
                  ?>
               </ul>
               <div id="quicksearch" <?php if($this->DisableQuickSearch)
               echo("class='disabledrow'"); ?>>
                  <span id="quicksearchicon" class="searchicon"></span>
                  <input type="text" name="quicksearch" id="quicksearchfield" class="searchfield" <?php if($this->DisableQuickSearch)
                 echo("disabled=\"disabled\""); ?> /></div>
            </div>
            <form id="mainform" action="index.php" method="post" onsubmit="return false">
               <div id="hiddeninputs">
                  <input type="hidden" id="pInput" name="p" value="<?php echo($_REQUEST['p']); ?>" />
                  <input type="hidden" id="fInput" name="f" value="store" />
                  <input type="hidden" id="idInput" name="id" value="<?php echo($this->Object->ID); ?>" />
                  <select name="IDs[]" id="IDs" multiple="multiple" style="display: none;">
                     <?php
                     if(!empty($this->IDs))
                     {
                        foreach($this->IDs as $ID)
                        {
                           echo("<option value='$ID' selected='selected' >$ID</option>");
                        }
                     }
                     else
                     {
                        echo("<option>0</option>");
                     }
                     ?>
                  </select>
                  <?php
                  // Add hidden inputs for fields not displayed here:
                  $arrVariables = $this->Object ? $_ARCHON->getClassVars(get_class($this->Object)) : array();

                  $arrAccessedFields = array();
                  foreach($this->AccessedFields as $FieldName)
                  {
                     $arrAccessedFields[] = strtolower($FieldName);
                  }

                  if(!empty($arrVariables))
                  {
                     foreach($arrVariables as $name => $defaultValue)
                     {
                        if(!in_array(strtolower($name), $arrAccessedFields))
                        {
                           if(isset($this->Object->$name) && !is_object($this->Object->$name) && !is_array($this->Object->$name) && strtolower($name) != 'id' && (stripos($name, 'password') === false))
                           {
                              echo("<input type='hidden' id='{$name}Input' name='$name' value='{$this->Object->getString($name)}' />\n");
                           }

                           $arrAccessedFields[] = strtolower($name);
                        }
                     }
                  }
                  ?>
               </div>
               <div id="moduletabs">
                  <ul>
                     <?php
                     foreach($arrTabs as $strTab)
                     {
                        echo($strTab);
                     }
                     ?>
                  </ul>

                  <div id="fragmentcontainer">
                     <div id="sectionloadingscreen"></div>
                     <div id="helpbox"><div id="helpcontents"><?php echo($strDefaultHelp); ?></div>

                     </div>
                     <a id="helptoggle" onclick="admin_ui_togglehelpbox();">&nbsp;</a>
                     <a id="helppin" onclick="admin_ui_pinhelp();"><img src="<?php echo($helppinsource); ?>" alt="pin help" /></a>
                     <a id="editphrase" <?php
               if(!$_ARCHON->Security->verifyPermissions(MODULE_PHRASES, UPDATE))
               {
                  echo('class="hidden"');
               }
                     ?>>
                        <img src="<?php echo($this->ImagePath); ?>/edit.gif" alt="edit help" />
                     </a>
                     <?php
                     // Output tabbed sections.
                     foreach($this->AdminSections as $objAdminSection)
                     {
                        if(!$objAdminSection->Modal)
                        {
                           $helptoggledclass = $_ARCHON->Security->Session->getRemoteVariable('pinhelp') ? ' helptoggled' : '';

                           echo("<div id='{$objAdminSection->Name}fragment' class='fragment $helptoggledclass'>\n");
                           echo("<div id='{$objAdminSection->Name}sectionbody'>\n");
                           echo("<span class='tabposition' style='display:none'>{$objAdminSection->Position}</span>");
                           $objAdminSection->outputInterface();
                           echo("</div>\n");
                           echo("</div>\n");
                        }
                     }
                     ?>
                  </div>
               </div>
               <div id='storebox'><a id="scrolltop" title="Scroll to Top" href="#" onclick="javascript:scroll(0,0); return false;">
                     &nbsp;
                  </a></div>
            </form>
            <?php
            // Output modals.
            foreach($this->AdminSections as $objAdminSection)
            {
               if($objAdminSection->Modal)
               {
                  echo("<div id='{$objAdminSection->Name}modal'>");
                  $objAdminSection->outputInterface();

                  echo("</div>");
               }
               if($objAdminSection->Name == 'dialogform')
               {
                  echo("<div id='{$objAdminSection->Name}body'>");

                  $objAdminSection->outputInterface();

                  echo("</div>");
               }
            }
            ?>
         </div>
         <?php
         require_once('footer.inc.php');
         ob_end_flush();
      }

      public function sendResponse($Message, $IDs = array(), $Error = false, $SuppressRedirect = false, $Location = NULL, $Target = NULL, $Name = NULL)
      {
         global $_ARCHON;
         $Message = $_ARCHON->processPhrase($Message);
         header('Content-type: text/xml; charset=UTF-8');
         echo("<?xml version='1.0' encoding='UTF-8'?>\n");
         ?>
         <archonresponse error="<?php echo(bool($Error)); ?>">
            <message><?php echo(encode($Message, ENCODE_HTML)); ?></message>
            <?php
            if(is_array($IDs) && !empty($IDs))
            {
               foreach($IDs as $ID)
               {
                  echo("<id>{$ID}</id>\n");
               }
            }
            if($Error && !empty($_ARCHON->ProblemFields))
            {
               foreach($_ARCHON->ProblemFields as $strProblemField)
               {
                  echo("<problemfield>{$strProblemField}</problemfield>\n");
               }
            }
            $strSuppressRedirect = ($SuppressRedirect || $Location) ? 'true' : 'false';
            echo("<suppressredirect>{$strSuppressRedirect}</suppressredirect>\n");
            if($Location)
            {
               $target = $Target ? ' target="' . $Target . '"' : '';

               echo("<location{$target}>" . htmlspecialchars($Location) . "</location>\n");
            }
            if($Name)
            {
               echo("<name>" . htmlspecialchars($Name) . "</name>\n");
            }
            ?>
         </archonresponse>
         <?php
      }

      public function searchResults($SearchMethod, $arrSearchParameters = array(), $arrExclusions = array(), $toStringArguments = array())
      {
         global $_ARCHON;

         if($_ARCHON->methodExists($_ARCHON, $SearchMethod))
         {
            if(!key_exists('q', $arrSearchParameters))
            {
               $arrSearchParameters = array_merge(array('q' => ''), $arrSearchParameters);
            }

            // Build list of arguments for the search call.
            $arrSearchArguments = array();
            foreach($arrSearchParameters as $strSearchParameter => $defaultValue)
            {
               $arrSearchArguments[$strSearchParameter] = isset($_REQUEST[$strSearchParameter]) ? $_REQUEST[$strSearchParameter] : $defaultValue;
            }

            // Check to see if a RepositoryID search argument is passed
            if(array_key_exists('repositoryid', $arrSearchArguments))
            {
               $repositoryid = $arrSearchArguments['repositoryid'];

               // restrict read by repositoryid, if it exists
               if(CONFIG_CORE_LIMIT_REPOSITORY_READ_PERMISSIONS)
               {
                  if(!$repositoryid || !$_ARCHON->Security->verifyRepositoryPermissions($repositoryid))
                  {
                     if($_ARCHON->Security->Session->User->RepositoryLimit)
                     {
                        $arrSearchArguments['repositoryid'] = array_keys($_ARCHON->Security->Session->User->Repositories);
                     }
                  }
               }
            }

            $arrObjects = call_user_func_array(array($_ARCHON, $SearchMethod), $arrSearchArguments);
         }
         else
         {
            $arrObjects = array();
            $searchError = "Search function missing!";
         }

         foreach($arrExclusions as $ExclusionID => $objExclusion)
         {
            if($arrObjects[$ExclusionID])
            {
               unset($arrObjects[$ExclusionID]);
            }
         }

         if($_REQUEST['searchtype'] == 'json')
         {
            $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

            $objNoSearchResultsFoundPhrase = Phrase::getPhrase('nosearchresultsfound', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
            $strNoSearchResultsFound = $objNoSearchResultsFoundPhrase ? $objNoSearchResultsFoundPhrase->getPhraseValue(ENCODE_HTML) : 'No Search Results Found';

            header('Content-type: application/json; charset=UTF-8');
            if(!$searchError)
            {
               $arrResults = array();
               foreach($arrObjects as $ID => $obj)
               {
                  $str = json_encode(call_user_func_array(array($obj, 'toString'), $toStringArguments));
                  $str = str_replace('&#039;', "'", $str);
                  $str = str_replace('&quot;', '\"', $str);
                  $arrResults[] = '{"id":"' . $ID . '","string":' . $str . '}';
               }


               if($callback)
               {
                  echo($callback . "(");
               }
               if(!empty($arrResults))
               {
                  echo("{\"results\":[" . implode(",", $arrResults) . "]}");
               }
               else
               {
                  echo("{\"results\":[{\"id\":\"0\",\"string\":" . json_encode($strNoSearchResultsFound) . "}]}");
               }
               if($callback)
               {
                  echo(");");
               }
            }
            else
            {
               if($callback)
               {
                  echo($callback . "(");
               }
               echo("{\"results\":[{\"id\":\"0\",\"string\":" . json_encode($searchError) . "}]}");
               if($callback)
               {
                  echo(");");
               }
            }
            die();
         }
         elseif($_REQUEST['searchtype'] == 'listsearch')
         {
            header('Content-type: text/html; charset=UTF-8');

            if(!$searchError)
            {
               foreach($arrObjects as $ID => $obj)
               {
                  echo("<option value='{$obj->ID}'>" . call_user_func_array(array($obj, 'toString'), $toStringArguments) . "</option>");
               }
            }
            else
            {
               echo("<option value='0'>" . encode($searchError, ENCODE_HTML) . "</option>");
            }
            die();
         }

         return $arrObjects;
      }

      public function setCarryOverFields($FieldNames = array())
      {
         $this->CarryOverFields = $FieldNames;
      }

      /**
       * Sets class of primary object for the current instantiation of the administrative interface.
       * It then loads the current object or objects in memory according to the ids set in $_REQUEST.
       *
       * @param string $ClassName
       * @return string HTML to be echoed
       */
      public function setClass($ClassName, $CanUpdate = true, $CanAdd = true, $CanDelete = true)
      {
         global $_ARCHON;

         if(!class_exists($ClassName))
         {
            return false;
         }
         else
         {
            $this->Class = $ClassName;
         }


         $this->CanUpdate = $CanUpdate;
         $this->CanAdd = $CanAdd;
         $this->CanDelete = $CanDelete;

         $this->IDs = $_REQUEST['ids'] ? $_REQUEST['ids'] : array();
         $this->IDs[] = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
         $this->IDs[] = $_REQUEST[strtolower($ClassName) . 'id'] ? $_REQUEST[strtolower($ClassName) . 'id'] : 0;

         $this->IDs = array_unique($this->IDs);

         // Check to see if the user selected ID 0 (a new ID) and
         // some existing IDs.  If so, pop off the new, because selecting
         // new and existing at the same time doesn't make any sense.
         if(($newkey = array_search('0', $this->IDs)) !== false && count($this->IDs) > 1)
         {
            unset($this->IDs[$newkey]);
         }

         if(current($this->IDs) != 0)
         {
            if(count($this->IDs) > 1)
            {
               // Get the first object, so createConsensusVariable will have something
               // to compare the first looped object with.
               $this->Object = New $this->Class(current($this->IDs));
               $this->Object->dbLoad();

               foreach($this->IDs as $ID)
               {
                  if($ID)
                  {
                     $tmpObject = New $this->Class($ID);

                     if(!$tmpObject->dbLoad())
                     {
                        die($_ARCHON->Error);
                     }

                     $this->Object = $_ARCHON->createConsensusVariable($this->Object, $tmpObject);
                  }
               }
            }
            else
            {
               $this->Object = New $this->Class(current($this->IDs));

               if(!$this->Object->dbLoad())
               {
                  die($_ARCHON->Error);
               }
            }
         }
         elseif($CanAdd)
         {
            $this->Object = New $this->Class($_REQUEST);
         }


         $this->AccessedFields = array();
      }

      public function setNameField($FieldName)
      {
         $this->NameFieldName = $FieldName;
      }

      /**
       * Returns HTML for a standard help button image. When clicked, an alert
       * box will appear showing the specified phrase.
       *
       * @param string $PhraseName
       * @param integer $PackageID
       * @param integer $ModuleID
       * @return string HTML to be echoed
       */
      public function createHelpButton($PhraseName, $PackageID, $ModuleID)
      {
         global $_ARCHON;

         $objClickForHelpPhrase = Phrase::getPhrase('clickforhelp', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
         $strClickForHelp = $objClickForHelpPhrase ? $objClickForHelpPhrase->getPhraseValue(ENCODE_HTML) : 'Click for help.';

         $objHelpPhrase = Phrase::getPhrase($PhraseName, $PackageID, $ModuleID, PHRASETYPE_DESC);
         $strHelp = $objHelpPhrase ? '<img src="' . $_ARCHON->AdministrativeInterface->ImagePath . '/help.gif" class="helpbutton" alt="help button" title="' . $strClickForHelp . '" border="0" onclick="alert(\'' . $objHelpPhrase->getPhraseValue(ENCODE_JAVASCRIPTTHENHTML) . '\'); return false;">' : '';

         return $strHelp;
      }

      public $AccessedFields = array();
      public $AdditionalHeaderControls = array();
      public $AdminSections = array();
      public $CanUpdate = true;
      public $CanAdd = true;
      public $CanDelete = true;
      public $CarryOverFields = array();
      public $DisableQuickSearch = false;
      public $EscapeXML = true;
      public $ForcedValues = array();
      public $FooterHTML = '';
      public $HeaderHTML = '';
      public $IDs = array();
      public $LoadingPackageID = 0;
      public $NameFieldName;
      public $Object = NULL;
      public $OverrideRow = NULL;
      public $OverrideSection = NULL;
      public $Redirect = NULL;
      public $ReloadSections = array();
      public $ReloadRows = array();
      public $ReloadFields = array();
      public $Script = NULL;
      public $SearchOptions = array();

   }

   $_ARCHON->mixClasses('AdministrativeInterface', 'Core_AdministrativeInterface');
   ?>
