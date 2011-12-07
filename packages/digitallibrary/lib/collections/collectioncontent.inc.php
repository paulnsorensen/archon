<?php
abstract class DigitalLibrary_CollectionContent
{
   /**
    * Loads Digital Library Content for CollectionContent instance
    *
    * @return boolean
    */
   public function dbLoadDigitalContent()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load DigitalLibraryContent: CollectionContent ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load DigitalLibraryContent: CollectionContent ID must be numeric.");
         return false;
      }

      $this->DigitalContent = array();

      $query = "SELECT * FROM tblDigitalLibrary_DigitalContent WHERE CollectionContentID = ? ORDER BY Title";
      $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No dlcontents found, return.
         $result->free();
         $prep->free();
         return true;
      }

      while($row = $result->fetchRow())
      {
//        	if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) || $row['DefaultAccessLevel'] != DIGITALLIBRARY_ACCESSLEVEL_NONE)
         if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) || $row['Browsable'])
         {
            $this->DigitalContent[$row['ID']] = New DigitalContent($row);
         }
      }
      $result->free();
      $prep->free();

      return true;
   }





   /**
    * Returns CollectionContent as a string
    *
    * @param integer $MakeIntoLink
    * @param string $ConcatinateLevelContainer
    * @param string $ConcatinateLevelContainerIdentifier
    * @param string $ConcatinateParentLevelContainer
    * @param string $ConcatinateParentLevelContainerIdentifier
    * @param string $Delimiter
    * @return string
    */
   public function toString($MakeIntoLink = LINK_NONE, $ConcatinateLevelContainer = true, $ConcatinateLevelContainerIdentifier = true, $ConcatinateParentLevelContainer = false, $ConcatinateParentLevelContainerIdentifier = false, $Delimiter = ", ")
   {
      global $_ARCHON;

      if(!empty($this->DigitalContent) && !$_ARCHON->PublicInterface->DisableTheme && !$_ARCHON->AdministrativeInterface && $this->ID)
      {
         // Only display link if some content not disabled
         $enabledCount = 0;
         $objEnabledDC = NULL;
         foreach($this->DigitalContent as $objDigitalContent)
         {
//                if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) || $objDigitalContent->DefaultAccessLevel != DIGITALLIBRARY_ACCESSLEVEL_NONE)
            
            if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) || $objDigitalContent->Browsable)
            {
               $enabledCount++;
               $objEnabledDC = $objDigitalContent;
            }
         }

         if($enabledCount > 1)
         {
            $DLURL = "?p=core/search&amp;collectioncontentid=$this->ID";
         }
         else if($objEnabledDC)
         {
            $DLURL = "?p=digitallibrary/digitalcontent&amp;id=" . $objEnabledDC->ID;
         }

         if($objEnabledDC)
         {
            

            $objViewContentPhrase = Phrase::getPhrase('tostring_viewcontent', PACKAGE_DIGITALLIBRARY, 0, PHRASETYPE_PUBLIC);
            $strViewContent = $objViewContentPhrase ? $objViewContentPhrase->getPhraseValue(ENCODE_HTML) : 'View associated digital content.';

            $String = "<a href='$DLURL'><img class='dl' src='{$_ARCHON->PublicInterface->ImagePath}/dl.gif' title='$strViewContent' alt='$strViewContent' /></a>";
         }
      }

      return $String;
   }

   /**
    * @var DigitalContent[]
    */
   public $DigitalContent = array();
}

$_ARCHON->setMixinMethodParameters('CollectionContent', 'DigitalLibrary_CollectionContent', 'toString', 'concatinate', MIX_AFTER);

$_ARCHON->mixClasses('CollectionContent', 'DigitalLibrary_CollectionContent');
?>