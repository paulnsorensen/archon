<?php

abstract class Core_AdminField
{

   public function addHelpURL($URL, $File, $External = false)
   {
      $this->ParentRow->addHelpURL($URL, $File, $External);
   }

   public function disableHelp()
   {
      $this->ParentRow->disableHelp();
   }

   //remove this?
   public function addQuickAdd($Package, $Module, $Callback = NULL)
   {
      global $_ARCHON;

      $objQuickAddPhrase = Phrase::getPhrase('quickadd', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strQuickAdd = $objQuickAddPhrase ? $objQuickAddPhrase->getPhraseValue(ENCODE_HTML) : 'quickadd';
      ob_start();
?>
      <span class="quickadd">
         <a href='#' onclick="admin_ui_dialogcallback(function() {<?php echo($Callback); ?>}); admin_ui_opendialog('<?php echo($Package); ?>', '<?php echo($Module); ?>'); return false;"><?php echo($strQuickAdd); ?></a>
      </span>
<?php
      $this->QuickAdd = ob_get_clean();
   }

   public function getEventHTML($FieldSelector)
   {
      if(empty($this->Events))
      {
         return '';
      }
      else
      {
         $HTML = "\n<script type='text/javascript'>\n/* <![CDATA[ */\n";
         foreach($this->Events as $event => $response)
         {
            $HTML .= "$('$FieldSelector').$event(function () { $response });\n";
         }
         $HTML .= "/* ]]> */\n</script>\n";

         return $HTML;
      }
   }

   public function getFieldName($FieldName = NULL)
   {
      global $_ARCHON;

      $FieldName = isset($FieldName) ? $FieldName : $this->Name;

      if($this->InMultiple)
      {

         $ArrayName = $this->ParentRow->ParentSection->MultipleArguments->ArrayName;
         $ID = $this->ParentRow->ParentSection->CurrentMultipleObject->ID;

         return "{$ArrayName}[$ID][$FieldName]";
      }
      else if($this->ParentRow->ParentSection->Name == 'browse')
      {
         return "{$FieldName}SearchOption";
      }
      else
      {
         return $FieldName;
      }
   }

   public function getFieldValue($FieldName = NULL, $DecodeBBCode = false)
   {
      global $_ARCHON;

      $FieldName = isset($FieldName) ? $FieldName : $this->Name;

      if(isset($_ARCHON->AdministrativeInterface->ForcedValues[$FieldName]))
      {
         return $_ARCHON->AdministrativeInterface->ForcedValues[$FieldName];
      }

      if($this->InMultiple)
      {
         $currentObj = $this->ParentRow->ParentSection->CurrentMultipleObject;
      }
      else
      {
         $currentObj = $_ARCHON->AdministrativeInterface->Object;
      }


// need regex etc. to deal with html arrays e.g. subObj[var]
      if($currentObj)
      {
         if(preg_match('/(\w+)\[(\w+)\]\[(\w+)\]/i', $FieldName, $arrMatches))
         {
            $subObj = $arrMatches[1];
            $subObjID = $arrMatches[2];
            $var = $arrMatches[3];

            return $currentObj->{$subObj}[intval($subObjID)] ? $currentObj->{$subObj}[intval($subObjID)]->getString($var, 0, false, $DecodeBBCode) : NULL;
         }
         elseif(preg_match('/(\w+)\[(\w+)\]/i', $FieldName, $arrMatches))
         {
            $subObj = $arrMatches[1];
            $var = $arrMatches[2];

            return $currentObj->$subObj ? $currentObj->$subObj->getString($var, 0, false, $DecodeBBCode) : NULL;
         }
         else
         {
            return $currentObj->getString($FieldName, 0, false, $DecodeBBCode);
         }
      }
      else
      {
         return NULL;
      }
   }

   public function getObject()
   {
      global $_ARCHON;

      if($this->InMultiple)
      {
         return $this->ParentRow->ParentSection->CurrentMultipleObject;
      }
      else
      {
         return $_ARCHON->AdministrativeInterface->Object;
      }
   }

   public function getRequestValue($FieldName = NULL)
   {
      global $_ARCHON;

      $FieldName = isset($FieldName) ? $FieldName : $this->Name;

      if($this->InMultiple)
      {
         $ArrayName = $this->ParentRow->ParentSection->MultipleArguments->ArrayName;
         $ID = $this->ParentRow->ParentSection->CurrentMultipleObject->ID;

         return $_REQUEST[strtolower($ArrayName)][$ID][strtolower($FieldName)];
      }
      else
      {
         return $_REQUEST[strtolower($FieldName)];
      }
   }

   public function outputAdvancedSelectForMultipleSection()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);

      $obj = $this->getObject();
      $obj = New $this->Arguments['Class']($obj->{$this->Name});
      $obj->dbLoad();


      if($obj->ID)
      {
         echo("<input type='hidden' name='$FieldName' id='$IDName' value='$obj->ID' />");
         echo("<div style='padding-left:8px; font-size:0.9em'>" . $obj->toString() . "</div>");
      }
      else
      {
         $this->outputAdvancedSelect();
      }

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputAdvancedSelect()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;

//            $strHelp = "<a class='helplink {phrasename: \"{$PhraseName}\", packageid: {$PackageID}, moduleid: {$ModuleID}}' title='{$strClickForHelp}' id='{$PhraseName}helplink'></a>";

      if(!isset($this->Arguments['toStringArguments']))
      {
         $this->Arguments['toStringArguments'] = array();
      }

      $metadata = '{';

      if($this->Arguments['params'])
      {
         $metadata .= 'params: {';
         $first = true;
         foreach($this->Arguments['params'] as $key => $value)
         {
            if(!$first)
            {
               $metadata .=', ';
            }
            $metadata .= $key . ': "' . $value . '"';
            $first = false;
         }
         $metadata .= '}';
      }
      if($this->Arguments['quickAdd'])
      {
         if($metadata != '{')
         {
            $metadata .= ', ';
         }
         $metadata .= 'quickAdd: "' . $this->Arguments['quickAdd'] . '"';
      }
      if($this->Arguments['searchOptions'])
      {
         if($metadata != '{')
         {
            $metadata .= ', ';
         }

         $metadata .= 'searchOptions: [';


         $first = true;
         foreach($this->Arguments['searchOptions'] as $array)
         {
            if(!$first)
            {
               $metadata .=', ';
            }

            $metadata .= '{';

            $f = true;
            foreach($array as $key => $value)
            {
               if(!$f)
               {
                  $metadata .=', ';
               }
               if(strpos($value, '{') === 0 || strpos($value, '[') === 0)
               {
                  $metadata .= $key . ': ' . $value;
               }
               else
               {
                  $metadata .= $key . ': "' . $value . '"';
               }
               $f = false;
            }
            $metadata .= '}';
            $first = false;
         }



         $metadata .= ']';
      }
      $metadata .= '}';


      if($this->Arguments['Multiple'])
      {
         call_user_func(array($_ARCHON->AdministrativeInterface->Object, $this->Arguments['RelatedArrayLoadFunction']));
      }
      else
      {
         $FieldValue = $this->getFieldValue();
      }

      if($this->Arguments['Multiple'])
      {
?>
         <select id="<?php echo($IDName); ?>Related<?php echo($this->Arguments['Class']); ?>IDs" name="Related<?php echo($this->Arguments['Class']); ?>IDs[]" class='hidden watchme advancedselect <?php echo($metadata); ?>' multiple="multiple">
   <?php
         if(!empty($_ARCHON->AdministrativeInterface->Object->{$this->Arguments['RelatedArrayName']}))
         {

            foreach($_ARCHON->AdministrativeInterface->Object->{$this->Arguments['RelatedArrayName']} as $ID => $objRelatedObject)
            {


               $strRelatedName = call_user_func_array(array($objRelatedObject, 'toString'), $this->Arguments['toStringArguments']);

//                        $strRelatedName = caplength($strRelatedName, CONFIG_CORE_RELATED_OPTION_MAX_LENGTH);

               echo("<option selected='selected' value='$ID'>$strRelatedName</option>");
            }
         }
         else
         {
            echo("<option value='0' style='display:none'></option>");
         }
   ?>
      </select>
<?php
      }
      else
      {

         $obj = new $this->Arguments['Class']($FieldValue);
         $optionString = $obj->toString();
?>
         <select id="<?php echo($IDName); ?>Input" name="<?php echo($FieldName); ?>" class='hidden advancedselect watchme <?php echo($metadata); ?>'>
   <?php
         echo("<option value='{$FieldValue}' selected='selected'>{$optionString}</option>\n");
   ?>
      </select>
<?php
      }
   }

   public function outputCheckBox()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();

      $checked = $FieldValue ? " checked='checked'" : '';
?>
      <input id="<?php echo($IDName); ?>Input" class="watchme" type="hidden" name="<?php echo($FieldName); ?>" value="<?php echo($FieldValue); ?>" />
      <input id="<?php echo($IDName); ?>CheckboxInput" class="fieldcheckbox" type="checkbox" name="chk<?php echo($FieldName); ?>" tabindex="1"<?php echo($checked); ?> />
<?php
      echo($this->getEventHTML("#{$IDName}Input\n"));
   }

   // Not currently supported by multiples.
   public function outputHierarchicalSelect()
   {
      global $_ARCHON;

      $wholeFieldName = $this->getFieldName();
      $wholeIDName = str_replace(array('[', ']'), '', $wholeFieldName);
      $wholeIDName = ($this->IDPrefix) ? $this->IDPrefix . $wholeIDName : $wholeIDName;
      $ContainerID = "{$wholeIDName}Field";

      echo("<div class='hierarchicalselectfield'>");

      $MasterFieldName = NULL;
      $MasterFieldValue = NULL;

      $RealFieldNames = array();
      $IDNames = array();
      foreach($this->FieldNames as $key => $FieldName)
      {
         $RealFieldNames[$key] = $this->getFieldName($FieldName);
         $IDNames[$key] = str_replace(array('[', ']'), '', $RealFieldNames[$key]);
         $IDNames[$key] = ($this->IDPrefix) ? $this->IDPrefix . $IDNames[$key] : $IDNames[$key];
      }

      // Find out if an id has been passed in through AJAX
      if($_REQUEST['adminoverridefield'] == $wholeIDName)
      {
         foreach($this->FieldNames as $key => $FieldName)
         {
            $tempValue = $_REQUEST[encoding_strtolower($FieldName)];

            if(isset($tempValue))
            {
               $MasterFieldName = $FieldName;
               $MasterFieldValue = $tempValue;
            }
         }
      }
      else // Find out if an id has been set in the database
      {
         foreach($this->FieldNames as $key => $FieldName)
         {
            $tempValue = $this->getFieldValue($FieldName);

            if($tempValue)
            {
               $MasterFieldName = $FieldName;
               $MasterFieldValue = $tempValue;
            }
         }
      }

      // If we haven't set anything using either method
      if(!isset($MasterFieldName))
      {
         $MasterFieldName = reset($this->FieldNames);
         $MasterFieldValue = 0;
      }

      // To know higher field names.
      $HigherFieldNames = array();
      $lastFieldName = NULL;
      foreach($this->FieldNames as $key => $FieldName)
      {
         $HigherFieldNames[$key] = $lastFieldName;
         $lastFieldName = $FieldName;
      }

      // Build up data arrays going up from master field
      $TraversalArrays = array();
      $FieldValues = array();
      $TopObjects = array();
      $tempTopObjects = NULL;
      $seenMasterField = false;
      foreach(array_reverse($this->FieldNames, true) as $key => $FieldName)
      {
         // Begin traversing up from master field.
         $seenMasterField = $seenMasterField || ($FieldName == $MasterFieldName);
         if(!$seenMasterField)
         {
            continue;
         }

         if($FieldName == $MasterFieldName)
         {
            $FieldValues[$key] = $MasterFieldValue;
         }
         else
         {
            $FieldValues[$key] = $tempTopObject->$FieldName;
         }

         if($this->ChildrenSources[$key]) // The field's class has its own hierarchy
         {
            if($FieldValues[$key])
            {
               $TraversalArrays[$key] = call_user_func(array($_ARCHON, $this->DataTraversalSources[$key]), $FieldValues[$key]);
               $tempTopObject = reset($TraversalArrays[$key]);
            }
            else
            {
               $TraversalArrays[$key] = array();
               $tempTopObject = count($this->FieldNames) > 1 ? New $this->ClassNames[$key]() : NULL;
            }

            $TopObjects[$key] = $tempTopObject;
         }
         else if(count($this->FieldNames) > 1) // The field's class has no hierarchy
         {
            $tempTopObject = New $this->ClassNames[$key]($FieldValues[$key]);
            $tempTopObject->dbLoad();
            $TopObjects[$key] = $tempTopObject;
         }
      }

      // Finally, time to output the whole thing
      foreach($this->FieldNames as $key => $FieldName)
      {
         $noSelectionPhraseName = $this->NoSelectionPhraseNames[$key] ? $this->NoSelectionPhraseNames[$key] : 'selectone';

         $PackageID = $this->NoSelectionPhraseName == 'selectone' ? 1 : $this->PackageID;
         $ModuleID = $this->NoSelectionPhraseName == 'selectone' ? 0 : $this->ModuleID;

         $objNoSelectionPhrase = Phrase::getPhrase($noSelectionPhraseName, $PackageID, $ModuleID, PHRASETYPE_ADMIN);
         $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

         echo("<div class='hierarchicalgroup'>\n");

         $RealFieldID = str_replace(array('[', ']'), '', $RealFieldNames[$key]);

         echo("<span class='hiddendata' id='{$RealFieldID}SelfFieldName'>{$this->FieldNames[$key]}</span>\n");

         // In case top select of a group is set to nothing option
         if($HigherFieldNames[$key])
         {
            $higherRealFieldName = $this->getFieldName($HigherFieldNames[$key]);
            $higherFieldValue = $TopObjects[$key]->{$HigherFieldNames[$key]};
            echo("<span class='hiddendata' id='{$RealFieldID}ParentFieldName'>$higherRealFieldName</span>\n");
            echo("<span class='hiddendata' id='{$RealFieldID}ParentFieldValue'>$higherFieldValue</span>\n");
         }

         // Display a hierarchy
         if($this->ChildrenSources[$key])
         {
            $objTravObject = New $this->ClassNames[$key]();

            if($FieldValues[$key])
            {
               $displaycount = 1;
               foreach($TraversalArrays[$key] as $objTravObject)
               {
                  if($displaycount != count($TraversalArrays[$key]))
                  {
                     $name = $this->InMultiple ? substr($RealFieldNames[$key], 0, -1) . $displaycount . ']' : $RealFieldNames[$key] . $displaycount;
                     $watch = '';
                  }
                  else
                  {
                     $name = $RealFieldNames[$key];
                     $watch = 'watchme';
                  }

                  $idname = str_replace(array('[', ']'), '', $name);
                  $idname = ($this->IDPrefix) ? $this->IDPrefix . $idname : $idname;
                  echo("<select name='$name' id='{$idname}Input' class='hierarchicalselect $watch' tabindex='1'>\n");
                  echo("<option value='{$objTravObject->ParentID}'>$strNoSelection</option>\n");

                  $higherFieldName = $HigherFieldNames[$key];
                  $argumentArray = !$objTravObject->ParentID && $higherFieldName ?
                          array($this->FieldNames[$key] => 0, $higherFieldName => $objTravObject->$higherFieldName) :
                          array($this->FieldNames[$key] => $objTravObject->ParentID);

                  $arrChildObjects = call_user_func_array(array($_ARCHON, $this->ChildrenSources[$key]), $argumentArray);
                  foreach($arrChildObjects as $ChildID => $objChildObject)
                  {
                     $optionString = is_string($objChildObject) ? $objChildObject : $objChildObject->toString();
                     $selected = ($objTravObject->ID == $ChildID) ? " selected='selected'" : '';
                     echo("<option value='$ChildID'$selected>" . caplength($optionString, $this->MaxLength) . "</option>\n");
                  }

                  echo("</select>\n");

                  $displaycount++;
               }
            }
            else
            {
               echo("<input type='hidden' name='{$RealFieldNames[$key]}' id='{$IDNames[$key]}Input' value='0'/>\n");
            }

            $higherFieldName = $HigherFieldNames[$key];
            if(!$objTravObject->ID && $higherFieldName)
            {
               $argumentArray = $higherFieldName == $MasterFieldName ?
                       array($this->FieldNames[$key] => 0, $higherFieldName => $MasterFieldValue) :
                       array($this->FieldNames[$key] => 0, $higherFieldName => $TopObjects[$key]->$higherFieldName);
            }
            else
            {
               $argumentArray = array($this->FieldNames[$key] => $objTravObject->ID);
            }

//                $higherFieldName = $HigherFieldNames[$key];
//                $argumentArray = !$objTravObject->ParentID && $higherFieldName ?
//                    array($this->FieldNames[$key] => 0, $higherFieldName => $objTravObject->$higherFieldName) :
//                    array($this->FieldNames[$key] => $objTravObject->ParentID);

            $arrChildObjects = call_user_func_array(array($_ARCHON, $this->ChildrenSources[$key]), $argumentArray);

            if(!empty($arrChildObjects))
            {
               $name = $this->InMultiple ? substr($RealFieldNames[$key], 0, -1) . 'New]' : $RealFieldNames[$key] . 'New';

               echo("<select name='{$name}' id='{$IDNames[$key]}NewInput' class='hierarchicalselect' tabindex='1'>\n");
               echo("<option value='0'>$strNoSelection</option>\n");

               foreach($arrChildObjects as $ChildID => $objChildObject)
               {
                  $optionString = is_string($objChildObject) ? $objChildObject : $objChildObject->toString();
                  echo("<option value='$ChildID'>" . caplength($optionString, $this->MaxLength) . "</option>\n");
               }

               echo("</select>\n");
            }
         }
         else
         {
            $higherFieldName = $HigherFieldNames[$key];
            if($higherFieldName)
            {
               $argumentArray = $higherFieldName == $MasterFieldName ?
                       array($higherFieldName => $MasterFieldValue) :
                       array($higherFieldName => $TopObjects[$key]->$higherFieldName);
            }
            else
            {
               $argumentArray = array();
            }

            $arrSelectChoices = call_user_func_array(array($_ARCHON, $this->DataTraversalSources[$key]), $argumentArray);

            if(!empty($arrSelectChoices))
            {
               echo("<select id='{$IDNames[$key]}Input' class='hierarchicalselect' name = '$RealFieldNames[$key]' tabindex='1'>\n");
               echo("<option value='0'>$strNoSelection</option>\n");
               foreach($arrSelectChoices as $ID => $objSelectChoice)
               {
                  $optionString = is_string($objSelectChoice) ? $objSelectChoice : $objSelectChoice->toString();

                  $selected = ($ID == $FieldValues[$key]) ? " selected='selected'" : '';
                  echo("<option value='$ID'$selected>" . caplength($optionString, $this->MaxLength) . "</option>\n");
               }
               echo("</select>");
            }
         }

         echo("</div>\n");
      }
      echo("</div>\n");

      echo($this->QuickAdd);

      echo($this->getEventHTML("#$ContainerID select"));
   }

   public function outputInformation()
   {
      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();
      $Information = $this->Information ? $this->Information : $FieldValue;


      if($_REQUEST['adminoverridefield'] == $FieldName)
      {
         $Information = $this->getRequestValue() ? $this->getRequestValue() : $FieldValue;
      }

      if(is_array($Information))
      {
         if(count($Information) == 1)
         {
            echo("<div id='{$IDName}' class='infodiv'><span>" . reset($Information) . "</span></div>");
         }
         else
         {            
            echo("<div class='infotablewrapper'><table id='{$IDName}' class='infotable'>");
            foreach($Information as $InfoID => $ListItem)
            {
               if($InfoID % 2 == 0)
               {
                  echo("<tr class='evenrow'><td>{$ListItem}</td></tr>");
               }
               else
               {
                  echo("<tr><td>{$ListItem}</td></tr>");
               }
            }
            echo("</table></div>");
         }
      }
      else
      {
         echo("<div id='{$IDName}' class='infodiv'><span>{$Information}</span></div>");
      }
   }

   public function outputInterface()
   {
      $Type = $this->Type;

      if($Type == 'advselect')
      {
         if(!$this->Arguments['MultipleSection'])
         {
            $this->outputAdvancedSelect();
         }
         else
         {
            $this->outputAdvancedSelectForMultipleSection();
         }
      }
      elseif($Type == 'checkbox')
      {
         $this->outputCheckBox();
      }
      elseif($Type == 'hidden')
      {
         $this->outputHiddenField();
      }
      elseif($Type == 'hierarchicalselect')
      {
         $this->outputHierarchicalSelect();
      }
      elseif($Type == 'html')
      {
         echo($this->CustomHTML);
      }
      elseif($Type == 'information')
      {
         $this->outputInformation();
      }
      elseif($Type == 'multipleselect')
      {
         $this->outputMultipleSelect();
      }
      elseif($Type == 'namefield')
      {
         $this->outputNameField();
      }
      elseif($Type == 'password')
      {
         $this->outputPasswordField();
      }
      elseif($Type == 'radio')
      {
         $this->outputRadioButtons();
      }
      elseif($Type == 'select')
      {
         $this->outputSelect();
      }
      elseif($Type == 'textarea')
      {
         $this->outputTextArea();
      }
      elseif($Type == 'textfield')
      {
         $this->outputTextField();
      }
      elseif($Type == 'timestamp')
      {
         $this->outputTimestampField();
      }
   }

   public function outputMultipleSelect()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      //$FieldValue = $this->getFieldValue();
      //TODO: Make getFieldValue lookup object arrays possibly given a dbLoad fn



      $this->SelectMultiplePhraseName = $this->SelectMultiplePhraseName ? $this->SelectMultiplePhraseName : 'selectmultiple';

      $PackageID = $this->SelectMultiplePhraseName == 'selectmultiple' ? 1 : $this->PackageID;
      $ModuleID = $this->SelectMultiplePhraseName == 'selectmultiple' ? 0 : $this->ModuleID;

      $objSelectMultiplePhrase = Phrase::getPhrase($this->SelectMultiplePhraseName, $PackageID, $ModuleID, PHRASETYPE_ADMIN);
      $strSelectMultiple = $objSelectMultiplePhrase ? $objSelectMultiplePhrase->getPhraseValue(ENCODE_HTML) : 'Ctrl + click to select multiple';

      $DataSource = $this->DataSource;
      $FieldValueSource = $this->FieldValueSource;

      if(!is_array($DataSource))
      {
         $arrSelectChoices = call_user_func(array($_ARCHON, $DataSource));
      }
      else
      {
         $arrSelectChoices = $DataSource;
      }

      if(!is_array($FieldValueSource))
      {
         $arrSelected = call_user_func(array($_ARCHON, $FieldValueSource));
      }
      else
      {
         $arrSelected = $FieldValueSource;
      }

      echo("<select id='{$IDName}Input' class='multipleselect' name = '$FieldName' multiple='multiple' tabindex='1'>\n");
      //echo("<option value='0'>$strNoSelection</option>\n");
      foreach($arrSelectChoices as $ID => $objSelectChoice)
      {
         // If a string array is passed in, we'll just assume the option texts are the values.

         if(is_string($objSelectChoice))
         {
            // No we won't
            //$ID = $objSelectChoice;
            $optionString = $objSelectChoice;
         }
         else
         {
            $optionString = $objSelectChoice->toString();
         }

         $selected = ($arrSelected[$ID]) ? " selected='selected'" : '';
         $optionString = caplength($optionString, $this->MaxLength);
         echo("<option value='{$ID}'{$selected}>{$optionString}</option>\n");
      }
      echo("</select>");
      echo("<div class='admincomment'>{$strSelectMultiple}</div>");


      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputPasswordField()
   {
      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $requiredfield = ($this->Required) ? 'requiredfield' : '';

      echo("<input id='{$IDName}Input' class='{$requiredfield}' type='password' name='$FieldName' size='$this->Size' maxlength='$this->MaxLength' tabindex='1' />");

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputRadioButtons()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();



      echo("<input type='hidden' class='watchme' id='{$IDName}Input' name='$FieldName' value='$FieldValue' />\n");

      echo("<div class='radiobuttonset'>\n");

      if(is_array($this->OptionPhraseNames))
      {
         foreach($this->OptionPhraseNames as $OptionNumber => $OptionPhraseName)
         {
            $PackageID = $OptionPhraseName == 'yes' || $OptionPhraseName == 'no' ? 1 : $this->PackageID;
            $ModuleID = $OptionPhraseName == 'yes' || $OptionPhraseName == 'no' ? 0 : $this->ModuleID;

            $objOptionPhrase = Phrase::getPhrase($OptionPhraseName, $PackageID, $ModuleID, PHRASETYPE_ADMIN);
            $strOption = $objOptionPhrase ? $objOptionPhrase->getPhraseValue(ENCODE_HTML) : $OptionPhraseName;

            if($OptionNumber == $FieldValue)
            {
               $checked = " checked='checked'";
            }
            else
            {
               $checked = '';
            }

            echo("<input type='radio' class='fieldradiobutton' id='{$IDName}{$OptionPhraseName}Input' name='{$FieldName}RadioButton' value='$OptionNumber'$checked /> <label for='{$IDName}{$OptionPhraseName}Input'>$strOption</label>\n");
         }

         echo("</div>\n");
      }

      echo($this->getEventHTML("#{$IDName}Field input"));
   }

   public function outputSelect()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();



      $this->NoSelectionPhraseName = $this->NoSelectionPhraseName ? $this->NoSelectionPhraseName : 'selectone';

      $PackageID = $this->NoSelectionPhraseName == 'selectone' ? 1 : $this->PackageID;
      $ModuleID = $this->NoSelectionPhraseName == 'selectone' ? 0 : $this->ModuleID;

      $objNoSelectionPhrase = Phrase::getPhrase($this->NoSelectionPhraseName, $PackageID, $ModuleID, PHRASETYPE_ADMIN);
      $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

      $DataSource = $this->DataSource;

      if(!is_array($DataSource))
      {
         if(is_array($this->DataSourceParams) && !empty($this->DataSourceParams))
         {
            $arrSelectChoices = call_user_func_array(array($_ARCHON, $DataSource), $this->DataSourceParams);
         }
         else
         {
            $arrSelectChoices = call_user_func(array($_ARCHON, $DataSource));
         }
      }
      else
      {
         $arrSelectChoices = $DataSource;
      }

      $requiredfield = ($this->Required) ? 'requiredfield' : '';

      $watch = ($this->Watch) ? 'watchme' : '';

      echo("<select id='{$IDName}Input' class='{$requiredfield} {$watch}' name = '$FieldName' tabindex='1'>\n");
      echo("<option value='0'>$strNoSelection</option>\n");
      foreach($arrSelectChoices as $ID => $objSelectChoice)
      {

         $optionString = is_string($objSelectChoice) ? $objSelectChoice : $objSelectChoice->toString();

         $selected = ($ID == $FieldValue) ? " selected='selected'" : '';
         $optionString = caplength($optionString, $this->MaxLength);
         echo("<option value='{$ID}'{$selected}>{$optionString}</option>\n");
      }
      echo("</select>");

      echo($this->QuickAdd);

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputNameField()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();
      $requiredfield = ($this->Required) ? 'requiredfield' : '';


      $objClickToEditPhrase = Phrase::getPhrase('clicktoedit', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strClickToEdit = $objClickToEditPhrase ? $objClickToEditPhrase->getPhraseValue(ENCODE_HTML) : 'Click to edit';

      echo("<textarea class='hidden watchme' id='{$IDName}Input' name='$FieldName' rows='$this->Rows' cols='$this->Columns' tabindex='1'>$FieldValue</textarea>");
      echo("<div id='{$IDName}' title='{$strClickToEdit}' class='editable focusable namefield {$requiredfield}'>" . $this->getFieldValue(NULL, true) . "</div>");

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputTextArea()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();
      $requiredfield = ($this->Required) ? 'requiredfield' : '';

      $objClickToEditPhrase = Phrase::getPhrase('clicktoedit', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strClickToEdit = $objClickToEditPhrase ? $objClickToEditPhrase->getPhraseValue(ENCODE_HTML) : 'Click to edit';

      echo("<textarea id='{$IDName}Input' name='$FieldName' class='hidden watchme' rows='$this->Rows' cols='$this->Columns' tabindex='1'>$FieldValue</textarea>");
      echo("<div id='{$IDName}' title='{$strClickToEdit}' class='editable focusable {$requiredfield}'>" . $this->getFieldValue(NULL, true) . "</div>");

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputTextField()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();

      $Size = $this->Size;

      if($this->FormatNumber && is_numeric($FieldValue))
      {
         $FieldValue = formatNumber($FieldValue);
      }

      $requiredfield = ($this->Required) ? 'requiredfield' : '';


      echo("<input id='{$IDName}Input' class='{$requiredfield} watchme' type='text' name='$FieldName' value='$FieldValue' size='$Size' maxlength='$this->MaxLength' tabindex='1' />");

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputHiddenField()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();


      if($this->FormatNumber && is_numeric($FieldValue))
      {
         $FieldValue = formatNumber($FieldValue);
      }

      echo("<input id='{$IDName}Input' type='hidden' name='$FieldName' value='$FieldValue' />");

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function outputTimestampField()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);
      $IDName = ($this->IDPrefix) ? $this->IDPrefix . $IDName : $IDName;
      $FieldValue = $this->getFieldValue();
      $requiredfield = ($this->Required) ? 'requiredfield' : '';

      $Size = $this->Size;

      $FieldValue = ($FieldValue != 0 && is_natural($FieldValue)) ? date(CONFIG_CORE_DATE_FORMAT, $FieldValue) : '';

      echo("<input id='{$IDName}Input' type='text' class='{$requiredfield}' name='$FieldName' value='$FieldValue' size='$Size' maxlength='$this->MaxLength' tabindex='1' />");

      echo($this->getEventHTML("#{$IDName}Input"));
   }

   public function required()
   {
      $this->Required = true;
   }

   public $Arguments = array();
   public $ChildrenSources = array();
   public $ClassNames = array();
   public $Columns = 50;
   public $CustomHTML = '';
   public $DataSource = '';
   public $DataSourceParams = array();
   public $DataSourceVar = '';
   public $Events = array();
   public $Info = '';
   public $IDPrefix = '';
   public $arrInfo = array();
   public $Name = '';
   public $FieldNames = array();
   public $FormatNumber = false;
   public $MaxLength = 100;
   public $InMultiple = false;
   public $NoSelectionPhraseName = '';
   public $NoSelectionPhraseNames = array();
   public $OptionPhraseNames = array(1 => 'yes', 0 => 'no');
   public $ParentRow = NULL;
   public $QuickAdd = NULL;
   public $Rows = 8;
   public $Required = false;
   public $Size = 50;
   public $SizeVar = '';
   public $DataTraversalSources = array();
   public $Type = 'HTML';
   public $TypeVar = '';
   public $ModuleID = 0;
   public $PackageID = 0;
   public $Watch = true;

}

$_ARCHON->mixClasses('AdminField', 'Core_AdminField');
?>
