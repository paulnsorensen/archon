<?php

isset($_ARCHON) or die();



@set_time_limit(0);


if($_REQUEST['f'] == 'build')
{
   $arrQueries = array();
   $arrQueries[] = "DROP TABLE IF EXISTS CIDs;";
   $arrQueries[] = "DROP TABLE IF EXISTS currCID;";
   $arrQueries[] = "CREATE TABLE CIDs (cid INT NOT NULL);";
   $arrQueries[] = "INSERT CIDs (SELECT DISTINCT CollectionID AS cid FROM tblCollections_Content);";
   $arrQueries[] = "ALTER TABLE CIDs ADD INDEX (cid);";
   $arrQueries[] = "CREATE TABLE currCID (cid INT NOT NULL);";


   foreach($arrQueries as $query)
   {
      $affected = $_ARCHON->mdb2->exec($query);
      if(PEAR::isError($affected))
      {
         echo($query."<br/>");

         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
   }

   $arrQueries = array();

   $arrQueries[] =
           "CREATE PROCEDURE buildnestedset()

BEGIN
  SET @done = 0;

collections: REPEAT

  SET @x = (SELECT COUNT(*) FROM CIDs);
  IF @x = 0 THEN SET @done = 1;
  END IF;

IF @done > 0 THEN LEAVE collections;
END IF;

DROP TABLE IF EXISTS Stack;

CREATE TABLE Stack
(stack_top INT NOT NULL,
 ID INT NOT NULL,
 lft INT NOT NULL,
 rgt INT NULL);

ALTER TABLE Stack ADD INDEX (stack_top);
ALTER TABLE Stack ADD INDEX (ID);
ALTER TABLE Stack ADD INDEX (lft);
ALTER TABLE Stack ADD INDEX (rgt);


INSERT currCID (SELECT MIN(cid) FROM CIDs);

SET @max_lft_rgt = 2 * (SELECT COUNT(ID) FROM tblCollections_Content WHERE CollectionID = (SELECT MIN(cid) FROM currCID)) + 2;

INSERT INTO Stack (stack_top, ID, lft, rgt) VALUES (1, 0, 1, @max_lft_rgt);

SET @lft_rgt = 2;
SET @Stack_pointer = 1;


WHILE @lft_rgt < @max_lft_rgt DO
BEGIN
 SET @c = (SELECT COUNT(*) FROM Stack AS S1, tblCollections_Content AS T1 WHERE S1.ID = T1.ParentID AND S1.stack_top = @stack_pointer AND T1.CollectionID = (SELECT MIN(cid) FROM currCID) AND T1.Lft != -1);
 IF @c > 0 THEN

      INSERT INTO Stack
      SELECT (@stack_pointer + 1), T1.ID, @lft_rgt, NULL
        FROM Stack AS S1, tblCollections_Content AS T1
       WHERE S1.ID = T1.ParentID
         AND S1.stack_top = @stack_pointer
         AND T1.CollectionID = (SELECT MIN(cid) FROM currCID)
         AND T1.Lft != -1
         ORDER BY T1.SortOrder
         LIMIT 1;

      UPDATE tblCollections_Content SET Lft = -1, Rgt = -1
         WHERE ID = (SELECT ID FROM Stack WHERE stack_top = @stack_pointer + 1);

      SET @stack_pointer = @stack_pointer + 1;

    ELSE
    BEGIN
      UPDATE Stack
         SET rgt = @lft_rgt,
             stack_top = -stack_top
       WHERE stack_top = @stack_pointer;
      SET @stack_pointer = @stack_pointer - 1;
    END;
    END IF;
  SET @lft_rgt = @lft_rgt + 1;
END;
  END WHILE;

  UPDATE Stack SET lft = lft - 1, rgt = rgt -1;
  UPDATE tblCollections_Content AS T1, Stack AS S1 SET T1.Lft = S1.lft, T1.Rgt = S1.rgt WHERE T1.ID = S1.ID;

  DELETE FROM CIDs WHERE cid = (SELECT MIN(cid) FROM currCID);
  DELETE FROM currCID WHERE 1 = 1;

  UNTIL @done END REPEAT collections;

END";
   $arrQueries[] = "CALL buildnestedset;";
   $arrQueries[] = "DROP PROCEDURE buildnestedset;";
   $arrQueries[] = "DROP TABLE IF EXISTS Stack;";
   $arrQueries[] = "DROP TABLE IF EXISTS CIDs;";
   $arrQueries[] = "DROP TABLE IF EXISTS currCID;";


//   $link = mysql_connect($_ARCHON->db->ServerAddress, $_ARCHON->db->Login, $_ARCHON->db->Password);
//   mysql_select_db($_ARCHON->db->DatabaseName, $link);

   foreach($arrQueries as $query)
   {
//      mysql_query($query, $link);
      $affected = $_ARCHON->mdb2->exec($query);
      if(PEAR::isError($affected))
      {
         echo($query."<br/>");

         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
   }

//   function buildNestedSet($id, $firstChild, $srcID)
//   {
//      global $_ARCHON, $cid;
//
//      if(!$firstChild)
//      {
//         $query = "SELECT Rgt FROM tblCollections_Content WHERE ID = $srcID";
//         $result = $_ARCHON->mdb2->query($query);
//         if(PEAR::isError($result))
//         {
//            trigger_error($result->getMessage(), E_USER_ERROR);
//         }
//         $row = $result->fetchRow();
//         $result->free();
//         $myRight = $row['Rgt'];
//
//         $affected = $_ARCHON->mdb2->exec("UPDATE tblCollections_Content SET Rgt = Rgt + 2 WHERE Rgt > $myRight AND CollectionID = $cid");
//         if(PEAR::isError($affected))
//         {
//            trigger_error($affected->getMessage(), E_USER_ERROR);
//         }
//         $affected = $_ARCHON->mdb2->exec("UPDATE tblCollections_Content SET Lft = Lft + 2 WHERE Lft > $myRight AND CollectionID = $cid");
//         if(PEAR::isError($affected))
//         {
//            trigger_error($affected->getMessage(), E_USER_ERROR);
//         }
//         $affected = $_ARCHON->mdb2->exec("UPDATE tblCollections_Content SET Lft = $myRight + 1, Rgt = $myRight + 2 WHERE ID = $id");
//         if(PEAR::isError($affected))
//         {
//            trigger_error($affected->getMessage(), E_USER_ERROR);
//         }
//      }
//      else
//      {
//         $query = "SELECT Lft FROM tblCollections_Content WHERE ID = $srcID";
//         $result = $_ARCHON->mdb2->query($query);
//         if(PEAR::isError($result))
//         {
//            trigger_error($result->getMessage(), E_USER_ERROR);
//         }
//         $row = $result->fetchRow();
//         $result->free();
//         $myLeft = $row['Lft'];
//
//         $affected = $_ARCHON->mdb2->exec("UPDATE tblCollections_Content SET Rgt = Rgt + 2 WHERE Rgt > $myLeft AND CollectionID = $cid");
//         if(PEAR::isError($affected))
//         {
//            trigger_error($affected->getMessage(), E_USER_ERROR);
//         }
//         $affected = $_ARCHON->mdb2->exec("UPDATE tblCollections_Content SET Lft = Lft + 2 WHERE Lft > $myLeft AND CollectionID = $cid");
//         if(PEAR::isError($affected))
//         {
//            trigger_error($affected->getMessage(), E_USER_ERROR);
//         }
//         $affected = $_ARCHON->mdb2->exec("UPDATE tblCollections_Content SET Lft = $myLeft + 1, Rgt = $myLeft + 2 WHERE ID = $id");
//         if(PEAR::isError($affected))
//         {
//            trigger_error($affected->getMessage(), E_USER_ERROR);
//         }
//      }
//
//      $objC = New CollectionContent($id);
//      $objC->dbLoad();
//      $objC->dbLoadContent();
//
//      $firstChild = true;
//      $srcID = $objC->ID;
//
//      foreach($objC->Content as $objContent)
//      {
//         if(!$firstChild)
//         {
//            buildNestedSet($objContent->ID, $firstChild, $srcID);
//         }
//         else
//         {
//            buildNestedSet($objContent->ID, $firstChild, $srcID);
//            $firstChild = false;
//         }
//         $srcID = $objContent->ID;
//      }
//
//   }

//
//   $objCollection = New Collection($cid);
//   $objCollection->dbLoadRootContent();
//   $lft = 0;
//   $rgt = 1;
//   foreach($objCollection->Content as $objRootContent)
//   {
//      $query = "UPDATE tblCollections_Content SET Lft = $lft, Rgt = $rgt WHERE ID = {$objRootContent->ID}";
//      $affected = $_ARCHON->mdb2->exec($query);
//      if(PEAR::isError($affected))
//      {
//         trigger_error($affected->getMessage(), E_USER_ERROR);
//      }
//
//      $lft += 2;
//      $rgt += 2;
//   }
//
//   foreach($objCollection->Content as $objRootContent)
//   {
//      $objRootContent->dbLoadContent();
//
//      $firstChild = true;
//      $srcID = $objRootContent->ID;
//
//      foreach($objRootContent->Content as $objContent)
//      {
//         if(!$firstChild)
//         {
//            buildNestedSet($objContent->ID, $firstChild, $srcID);
//         }
//         else
//         {
//            buildNestedSet($objContent->ID, $firstChild, $srcID);
//            $firstChild = false;
//         }
//         $srcID = $objContent->ID;
//      }
//   }


//   $query = "SELECT * FROM tblCollections_Content WHERE CollectionID = $cid ORDER BY ParentID, LevelContainerID, SortOrder";
//
//   $result = $_ARCHON->mdb2->query($query);
//
//   $rootContent = array();
//
//   while($row = $result->fetchRow())
//   {
//      if($row['ParentID'] == 0)
//      {
//
//      }
//   }

}
else
{

   $cid = $_REQUEST['collectionid'];

   if(!$cid || !is_natural($cid))
   {
      die();
   }

   $query = "SELECT node.*, (COUNT(parent.ID) - 1) AS depth
FROM tblCollections_Content AS node,
tblCollections_Content AS parent
WHERE node.CollectionID = $cid AND parent.CollectionID = $cid AND node.lft BETWEEN parent.lft AND parent.rgt
GROUP BY node.ID ORDER BY node.Lft";
   $start = microtime(true);
   $result = $_ARCHON->mdb2->query($query);
   $time = microtime(true) - $start;
   echo("Depth Query Time: $time <br/>");

   if(PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
   $time = 0;

   $arrLevelContainers = $_ARCHON->getAllLevelContainers();
   $imagepath = $_ARCHON->PublicInterface->ImagePath;


   function do_nothing($pid, $id)
   {
      return '';
   }

   function admin_string($pid, $id)
   {
      global $cid, $imagepath, $strEditThis;

      return "<a href='?p=admin/collections/collectioncontent&amp;collectionid={$cid}&amp;parentid={$pid}&amp;id={$id}' rel='external'><img class='edit' src='{$imagepath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";

   }

   function public_string($pid, $id)
   {
      global $cid, $url, $imagepath, $strAddTo, $strRemove;

      if($arrCart->Collections[$this->CollectionID]->Content[$this->ID])
      {
         return "<a href='?p=collections/research&amp;f=delete&amp;collectionid={$cid}&amp;collectioncontentid={$id}&amp;go=" . $url . "'><img class='cart' src='{$imagepath}/removefromcart.gif' title='$strRemove' alt='$strRemove'/></a>";
      }
      else
      {
         return "<a href='?p=collections/research&amp;f=add&amp;collectionid={$cid}&amp;collectioncontentid={$id}&amp;go=" . $url . "'><img class='cart' src='{$imagepath}/addtocart.gif' title='$strAddTo' alt='$strAddTo'/></a>";
      }
   }

   if($_ARCHON->Security->verifyPermissions(MODULE_COLLECTIONCONTENT, UPDATE))
   {
      $objEditThisPhrase = Phrase::getPhrase('tostring_editthis', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
      $strEditThis = $objEditThisPhrase ? $objEditThisPhrase->getPhraseValue(ENCODE_HTML) : 'Edit This';

      $function = 'admin_string';

//      $String .= "<a href='?p=admin/collections/collectioncontent&amp;collectionid={$cid}&amp;parentid={$this->ParentID}&amp;id={$this->ID}' rel='external'><img class='edit' src='{$_ARCHON->PublicInterface->ImagePath}/edit.gif' title='$strEditThis' alt='$strEditThis' /></a>";
   }
   elseif(!$_ARCHON->Security->userHasAdministrativeAccess()) // && $this->enabled())

   {
      $objRemovePhrase = Phrase::getPhrase('tostring_remove', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strRemove = $objRemovePhrase ? $objRemovePhrase->getPhraseValue(ENCODE_HTML) : 'Remove from your cart.';
      $objAddToPhrase = Phrase::getPhrase('tostring_addto', PACKAGE_COLLECTIONS, 0, PHRASETYPE_PUBLIC);
      $strAddTo = $objAddToPhrase ? $objAddToPhrase->getPhraseValue(ENCODE_HTML) : 'Add to your cart.';

      $url = urlencode($_SERVER['REQUEST_URI']);

      $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();

      $function = 'public_string';

//      if($arrCart->Collections[$this->CollectionID]->Content[$this->ID])
//      {
//         $String .= "<a href='?p=collections/research&amp;f=delete&amp;collectionid={$cid}&amp;collectioncontentid={$this->ID}&amp;go=" . urlencode($_SERVER['REQUEST_URI']) . "'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/removefromcart.gif' title='$strRemove' alt='$strRemove'/></a>";
//      }
//      else
//      {
//         $String .= "<a href='?p=collections/research&amp;f=add&amp;collectionid={$cid}&amp;collectioncontentid={$this->ID}&amp;go=" . urlencode($_SERVER['REQUEST_URI']) . "'><img class='cart' src='{$_ARCHON->PublicInterface->ImagePath}/addtocart.gif' title='$strAddTo' alt='$strAddTo'/></a>";
//      }
   }
   else
   {
      $function = 'do_nothing';
   }


   while($row = $result->fetchRow())
   {
//      $objContent = New FAContent($row);
      $start = microtime(true);

      for($i = 0; $i < $row['depth']; $i++)
      {
         echo("&nbsp;&nbsp;");
      }
//      echo($objContent->toString() . "<br />");

      if($row['LevelContainerID'])
      {
         $encoding_substring = $arrLevelContainers[$row['LevelContainerID']]->getString('LevelContainer');
      }

      if($row['LevelContainerIdentifier'])
      {
         $encoding_substring .= ' ' . $row['LevelContainerIdentifier'];
      }

      if(($row['Title'] || $row['Date']) && $encoding_substring)
      {
         $encoding_substring .= ': ';
      }

      if($row['Title'])
      {
         $encoding_substring .= $row['Title'];
      }

      if($row['Date'])
      {
         if($row['Title'])
         {
            $encoding_substring .= ', ';
         }

         $encoding_substring .= $row['Date'];
      }

      $string = $encoding_substring . $function($row['ParentID'], $row['ID']);

      echo($string."<br/>");

      $time += microtime(true) - $start;

   }
   $result->free();


   echo("<br/><br/><br/><br/>");

   echo("echo Time: $time <br/>");

//
//
//   $start = microtime(true);
//   $arr = $_ARCHON->traverseCollectionContent(73551);
//   $time = microtime(true) - $start;
//
//   echo("traverseColllectionContent - Adjacency List Time: $time <br/>");
//
//   echo("<br/><br/>");
//
//   $count = 0;
//   foreach($arr as $objContent)
//   {
//      for($i = 0; $i < $count; $i++)
//      {
//         echo("&nbsp;&nbsp;");
//      }
//      echo($objContent->toString() . "<br />");
//      $count++;
//   }
//
//   echo("<br/><br/><br/>");
//
//
//   $start = microtime(true);
//   $arr = $_ARCHON->traverseCollectionContent2(73551);
//   $time = microtime(true) - $start;
//
//   echo("traverseColllectionContent - Nested Set Time: $time <br/>");
//   echo("<br/><br/>");
//
//
//   $count = 0;
//   foreach($arr as $objContent)
//   {
//      for($i = 0; $i < $count; $i++)
//      {
//         echo("&nbsp;&nbsp;");
//      }
//      echo($objContent->toString() . "<br />");
//      $count++;
//   }



// echo("<br/><br/><br/><br/>");
//
//$objCollection = New Collection($cid);
//
//   $start = microtime(true);
//   $objCollection->dbLoadContent();
//   $time = microtime(true) - $start;
//
//   echo("dbLoadContent - Adjacency List Time: $time <br/>");
//echo(count($objCollection->Content));
//
//unset($objCollection);

//   $objCollection = New Collection($cid);
//
//
//   echo("<br/><br/>");
//
//
//   $start = microtime(true);
//   $objCollection->dbLoadContent2();
//   $time = microtime(true) - $start;
//
//   echo("dbLoadContent - Nested Set Time: $time <br/>");
//
//   echo(count($objCollection->Content));
//
//   echo("<br/><br/>");
//
//
//   $time = 0;
//   ob_start();
//   foreach($objCollection->Content as $objContent)
//   {
//      $start = microtime(true);
//      echo($objContent->toString(LINK_NONE, true, true)."<br/>");
//      $time += microtime(true) - $start;
//
//      $start = microtime(true);
//      echo($objContent->toStringLite(LINK_NONE, true, true)."<br/>");
//      $time1 += microtime(true) - $start;
//
//      $start = microtime(true);
//
//      if(trim($objContent->Description))
//      {
//         echo("<dd class='faitemcontent'>" . $objContent->getString('Description') . "</dd>\n");
//      }
//      $time2 += microtime(true) - $start;
//
//      $start = microtime(true);
//
//      if($objContent->UserFields)
//      {
//         echo("<dd class='faitemcontent'>" . $_ARCHON->createStringFromUserFieldArray($objContent->UserFields, "</dd>\n<dd class='faitemcontent'>\n") . "</dd>\n");
//      }
//      $time3 += microtime(true) - $start;
//
//      $start = microtime(true);
//      $objContent->enabled();
//      $time4 += microtime(true) - $start;
//
//
//   }
//   $string = ob_get_clean();
//
//   echo("toString Time: $time <br/>");
//   echo("toStringLite Time: $time1 <br/>");
//   echo("Description Time: $time2 <br/>");
//   echo("UserFields Time: $time3 <br/>");
//   echo("Enabled Check Time: $time4 <br/>");
//   echo("<br/><br/>$string");
}


?>
