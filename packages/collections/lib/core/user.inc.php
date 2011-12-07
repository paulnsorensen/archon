<?php

abstract class Collections_User
{

   /**
    * Add CollectionID and CollectionContentID to Researcher Cart
    *
    * @param integer $CollectionContentID
    * @return boolean
    */
   public function dbAddToCart($CollectionID, $CollectionContentID = 0)
   {
      global $_ARCHON;

      // Check Permissions
      if(($_ARCHON->Security->userHasAdministrativeAccess() && !$_ARCHON->Security->verifyPermissions(MODULE_PUBLICUSERS, UPDATE)) || (!$_ARCHON->Security->userHasAdministrativeAccess() && $this->ID != $_ARCHON->Security->Session->getUserID()))
      {
         $_ARCHON->declareError("Could not add CartItem: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not add CartItem: Researcher ID not defined.");
         return false;
      }

      $CollectionContentID = $CollectionContentID ? $CollectionContentID : 0;

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not add CartItem: Researcher ID must be numeric.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $_ARCHON->declareError("Could not add CartItem: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($CollectionContentID))
      {
         $_ARCHON->declareError("Could not add CartItem: CollectionContent ID must be numeric.");
         return false;
      }

      static $collectionPrep = NULL;
      if(!isset($collectionPrep))
      {
         $query = "SELECT ID FROM tblCollections_Collections WHERE ID = ?";
         $collectionPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $collectionPrep->execute($CollectionID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not add CartItem: Collection ID $CollectionID not found in database.");
         return false;
      }


      if($CollectionContentID)
      {
         static $contentPrep = NULL;
         if(!isset($contentPrep))
         {
            $query = "SELECT ID FROM tblCollections_Content WHERE ID = ?";
            $contentPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
         }
         $result = $contentPrep->execute($CollectionContentID);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $row = $result->fetchRow();
         $result->free();

         if(!$row['ID'])
         {
            $_ARCHON->declareError("Could not add CartItem: CollectionContent ID $CollectionContentID not found in database.");
            return false;
         }
      }

      static $deletePrep = NULL;
      if(!isset($deletePrep))
      {
         $query = "DELETE FROM tblCollections_ResearchCarts WHERE ResearcherID = ? AND CollectionID = ? AND CollectionContentID = ?";
         $deletePrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $deletePrep->execute(array($this->ID, $CollectionID, $CollectionContentID));
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $insertPrep = NULL;
      if(!isset($insertPrep))
      {
         $query = "INSERT INTO tblCollections_ResearchCarts (ResearcherID, CollectionID, CollectionContentID) VALUES (?, ?, ?)";
         $insertPrep = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
      }
      $affected = $insertPrep->execute(array($this->ID, $CollectionID, $CollectionContentID));
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $checkPrep = NULL;
      if(!isset($checkPrep))
      {
         $checkquery = "SELECT ID FROM tblCollections_ResearchCarts WHERE ResearcherID = ? AND CollectionID = ? AND CollectionContentID = ?";
         $checkPrep = $_ARCHON->mdb2->prepare($checkquery, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
      }
      $result = $checkPrep->execute(array($this->ID, $CollectionID, $CollectionContentID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not add CartItem: Unable to update the database table.");
         return false;
      }

      $this->dbLoadCart();

      //$_ARCHON->log("tblCollections_ResearchCarts", $row['ID']);
      //$_ARCHON->log("tblResearch_Researchers", $this->ID);

      return true;
   }

   /**
    * Delete CollectionID and CollectionContentID from Researcher Cart
    *
    * @param integer $CollectionID
    * @param integer $CollectionContentID[optional]
    * @return boolean
    */
   public function dbDeleteFromCart($CollectionID, $CollectionContentID = 0)
   {
      global $_ARCHON;

      // Check Permissions
      if(($_ARCHON->Security->userHasAdministrativeAccess() && !$_ARCHON->Security->verifyPermissions(MODULE_PUBLICUSERS, UPDATE)) || (!$_ARCHON->Security->userHasAdministrativeAccess() && $this->ID != $_ARCHON->Security->Session->getUserID()))
      {
         $_ARCHON->declareError("Could not delete CartItem: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not delete CartItem: Researcher ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not delete CartItem: Researcher ID must be numeric.");
         return false;
      }

      if(!is_natural($CollectionID))
      {
         $_ARCHON->declareError("Could not delete CartItem: Collection ID must be numeric.");
         return false;
      }

      if(!is_natural($CollectionContentID))
      {
         $_ARCHON->declareError("Could not delete CartItem: CollectionContent ID must be numeric.");
         return false;
      }

      if($CollectionContentID)
      {
         $query = "DELETE FROM tblCollections_ResearchCarts WHERE ResearcherID = ? AND CollectionID = ? AND CollectionContentID = ?";
         $types = array('integer', 'integer', 'integer');
         $vars = array($this->ID, $CollectionID, $CollectionContentID);
      }
      else
      {
         $query = "DELETE FROM tblCollections_ResearchCarts WHERE ResearcherID = ? AND CollectionID = ?";
         $types = array('integer', 'integer');
         $vars = array($this->ID, $CollectionID);
      }

      static $deletePreps = array();
      if(!isset($deletePreps[$query]))
      {
         $deletePreps[$query] = $_ARCHON->mdb2->prepare($query, $types, MDB2_PREPARE_MANIP);
      }
      $affected = $deletePreps[$query]->execute($vars);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      if($CollectionContentID)
      {
         $checkquery = "SELECT ID FROM tblCollections_ResearchCarts WHERE ResearcherID = ? AND CollectionID = ? AND CollectionContentID = ?";
         $checktypes = array('integer', 'integer', 'integer');
         $checkvars = array($this->ID, $CollectionID, $CollectionContentID);
      }
      else
      {
         $checkquery = "SELECT ID FROM tblCollections_ResearchCarts WHERE ResearcherID = ? AND CollectionID = ?";
         $checktypes = array('integer', 'integer');
         $checkvars = array($this->ID, $CollectionID);
      }

      static $checkPreps = array();
      if(!isset($checkPreps[$checkquery]))
      {
         $checkPreps[$checkquery] = $_ARCHON->mdb2->prepare($checkquery, $checktypes, MDB2_PREPARE_RESULT);
      }

      $result = $checkPreps[$checkquery]->execute($checkvars);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not delete CartItem: Unable to delete from the database table.");
         return false;
      }

      if(is_array($this->Cart))
      {
         unset($this->Cart[$CollectionID][$CollectionContentID]);
      }
      else
      {
         unset($this->Cart->Collections[$CollectionID]->Content[$CollectionContentID]);
      }

      return true;
   }

   /**
    * Clear Researcher Cart
    *
    * @return boolean
    */
   public function dbEmptyCart()
   {
      global $_ARCHON;

      // Check Permissions
      if(($_ARCHON->Security->userHasAdministrativeAccess() && !$_ARCHON->Security->verifyPermissions(MODULE_PUBLICUSERS, UPDATE)) || (!$_ARCHON->Security->userHasAdministrativeAccess() && $this->ID != $_ARCHON->Security->Session->getUserID()))
      {
         $_ARCHON->declareError("Could not delete CartItem: Permission Denied.");
         return false;
      }

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not delete CartItem: Researcher ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not delete CartItem: Researcher ID must be numeric.");
         return false;
      }

      static $deletePrep = NULL;
      if(!isset($deletePrep))
      {
         $query = "DELETE FROM tblCollections_ResearchCarts WHERE ResearcherID = ?";
         $deletePrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_MANIP);
      }
      $affected = $deletePrep->execute($this->ID);
      if(PEAR::isError($affected))
      {
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      static $checkPrep = NULL;
      if(!isset($checkPrep))
      {
         $checkquery = "SELECT ID FROM tblCollections_ResearchCarts WHERE ResearcherID = ?";
         $checkPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $checkPrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $_ARCHON->declareError("Could not delete CartItem: Unable to delete from the database table.");
         return false;
      }

      $this->Cart = array();

      return true;
   }

   /**
    * Loads CollectionContentIDs in cart for Researcher instance
    *
    * @return boolean
    */
   public function dbLoadCart()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load Researcher: Researcher ID not defined.");
         return false;
      }

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load Researcher: Researcher ID must be numeric.");
         return false;
      }

      $prep = $_ARCHON->mdb2->prepare("SELECT * FROM tblCollections_ResearchCarts WHERE ResearcherID = ?", 'integer', MDB2_PREPARE_RESULT);
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      if(!$result->numRows())
      {
         // No cart items found, return.
         $result->free();
         return true;
      }

      while($row = $result->fetchRow())
      {
         $arrEntries[] = $row;
      }
      $result->free();

      $this->Cart = $_ARCHON->createCartFromArray($arrEntries);

      return true;
   }

   /**
    * @var CollectionID[]|CollectionContentID[]
    */
   public $Cart = array();

}

$_ARCHON->mixClasses('User', 'Collections_User');
?>