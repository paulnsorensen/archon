<?php
abstract class DigitalLibrary_Creator
{
   /**
    * Deletes Creator from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      $ID = $this->ID;

      //First delete the creator from the creators table by calling creator package's dbDelete
      if(!$this->callOverridden())
      {
         return false;
      }

      // Delete any references to the creator in DigitalLibrary Creator Indesx
      static $indexPrep = NULL;
      $indexPrep = $indexPrep ? $indexPrep : $_ARCHON->mdb2->prepare('DELETE FROM tblDigitalLibrary_DigitalContentCreatorIndex WHERE CreatorID = ?', 'integer', MDB2_PREPARE_MANIP);
      $affected = $indexPrep->execute($ID);
      if (PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      return true;
   }


   /**
    * Loads Digital Content from the database
    *
    * This function loads digital content that fall under this creator
    *
    * @return boolean
    */
   public function dbLoadDigitalContent()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load DigitalContent: Creator ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load DigitalContent: Creator ID must be numeric.");
         return false;
      }

      $this->DigitalContent = array();

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT tblDigitalLibrary_DigitalContent.* FROM tblDigitalLibrary_DigitalContent JOIN tblDigitalLibrary_DigitalContentCreatorIndex ON tblDigitalLibrary_DigitalContent.ID = tblDigitalLibrary_DigitalContentCreatorIndex.DigitalContentID WHERE tblDigitalLibrary_DigitalContentCreatorIndex.CreatorID = ? ORDER BY tblDigitalLibrary_DigitalContent.Title";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
//        	if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) || $row['DefaultAccessLevel'] != DIGITALLIBRARY_ACCESSLEVEL_NONE)
         if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) || $row['Browsable'])
         {
            $objDigitalContent = New DigitalContent($row);
            $this->DigitalContent[$row['ID']] = $objDigitalContent;

            if($row['CollectionContentID'])
            {
               if(!$this->Content[$row['CollectionContentID']])
               {
                  $this->Content[$row['CollectionContentID']] = New CollectionContent($row['CollectionContentID']);
                  $this->Content[$row['CollectionContentID']]->dbLoad();
               }

               $this->Content[$objDigitalContent->CollectionContentID]->DigitalContent[$objDigitalContent->ID] = $objDigitalContent;
            }
         }
      }
      $result->free();

      return true;
   }

   /**
    * Array containing DigitalContent for Collection
    *
    * @var DigitalContent[]
    */
   public $DigitalContent = array();
}

$_ARCHON->setMixinMethodParameters('Creator', 'DigitalLibrary_Creator', 'dbDelete', NULL, MIX_OVERRIDE);

$_ARCHON->mixClasses('Creator', 'DigitalLibrary_Creator');
?>