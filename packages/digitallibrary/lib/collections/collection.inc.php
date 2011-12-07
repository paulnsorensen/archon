<?php
abstract class DigitalLibrary_Collection
{
   /**
    * Loads Collection and all related data and objects
    *
    * @return boolean
    */
   public function dbLoadAll($RootContentID = LOADCONTENT_ALL)
   {
      if(!$this->dbLoadDigitalContent($RootContentID))
      {
         return false;
      }

      return true;
   }




   /**
    * Loads Digital Content for Collection instance
    *
    * @return boolean
    */
   public function dbLoadDigitalContent($RootCollectionContentID = LOADCONTENT_ALL)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load DigitalContent: Collection ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load DigitalContent: Collection ID must be numeric.");
         return false;
      }

      $this->DigitalContent = array();

      static $digitalContentPrep = NULL;
      if(!isset($digitalContentPrep))
      {
         $query = "SELECT * FROM tblDigitalLibrary_DigitalContent WHERE CollectionID = ? AND
                (CollectionContentID = 0 OR CollectionContentID IN
                    (SELECT tblCollections_Content.ID FROM tblCollections_Content WHERE
                        (tblCollections_Content.RootContentID = ?
                            OR ? = " . LOADCONTENT_ALL . " OR ? = " . LOADCONTENT_NONE . ")
                        OR tblCollections_Content.RootContentID = 0))
                ORDER BY Title";

         $digitalContentPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
         if (PEAR::isError($digitalContentPrep))
         {
            echo($query);
            trigger_error($digitalContentPrep->getMessage(), E_USER_ERROR);
         }
      }
      $result = $digitalContentPrep->execute(array($this->ID, $RootCollectionContentID, $RootCollectionContentID, $RootCollectionContentID));
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         $result->free();
         return true;
      }

      $readPermissions = $_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ);
      while($row = $result->fetchRow())
      {
//            if($readPermissions || $row['DefaultAccessLevel'] != DIGITALLIBRARY_PUBLICACCESS_NONE)
         if($readPermissions || $row['Browsable'])
         {
            $objDigitalContent = New DigitalContent($row);
            $this->DigitalContent[$row['ID']] = $objDigitalContent;
         }
      }
      $result->free();

      if($RootCollectionContentID == LOADCONTENT_NONE)
      {
         return true;
      }

      // load only pieces of collection content for the just loaded digital content
      static $collectionContentPrep = NULL;
      if(!isset($collectionContentPrep))
      {
         $query = "SELECT tblCollections_Content.*, tblDigitalLibrary_DigitalContent.ID AS DigitalContentID FROM tblCollections_Content
                INNER JOIN tblDigitalLibrary_DigitalContent ON tblCollections_Content.ID = tblDigitalLibrary_DigitalContent.CollectionContentID WHERE
                tblCollections_Content.CollectionID = ? AND
                ((tblCollections_Content.RootContentID = ?
                        OR ? = " . LOADCONTENT_ALL . ")
                    OR tblCollections_Content.RootContentID = 0)";
         $collectionContentPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $collectionContentPrep->execute(array($this->ID, $RootCollectionContentID, $RootCollectionContentID));

      while($row = $result->fetchRow())
      {
         if(!$this->Content[$row['ID']])
         {
            $this->Content[$row['ID']] = New CollectionContent($row);
         }

         $this->Content[$row['ID']]->DigitalContent[$row['DigitalContentID']] = $this->DigitalContent[$row['DigitalContentID']];
      }

//        if($RootCollectionContentID != LOADCONTENT_NONE && $row['CollectionContentID'])
//        {
//            if(!$this->Content[$row['CollectionContentID']])
//            {
//                $this->Content[$row['CollectionContentID']] = New CollectionContent($row['CollectionContentID']);
//                $this->Content[$row['CollectionContentID']]->dbLoad();
//            }
//
//            $this->Content[$objDigitalContent->CollectionContentID]->DigitalContent[$objDigitalContent->ID] = $objDigitalContent;
//        }

      return true;
   }




   /**
    * Returns Collection as a string
    *
    * @param integer $MakeIntoLink
    * @param boolean $ConcatinateLevelContainer
    * @param boolean $ConcatinateLevelContainerIdentifier
    * @param boolean $ConcatinateParentLevelContainer
    * @param boolean $ConcatinateParentLevelContainerIdentifier
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
            if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) || $objDigitalContent->Browsable)
            {
               $enabledCount++;
               $objEnabledDC = $objDigitalContent;
            }
         }

         if($enabledCount > 1)
         {
            $DLURL = "?p=core/search&amp;collectionid=$this->ID";
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
    * Array containing DigitalContent for Collection
    *
    * @var DigitalContent[]
    */
   public $DigitalContent = array();
}

$_ARCHON->setMixinMethodParameters('Collection', 'DigitalLibrary_Collection', 'dbLoadAll', 'boolean_and', MIX_AFTER);
$_ARCHON->setMixinMethodParameters('Collection', 'DigitalLibrary_Collection', 'toString', 'concatinate', MIX_AFTER);

$_ARCHON->mixClasses('Collection', 'DigitalLibrary_Collection');

?>