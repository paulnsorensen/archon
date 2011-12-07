<?php
abstract class DigitalLibrary_AdminField
{
   public function outputInterface()
   {
      if($this->Type == 'uploadfield')
      {
         $this->outputUploadField();
      }
      else
      {
         $this->callOverridden();
      }
   }





   /* this needs to pertain to the file class directly */
   public function outputUploadField()
   {
      global $_ARCHON;

      $FieldName = $this->getFieldName();
      $IDName = str_replace(array('[', ']'), '', $FieldName);

      $obj = $this->getObject();

      if($obj instanceof File)
      {
         $objFile = $obj;
      }
      else
      {
         $FileID = $this->getFieldValue();
         $objFile = New File($FileID);
         $objFile->dbLoad();
      }

      if($objFile->ID)
      {
         echo("<div class='infodiv'><a href='?p=digitallibrary/getfile&amp;id={$objFile->ID}' rel='external'>{$objFile->getString('Filename')}</a> ".formatsize($objFile->Size)."</div>");
      }
      else
      {
         $objNoSelectionPhrase = Phrase::getPhrase('selectone', 1, 0, PHRASETYPE_ADMIN);
         $strNoSelection =  $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';
         $objUploadFile = Phrase::getPhrase('uploadfile', 1, 0, PHRASETYPE_ADMIN);
         $strUploadFile =  $objUploadFile ? $objUploadFile->getPhraseValue(ENCODE_HTML) : 'uploadfile';


         ?>

<select id="<?php echo($IDName); ?>" style="float:left" name="<?php echo($FieldName); ?>">
   <option value='0'><?php echo($strNoSelection); ?></option>
            <?php
            $arrNewFiles = $_ARCHON->getNewFileList();
            foreach($arrNewFiles as $ID => $Filename)
            {
               echo("<option value='{$ID}'>{$Filename}</option>");
            }
            ?>
</select>
<a href='#' class="adminformbutton" onclick="admin_ui_dialogcallback(function() {admin_ui_reloadfield('<?php echo($FieldName); ?>');}); admin_ui_opendialog('digitallibrary', 'digitallibrary', 'uploadfile'); return false;"><?php echo($strUploadFile); ?></a>

         <?php
      }

      echo($this->getEventHTML("#{$IDName}Input"));
   }
}

$_ARCHON->setMixinMethodParameters('AdminField', 'DigitalLibrary_AdminField', 'outputInterface', NULL, MIX_OVERRIDE);

$_ARCHON->mixClasses('AdminField', 'DigitalLibrary_AdminField');
?>