<?php

abstract class DigitalLibrary_File
{

   /**
    * Deletes File from the database
    *
    * @return boolean
    */
   public function dbDelete()
   {
      global $_ARCHON;

      if(!$_ARCHON->deleteObject($this, MODULE_DIGITALLIBRARY, 'tblDigitalLibrary_Files'))
      {
         return false;
      }

      return true;
   }

   public function loadFileInfoFromID()
   {
      global $_ARCHON;

      if($this->ID <= 0)
      {
         $_ARCHON->declareError("Could not load File Information. Invalid ID");
         return false;
      }

      $query = "SELECT Filename,Size FROM tblDigitalLibrary_Files WHERE DigitalContentID = -1 AND ID = {$this->ID}";
      $result = $_ARCHON->mdb2->query($query);
      if(PEAR::isError($result))
      {
         echo($query);
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      $row = $result->fetchRow();

      $this->Filename = $row['Filename'];
      $this->Size = $row['Size'];
      $result->free();

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load File Info: File not found.");
         return false;
      }

      return true;
   }

   /**
    * Loads File from the database
    *
    * @return boolean
    */
   public function dbLoad($Pointer = DIGITALLIBRARY_FILE_FULL)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError("Could not load File: File ID not defined.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not load File: File ID must be numeric.");
         return false;
      }
      elseif(!$this->verifyLoadPermissions($Pointer))
      {
         $_ARCHON->declareError("Could not load File: Permission Denied.");
         return false;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $query = "SELECT ID, DefaultAccessLevel, DigitalContentID, Title, Filename, FileTypeID, Size, DisplayOrder FROM tblDigitalLibrary_Files WHERE ID = ?";
         $prep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if(!$row['ID'])
      {
         $_ARCHON->declareError("Could not load File: File ID $this->ID not found in database.");
         return false;
      }

      if(is_numeric($row['DigitalContentID']) && $row['DigitalContentID'] > 0)
      {

         $this->DigitalContent = New DigitalContent($row['DigitalContentID']);

         if(!$this->DigitalContent->dbLoad())
         {
            return false;
         }
      }

      $row = array_change_key_case($row);
      $arrVariables = get_object_vars($this);
      foreach($arrVariables as $name => $defaultvalue)
      {
         if(isset($row[strtolower($name)]))
         {
            $this->$name = $row[strtolower($name)];
         }
      }

      // Media Type will be loaded by filetype.
      if($this->FileTypeID)
      {
         $this->FileType = New FileType($this->FileTypeID);
         $this->FileType->dbLoad();
      }

      return true;
   }

   /**
    * Stores File to the database
    *
    * @return boolean
    */
   public function dbStore()
   {
      global $_ARCHON;

      preg_match('/\\.([^.]+)$/ui', $this->Filename, $arrMatch);
      $FileExtension = encoding_strtolower($arrMatch[1]);
      $this->FileTypeID = $_ARCHON->getFileTypeForExtension($FileExtension)->ID;

      if(!$FileExtension || !$this->FileTypeID)
      {
         $_ARCHON->declareError("Could not store File: File extension $FileExtension is not defined in the file types manager, and only defined file types may be added.");
         return false;
      }

      if($this->TempFileName)
      {
         if(!$this->Size)
         {
            $this->Size = filesize($this->TempFileName);
         }
      }
      elseif(!$this->ID)
      {
         $_ARCHON->declareError("Could not add File: Local copy of the file not found.");
         return false;
      }

      if($this->DigitalContentID == -1)
      {
         $checkquery = "SELECT ID FROM tblDigitalLibrary_Files WHERE Filename = ? AND DigitalContentID = ? AND ID != ?";
         $checktypes = array('text', 'integer', 'integer');
         $checkvars = array($this->Filename, $this->DigitalContentID, $this->ID);
         $checkqueryerror = "A File with the same FilenameAndContent already exists in the database";
         $problemfields = array('Filename');
         $requiredfields = array('Filename');
      }
      elseif($this->DigitalContentID > 0)
      {
         $checkquery = "SELECT ID FROM tblDigitalLibrary_Files WHERE Title = ? AND Filename = ? AND DigitalContentID = ? AND ID != ?";
         $checktypes = array('text', 'text', 'integer', 'integer');
         $checkvars = array($this->Title, $this->Filename, $this->DigitalContentID, $this->ID);
         $checkqueryerror = "A File with the same TitleAndFilenameAndContent already exists in the database";
         $problemfields = array('Title', 'Filename');
         $requiredfields = array('Title', 'Filename');
      }
      else
      {
         $_ARCHON->declareError("Could not store File: Invalid DigitalContentID");
         return false;
      }

      $ignoredfields = array('fpointer', 'filesize', 'fposition');

      if(!$_ARCHON->storeObject($this, MODULE_DIGITALLIBRARY, 'tblDigitalLibrary_Files', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields, $ignoredfields))
      {
         return false;
      }

      // Time to store the actual file contents.
      if($this->TempFileName)
      {
         $FileHandle = fopen($this->TempFileName, 'rb');
         $FileData = fread($FileHandle, 1024 * CONFIG_DIGITALLIBRARY_PREVIEW_LONG_MAX_SIZE);

         $this->FilePreviewLong = $FileData;
         $this->FilePreviewShort = substr($this->FilePreviewLong, 0, 1024 * CONFIG_DIGITALLIBRARY_PREVIEW_SHORT_MAX_SIZE);

         if(!$this->FileType)
         {
            $this->FileType = New FileType($this->FileTypeID);
            $this->FileType->dbLoad();
         }



         if(!empty($FileData))
         {
            $FileHex = unpack("H*hex", $FileData);

            $query = "UPDATE tblDigitalLibrary_Files SET FileContents = " . '0x' . $FileHex['hex'] . " WHERE ID = '{$this->ID}'";
            $affected = $_ARCHON->mdb2->exec($query);
            if(PEAR::isError($affected))
            {
               trigger_error($affected->getMessage(), E_USER_ERROR);
            }

            unset($FileHex);

            // Read data in 2MB chunks
            while(!feof($FileHandle))
            {
               $FileData = fread($FileHandle, 2 * 1024 * 1024);
               //                        $FileHex = '0x'.bin2hex($FileData);
               $FileHex = unpack("H*hex", $FileData);

               $concatFunction = ($_ARCHON->db->ServerType == 'MSSQL') ? "(FileContents + " : "CONCAT(FileContents, ";
               $query = "UPDATE tblDigitalLibrary_Files SET FileContents = " . $concatFunction . '0x' . $FileHex['hex'] . ") WHERE ID = '{$this->ID}'";
               $affected = $_ARCHON->mdb2->exec($query);
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }

               unset($FileHex);
            }

            fclose($FileHandle);

            $lengthFunction = ($_ARCHON->db->ServerType == 'MSSQL') ? 'DATALENGTH' : 'LENGTH';

            static $lengthPrep = NULL;
            if(!isset($lengthPrep))
            {
               $query = "SELECT $lengthFunction(FileContents) as FileContentsSize FROM tblDigitalLibrary_Files WHERE ID = ?";
               $lengthPrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
            }
            $result = $lengthPrep->execute($this->ID);
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $row = $result->fetchRow();
            $result->free();

            if($row['FileContentsSize'] != $this->Size)
            {
               $_ARCHON->declareError("Could not insert File: File stored in the database is {$row['FileContentsSize']} Bytes but should be $this->Size Bytes.");
               return false;
            }

            if($this->FileType->MediaType->MediaType == 'Image')
            {
               $imgtype = preg_replace("/(\w+)\/(\w+)/ui", "\$2", $this->FileType->ContentType);

               $ThumbnailSmall = null;
               $ThumbnailMedium = null;

               if(function_exists("image$imgtype"))
               {
                  $imgdata = file_get_contents($this->TempFileName);

                  $ThumbnailMedium = $_ARCHON->createImageThumbnail($imgdata, $imgtype, CONFIG_DIGITALLIBRARY_THUMBNAIL_WIDTH_MEDIUM);
                  $ThumbnailSmall = $_ARCHON->createImageThumbnail($imgdata, $imgtype, CONFIG_DIGITALLIBRARY_THUMBNAIL_WIDTH_SMALL);
               }

               $this->FilePreviewShort = $ThumbnailSmall ? $ThumbnailSmall : $this->FilePreviewShort;
               $this->FilePreviewLong = $ThumbnailMedium ? $ThumbnailMedium : $this->FilePreviewLong;

               unset($ThumbnailMedium);
               unset($ThumbnailSmall);
               unset($imgdata);
            }


            // See if we need to store any previews
//            if($this->Size > strlen($this->FilePreviewLong))
            if($this->FilePreviewLong)
            {
               // We have to store previewlong
               $FileHex = unpack("H*hex", $this->FilePreviewLong);

               $query = "UPDATE tblDigitalLibrary_Files SET FilePreviewLong = " . '0x' . $FileHex['hex'] . " WHERE ID = '{$this->ID}'";
               $affected = $_ARCHON->mdb2->exec($query);
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }

               unset($FileHex);
            }
            //if(strlen($this->FilePreviewLong->FileContents) > strlen($this->FilePreviewShort->FileContents))
            if($this->FilePreviewShort)
            {
               // We have to store previewshort
               $FileHex = unpack("H*hex", $this->FilePreviewShort);

               $query = "UPDATE tblDigitalLibrary_Files SET FilePreviewShort = " . '0x' . $FileHex['hex'] . " WHERE ID = '{$this->ID}'";
               $affected = $_ARCHON->mdb2->exec($query);
               if(PEAR::isError($affected))
               {
                  trigger_error($affected->getMessage(), E_USER_ERROR);
               }
            }
         }
      }

      unset($FileData);
      unset($FileHex);

      $success = true;

      if($_ARCHON->config->CacheFiles)
      {
         if($this->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_FULL)
         {
            $success = $this->addFileCache() && $success;
            $success = $this->addFilePreviewCache() && $success;
         }

         if($this->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY)
         {
            $success = $this->addFilePreviewCache() && $success;
            $success = $this->removeFileCache() && $success;
         }

         if($this->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_NONE)
         {
            $success = $this->removeFileCache() && $success;
            $success = $this->removeFilePreviewCache() && $success;
         }
      }

      return $success;
   }

   public function addFileCache()
   {
      global $_ARCHON;

      if($_ARCHON->config->CacheFiles)
      {
         if(!$this->ID)
         {
            $_ARCHON->declareError('Could not get file URL. ID not defined');
            return false;
         }

         if(!isset($this->Filename))
         {
            if(!$this->dbLoad())
            {
               return false;
            }
         }

         $file_cache_path = 'packages/digitallibrary/files/' . $this->ID;

         if(!is_writable('packages/digitallibrary/files/'))
         {
            $_ARCHON->declareError('Could not write to directory: packages/digitallibrary/files/. Please make sure the proper permissions are set.');
            return false;
         }

         if(!file_exists($file_cache_path))
         {
            $mode = $_ARCHON->config->CachePermissions ? $_ARCHON->config->CachePermissions : 0755;
            mkdir($file_cache_path, $mode);
         }

         if(!file_exists($file_cache_path . '/' . $this->Filename))
         {
            $fp = fopen($file_cache_path . '/' . $this->Filename, 'wb');
            if($this->fopen(DIGITALLIBRARY_FILE_FULL))
            {

               while(!$this->feof())
               {
                  fwrite($fp, $this->fread(1048576));
               }
               $this->fclose();
            }
            else
            {
               $_ARCHON->declareError("Could not cache file. File would not open.");
            }
            fclose($fp);
         }
      }

      return true;
   }

   public function removeFileCache()
   {
      global $_ARCHON;

      if($_ARCHON->config->CacheFiles)
      {
         if(!$this->ID)
         {
            $_ARCHON->declareError('Could not get file URL. ID not defined');
            return false;
         }

         if(!isset($this->Filename))
         {
            if(!$this->dbLoad())
            {
               return false;
            }
         }

         $file_cache_path = 'packages/digitallibrary/files/' . $this->ID;

         if(file_exists($file_cache_path . '/' . $this->Filename))
         {
            unlink($file_cache_path . '/' . $this->Filename);
         }

         @rmdir($file_cache_path);
      }

      return true;
   }

   public function addFilePreviewCache()
   {
      global $_ARCHON;

      if($_ARCHON->config->CacheFiles)
      {
         if(!$this->ID)
         {
            $_ARCHON->declareError('Could not get file URL. ID not defined');
            return false;
         }

         if(!isset($this->Filename))
         {
            if(!$this->dbLoad())
            {
               return false;
            }
         }

         $file_cache_path = 'packages/digitallibrary/files/' . $this->ID;

         // preview long
         if(!file_exists($file_cache_path . '/pl_' . $this->Filename))
         {
            // make sure there is a preview file to open before creating the directory
            if($this->fopen(DIGITALLIBRARY_FILE_PREVIEWLONG))
            {
               if(!is_writable('packages/digitallibrary/files/'))
               {
                  $_ARCHON->declareError('Could not write to directory: packages/digitallibrary/files/. Please make sure the proper permissions are set');
                  return false;
               }

               if(!file_exists($file_cache_path))
               {
                  $mode = $_ARCHON->config->CachePermissions ? $_ARCHON->config->CachePermissions : 0755;
                  mkdir($file_cache_path, $mode);
               }

               // prefix file name with pl_ so we don't have to worry about figuring out the file type
               $fp = fopen($file_cache_path . '/pl_' . $this->Filename, 'wb');

               while(!$this->feof())
               {
                  fwrite($fp, $this->fread(1048576));
               }
               $this->fclose();
               fclose($fp);
            }
         }

         // preview short
         if(!file_exists($file_cache_path . '/ps_' . $this->Filename))
         {
            // make sure there is a preview file to open before creating the directory
            if($this->fopen(DIGITALLIBRARY_FILE_PREVIEWSHORT))
            {
               if(!is_writable('packages/digitallibrary/files/'))
               {
                  $_ARCHON->declareError('Could not write to directory: packages/digitallibrary/files/. Please make sure the proper permissions are set');
                  return false;
               }

               if(!file_exists($file_cache_path))
               {
                  $mode = $_ARCHON->config->CachePermissions ? $_ARCHON->config->CachePermissions : 0755;
                  mkdir($file_cache_path, $mode);
               }

               // prefix file name with ps_ so we don't have to worry about figuring out the file type
               $fp = fopen($file_cache_path . '/ps_' . $this->Filename, 'wb');

               while(!$this->feof())
               {
                  fwrite($fp, $this->fread(1048576));
               }
               $this->fclose();
               fclose($fp);
            }
         }
      }

      return true;
   }

   public function removeFilePreviewCache()
   {
      global $_ARCHON;

      if($_ARCHON->config->CacheFiles)
      {
         if(!$this->ID)
         {
            $_ARCHON->declareError('Could not get file URL. ID not defined');
            return false;
         }

         if(!isset($this->Filename))
         {
            if(!$this->dbLoad())
            {
               return false;
            }
         }

         $file_cache_path = 'packages/digitallibrary/files/' . $this->ID;

         if(file_exists($file_cache_path . '/ps_' . $this->Filename))
         {
            unlink($file_cache_path . '/ps_' . $this->Filename);
         }

         if(file_exists($file_cache_path . '/pl_' . $this->Filename))
         {
            unlink($file_cache_path . '/pl_' . $this->Filename);
         }

         @rmdir($file_cache_path);
      }

      return true;
   }

   /**
    * Closes file
    *
    * @return boolean
    */
   public function fclose()
   {
      global $_ARCHON;

      if(!$this->fpointer)
      {
         $_ARCHON->declareError("Could not close File: File is not open.");
         return false;
      }

      $this->fpointer = NULL;
      $this->filesize = 0;
      $this->fposition = 1;

      return true;
   }

   /**
    * Returns whether the end of the file has been reached.
    *
    * @return boolean
    */
   public function feof()
   {
      global $_ARCHON;

      if(!$this->fpointer)
      {
         $_ARCHON->declareError("Could not return EOF status: File is not open.");
         return true;
      }

      if($this->filesize > $this->fposition)
      {
         return false;
      }
      else
      {
         return true;
      }
   }

   /**
    * Returns the size of the open file in bytes
    *
    * @return boolean
    */
   public function filesize()
   {
      if(!$this->fpointer)
      {
         $_ARCHON->declareError("Could not return filesize: File is not open.");
         return false;
      }

      return $this->filesize;
   }

   /**
    * Opens file from the database
    *
    * @param integer $Pointer
    * @return boolean
    */
   public function fopen($Pointer = DIGITALLIBRARY_FILE_FULL)
   {
      global $_ARCHON;

      if(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not open File: ID must be numeric.");
         return false;
      }
      elseif($this->fpointer)
      {
         $_ARCHON->declareError("Could not open File: File is already open.");
         return false;
      }
      elseif(!$this->verifyLoadPermissions($Pointer))
      {
         $_ARCHON->declareError("Could not open File: Permission Denied.");
         return false;
      }

      static $accessSizePrep = NULL;
      if(!isset($accessSizePrep))
      {
         $lengthFunction = ($_ARCHON->db->ServerType == 'MSSQL') ? 'DATALENGTH' : 'LENGTH';
         $query = "SELECT DigitalContentID, $lengthFunction(FileContents) as FileContentsSize, $lengthFunction(FilePreviewLong) as FilePreviewLongSize, $lengthFunction(FilePreviewShort) as FilePreviewShortSize FROM tblDigitalLibrary_Files WHERE ID = ?";
         $accessSizePrep = $_ARCHON->mdb2->prepare($query, 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $accessSizePrep->execute($this->ID);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($Pointer == DIGITALLIBRARY_FILE_FULL)
      {
         $this->fpointer = 'FileContents';
         $this->filesize = $row['FileContentsSize'];
      }
      elseif($Pointer == DIGITALLIBRARY_FILE_PREVIEWLONG)
      {
         if($row['FilePreviewLongSize'])
         {
            $this->fpointer = 'FilePreviewLong';
            $this->filesize = $row['FilePreviewLongSize'];
         }
         else
         {
            $Pointer = DIGITALLIBRARY_FILE_FULL;
            $this->fpointer = 'FileContents';
            $this->filesize = $row['FileContentsSize'];
         }
      }
      elseif($Pointer == DIGITALLIBRARY_FILE_PREVIEWSHORT)
      {
         if($row['FilePreviewShortSize'])
         {
            $this->fpointer = 'FilePreviewShort';
            $this->filesize = $row['FilePreviewShortSize'];
         }
         elseif($row['FilePreviewLongSize'])
         {
            $this->fpointer = 'FilePreviewLong';
            $this->filesize = $row['FilePreviewLongSize'];
         }
         else
         {
            $Pointer = DIGITALLIBRARY_FILE_FULL;
            $this->fpointer = 'FileContents';
            $this->filesize = $row['FileContentsSize'];
         }
      }
      else
      {
         $_ARCHON->declareError("Could not open File: Invalid Pointer.");
         return false;
      }

      $this->fposition = 1;

      return true;
   }

   /**
    * Reads File data from the database
    *
    * @param integer $Bytes
    * @return boolean
    */
   public function fread($Bytes)
   {
      global $_ARCHON;

      if(!$this->fpointer)
      {
         $_ARCHON->declareError("Could not read File: File is not open.");
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         $_ARCHON->declareError("Could not read File: ID must be numeric.");
         return false;
      }
      elseif(!is_natural($Bytes))
      {
         $_ARCHON->declareError("Could not read File: Bytes must be numeric.");
         return false;
      }

      static $preps = array();
      if(!isset($preps[$this->fpointer]))
      {
         $query = "SELECT SUBSTRING({$_ARCHON->mdb2->quoteIdentifier($this->fpointer)}, ?, ?) as FilePacket FROM tblDigitalLibrary_Files WHERE ID = ?";
         $preps[$this->fpointer] = $_ARCHON->mdb2->prepare($query, array('integer', 'integer', 'integer'), MDB2_PREPARE_RESULT);
         if(PEAR::isError($preps[$this->fpointer]))
         {
            trigger_error($preps[$this->fpointer]->getMessage(), E_USER_ERROR);
         }
      }
      $result = $preps[$this->fpointer]->execute(array($this->fposition, $Bytes, $this->ID));
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['FilePacket'])
      {
         $this->fposition += strlen($row['FilePacket']);
         return $row['FilePacket'];
      }
      else
      {
         return true;
      }
   }

   /**
    * Sets the file position
    *
    * @return boolean
    */
   public function fseek($offset, $whence = SEEK_SET)
   {
      global $_ARCHON;

      if(!$this->fpointer)
      {
         $_ARCHON->declareError("Could not seek File position: File is not open.");
         return false;
      }

      if($whence == SEEK_CUR)
      {
         $newfposition = $this->fposition + $offset;
      }
      elseif($whence == SEEK_SET)
      {
         $newfposition = $offset;
      }
      elseif($whence == SEEK_END)
      {
         $newfposition = $this->filesize + $offset;
      }

      if($newfposition < 1)
      {
         $_ARCHON->declareError("Could not seek File position: Offset would set the file position to before the beginning of the file.");
         return false;
      }

      $this->fposition = $newfposition;

      return true;
   }

   /**
    * Returns file position
    *
    * @return boolean
    */
   public function ftell()
   {
      global $_ARCHON;

      if(!$this->fpointer)
      {
         $_ARCHON->declareError("Could not tell File position: File is not open.");
         return false;
      }

      return $this->fposition;
   }

   /**
    * Rewinds file position
    *
    * @return boolean
    */
   public function rewind()
   {
      global $_ARCHON;

      if(!$this->fpointer)
      {
         $_ARCHON->declareError("Could not rewind File position: File is not open.");
         return false;
      }

      $this->fposition = 1;

      return true;
   }

   public function getCachedFileArray()
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError('Could not get cached file array. ID not defined');
         return false;
      }

      if(!isset($this->Filename))
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }

      $cachedFiles = array();

      $file_cache_path = 'packages/digitallibrary/files/' . $this->ID;

      if(file_exists($file_cache_path . '/' . $this->Filename))
         $cachedFiles[] = $file_cache_path . '/' . $this->Filename;
      if(file_exists($file_cache_path . '/pl_' . $this->Filename))
         $cachedFiles[] = $file_cache_path . '/pl_' . $this->Filename;
      if(file_exists($file_cache_path . '/ps_' . $this->Filename))
         $cachedFiles[] = $file_cache_path . '/ps_' . $this->Filename;

      return $cachedFiles;
   }

   public function getFileURL($Pointer = DIGITALLIBRARY_FILE_FULL)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         $_ARCHON->declareError('Could not get file URL. ID not defined');
         return false;
      }

      if(!isset($this->Filename))
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }

      if($_ARCHON->config->CacheFiles)
      {
         $file_cache_path = 'packages/digitallibrary/files/' . $this->ID;

         if($Pointer == DIGITALLIBRARY_FILE_PREVIEWSHORT)
         {
            if(file_exists($file_cache_path . '/ps_' . $this->Filename))
            {
               return $file_cache_path . '/ps_' . $this->Filename;
            }
         }
         elseif($Pointer == DIGITALLIBRARY_FILE_PREVIEWLONG)
         {
            if(file_exists($file_cache_path . '/pl_' . $this->Filename))
            {
               return $file_cache_path . '/pl_' . $this->Filename;
            }
         }
         else
         {
            if(file_exists($file_cache_path . '/' . $this->Filename))
            {
               return $file_cache_path . '/' . $this->Filename;
            }
         }
      }

      $url = "index.php?p=digitallibrary/getfile&amp;id=" . $this->ID;
      if($Pointer == DIGITALLIBRARY_FILE_PREVIEWSHORT)
      {
         $url .= "&amp;preview=short";
      }
      elseif($Pointer == DIGITALLIBRARY_FILE_PREVIEWLONG)
      {
         $url .= "&amp;preview=long";
      }

      return $url;
   }

   public function verifyDeletePermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, UPDATE))
      {
         return false;
      }

      if(!$this->verifyRepositoryPermissions())
      {
         $_ARCHON->declareError("Could not delete File: DigitalContent may only be altered for the primary repository.");
         return false;
      }

      return true;
   }

   /**
    * Verifies Load Permissions of DigitalContent
    *
    * @return boolean
    */
   public function verifyLoadPermissions($Pointer = DIGITALLIBRARY_FILE_FULL)
   {
      global $_ARCHON;

      if(!$this->ID)
      {
         return false;
      }
      elseif(!is_natural($this->ID))
      {
         return false;
      }

      if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
      {
         return true;
      }

      if($_ARCHON->Security->userHasAdministrativeAccess())
      {
         return true;
      }

      static $prep = NULL;
      if(!isset($prep))
      {
         $prep = $_ARCHON->mdb2->prepare("SELECT DigitalContentID, DefaultAccessLevel FROM tblDigitalLibrary_Files WHERE ID = ?", 'integer', MDB2_PREPARE_RESULT);
      }
      $result = $prep->execute($this->ID);

      if($result->numRows() != 1)
      {
         $result->free();
         return false;
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['DefaultAccessLevel'] == DIGITALLIBRARY_ACCESSLEVEL_FULL || ($row['DefaultAccessLevel'] == DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY && $Pointer != DIGITALLIBRARY_FILE_FULL))
      {
         return true;
      }

      return false;
   }

   public function verifyRepositoryPermissions()
   {
      global $_ARCHON;

      if(!$_ARCHON->Security->Session->User->RepositoryLimit)
      {
         return true;
      }

      if($this->DigitalContentID > 0)
      {
         if(!$this->DigitalContent || $this->DigitalContent->ID != $this->DigitalContentID)
         {
            $this->DigitalContent = New DigitalContent($this->DigitalContentID);
            $this->DigitalContent->dbLoad();
         }

         if($this->DigitalContent->CollectionID)
         {
            if(!$this->DigitalContent->Collection || $this->DigitalContent->Collection->ID != $this->DigitalContent->CollectionID)
            {
               $this->DigitalContent->Collection = New Collection($this->DigitalContent->CollectionID);
               $this->DigitalContent->Collection->dbLoad();
            }

            if(!$_ARCHON->Security->verifyRepositoryPermissions($this->DigitalContent->Collection->RepositoryID))
            {
               return false;
            }
         }
      }

      return true;
   }

   public function verifyStorePermissions()
   {
      global $_ARCHON;

      if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, UPDATE))
      {
         if(!$this->verifyRepositoryPermissions())
         {
            $_ARCHON->declareError("Could not store File: DigitalContent may only be altered for the primary repository.");
            return false;
         }

         return true;
      }

      return true;
   }

   public function cached()
   {
      global $_ARCHON;

      if(!$this->ID || $this->ID < 0 || !$_ARCHON->config->CacheFiles)
      {
         return true;
      }

      if(!isset($this->Filename))
      {
         if(!$this->dbLoad())
         {
            return true;
         }
      }

      $file_cache_path = 'packages/digitallibrary/files/' . $this->ID;

      switch($this->DefaultAccessLevel)
      {
         case DIGITALLIBRARY_ACCESSLEVEL_FULL:
            return file_exists($file_cache_path . '/' . $this->Filename) && file_exists($file_cache_path . '/pl_' . $this->Filename) && file_exists($file_cache_path . '/ps_' . $this->Filename);
         case DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY:
            return file_exists($file_cache_path . '/pl_' . $this->Filename) && file_exists($file_cache_path . '/ps_' . $this->Filename);
         default:
            return true;
      }
   }

   public function toString()
   {
      global $_ARCHON;

      if(!isset($this->Filename))
      {
         if(!$this->dbLoad())
         {
            return false;
         }
      }

      return $this->getString('Filename');
   }

   /**
    * @var integer
    */
   public $ID = 0;
   /**
    * @var integer
    */
   public $DefaultAccessLevel = DIGITALLIBRARY_ACCESSLEVEL_FULL;
   /**
    * @var integer
    */
   public $DigitalContentID = 0;
   /**
    * @var string
    */
   public $Title = '';
   /**
    * @var string
    */
   public $Filename = '';
   /**
    * @var integer
    */
   public $FileTypeID = 0;
   /**
    * @var integer
    */
   public $Size = 0;
   /**
    * @var integer
    */
   public $DisplayOrder = 0;
   /**
    * @var DigitalContent
    */
   public $DigitalContent = NULL;
   /**
    * @var FileContents
    */
   public $FileContents = NULL;
   /**
    * @var FileContents
    */
   public $FilePreviewLong = NULL;
   /**
    * @var FileType
    */
   public $FileType = NULL;
   /**
    * @var TempFileName
    */
   public $TempFileName = NULL;
   /**
    * @var string
    */
   private $fpointer = '';
   /**
    * @var integer
    */
   private $filesize = 0;
   /**
    * @var integer
    */
   private $fposition = 1;
}

$_ARCHON->mixClasses('File', 'DigitalLibrary_File');
?>
