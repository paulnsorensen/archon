<?php

abstract class Collections_ResearchCart
{

   /**
    * Add CollectionID and CollectionContentID to Researcher Cart
    *
    * @param integer $CollectionContentID
    * @return boolean
    */
   public function addToCart($CollectionID, $CollectionContentID = 0)
   {
      global $_ARCHON;

      if($_ARCHON->Security->userHasAdministrativeAccess())
      {
         $_ARCHON->declareError("Could not add CartItem: Current user is not a researcher.");
         return false;
      }

      if(!$CollectionID)
      {
         $_ARCHON->declareError("Could not add CartItem: Collection ID not defined.");
         return false;
      }

      $CollectionContentID = $CollectionContentID ? $CollectionContentID : 0;

      if($_ARCHON->Security->isAuthenticated())
      {
         $_ARCHON->Security->Session->User->dbAddToCart($CollectionID, $CollectionContentID);
      }
      else
      {
         $objCollection = New Collection($CollectionID);

         //TODO: see if this really needs to be dbLoaded
         if(!$objCollection->dbLoad())
         {
            $_ARCHON->declareError("Could not add CartItem: There was already an error.");
            return false;
         }

         if($CollectionContentID)
         {
            $objContent = New CollectionContent($CollectionContentID);

            if(!$objContent->dbLoad())
            {
               $_ARCHON->declareError("Could not add CartItem: There was already an error.");
               return false;
            }
         }

         $_ARCHON->Security->Session->setRemoteVariable('Cart', str_replace($_REQUEST['collectionid'] . ':' . $_REQUEST['collectioncontentid'] . ',', '', $_ARCHON->Security->Session->getRemoteVariable('Cart')) . $_REQUEST['collectionid'] . ':' . $_REQUEST['collectioncontentid'] . ',', true);
      }

      return true;
   }

   /**
    * Delete CollectionID and CollectionContentID from Researcher Cart
    *
    * @param integer $CollectionID
    * @param integer $CollectionContentID[optional]
    * @return boolean
    */
   public function deleteFromCart($CollectionID, $CollectionContentID = 0)
   {
      global $_ARCHON;

      if($_ARCHON->Security->userHasAdministrativeAccess())
      {
         $_ARCHON->declareError("Could not delete CartItem: Current user is not a researcher.");
         return false;
      }

      if(!$CollectionID)
      {
         $_ARCHON->declareError("Could not delete CartItem: Collection ID not defined.");
         return false;
      }

      if($_ARCHON->Security->isAuthenticated())
      {
         $retval = $_ARCHON->Security->Session->User->dbDeleteFromCart($CollectionID, $CollectionContentID);
      }
      else
      {
         if($CollectionContentID)
         {
            $retval = $_ARCHON->Security->Session->setRemoteVariable('Cart', str_replace($CollectionID . ':' . $CollectionContentID . ',', '', $_ARCHON->Security->Session->getRemoteVariable('Cart')), true);
            unset($this->Cart[$CollectionID][$CollectionContentID]);
         }
         else
         {
            $retval = $_ARCHON->Security->Session->setRemoteVariable('Cart', preg_replace('/' . $CollectionID . ':[\d]+,/u', '', $_ARCHON->Security->Session->getRemoteVariable('Cart')), true);
            unset($this->Cart[$CollectionID]);
         }
      }

      return $retval;
   }

   /**
    * Emptys Researcher Cart
    *
    * @return boolean
    */
   public function emptyCart()
   {
      global $_ARCHON;

      if($_ARCHON->Security->userHasAdministrativeAccess())
      {
         $_ARCHON->declareError("Could not empty Cart: Current user is not a researcher.");
         return false;
      }

      if($_ARCHON->Security->isAuthenticated())
      {
         $_ARCHON->Security->Session->User->dbEmptyCart();
      }
      else
      {
         $_ARCHON->Security->Session->unsetRemoteVariable('Cart', true);
      }

      $this->Cart = array();

      return true;
   }

   /**
    * Loads cart for
    *
    * @return boolean
    */
   public function getCart()
   {
      global $_ARCHON;

      if($_ARCHON->Security->userHasAdministrativeAccess())
      {
         $_ARCHON->declareError("Could not get Cart: Current user is not a researcher.");
         return false;
      }

      if($_ARCHON->Security->isAuthenticated())
      {
         if(empty($_ARCHON->Security->Session->User->Cart))
         {
            $_ARCHON->Security->Session->User->dbLoadCart();
         }

         $this->Cart = & $_ARCHON->Security->Session->User->Cart;
      }
      elseif(encoding_substr_count($_ARCHON->Security->Session->getRemoteVariable('Cart'), ',') != $this->getCartCount())
      {
         $arrImplodedEntries = explode(',', $_ARCHON->Security->Session->getRemoteVariable('Cart'));

         if(!empty($arrImplodedEntries))
         {
            $Count = 0;

            foreach($arrImplodedEntries as $strIDs)
            {
               if($strIDs)
               {
                  list($arrEntries[$Count]['CollectionID'], $arrEntries[$Count]['CollectionContentID']) = explode(':', $strIDs);
                  $Count++;
               }
            }
         }

         $this->Cart = $_ARCHON->createCartFromArray($arrEntries);
      }

      return $this->Cart;
   }

   public function getCartRepositories()
   {
      global $_ARCHON;

      if(!$this->Cart)
      {
         $this->getCart();
      }

      $arrRepositories = array();

      if($this->Cart->Collections)
      {
         foreach($this->Cart->Collections as $CollectionID => $arrObjs)
         {
            foreach($arrObjs->Content as $ContentID => $obj)
            {
               if($obj instanceof Collection)
               {
                  $objCollection = $obj;
                  unset($objContent);
               }
               else
               {
                  $objCollection = $obj->Collection;
                  $objContent = $obj;
               }

               if(!isset($arrRepositories[$objCollection->RepositoryID]))
               {
                  $objRepository = New Repository($objCollection->RepositoryID);
                  $objRepository->dbLoad();
                  $arrRepositories[$objCollection->RepositoryID] = $objRepository;
               }
            }
         }
      }
      else
      {
         $arrRepositories = $_ARCHON->getAllRepositories();
         foreach($arrRepositories as $objRepository)
         {
            if(!$objRepository->ResearchFunctionality & RESEARCH_COLLECTIONS)
            {
               unset($arrRepositories[$objRepository->ID]);
            }
         }
      }
      return $arrRepositories;
   }

   /**
    * Loads cart for
    *
    * @return integer
    */
   public function getCartCount()
   {
      global $_ARCHON;

      $Count = 0;

      if(!empty($this->Cart->Collections))
      {
         foreach($this->Cart->Collections as $arrObjs)
         {
            foreach($arrObjs->Content as $obj)
            {
               $Count++;
            }
         }
      }

      return $Count;
   }

   public function getCartDetailsArray()
   {
      global $_ARCHON;

      $cart = $this->getCart();


      $arrDetails = array();

      if($cart && $cart->Collections)
      {

         foreach($cart->Collections as $CollectionID => $arrObjs)
         {
            foreach($arrObjs->Content as $ContentID => $obj)
            {
               if($obj instanceof Collection)
               {
                  $objCollection = $obj;
                  unset($objContent);
               }
               else
               {
                  $objCollection = $obj->Collection;
                  $objContent = $obj;
               }

               $details = '';

               if(CONFIG_COLLECTIONS_SEARCH_BY_CLASSIFICATION && $objCollection->ClassificationID && $objCollection->ClassificationID != $PrevClassificationID)
               {
                  $details .= "{$objCollection->Classification->toString(LINK_NONE, true, false, true, false)}/$objCollection->CollectionIdentifier ";
                  $details .= $objCollection->Classification->toString(LINK_NONE, false, true, false, true, '/') . " -- ";
               }
               else
               {
                  $details .= "$objCollection->CollectionIdentifier ";
               }

               $details .= $objCollection->toString(LINK_NONE) . ". ";


               if($objContent)
               {
                  $details .= $objContent->toString(LINK_NONE, true, true, true, true, ', ');
               }

               $details .= "\n\n";

               $arrDetails[$objCollection->RepositoryID] .= $details;
            }
         }
      }
      return $arrDetails;
   }

   public $Cart = array();

}

$_ARCHON->mixClasses('ResearchCart', 'Collections_ResearchCart');
?>