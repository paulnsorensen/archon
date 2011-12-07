<?php

abstract class Core_AdminRow
{

   public function getAPRCode()
   {
      global $_ARCHON;

      if (!isset($this->APRCode))
      {
         $this->APRCode = $_ARCHON->Packages[$this->PackageID]->APRCode;
      }
      return $this->APRCode;
   }

   public function disable()
   {
      $this->Disabled = true;
   }

   public function disableHelp()
   {
      $this->HelpDisabled = true;
   }

   public function outputHelpURL()
   {
      global $_ARCHON;

      static $lang = NULL;

      if (!isset($lang))
      {
         $strLanguageShort = $_ARCHON->getLanguageShortFromID($_ARCHON->Security->Session->getLanguageID());

         if (file_exists("packages/" . $this->getAPRCode() . "/adminhelp/" . $this->AdvancedHelpURL['url'] . "/" . $strLanguageShort . "/"))
         {
            $lang = $strLanguageShort;
         }
         else
         {
            $lang = 'eng'; //assuming english files will always exist
         }
      }

      return "packages/" . $this->getAPRCode() . "/adminhelp/" . $this->AdvancedHelpURL['url'] . "/" . $lang . "/" . $this->AdvancedHelpURL['file'] . ".html";
   }

   public function addHelpURL($URL, $File, $External = false)
   {
      $this->AdvancedHelpURL['url'] = $URL;
      $this->AdvancedHelpURL['file'] = $File;
      $this->AdvancedHelpURL['external'] = $External;
   }

   public function getHelpLink($PhraseName, $PackageID, $ModuleID)
   {
      global $_ARCHON;

      if ($this->HelpDisabled)
      {
         return '';
      }

      $objClickForHelpPhrase = Phrase::getPhrase('clickforhelp', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strClickForHelp = $objClickForHelpPhrase ? $objClickForHelpPhrase->getPhraseValue(ENCODE_HTML) : 'Click for help.';

      $strHelp = "<a class='helplink {phrasename: \"{$PhraseName}\", packageid: {$PackageID}, moduleid: {$ModuleID}}' title='{$strClickForHelp}' id='{$PhraseName}helplink'></a>";


      if ($this->AdvancedHelpURL)
      {
         if (!$this->AdvancedHelpURL['external'])
         {

            $strHelp .= "<a class='advhelplink' title='{$strClickForHelp}' onclick='admin_ui_loadadvancedhelp(\"{$this->outputHelpURL()}\"); return false;'></a>";
         }
         else
         {
            $strHelp .= "<a class='advhelplink external' title='{$strClickForHelp}' rel='external' href='{$this->outputHelpURL()}'></a>";
         }
      }

      return $strHelp;
   }

   public function insertAdvancedSelect($FieldName, $Arguments = array(), $Events = array())
   {
      $objAdminField = $this->insertField($FieldName, 'advselect', $Events);
      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      $objAdminField->Arguments = $Arguments;

      // also need to know search options? page and quick add locations (which is likely the same)

//      $objAdminField->Arguments->Class = $Class;
//      $objAdminField->Arguments->SearchMethodOrDataSource = $SearchMethodOrDataSource;
//      $objAdminField->Arguments->RelatedArrayName = $RelatedArrayName;
//      $objAdminField->Arguments->RelatedArrayLoadFunction = $RelatedArrayLoadFunction;
//      $objAdminField->Arguments->SearchParameters = $SearchParameters;
//      $objAdminField->Arguments->SearchOptions = $SearchOptions;
//      $objAdminField->Arguments->toStringArguments = $toStringArguments;
//      $objAdminField->Arguments->ModuleID = $ModuleID;
//      $objAdminField->Arguments->PackageName = $PackageName;


      return $objAdminField;
   }

   public function insertCheckBox($FieldName, $Events = array())
   {

      $objAdminField = $this->insertField($FieldName, 'checkbox', $Events);

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

 

   public function setEnableConditions($DependentField, $DependentValue, $ClearFields = true, $Invert = false)
   {
      global $_ARCHON;

      if (!$DependentValue || empty($DependentValue))
      {
         return false;
      }

      $selector = ":input[name=\"{$DependentField}\"]";

      $strClearFields = ($ClearFields) ? 'true' : 'false';

      $HTML = "admin_ui_delegationbind('change load', '{$selector}', function (e) {\n";
      $HTML .= "var dependentVal = $(e.target).val();\n";
      if (is_array($DependentValue))
      {
         $HTML .= "var rowSwitch = ((dependentVal == ";
         $HTML .= implode(") || (dependentVal == ", $DependentValue);
         $HTML .= "))\n";
      }
      else
      {
         $HTML .= "var rowSwitch = (dependentVal == {$DependentValue});\n";
      }

      if ($Invert)
      {
         $HTML .= "rowSwitch = !rowSwitch;\n";
      }

      $HTML .= "admin_ui_togglerow('{$this->Name}', rowSwitch, {$strClearFields});\n";

      $HTML .= "});\n";


      $_ARCHON->AdministrativeInterface->addScript($HTML);
      $_ARCHON->AdministrativeInterface->addScript("$('{$selector}').addClass('bound'); $('{$selector}').trigger('load');\n");

      return true;
   }

   public function insertField($Name, $Type = 'general', $Events = array())
   {
      global $_ARCHON;

      $Name = $Name ? $Name : $this->Name . 'Field' . count($this->AdminFields);

      $this->AdminFields[$Name] = New AdminField();
      $this->AdminFields[$Name]->Name = $Name;
      $this->AdminFields[$Name]->Type = $Type;
      $this->AdminFields[$Name]->ParentRow = $this;
      $this->AdminFields[$Name]->Events = $Events;
      $this->AdminFields[$Name]->InMultiple = $this->InMultiple;
      $this->AdminFields[$Name]->ModuleID = $_ARCHON->AdministrativeInterface->LoadingPackageID ? 0 : $_ARCHON->Module->ID;
      $this->AdminFields[$Name]->PackageID = $_ARCHON->AdministrativeInterface->LoadingPackageID ?
              $_ARCHON->AdministrativeInterface->LoadingPackageID : $_ARCHON->Package->ID;

      if ($this->IDPrefix)
      {
         $this->AdminFields[$Name]->IDPrefix = $this->IDPrefix;
      }

      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $Name : $Name;

      if ($_REQUEST['adminoverridefield'] == $IDName && !$this->InMultiple) //&& $this->ParentSection->Name != 'browse')
      {
         $_ARCHON->AdministrativeInterface->OverrideField = $this->AdminFields[$Name];
      }
      elseif ($this->InMultiple && $_REQUEST['adminoverridefield'] && preg_match('/([\w]+)\[([\d]+)\]\[([\w]+)\]/u', $_REQUEST['adminoverridefield'], $matches) && $matches[3] == $IDName)
      {
         $_ARCHON->AdministrativeInterface->OverrideField = $this->AdminFields[$Name];
         $this->ParentSection->CurrentMultipleObject = New $this->ParentSection->MultipleArguments->Class($matches[2]);
      }

      $_ARCHON->AdministrativeInterface->AccessedFields[] = $Name;

      return $this->AdminFields[$Name];
   }

   public function insertHierarchicalSelect($FieldNames, $DataTraversalSources, $ChildrenSources, $ClassNames, $NoSelectionPhraseNames = NULL, $Events = array())
   {
      global $_ARCHON;

      $FieldNames = is_array($FieldNames) ? $FieldNames : array($FieldNames);
      $FieldName = end($FieldNames);

      $objAdminField = $this->insertField($FieldName, 'hierarchicalselect', $Events);
      $objAdminField->FieldNames = $FieldNames;
      $objAdminField->DataTraversalSources = is_array($DataTraversalSources) ? $DataTraversalSources : array($DataTraversalSources);
      $objAdminField->ChildrenSources = is_array($ChildrenSources) ? $ChildrenSources : array($ChildrenSources);
      $objAdminField->ClassNames = is_array($ClassNames) ? $ClassNames : array($ClassNames);
      $objAdminField->NoSelectionPhraseName = is_array($NoSelectionPhraseNames) ? $NoSelectionPhraseNames : array($NoSelectionPhraseNames);

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      foreach ($FieldNames as $iterFieldName)
      {
         if ($iterFieldName != $FieldName)
         {
            $_ARCHON->AdministrativeInterface->AccessedFields[] = $iterFieldName;
         }
      }

      return $objAdminField;
   }

   public function insertHTML($HTML, $FieldName = '')
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'html');
      $objAdminField->CustomHTML = $HTML;

      return $objAdminField;
   }

   public function insertInformation($FieldName, $Information = NULL, $AutoReload = true) //, $UpdateOnSubmit = false, $ProgressBar = false)
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'information', $Events);
      $objAdminField->Information = $Information;

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}";
      }

      /* Automatically update this field */
      if ($AutoReload)
      {
         $_ARCHON->AdministrativeInterface->addReloadField($objAdminField);
      }

      return $objAdminField;
   }

   public function insertMultipleSelect($FieldName, $DataSource, $FieldValueSource, $SelectMultiplePhraseName = NULL, $MaxLength = 50, $Events = array())
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'multipleselect', $Events);
      $objAdminField->DataSource = $DataSource;
      $objAdminField->FieldValueSource = $FieldValueSource;
      $objAdminField->SelectMultiplePhraseName = $SelectMultiplePhraseName;
      $objAdminField->MaxLength = $MaxLength;

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

   public function insertNewLine($FieldName = '')
   {
      return $this->insertHTML('<br />', $FieldName);
   }

   
   public function insertPasswordField($FieldName, $Size = 50, $MaxLength = 100, $Events = array())
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'password', $Events);
      $objAdminField->Size = $Size;
      $objAdminField->MaxLength = $MaxLength;

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

   public function insertRadioButtons($FieldName, $OptionPhraseNames = array(1 => 'yes', 0 => 'no'), $Events = array())
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'radio', $Events);
      $objAdminField->OptionPhraseNames = $OptionPhraseNames;

      if (!$this->FirstFieldID)
      {
//$this->FirstFieldID = "{$FieldName}" . reset($OptionPhraseNames) . "Input";
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

   public function insertSelect($FieldName, $DataSource, $DataSourceParams = array(), $NoSelectionPhraseName = NULL, $MaxLength = 50, $Events = array())
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'select', $Events);
      $objAdminField->DataSource = $DataSource;
      $objAdminField->DataSourceParams = $DataSourceParams;
      $objAdminField->NoSelectionPhraseName = $NoSelectionPhraseName;
      $objAdminField->MaxLength = $MaxLength;


      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

   public function insertTextArea($FieldName, $Rows = 2, $Columns = 40, $Events = array())
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'textarea', $Events);
      $objAdminField->Rows = $Rows;
      $objAdminField->Columns = $Columns;

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

   public function insertNameField($FieldName, $Size = 50, $MaxLength = 100, $Events = array())
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'namefield', $Events);
      $objAdminField->Size = $Size;
      $objAdminField->MaxLength = $MaxLength;

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

 

   public function insertTextField($FieldName, $Size = 50, $MaxLength = 100, $Events = array(), $FormatNumber = false)
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'textfield', $Events);
      $objAdminField->Size = $Size;
      $objAdminField->MaxLength = $MaxLength;
      $objAdminField->FormatNumber = $FormatNumber;

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

   public function insertTimestampField($FieldName, $Size = 50, $MaxLength = 100, $Events = array())
   {
      global $_ARCHON;

      $objAdminField = $this->insertField($FieldName, 'timestamp', $Events);
      $objAdminField->Size = $Size;
      $objAdminField->MaxLength = $MaxLength;

      if (!$this->FirstFieldID)
      {
         $this->FirstFieldID = "{$FieldName}Input";
      }

      return $objAdminField;
   }

   public function outputInterface()
   {
      global $_ARCHON;

      $PhraseName = $this->PhraseName ? $this->PhraseName : $this->Name;
      if ($PhraseName && $PhraseName != '')
      {
         $objLabelPhrase = Phrase::getPhrase($PhraseName, $this->PackageID, $this->ModuleID, PHRASETYPE_ADMIN);
      }
      $strLabel = $objLabelPhrase ? $objLabelPhrase->getPhraseValue() : ($this->NoName ? '' : $PhraseName);
      $strHelpLink = $this->getHelpLink($PhraseName, $this->PackageID, $this->ModuleID);

      if ($this->InMultiple)
      {
?>
         <th class="labelcell"><?php echo($strLabel); ?><?php echo($strHelpLink); ?></th>
<?php
      }
      else
      {

         $disabledrow = ($this->Disabled) ? 'disabledrow' : '';
?>
         <tr id="<?php echo($this->Name); ?>row" class="adminrow <?php echo($disabledrow); ?>">
   <?php
         $FirstFieldID = str_replace(array('[', ']'), '', $this->FirstFieldID);
         $for_attr = ($FirstFieldID) ? ' for="' . $FirstFieldID . '"' : '';
   ?>
         <td class="labelcell"><div class='helparea'><?php echo($strHelpLink); ?></div>
            <label<?php echo($for_attr); ?>><?php echo($strLabel); ?></label></td>
         <td class="fieldcell">
      <?php
         foreach ($this->AdminFields as $objAdminField)
         {
            $FieldID = str_replace(array('[', ']'), '', $objAdminField->getFieldName());

            if ($this->IDPrefix)
            {
               $objAdminField->IDPrefix = $this->IDPrefix;
               $FieldID = $this->IDPrefix . $FieldID;
            }
            echo("<div id='{$FieldID}Field' class='adminfieldwrapper'>\n");
            $objAdminField->outputInterface();
            echo("</div>\n");
         }
      ?>
      </td>
   </tr>
<?php
      }
   }

   public $AdminFields = array();
   public $AdvancedHelpURL = NULL;
   public $APRCode;
   public $Disabled = false;
   /**
    * Stored name of first field in the row for use as the for attribute of the label element.
    *
    * @var string
    */
   public $FirstFieldID = '';
   public $HelpDisabled = false;
   public $InMultiple = false;
   public $ModuleID = 0;
   /**
    * Name of row. Name will be used with current module as the phrasename for the label and help box.
    *
    * @var string
    */
   public $Name = '';
   public $NoName = true;
   public $PackageID = 0;
   public $ParentSection = NULL;
   public $PhraseName = NULL;
   public $IDPrefix = '';
}

$_ARCHON->mixClasses('AdminRow', 'Core_AdminRow');
?>
