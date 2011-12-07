<?php

/**
 * class ArchonInstaller
 *
 * This file provides functions used in installing, upgrading and uninstalling
 * Archon and its packages
 *
 * @author Paul Sorensen
 */

class ArchonInstaller
{

   public static function handleError($objPEAR, $query)
   {
      global $_ARCHON;

      if (PEAR::isError($objPEAR))
      {
         $error = '';

         if ($_ARCHON->mdb2->inTransaction())
         {
            $_ARCHON->mdb2->rollback();
            $error .= "An error ocurred. Rolling back transaction --- \n";
         }

         $error .= $query."\n ---";
         $error .= $objPEAR->getMessage();
         ArchonInstaller::updateDBProgressTable('ERROR', $error);
         trigger_error($objPEAR->getMessage(), E_USER_ERROR);
      }
   }


   public static function execQuery($query)
   {
      global $_ARCHON;

      $affected = $_ARCHON->mdb2->exec($query);

      ArchonInstaller::handleError($affected, $query);

   }





   public static function execQueries($queries)
   {
      if(!is_array($queries))
      {
         $queries = array($queries);
      }
      foreach($queries as $query)
      {
         ArchonInstaller::execQuery($query);
      }
   }




   public static function execSQLFile($filename)
   {
      global $_ARCHON;

      $arrQueries = array();

      if(file_exists($filename))
      {
         $arrQueries = file($filename);
         $arrQueries = str_replace("\\n", "\r\n", $arrQueries);
      }

      if(!empty($arrQueries))
      {
         $query = '';

         foreach($arrQueries as $linequery)
         {
            if(substr($linequery, 0, 2) == "--")
            {
               ArchonInstaller::updateDBProgressTable('', substr($linequery, 3, strlen($linequery) - 3));
            }
            else
            {
               $query .= $linequery;
               if(substr(trim($linequery), -1, 1) == ';' || substr(trim($linequery), -2, 1) == ';')
               {
                  ArchonInstaller::execQuery($query);

                  $query = '';
               }
            }
         }
      }

      echo($_ARCHON->mdb2->getDebugOutput());
   }


   public static function upgradeDB($PackagePath, $arrUpgradeDirs, $strPackageName = '')
   {
      global $_ARCHON;

      @set_time_limit(0);

      foreach($arrUpgradeDirs as $UpgradeDir)
      {
         if ($_ARCHON->mdb2->supports('transactions'))
         {
            $_ARCHON->mdb2->beginTransaction();
         }


         $cwd = getcwd();
         chdir($PackagePath.$UpgradeDir);

         ArchonInstaller::updateDBProgressTable('', "Upgrading $strPackageName to version $UpgradeDir...");

         if($_ARCHON->db->ServerType == 'MySQL' && file_exists("structure-mysql.sql"))
         {
            ArchonInstaller::execSQLFile("structure-mysql.sql");
         }
         elseif($_ARCHON->db->ServerType == 'MSSQL' && file_exists("structure-mssql.sql"))
         {
            ArchonInstaller::execSQLFile("structure-mssql.sql");
         }

         if($_ARCHON->db->ServerType == 'MySQL' && file_exists("insert-mysql.sql"))
         {
            ArchonInstaller::execSQLFile("insert-mysql.sql");
         }
         elseif($_ARCHON->db->ServerType == 'MSSQL' && file_exists("insert-mssql.sql"))
         {
            ArchonInstaller::execSQLFile("insert-mssql.sql");
         }

         if(file_exists("insert.sql"))
         {
            ArchonInstaller::execSQLFile("insert.sql");
         }

         if($_ARCHON->db->ServerType == 'MySQL' && file_exists("update-mysql.sql"))
         {
            ArchonInstaller::execSQLFile("update-mysql.sql");
         }
         elseif($_ARCHON->db->ServerType == 'MSSQL' && file_exists("update-mssql.sql"))
         {
            ArchonInstaller::execSQLFile("update-mssql.sql");
         }

         if(file_exists("update.sql"))
         {
            ArchonInstaller::execSQLFile("update.sql");
         }

         if(file_exists("update.php"))
         {
            require_once("update.php");
         }

         if($_ARCHON->db->ServerType == 'MySQL' && file_exists("drop-mysql.sql"))
         {
            ArchonInstaller::execSQLFile("drop-mysql.sql");
         }
         elseif($_ARCHON->db->ServerType == 'MSSQL' && file_exists("drop-mssql.sql"))
         {
            ArchonInstaller::execSQLFile("drop-mssql.sql");
         }

         chdir($cwd);

         if ($_ARCHON->mdb2->inTransaction())
         {
            $_ARCHON->mdb2->commit();
            ArchonInstaller::updateDBProgressTable('', "Transaction Committed!");
         }
      }
   }


   public static function getUpgradeDirs($path)
   {
      global $_ARCHON, $DBVersion;

      $dh = opendir($path);

      $arrUpgradeDirs = array();

      while(false !== ($file = readdir($dh)))
      {
         if(preg_match('/([\d].[\d]{2}[a|b|rc]?[\d]?)/', $file, $matches))
         {
            if(is_dir($path.'/'.$matches[0]) && (version_compare($matches[0], $DBVersion) == 1) && (version_compare($matches[0], $_ARCHON->Version) != 1))
            {
               $arrUpgradeDirs[] = $matches[0];
            }
         }
      }
      if(!empty($arrUpgradeDirs))
      {
         usort($arrUpgradeDirs, 'version_compare');
      }
      else
      {
         ArchonInstaller::updateDBProgressTable('ERROR', "No upgrade directories were found!");
         die("No upgrade directories were found!");
      }

      return $arrUpgradeDirs;
   }


   public static function getPhraseLanguagesArray()
   {
      global $_ARCHON;

      $arrLanguages = array();
      $arrInstalledLanguages = array();

      if($handle = opendir("packages/core/install/phrasexml"))
      {
         while(false !== ($file = readdir($handle)))
         {
            if(preg_match('/([\\w]+)-core\\.xml/ui', $file, $arrMatch))
            {
               $LanguageID = $_ARCHON->getLanguageIDFromString($arrMatch[1]);
               if($LanguageID)
               {
                  $objLanguage = New Language($LanguageID);
                  $objLanguage->dbLoad();
                  $arrLanguages[$LanguageID] = $objLanguage;

                  $arrPhrases = $_ARCHON->searchPhrases('', PACKAGE_CORE, NULL, PHRASETYPE_ADMIN, $objLanguage->ID, 1);
                  if(!empty($arrPhrases))
                  {
                     $arrInstalledLanguages[] = $objLanguage->ID;
                  }
               }
            }
         }
      }
      return array('languages' => $arrLanguages, 'installed' => $arrInstalledLanguages);

   }


   public static function installDB($PackagePath)
   {
      global $_ARCHON;

      $DBProgressTableExists = ArchonInstaller::DBProgressTableExists();
      if(!$DBProgressTableExists)
      {
         ArchonInstaller::createDBProgressTable();
      }

      $cwd = getcwd();
      chdir($PackagePath);

      if($_ARCHON->db->ServerType == 'MySQL')
      {
         ArchonInstaller::execSQLFile("install-mysql.sql");
      }
      elseif($_ARCHON->db->ServerType == 'MSSQL')
      {
         ArchonInstaller::execSQLFile("install-mssql.sql");
      }
      else
      {
         die("ServerType not defined or invalid");
      }

      chdir($cwd);

      if(!$DBProgressTableExists)
      {
         ArchonInstaller::dropDBProgressTable();
      }
   }


   public static function uninstallDB($PackagePath)
   {
      global $_ARCHON;

      ArchonInstaller::dropDBProgressTable();
      ArchonInstaller::createDBProgressTable();

      $cwd = getcwd();
      chdir($PackagePath);

      if($_ARCHON->db->ServerType == 'MySQL')
      {
         ArchonInstaller::execSQLFile("uninstall-mysql.sql");
      }
      elseif($_ARCHON->db->ServerType == 'MSSQL')
      {
         ArchonInstaller::execSQLFile("uninstall-mssql.sql");
      }
      else
      {
         die("ServerType not defined or invalid");
      }

      chdir($cwd);

      ArchonInstaller::dropDBProgressTable();
   }


   public static function checkForMDB2()
   {
      global $_ARCHON;

      ob_start();
      if(!readfile('MDB2.php', true))
      {
         ob_end_clean();
         die("MDB2 is either not correctly installed or not in your include paths. <br /><br /> <a href='http://archon.org/mdb2.html'>Click here</a> for more information on how to make sure MDB2 is installed and correctly configured.");
      }
      if($_ARCHON->db->ServerType == 'MSSQL' && !readfile('MDB2/Driver/mssql.php',true))
      {
         ob_end_clean();
         die("MDB2 MSSQL Driver is not correctly installed.  <br /><br /> <a href='http://archon.org/mdb2.html'>Click here</a> for more information on how to make sure MDB2 is installed and correctly configured.");
      }
      elseif($_ARCHON->db->ServerType == 'MySQL' && !readfile('MDB2/Driver/mysql.php',true))
      {
         ob_end_clean();
         die("MDB2 MySQL Driver is not correctly installed.  <br /><br /> <a href='http://archon.org/mdb2.html'>Click here</a> for more information on how to make sure MDB2 is installed and correctly configured.");
      }
      ob_end_clean();
      return true;
   }


   public static function getPackageID($APRCode)
   {
      global $_ARCHON;

      $query = "SELECT ID FROM tblCore_Packages WHERE APRCode = '{$APRCode}'";
      $result = $_ARCHON->mdb2->query($query);
      ArchonInstaller::handleError($result, $query);
      $row = $result->fetchRow();
      $packageid = ($row['ID']) ? $row['ID'] : -1;
      $result->free();

      return $packageid;
   }


   public static function createDBProgressTable()
   {
      global $_ARCHON;

      $query = "CREATE TABLE db_progress (state VARCHAR(5) NOT NULL DEFAULT  '', message VARCHAR(500) NOT NULL DEFAULT  '')";

      $affected = $_ARCHON->mdb2->exec($query);

      if (PEAR::isError($affected))
      {
         echo("ERROR: Could not create progress table -- ".$query."\n");
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }

      $query = "INSERT INTO db_progress (state, message) VALUES ('','')";

      $affected = $_ARCHON->mdb2->exec($query);

      if (PEAR::isError($affected))
      {
         echo("ERROR: Could not create progress table -- ".$query."\n");
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
   }

   public static function dropDBProgressTable()
   {
      global $_ARCHON;

      if($_ARCHON->db->ServerType == 'MSSQL')
      {
         $query = "IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'db_progress') DROP TABLE db_progress";
      }
      elseif($_ARCHON->db->ServerType == 'MySQL')
      {
         $query = "DROP TABLE IF EXISTS db_progress";
      }

      $affected = $_ARCHON->mdb2->exec($query);

      if (PEAR::isError($affected))
      {
         echo("ERROR: Could not drop progress table -- ".$query."\n");
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
   }

   public static function updateDBProgressTable($state, $message)
   {
      global $_ARCHON;

      $query = "UPDATE db_progress SET state = '{$state}', message = '{$_ARCHON->mdb2->escape($message)}'";

      $affected = $_ARCHON->mdb2->exec($query);

      if (PEAR::isError($affected))
      {
         echo("ERROR: Could not update progress table -- ".$query."\n");
         trigger_error($affected->getMessage(), E_USER_ERROR);
      }
   }

   public static function printDBProgress()
   {
      global $_ARCHON;

      $query = "SELECT * FROM db_progress";
      $result = $_ARCHON->mdb2->query($query);
      if (PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();
      $arrResponse = array('state'=>$row['state'], 'message'=>$row['message']);
      $result->free();
      echo(json_encode($arrResponse));
   }

   public static function DBProgressTableExists()
   {
      global $_ARCHON;

      $query = "SELECT * FROM db_progress";
      $result = $_ARCHON->mdb2->query($query);
      if(PEAR::isError($result))
      {
         $exists = false;
      }
      else
      {
         $exists = true;
         $result->free();
      }

      return $exists;
   }


}

?>
