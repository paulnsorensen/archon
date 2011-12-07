<?php

abstract class Core_AdminSection
{

   public function disable($SuppressOutput = true)
   {
      $this->Disabled = true;
      $this->SuppressOutput = $SuppressOutput;
   }

   public function getRow($Name)
   {
      return $this->AdminRows[$Name];
   }

   // WARNING!! This is only implimented for multiple sections right now.
   // -- added to regular sections as well
   public function insertHiddenField($Name)
   {
      $this->HiddenFields[] = $Name;
   }

   public function insertRow($Name = NULL, $PhraseName = NULL)
   {
      global $_ARCHON;

      $NoName = !isset($Name);
      $Name = $NoName ? 'Row' . count($this->AdminRows) : $Name;

      $this->AdminRows[$Name] = New AdminRow();
      $this->AdminRows[$Name]->NoName = $NoName;
      $this->AdminRows[$Name]->Name = $Name;
      $this->AdminRows[$Name]->ParentSection = $this;
      $this->AdminRows[$Name]->InMultiple = ($this->Type == 'multiple');
      $this->AdminRows[$Name]->PhraseName = $PhraseName;
      $this->AdminRows[$Name]->ModuleID = $_ARCHON->AdministrativeInterface->LoadingPackageID ? 0 : $_ARCHON->Module->ID;
      $this->AdminRows[$Name]->PackageID = $_ARCHON->AdministrativeInterface->LoadingPackageID ? $_ARCHON->AdministrativeInterface->LoadingPackageID : $_ARCHON->Package->ID;

      if ($this->Type == 'dialog')
      {
         $this->AdminRows[$Name]->IDPrefix = 'dialog-';
      }

      if ($_REQUEST['adminoverriderow'] == $Name)
      {
         $_ARCHON->AdministrativeInterface->OverrideRow = $this->AdminRows[$Name];
      }

      return $this->AdminRows[$Name];
   }

   // WARNING: Only works within standard sections.
   public function insertSubSection($objAdminSection)
   {
      global $_ARCHON;

      $Name = "section-{$objAdminSection->Name}";

      $this->AdminRows[$Name] = $objAdminSection;
      $this->AdminRows[$Name]->ModuleID = $_ARCHON->Module->ID;
      $this->AdminRows[$Name]->PackageID = $_ARCHON->Package->ID;

      return $this->AdminRows[$Name];
   }

   public function outputBrowseInterface()
   {
      global $_ARCHON;



      $objFilterPhrase = Phrase::getPhrase('filter', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strFilter = $objFilterPhrase ? $objFilterPhrase->getPhraseValue(ENCODE_HTML) : 'filter';
      $objLimitPhrase = Phrase::getPhrase('limit', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strLimit = $objLimitPhrase ? $objLimitPhrase->getPhraseValue(ENCODE_HTML) : 'limit';

      $CarryOverFieldStrings = array();
      foreach ($_ARCHON->AdministrativeInterface->CarryOverFields as $CarryOverField)
      {
         $CarryOverFieldStrings[] = "'$CarryOverField'";
      }

      $jsCarryOverFields = '[' . implode(', ', $CarryOverFieldStrings) . ']';

      $disabledTabs = range(0, count($_ARCHON->AdministrativeInterface->AdminSections) - 1);
      unset($disabledTabs[$this->Position]);
      $jsDisabledTabs = js_array($disabledTabs, false);
?>
      <script type="text/javascript">
         /* <![CDATA[ */

         var filterTimeout;
         function useBrowseFilter(highlight)
         {
            if($('#moduletabs').tabs('option','selected') != $('#browsesectionbody .tabposition').text())
            {
               return;
            }

            if(highlight)
            {
               $('#browsefilterfield').effect('highlight');
            }

            $('#browselistselect').load('index.php', {
               p: request_p,
               f: 'search',
               q: $('#browsefilterfield').val(),
               searchtype: 'listsearch',
               limit: $('#browselimitfield').val()
<?php
      if ($_ARCHON->AdministrativeInterface->Object && $_ARCHON->classVarExists($_ARCHON->AdministrativeInterface->Object, 'ParentID'))
      {
         echo(",\n");
         echo("parentid: '{$_ARCHON->AdministrativeInterface->Object->ParentID}'");
      }
      foreach ($_ARCHON->AdministrativeInterface->SearchOptions as $SearchOption)
      {
         foreach ($SearchOption->FieldNames as $FieldName)
         {
            echo(",\n");
            echo("{$FieldName}: function () { return $('#{$FieldName}SearchOptionInput').val(); }");
         }
      }
?>
      }, function () {
         $('#browselistselect').change();
      });
      }

      var browseListTimeout;
      function browseListChange()
      {
      if($('#browselistselect>:selected').length == 0)
      {
         if(!permissionsDelete || $('#IDs>option').val() == 0)
         {
            $('#deletecontrol').addClass('disabled');
         }
      }
      else if($('#browselistselect>:selected').length == 1)
      {
         location.href = 'index.php?p='+request_p+'&id=' + $('#browselistselect>:selected').val();
      }
      else if($('#browselistselect>:selected').length > 1 && $('#moduletabs').tabs('option','selected') == 0)
      {
         $('#moduletabs').tabs('option','disabled', <?php echo($jsDisabledTabs); ?>);
         $('.ui-tabs-disabled').fadeTo('normal', 0.35);
         if(permissionsDelete)
         {
            $('#deletecontrol').removeClass('disabled');
         }      
      }
      }

      $(function () {
      $('#browsefilterfield').bind(($.browser.opera ? "keypress" : "keydown") + ".filter", function (event) {
         if(event.keyCode == 13)
         {
            return false;
         }

         clearTimeout(filterTimeout);
         filterTimeout = setTimeout(function () { useBrowseFilter(true); }, 400);
      });

      $('#browselimitfield').bind(($.browser.opera ? "keypress" : "keydown") + ".filter", function (event) {
         if(event.keyCode == 13)
         {
            return false;
         }

         clearTimeout(filterTimeout);
         filterTimeout = setTimeout(function () { useBrowseFilter(true); }, 600);
      });


      $('#browselistselect').change(function (event) {
         clearTimeout(browseListTimeout);
         browseListTimeout = setTimeout(browseListChange, 1200);
      });

      $('#browselistselect').dblclick(function () {
         location.href = 'index.php?p='+request_p+'&id=' + $('#browselistselect>:selected').val();
      });

      $('#moduletabs').bind('tabsshow', function (event, ui) {
         if($('#moduletabs').tabs('option','selected') == $('#browsesectionbody .tabposition').text())
         {
            useBrowseFilter();
         }
      });

      useBrowseFilter();
      });
      /* ]]> */
      </script>
      <div id="searchoptions">
   <?php
      if (!empty($_ARCHON->AdministrativeInterface->SearchOptions))
      {
         foreach ($_ARCHON->AdministrativeInterface->SearchOptions as $FieldName => $SearchOption)
         {
            if ($SearchOption->SelectField)
            {
               $objOptionLabelPhrase = Phrase::getPhrase($SearchOption->LabelPhraseName, $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
               $strOptionLabel = $objOptionLabelPhrase ? $objOptionLabelPhrase->getPhraseValue(ENCODE_HTML) : $SearchOption->LabelPhraseName;
               $objNoSelectionPhrase = Phrase::getPhrase('selectone', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
               $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

               if (!($this->Object->ID || isset($_REQUEST[strtolower($FieldName)])))
               {
                  foreach ($SearchOption->FieldNames as $key => $subFieldName)
                  {
                     $_ARCHON->AdministrativeInterface->ForcedValues[$subFieldName] = $SearchOption->DefaultValues[$key];
                  }
               }

               echo("<div class='searchoption'>\n");
               echo("<div class='searchoptionlabel'>$strOptionLabel</div>\n");

               $FieldID = str_replace(array('[', ']'), '', $SearchOption->SelectField->getFieldName());
               echo("<div id='{$FieldID}Field' class='adminfieldwrapper'>\n");
               $SearchOption->SelectField->outputInterface();
               echo("</div>\n");

               echo("</div>\n");


               if (!($this->Object->ID || isset($_REQUEST[strtolower($FieldName)])))
               {
                  foreach ($SearchOption->FieldNames as $key => $subFieldName)
                  {
                     unset($_ARCHON->AdministrativeInterface->ForcedValues[$subFieldName]);
                  }
               }
            }
            else
            {
               $value = ($this->Object->ID || isset($_REQUEST[strtolower($FieldName)])) ? $_ARCHON->AdministrativeInterface->Object->$FieldName : reset($SearchOption->DefaultValues);
               echo("<input id='{$FieldName}SearchOptionInput' type='hidden' value='$value' />");
            }
         }
      }
   ?>
   </div>
   <div id="filterline">
      <label for="browsefilterfield"><?php echo($strFilter); ?></label>
      <input type="text" id="browsefilterfield" name="q" value="" maxlength="200" />
      <div class="browselimit"><label for="browselimitfield"><?php echo($strLimit); ?></label>
         <input type="text" id="browselimitfield" name="limit" value="<?php echo(CONFIG_CORE_SEARCH_RESULTS_LIMIT); ?>" maxlength="11" /></div>
   </div>
   <div id="browselist">
      <select id="browselistselect" multiple="multiple">
         <option value="0">&nbsp;</option>
      </select>
   </div>
<?php
   }

   public function outputDialogInterface()
   {
      global $_ARCHON;

      echo("<div id='dialogloadingscreen'></div>\n");
      echo("<div id='dialogresponse'></div>\n");

      if ($this->DialogArguments->Type == 'form')
      {
         if (!$this->DialogArguments->Object)
         {
            $this->DialogArguments->Object = $this->AdministrativeInterface->$Object;
         }
         $id = $this->DialogArguments->Object->ID ? $this->DialogArgumentsObject->ID : 0;
         $IDs = $_REQUEST['ids'] ? $_REQUEST['ids'] : array();
         if (empty($IDs))
         {
            $IDs[] = $_REQUEST['id'] ? $_REQUEST['id'] : $id;
         }
?>
         <form id="dialogform" action="index.php" method="post">
            <div id="hiddeninputs">
               <input type="hidden" id="dialog-pInput" name="p" value="<?php echo($this->DialogArguments->PValue); ?>" />
               <input type="hidden" id="dialog-fInput" name="f" value="<?php echo($this->DialogArguments->FValue); ?>" />
               <input type="hidden" id="dialog-idInput" name="id" value="<?php echo($this->DialogArguments->Object->ID); ?>" />
               <select name="IDs[]" id="dialog-IDs" multiple="multiple" style="display: none;">
         <?php
         if (!empty($IDs))
         {
            foreach ($IDs as $ID)
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
   </div>
   <table>
      <?php
         foreach ($this->AdminRows as $objAdminRow)
         {
            if (get_class($objAdminRow) == 'AdminSection')
            { //this could be useful
               echo("</table>");
               echo("<div class='subsection'>");
//                     $objAdminRow->IDPrefix = 'dialog-';
               $objAdminRow->outputInterface();
               echo("</div>");
               echo("<table>");
            }
            else
            {
               $objAdminRow->IDPrefix = 'dialog-';
               $objAdminRow->outputInterface();
            }
         }
      ?>
      </table>
   </form>
<?php
      }
   }

   public function outputInterface()
   {
      global $_ARCHON;

      if ($this->SuppressOutput)
      {
         return;
      }

      if ($this->Type == 'browse')
      {
         $this->outputBrowseInterface();
      }
      elseif ($this->Type == 'custom')
      {
         echo($this->CustomHTML);
      }
      elseif ($this->Type == 'dialog')
      {
         $this->outputDialogInterface();
      }     
      elseif ($this->Type == 'multiple')
      {
         $this->outputMultipleInterface();
      }
      elseif ($this->Type == 'permissions')
      {
         $this->outputPermissionsInterface();
      }     
      else
      {
         foreach ($this->HiddenFields as $fieldName)
         {
            $Name = $fieldName ? $fieldName : 'Field' . count($this->HiddenFields);

            $hiddenField = New AdminField();
            $hiddenField->Name = $Name;
            $hiddenField->Type = 'hidden';
            $hiddenField->outputInterface();
         }
         $class = $this->Class ? " class='" . $this->Class . "'" : '';

         echo("<table" . $class . ">");

         foreach ($this->AdminRows as $objAdminRow)
         {
            if (get_class($objAdminRow) == 'AdminSection')
            {
               echo("</table>");
               echo("<div class='subsection'>");
               $objAdminRow->outputInterface();
               echo("</div>");
               echo("<table>");
            }
            else
            {
               $objAdminRow->outputInterface();
            }
         }
         echo("</table>");
         $class = $this->Class ? " class='labelfiller " . $this->Class . "'" : "class='labelfiller'";
         echo("<div $class></div>");
      }
   }

   public function outputPermissionsInterface()
   {
      global $_ARCHON;


      $objPackagePhrase = Phrase::getPhrase('permissions_package', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strPackage = $objPackagePhrase ? $objPackagePhrase->getPhraseValue(ENCODE_HTML) : 'Module Package';
      $objModulePhrase = Phrase::getPhrase('permissions_module', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strModule = $objModulePhrase ? $objModulePhrase->getPhraseValue(ENCODE_HTML) : 'Module';
      $objPermissionsReadPhrase = Phrase::getPhrase('permissions_read', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strPermissionsRead = $objPermissionsReadPhrase ? $objPermissionsReadPhrase->getPhraseValue(ENCODE_HTML) : 'Read';
      $objPermissionsAddPhrase = Phrase::getPhrase('permissions_add', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strPermissionsAdd = $objPermissionsAddPhrase ? $objPermissionsAddPhrase->getPhraseValue(ENCODE_HTML) : 'Add';
      $objPermissionsUpdatePhrase = Phrase::getPhrase('permissions_update', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strPermissionsUpdate = $objPermissionsUpdatePhrase ? $objPermissionsUpdatePhrase->getPhraseValue(ENCODE_HTML) : 'Update';
      $objPermissionsDeletePhrase = Phrase::getPhrase('permissions_delete', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strPermissionsDelete = $objPermissionsDeletePhrase ? $objPermissionsDeletePhrase->getPhraseValue(ENCODE_HTML) : 'Delete';
      $objPermissionsFullControlPhrase = Phrase::getPhrase('permissions_fullcontrol', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strPermissionsFullControl = $objPermissionsFullControlPhrase ? $objPermissionsFullControlPhrase->getPhraseValue(ENCODE_HTML) : 'Full Control';

      $arrModules = $_ARCHON->getAllModules();
      if ($_REQUEST['adminoverridesection'] == $this->Name)
      {
         $_ARCHON->loadModulePhrases();
      }

      /* on change, set to true to indicate need for storing advanced permissions data */
      echo("<input type='hidden' name='setadvpermissions' value='false' />");

      echo("<table id='{$this->Name}permissionstable' class='permissionstable'>\n");

      echo("<tr class='labelcell'>
          <th >$strPackage</th>
          <th>$strModule</th>
          <th>$strPermissionsRead</th>
          <th>$strPermissionsAdd</th>
          <th>$strPermissionsUpdate</th>
          <th>$strPermissionsDelete</th>
          <th>$strPermissionsFullControl</th>
        </tr>");

      $arrPermissions = $_ARCHON->{$this->PermissionArguments->GetPermissionsFunction}($_ARCHON->AdministrativeInterface->Object->ID);

      if (!empty($arrModules))
      {
         foreach ($arrModules as $ModuleID => $objModule)
         {


            $cr = (READ & $arrPermissions[$ModuleID]) ? ' checked=\'checked\'' : '';
            $ca = (ADD & $arrPermissions[$ModuleID]) ? ' checked=\'checked\'' : '';
            $cu = (UPDATE & $arrPermissions[$ModuleID]) ? ' checked=\'checked\'' : '';
            $cd = (DELETE & $arrPermissions[$ModuleID]) ? ' checked=\'checked\'' : '';
            $cf = (FULL_CONTROL & $arrPermissions[$ModuleID]) ? ' checked=\'checked\'' : '';
?>
            <tr>
               <td><?php echo($objModule->Package->toString()); ?></td>
               <td><?php echo($objModule->toString()); ?></td>
               <td><?php echo("<input type='checkbox' name='Read[{$ModuleID}]' value='" . READ . "' $cr />"); ?></td>
               <td><?php echo("<input type='checkbox' name='Add[{$ModuleID}]' value='" . ADD . "' $ca />"); ?></td>
               <td><?php echo("<input type='checkbox' name='Update[{$ModuleID}]' value='" . UPDATE . "' $cu />"); ?></td>
               <td><?php echo("<input type='checkbox' name='Delete[{$ModuleID}]' value='" . DELETE . "' $cd />"); ?></td>
               <td><?php echo("<input class='fullcontrol' type='checkbox' name='FullControl[{$ModuleID}]' value='" . FULL_CONTROL . "' $cf />"); ?></td>
            </tr>
<?php
         }
      }

      echo("</table>\n");
?>
      <script type="text/javascript">
         /* <![CDATA[ */
         $(function(){      
            admin_ui_delegationbind('click', '.permissionstable input:checkbox', function (e) {
               $("input[name='setadvpermissions']").val(true);
               var isChecked = $(e.target).attr("checked");
               if(isChecked == '')
               {
                  $(e.target).parent().siblings().children('.fullcontrol').attr("checked", '');
               }
            });
         });
         /* ]]> */
      </script>
<?php
   }

   public function outputMultipleInterface()
   {
      global $_ARCHON;

      call_user_func(array($_ARCHON->AdministrativeInterface->Object, $this->MultipleArguments->ArrayLoadFunction));

      // Put a blank entry on the beginning of the array to allow the user to add
      // a new entry.
      $objNewObject = New $this->MultipleArguments->Class;
      $objNewObject->__construct(0);

      if (!empty($this->MultipleArguments->DefaultValues))
      {
         foreach ($this->MultipleArguments->DefaultValues as $FieldName => $DefaultValue)
         {
            $objNewObject->$FieldName = $DefaultValue;
         }
      }

      array_unshift($_ARCHON->AdministrativeInterface->Object->{$this->MultipleArguments->ArrayName}, $objNewObject);

      echo("<table class='multipletable'>\n");

      echo("<tr>\n");

      foreach ($this->AdminRows as $objAdminRow)
      {
         $objAdminRow->outputInterface();
      }
      echo("<th class='labelcell'></th>\n");

      echo("</tr>\n");

      foreach ($_ARCHON->AdministrativeInterface->Object->{$this->MultipleArguments->ArrayName} as $objObject)
      {
         $this->CurrentMultipleObject = $objObject;

         echo("<tr id='{$this->MultipleArguments->ArrayName}{$objObject->ID}row' class='multiplerow{$objObject->ID}'>\n");
         foreach ($this->AdminRows as $objAdminRow)
         {
            echo("<td class='multiplefield'>\n");
            foreach ($objAdminRow->AdminFields as $objAdminField)
            {
               $FieldID = str_replace(array('[', ']'), '', $objAdminField->getFieldName());
               echo("<div id='{$FieldID}Field' class='adminfieldwrapper'>\n");
               $objAdminField->outputInterface();
               echo("</div>\n");
            }
            echo("</td>\n");
         }

         echo("<td style='padding-right:10px'>\n");

         foreach ($this->HiddenFields as $fieldName)
         {
            echo("<input type='hidden' id='{$this->MultipleArguments->ArrayName}{$objObject->ID}{$fieldName}Input' name='{$this->MultipleArguments->ArrayName}[{$objObject->ID}][{$fieldName}]' value='{$objObject->getString($fieldName)}' />\n");
         }
         if ($objObject->ID)
         {
?>
            <input type="hidden" id="<?php echo($this->MultipleArguments->ArrayName); ?><?php echo($objObject->ID); ?>_fDeleteInput" name="<?php echo($this->MultipleArguments->ArrayName); ?>[<?php echo($objObject->ID); ?>][_fDelete]" value="0" />
            <a id="multipledelete<?php echo($objObject->ID); ?>" class="adminformbutton" onclick="admin_ui_markmultipledeletion('<?php echo($this->MultipleArguments->ArrayName); ?>', '<?php echo($objObject->ID); ?>')">Delete</a>

<?php
         }
         echo("</td>\n</tr>\n");
      }

      echo("</table>\n");
   }

   
        

      public function setCustomArguments($CustomHTML)
      {
         $this->CustomHTML = $CustomHTML;
      }

      public function setDialogArguments($DialogType = 'form', $Object = NULL, $PValue = NULL, $FValue = NULL)
      {
         global $_ARCHON;

         $this->Modal = true;
         $this->DialogArguments->Type = $DialogType;
         $this->DialogArguments->Object = $Object;
         $this->DialogArguments->PValue = $PValue;
         $this->DialogArguments->FValue = $FValue;
         $_ARCHON->AdministrativeInterface->OverrideSection = $this;
      }

      public function setMultipleArguments($Class, $ArrayName, $ArrayLoadFunction, $arrDefaultValues = NULL)
      {
         global $_ARCHON;

         $this->MultipleArguments->Class = $Class;
         $this->MultipleArguments->ArrayName = $ArrayName;
         $this->MultipleArguments->ArrayLoadFunction = $ArrayLoadFunction;
         $this->MultipleArguments->DefaultValues = isset($arrDefaultValues) ? $arrDefaultValues : array();
      }

      public function setPermissionsArguments($GetPermissionsFunction)
      {
         global $_ARCHON;

         $this->PermissionArguments->GetPermissionsFunction = $GetPermissionsFunction;

         $_ARCHON->AdministrativeInterface->addReloadSection($this);
      }

     

      public function setClass($Class)
      {
         $this->Class = $Class;
      }

//WARNING: This is only implemented for relation interfaces, currently
      public function insertSearchOption($FieldName, $DataTraversalSource = NULL, $LabelPhraseName = '', $ClassName = NULL, $DefaultValue = 0)
      {
         $this->SearchOptions[$FieldName]->FieldName = $this->Name . $FieldName . "SearchOption";
         $this->SearchOptions[$FieldName]->DataTraversalSource = $DataTraversalSource;
         //$this->SearchOptions[$FieldName]->ChildrenSource = $ChildrenSource;
         $this->SearchOptions[$FieldName]->ClassName = $ClassName;
         $this->SearchOptions[$FieldName]->LabelPhraseName = $LabelPhraseName;
         $this->SearchOptions[$FieldName]->DefaultValue = $DefaultValue;

         if ($this->SearchOptions[$FieldName]->DataTraversalSource)
         {
            $optionRow = new AdminRow();
            $selectField = $optionRow->insertSelect(
                            $this->SearchOptions[$FieldName]->FieldName, $this->SearchOptions[$FieldName]->DataTraversalSource
            );

            $this->SearchOptions[$FieldName]->SelectField = $selectField;

//            if($_REQUEST['adminoverridefield'] == "{$FieldName}SearchOption")
//            {
//                $_ARCHON->AdministrativeInterface->OverrideField = $searchField;
//            }
         }
      }

      public function toString()
      {
         global $_ARCHON;

         if ($this->Name != 'browse' && $this->Name != 'general')
         {
            $objNamePhrase = Phrase::getPhrase($this->Name, $this->PackageID, $this->ModuleID, PHRASETYPE_ADMIN);
            $strName = $objNamePhrase ? $objNamePhrase->getPhraseValue(ENCODE_HTML) : $this->Name;
         }
         else
         {
            $objNamePhrase = Phrase::getPhrase("adminsection_{$this->Name}", PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
            $strName = $objNamePhrase ? $objNamePhrase->getPhraseValue(ENCODE_HTML) : $this->Name;
         }

         return $strName;
      }

      public $CurrentMultipleObject = NULL;
      public $Class = NULL;
      public $Disabled = false;
      public $RequiredForNewObjects = false;
      public $HiddenFields = array();
      public $Modal = false;
      public $ModuleID = 0;
      /**
       * Name of the section.
       *
       * @var string
       */
      public $Name = '';
      public $PackageID = 0;
      public $Position = 0;
      public $SuppressOutput = NULL;
      /**
       * @var string $Type
       */
      public $Type = NULL;
      /**
       * @var AdminRow[] $AdminRows
       */
      public $AdminRows = array();
      public $SearchOptions = array();
   }

   $_ARCHON->mixClasses('AdminSection', 'Core_AdminSection');
?>
