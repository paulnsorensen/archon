<?php

abstract class DigitalLibrary_Archon
{

   /**
    * Returns the number of Digital Content in the database
    *
    * If $Alphabetical is set to true, an array will be returned with keys of
    * a-z, #, and * each holding the count for Digital Content Titles starting
    * with that character.  # represents all digital content starting with a number,
    * and * holds the total count of all digital content.
    *
    * @param boolean $Alphabetical[optional]
    * @param boolean $ExcludeNoDefaultAccessLevelContent[optional]
    * @return integer|Array
    */
   public function countDigitalContent($Alphabetical = false, $ExcludeNotBrowsableContent = false)
   {
      if(!$this->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
      {
         $ExcludeNotBrowsableContent = true;
      }

      //      if($ExcludeNoDefaultAccessLevelContent)
      //      {
      //         $ConditionsAND = 'DefaultAccessLevel > ' . DIGITALLIBRARY_ACCESSLEVEL_NONE;
      //      }

      if($ExcludeNotBrowsableContent)
      {
         //         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND = 'Browsable = 1';
      }

      if($Alphabetical)
      {
         if($ConditionsAND)
         {
            $ConditionsAND = 'AND (' . $ConditionsAND . ')';
         }

         $arrIndex = array();
         $sum = 0;

         $query = "SELECT ID FROM tblDigitalLibrary_DigitalContent WHERE (Title LIKE '0%' OR Title LIKE '1%' OR Title LIKE '2%' OR Title LIKE '3%' OR Title LIKE '4%' OR Title LIKE '5%' OR Title LIKE '6%' OR Title LIKE '7%' OR Title LIKE '8%' OR Title LIKE '9%') $ConditionsAND";
         $result = $this->mdb2->query($query);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $arrIndex['#'] = $result->numRows();
         $result->free();
         $sum += $arrIndex['#'];

         $prep = $this->mdb2->prepare("SELECT ID FROM tblDigitalLibrary_DigitalContent WHERE Title LIKE ? $ConditionsAND", 'text', MDB2_PREPARE_RESULT);
         for($i = 65; $i < 91; $i++)
         {
            $char = chr($i);

            $result = $prep->execute("$char%");
            if(PEAR::isError($result))
            {
               trigger_error($result->getMessage(), E_USER_ERROR);
            }

            $arrIndex[$char] = $result->numRows();
            $result->free();
            $arrIndex[encoding_strtolower($char)] = & $arrIndex[$char];
            $sum += $arrIndex[$char];
         }
         $prep->free();

         $arrIndex['*'] = $sum;

         return $arrIndex;
      }
      else
      {
         if($ConditionsAND)
         {
            $query = "SELECT ID FROM tblDigitalLibrary_DigitalContent WHERE $ConditionsAND";
         }
         else
         {
            $query = "SELECT ID FROM tblDigitalLibrary_DigitalContent";
         }

         $result = $this->mdb2->query($query);
         if(PEAR::isError($result))
         {
            trigger_error($result->getMessage(), E_USER_ERROR);
         }

         $numRows = $result->numRows();
         $result->free();

         return $numRows;
      }
   }

   /**
    * Creates a thumbnail of an image.
    *
    * Image should contain the raw image data.  Image type should contain
    * the image format such that the function image$ImageType can be called
    * to create the thumbnail.  If only width or height is specified, the other
    * will be calculated based upon the proportions of the original image.
    *
    * @param string $Image
    * @param string $ImageType
    * @param integer $Width[optional]
    * @param integer $Height[optional]
    */
   public function createImageThumbnail($Image, $ImageType, $Width = NULL, $Height = NULL)
   {
      if(!function_exists("image$ImageType"))
      {
         $this->declareError("Could not create ImageThumbnail: Function image$ImageType does not exist.  Please verify the specified Image Type is correct and the proper libraries are installed.");
         return false;
      }

      if(!$Width && !$Height)
      {
         $this->declareError("Could not create ImageThumbnail: No width or height specified.");
         return false;
      }

      $sourceimg = imagecreatefromstring($Image);

      if($sourceimg === false)
      {
         $this->declareError("Could not create ImageThumbnail: Please verify the image is not corrupt.");
         return false;
      }

      $sourcex = imagesx($sourceimg);
      $sourcey = imagesy($sourceimg);

      if($Width && !$Height)
      {
         $Height = $sourcey * ($Width / $sourcex);
      }
      else if($Height && !$Width)
      {
         $Width = $sourcex * ($Height / $sourcey);
      }

      // If the image is smaller than the thumbnail would be, return the image.
      if($sourcex <= $Width && $sourcey <= $Height)
      {
         return $Image;
      }

      $thumbnailimg = imagecreatetruecolor($Width, $Height);

      if(!imagecopyresampled($thumbnailimg, $sourceimg, 0, 0, 0, 0, $Width, $Height, $sourcex, $sourcey))
      {
         $this->declareError("Could not create ImageThumbnail: imagecopyresampled failed.");
         return false;
      }

      // This prevents overuse of memory for some reason.
      unset($sourceimg);

      ob_start();

      //call_user_func("image$ImageType", $thumbnailimg);
      eval("image$ImageType(\$thumbnailimg);");
      unset($thumbnailimg);

      return ob_get_clean();
   }

   public function createEmailDetailsForHighResolutionRequest($DigitalContentID, $FileID)
   {
      $details = "The following high-resolution image was requested:\n";

      if(isset($DigitalContentID))
      {
         $objDigitalContent = New DigitalContent($DigitalContentID);
         $objDigitalContent->dbLoad();

         $details .= "Digital Content Title: " . $objDigitalContent->toString() . "\n";
         $details .= ( $objDigitalContent->getString('Identifier') != '') ? "Digital Content Identifier: " . $objDigitalContent->getString('Identifier') . "\n" : '';

         if($FileID)
         {
            $objFile = New File($FileID);
            $objFile->dbLoad();
            $details .= "File Title: " . $objFile->getString('Title') . "\n";
            $details .= "File Name: " . $objFile->getString('Filename') . "\n";
         }
      }
      else
      {
         $details .= "Error: Image ID not available\n";
      }

      return $details;
   }

   /**
    * Creates a formatted string from an array of DigitalContent objects
    *
    * @param DigitalContent[] $arrDigitalContent
    * @param string $Delimiter[optional]
    * @param integer $MakeIntoLink[optional]
    * @param boolean $DirectlyLinkToContentURL[optional]
    * @return string
    */
   public function createStringFromDigitalContentArray($arrDigitalContent, $Delimiter = ', ', $MakeIntoLink = LINK_NONE, $DirectlyLinkToContentURL=false)
   {
      if(empty($arrDigitalContent))
      {
         $this->declareError("Could not create DigitalContent String: No IDs specified.");
         return false;
      }

      $objLast = end($arrDigitalContent);

      foreach($arrDigitalContent as $objDigitalContent)
      {
         $string .= $objDigitalContent->toString($MakeIntoLink, $DirectlyLinkToContentURL);

         if($objDigitalContent->ID != $objLast->ID)
         {
            $string .= $Delimiter;
         }
      }

      return $string;
   }

   /**
    * Retrieves all Digital Content from the database
    *
    * If $MakeIntoIndex is false, the returned array of DigitalContent objects
    * is sorted by DigitalContent and has IDs as keys.
    *
    * If $MakeIntoIndex is true, the returned array is a
    * two dimensional array, with the first dimension indexed with
    * 0 (representing numeric characters) and the lowercase characters a-z.
    * Each of those arrays will contain a sorted set of DigitalContent objects, with
    * the DigitalContent's IDs as keys.
    *
    * @param boolean $MakeIntoIndex[optional]
    * @param boolean $ExcludeNoDefaultAccessLevelContent[optional]
    * @return DigitalContent[]
    */
   public function getAllDigitalContent($MakeIntoIndex = false, $ExcludeNotBrowsableContent = false)// $ExcludeNoDefaultAccessLevelContent = false)
   {
      if(!$this->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
      {
         $ExcludeNoDefaultAccessLevelContent = true;
      }

      //      if($ExcludeNoDefaultAccessLevelContent)
      //      {
      //         $ConditionsAND = 'DefaultAccessLevel > ' . DIGITALLIBRARY_ACCESSLEVEL_NONE;
      //      }

      if($ExcludeNotBrowsableContent)
      {
         $ConditionsAND .= $ConditionsAND ? 'AND ' : '';
         $ConditionsAND .= 'Browsable = 1';
      }

      $arrDigitalContent = $this->loadTable("tblDigitalLibrary_DigitalContent", "DigitalContent", "Title", $ConditionsAND);

      if($MakeIntoIndex)
      {
         $arrIndex = array();

         if(!empty($arrDigitalContent))
         {
            foreach($arrDigitalContent as &$objDigitalContent)
            {
               $strDigitalContent = $objDigitalContent->Title;

               if(is_natural($strDigitalContent{0}))
               {
                  $arrIndex['#'][$objDigitalContent->ID] = $objDigitalContent;
               }

               $arrIndex[encoding_strtolower($strDigitalContent{0})][$objDigitalContent->ID] = $objDigitalContent;
            }

            ksort($arrIndex);
         }

         return $arrIndex;
      }
      else
      {
         return $arrDigitalContent;
      }
   }

   public function getNewFileList()
   {
      $arrNewFiles = array();

      $query = "SELECT ID,Filename FROM tblDigitalLibrary_Files WHERE DigitalContentID = -1 ORDER BY Filename";
      $result = $this->mdb2->query($query);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
         $arrNewFiles[$row['ID']] = $row['Filename'];
      }
      $result->free();

      return $arrNewFiles;
   }

   public function getLinkedFileList()
   {
      $arrFiles = array();

      $query = "SELECT ID,Filename,DigitalContentID FROM tblDigitalLibrary_Files WHERE DigitalContentID > 0 ORDER BY Filename";
      $result = $this->mdb2->query($query);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
         $arrFiles[$row['ID']] = array('ID' => $row['ID'], 'DigitalContentID' => $row['DigitalContentID'], 'Filename' => $row['Filename']);
      }
      $result->free();

      return $arrFiles;
   }

   public function getLinkedFiles()
   {
      $arrFiles = array();

      $query = "SELECT ID,DefaultAccessLevel,DigitalContentID,Title,Filename,FileTypeID,Size,DisplayOrder FROM tblDigitalLibrary_Files WHERE DigitalContentID > 0 ORDER BY Filename";
      $result = $this->mdb2->query($query);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
         $arrFiles[$row['ID']] = New File($row);
      }
      $result->free();

      return $arrFiles;
   }

   /**
    * Retrieves all File Types from the database
    *
    * The returned array of FileTypes objects
    * is sorted by FileTypes and has IDs as keys.
    *
    * @return FileTypes[]
    */
   public function getAllFileTypes()
   {
      return $this->loadTable("tblDigitalLibrary_FileTypes", "FileType", "FileType");
   }

   /**
    * Retrieves all Media Types from the database
    *
    * The returned array of MediaTypes objects
    * is sorted by MediaTypes and has IDs as keys.
    *
    * @return MediaTypes[]
    */
   public function getAllMediaTypes()
   {
      return $this->loadTable("tblDigitalLibrary_MediaTypes", "MediaType", "MediaType");
   }

   /**
    * Retrieves an array of DigitalContent objects that begin with
    * the character specified by $Char
    *
    * @param string $Char
    * @param boolean $ExcludeNoDefaultAccessLevelContent[optional]
    * @return DigitalContent[]
    */
   public function getDigitalContentForChar($Char, $ExcludeNotBrowsableContent = false, $RepositoryID = 0)
   {
      if(!$this->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
      {
         $ExcludeNotBrowsableContent = true;
      }

      if(!$Char)
      {
         $this->declareError("Could not get DigitalContent: Character not defined.");
         return false;
      }

      $arrDigitalContent = array();

      //      if($ExcludeNoDefaultAccessLevelContent)
      //      {
      //         $ConditionsAND = 'DefaultAccessLevel > ' . DIGITALLIBRARY_ACCESSLEVEL_NONE;
      //      }

      if($ExcludeNotBrowsableContent)
      {
         //         $ConditionsAND .= $ConditionsAND ? ' AND ' : '';
         $ConditionsAND = 'Browsable = 1';
      }

      if($ConditionsAND)
      {
         $ConditionsAND = 'AND (' . $ConditionsAND . ')';
      }

      if(!$RepositoryID)
      {

         if($Char == '#')
         {
            $query = "SELECT * FROM tblDigitalLibrary_DigitalContent WHERE (Title LIKE '0%' OR Title LIKE '1%' OR Title LIKE '2%' OR Title LIKE '3%' OR Title LIKE '4%' OR Title LIKE '5%' OR Title LIKE '6%' OR Title LIKE '7%' OR Title LIKE '8%' OR Title LIKE '9%') $ConditionsAND ORDER BY Title";
         }
         else
         {
            $query = "SELECT * FROM tblDigitalLibrary_DigitalContent WHERE Title LIKE '{$this->mdb2->escape($Char, true)}%' $ConditionsAND ORDER BY Title";
         }
      }
      else
      {
         if($Char == '#')
         {
            $query = "SELECT * FROM tblDigitalLibrary_DigitalContent WHERE (Title LIKE '0%' OR Title LIKE '1%' OR Title LIKE '2%' OR Title LIKE '3%' OR Title LIKE '4%' OR Title LIKE '5%' OR Title LIKE '6%' OR Title LIKE '7%' OR Title LIKE '8%' OR Title LIKE '9%') $ConditionsAND ORDER BY Title";
            $query = "SELECT tblDigitalLibrary_DigitalContent.*
FROM tblDigitalLibrary_DigitalContent
LEFT JOIN tblCollections_Collections ON tblDigitalLibrary_DigitalContent.CollectionID = tblCollections_Collections.ID
WHERE (
tblCollections_Collections.RepositoryID = $RepositoryID
OR tblDigitalLibrary_DigitalContent.CollectionID = 0
)
AND (tblDigitalLibrary_DigitalContent.Title LIKE '0%' OR tblDigitalLibrary_DigitalContent.Title LIKE '1%' OR tblDigitalLibrary_DigitalContent.Title LIKE '2%' OR tblDigitalLibrary_DigitalContent.Title LIKE '3%' OR tblDigitalLibrary_DigitalContent.Title LIKE '4%' OR tblDigitalLibrary_DigitalContent.Title LIKE '5%' OR tblDigitalLibrary_DigitalContent.Title LIKE '6%' OR tblDigitalLibrary_DigitalContent.Title LIKE '7%' OR tblDigitalLibrary_DigitalContent.Title LIKE '8%' OR tblDigitalLibrary_DigitalContent.Title LIKE '9%')
$ConditionsAND
ORDER BY tblDigitalLibrary_DigitalContent.Title";
         }
         else
         {
            $query = "SELECT tblDigitalLibrary_DigitalContent.*
FROM tblDigitalLibrary_DigitalContent
LEFT JOIN tblCollections_Collections ON tblDigitalLibrary_DigitalContent.CollectionID = tblCollections_Collections.ID
WHERE (
tblCollections_Collections.RepositoryID = $RepositoryID
OR tblDigitalLibrary_DigitalContent.CollectionID = 0
)
AND tblDigitalLibrary_DigitalContent.Title LIKE '{$this->mdb2->escape($Char, true)}%'
$ConditionsAND
ORDER BY tblDigitalLibrary_DigitalContent.Title";
         }
      }
      $result = $this->mdb2->query($query);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }
      while($row = $result->fetchRow())
      {
         $arrDigitalContent[$row['ID']] = New DigitalContent($row);
      }
      $result->free();

      return $arrDigitalContent;
   }

   /**
    * Returns the appropriate FileType for a specified extension.
    *
    * @param string $Extension
    * @return FileType
    */
   public function getFileTypeForExtension($Extension)
   {
      if(!$Extension)
      {
         return false;
      }

      $query = "SELECT * FROM tblDigitalLibrary_FileTypes WHERE FileExtensions LIKE '%.{$this->mdb2->escape($Extension, true)}%'";
      $this->mdb2->setLimit(1);
      $result = $this->mdb2->query($query);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      if($row['ID'])
      {
         $objFileType = New FileType($row);
      }
      else
      {
         $objFileType = New FileType(0);
      }

      return $objFileType;
   }

   /**
    * Returns MediaTypeID value for a media type
    * passed as a string.
    *
    * @param string $String
    * @return integer
    */
   public function getMediaTypeIDFromString($String)
   {
      // Case insensitve, but exact match
      $this->mdb2->setLimit(1);
      $prep = $this->mdb2->prepare("SELECT ID FROM tblDigitalLibrary_MediaTypes WHERE MediaType LIKE ?", 'text', MDB2_PREPARE_RESULT);
      $result = $prep->execute($String);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      $row = $result->fetchRow();
      $result->free();

      $row['ID'] = $row['ID'] ? $row['ID'] : 0;

      return $row['ID'];
   }

   /**
    * Searches the DigitalContent database
    *
    * Note that $SubjectID and $CreatorID will
    * not affect the search if their respective
    * packages are not installed and enabled
    *
    * @param string $SearchQuery
    * @param integer $SearchFlags[optional]
    * @param integer $CollectionID[optional]
    * @param integer $CollectionContentID[optional]
    * @param integer $SubjectID[optional]
    * @param integer $CreatorID[optional]
    * @param integer $FileTypeID[optional]
    * @param integer $MediaTypeID[optional]
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return DigitalContent[]
    */
   public function searchDigitalContent($SearchQuery, $SearchFlags = SEARCH_DIGITALCONTENT, $RepositoryID = 0, $CollectionID = 0, $CollectionContentID = 0, $SubjectID = 0, $CreatorID = 0, $FileTypeID = 0, $MediaTypeID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $arrDigitalContent = array();
      $arrPrepQueries = array();

      if(!($SearchFlags & SEARCH_DIGITALCONTENT))
      {
         return $arrDigitalContent;
      }

      if(!$this->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ))
      {
         $SearchFlags &= ~ (SEARCH_NOTBROWSABLE);
      }

      if(!($SearchFlags & SEARCH_NOTBROWSABLE))
      {
         $browsablequery = 'AND tblDigitalLibrary_DigitalContent.Browsable = 1';
      }

      //TODO: Reimplement this but only for files
      //      if($SearchFlags != SEARCH_DIGITALCONTENT)
      //      {
      //         $publicaccessquery = ' AND (';
      //
      //         if($SearchFlags & SEARCH_DEFAULTACCESSLEVEL_NONE)
      //         {
      //            $publicaccessquery .= 'tblDigitalLibrary_DigitalContent.DefaultAccessLevel = ' . DIGITALLIBRARY_ACCESSLEVEL_NONE;
      //
      //            if($SearchFlags & SEARCH_DEFAULTACCESSLEVEL_PREVIEWONLY)
      //            {
      //               $publicaccessquery .= ' OR tblDigitalLibrary_DigitalContent.DefaultAccessLevel = ' . DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY;
      //            }
      //
      //            if($SearchFlags & SEARCH_DEFAULTACCESSLEVEL_FULL)
      //            {
      //               $publicaccessquery .= ' OR tblDigitalLibrary_DigitalContent.DefaultAccessLevel = ' . DIGITALLIBRARY_ACCESSLEVEL_FULL;
      //            }
      //         }
      //         elseif($SearchFlags & SEARCH_DEFAULTACCESSLEVEL_PREVIEWONLY)
      //         {
      //            $publicaccessquery .= 'tblDigitalLibrary_DigitalContent.DefaultAccessLevel = ' . DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY;
      //
      //            if($SearchFlags & SEARCH_DEFAULTACCESSLEVEL_FULL)
      //            {
      //               $publicaccessquery .= ' OR tblDigitalLibrary_DigitalContent.DefaultAccessLevel = ' . DIGITALLIBRARY_ACCESSLEVEL_FULL;
      //            }
      //         }
      //         else
      //         {
      //            $publicaccessquery = 'tblDigitalLibrary_DigitalContent.DefaultAccessLevel = ' . DIGITALLIBRARY_ACCESSLEVEL_FULL;
      //         }
      //
      //         $publicaccessquery .= ')';
      //      }
      $publicaccessquery = '';
      $publicaccesstypes = array();
      $publicaccessvars = array();

      if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
      {
         $limitparams = array($Limit, $Offset);
      }
      else if(is_natural($Offset) && $Offset > 0)
      {
         $limitparams = array(4294967295, $Offset);
      }
      else if(is_natural($Limit) && $Limit > 0)
      {
         $limitparams = array($Limit);
      }
      else
      {
         $limitparams = array(4294967295);
      }

      if($RepositoryID && is_natural($RepositoryID))
      {
         $repositoryjoinquery = " LEFT JOIN tblCollections_Collections ON (tblDigitalLibrary_DigitalContent.CollectionID = tblCollections_Collections.ID)";
         $repositoryquery = " AND (tblDigitalLibrary_DigitalContent.CollectionID = 0 OR tblCollections_Collections.RepositoryID = ?)";
         $repositorytypes = array('integer');
         $repositoryvars = array($RepositoryID);
      }
      else
      {
         $repositoryquery = "";
         $repositorytypes = array();
         $repositoryvars = array();
      }

      if($FileTypeID && is_natural($FileTypeID))
      {
         // Same enabled query needed, just on a different table.
         //         $publicaccesssubquery = str_replace('tblDigitalLibrary_DigitalContent', 'tblDigitalLibrary_Files', $publicaccessquery);
         $publicaccesssubquery = '';
         $filetypejoinquery = " INNER JOIN tblDigitalLibrary_Files ON (tblDigitalLibrary_Files.DigitalContentID = tblDigitalLibrary_DigitalContent.ID)";

         //         $filetypequery = " AND EXISTS (SELECT tblDigitalLibrary_Files.ID FROM tblDigitalLibrary_Files WHERE tblDigitalLibrary_Files.DigitalContentID = tblDigitalLibrary_DigitalContent.ID AND tblDigitalLibrary_Files.FileTypeID = ?$publicaccesssubquery)";
         $filetypequery = " AND tblDigitalLibrary_Files.FileTypeID = ?$publicaccesssubquery)";
         $filetypetypes = array_merge(array('integer'), $publicaccesstypes);
         $filetypevars = array_merge(array($FileTypeID), $publicaccessvars);

         $mediatypetypes = array();
         $mediatypevars = array();
      }
      else
      {
         $filetypetypes = array();
         $filetypevars = array();

         // cannot do file type and media type search at same time
         if($MediaTypeID && is_natural($MediaTypeID))
         {
            //            $publicaccesssubquery = str_replace('tblDigitalLibrary_DigitalContent', 'tblDigitalLibrary_Files', $publicaccessquery);
            $publicaccesssubquery = '';
            //         $mediatypequery = " AND EXISTS (SELECT tblDigitalLibrary_Files.ID FROM tblDigitalLibrary_Files INNER JOIN tblDigitalLibrary_FileTypes ON tblDigitalLibrary_FileTypes.ID = tblDigitalLibrary_Files.FileTypeID WHERE tblDigitalLibrary_Files.DigitalContentID = tblDigitalLibrary_DigitalContent.ID AND tblDigitalLibrary_FileTypes.MediaTypeID = ?$publicaccesssubquery)";
            $mediatypejoinquery = " INNER JOIN tblDigitalLibrary_Files ON (tblDigitalLibrary_Files.DigitalContentID = tblDigitalLibrary_DigitalContent.ID) INNER JOIN tblDigitalLibrary_FileTypes ON tblDigitalLibrary_FileTypes.ID = tblDigitalLibrary_Files.FileTypeID";
            $mediatypequery = " AND tblDigitalLibrary_FileTypes.MediaTypeID = ?$publicaccesssubquery";
            $mediatypetypes = array_merge(array('integer'), $publicaccesstypes);
            $mediatypevars = array_merge(array($MediaTypeID), $publicaccessvars);
         }
         else
         {
            $mediatypetypes = array();
            $mediatypevars = array();
         }
      }

      if($SubjectID && is_natural($SubjectID) && defined('PACKAGE_SUBJECTS'))
      {
         $arrIndexSearch['Subject'] = array($SubjectID => NULL);
      }
      elseif($CreatorID && is_natural($CreatorID) && defined('PACKAGE_CREATORS'))
      {
         $arrIndexSearch['Creator'] = array($CreatorID => NULL);
      }
      else
      {
         if($CollectionID && is_natural($CollectionID))
         {
            $collectionquery = " AND tblDigitalLibrary_DigitalContent.CollectionID = ?";
            $collectiontypes = array('integer');
            $collectionvars = array($CollectionID);
         }
         else
         {
            $collectiontypes = array();
            $collectionvars = array();
         }

         if($CollectionContentID && is_natural($CollectionContentID))
         {
            $collectioncontentquery = " AND tblDigitalLibrary_DigitalContent.CollectionContentID = ?";
            $collectioncontenttypes = array('integer');
            $collectioncontentvars = array($CollectionContentID);
         }
         else
         {
            $collectioncontenttypes = array();
            $collectioncontentvars = array();
         }

         $arrWords = $this->createSearchWordArray($SearchQuery);
         $textquery = '';
         $texttypes = array();
         $textvars = array();

         if(!empty($arrWords))
         {
            $i = 0;
            foreach($arrWords as $word)
            {
               $i++;
               if($word{0} == "-")
               {
                  $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
                  $textquery .= "(tblDigitalLibrary_DigitalContent.Title NOT LIKE ? AND tblDigitalLibrary_DigitalContent.Scope NOT LIKE ? AND tblDigitalLibrary_DigitalContent.PhysicalDescription NOT LIKE ? AND tblDigitalLibrary_DigitalContent.Contributor NOT LIKE ? AND tblDigitalLibrary_DigitalContent.Identifier NOT LIKE ?)";
                  array_push($texttypes, 'text', 'text', 'text', 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%", "%$word%", "%$word%", "%$word%");
               }
               else
               {
                  $textquery .= "(tblDigitalLibrary_DigitalContent.Title LIKE ? OR tblDigitalLibrary_DigitalContent.Scope LIKE ? OR tblDigitalLibrary_DigitalContent.PhysicalDescription LIKE ? OR tblDigitalLibrary_DigitalContent.Contributor LIKE ? OR tblDigitalLibrary_DigitalContent.Identifier LIKE ?)";
                  array_push($texttypes, 'text', 'text', 'text', 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%", "%$word%", "%$word%", "%$word%");
               }

               if($i < count($arrWords))
               {
                  $textquery .= " AND ";
               }
            }
         }
         else
         {
            //                $textquery = "tblDigitalLibrary_DigitalContent.Title LIKE '%%'";
            //TODO: Make this less of a hack, if possible
            $textquery = "1 = 1";
         }

         if($textquery || $mediatypequery || $filetypequery || $collectionquery || $collectioncontentquery || $browsablequery || $publicaccessquery)
         {

            $joinquery = '';

            if($repositoryjoinquery)
            {
               $joinquery .= $repositoryjoinquery;
            }

            if($mediatypejoinquery)
            {
               $joinquery .= $mediatypejoinquery;
            }
            elseif($filetypejoinquery)
            {
               $joinquery .= $filetypejoinquery;
            }


            $wherequery = "$joinquery WHERE $textquery $repositoryquery $mediatypequery $filetypequery $collectionquery $collectioncontentquery $browsablequery $publicaccessquery";
            $wheretypes = array_merge($texttypes, $repositorytypes, $mediatypetypes, $filetypetypes, $collectiontypes, $collectioncontenttypes, $publicaccesstypes);
            $wherevars = array_merge($textvars, $repositoryvars, $mediatypevars, $filetypevars, $collectionvars, $collectioncontentvars, $publicaccessvars);
         }
         else
         {
            $wherequery = '';
            $wheretypes = array();
            $wherevars = array();
         }

         $prepQuery->query = "SELECT DISTINCT tblDigitalLibrary_DigitalContent.ID,tblDigitalLibrary_DigitalContent.Title,tblDigitalLibrary_DigitalContent.ContentURL,tblDigitalLibrary_DigitalContent.Identifier FROM tblDigitalLibrary_DigitalContent $wherequery ORDER BY tblDigitalLibrary_DigitalContent.Title";
         $prepQuery->types = $wheretypes;
         $prepQuery->vars = $wherevars;
         $arrPrepQueries[] = $prepQuery;

         if(defined('PACKAGE_SUBJECTS') && ($SearchFlags & SEARCH_SUBJECTS) && ($SearchFlags & SEARCH_RELATED))
         {
            $arrIndexSearch['Subject'] = $this->searchSubjects($SearchQuery);
         }

         if(defined('PACKAGE_CREATORS') && ($SearchFlags & SEARCH_CREATORS) && ($SearchFlags & SEARCH_RELATED))
         {
            $arrIndexSearch['Creator'] = $this->searchCreators($SearchQuery);
         }
      }

      if(!empty($arrIndexSearch))
      {
         foreach($arrIndexSearch as $Type => $arrObjects)
         {
            if(!empty($arrObjects))
            {
               foreach($arrObjects as $ID => $junk)
               {
                  $joinquery = '';

                  if($repositoryjoinquery)
                  {
                     $joinquery .= $repositoryjoinquery;
                  }

                  if($mediatypejoinquery)
                  {
                     $joinquery .= $mediatypejoinquery;
                  }
                  elseif($filetypejoinquery)
                  {
                     $joinquery .= $filetypejoinquery;
                  }



                  $whereTmp = "$repositoryquery $mediatypequery $filetypequery $browsablequery $publicaccessquery";
                  $prepQuery->query = "SELECT tblDigitalLibrary_DigitalContent.* FROM tblDigitalLibrary_DigitalContent JOIN {$this->mdb2->quoteIdentifier("tblDigitalLibrary_DigitalContent{$Type}Index")} ON {$this->mdb2->quoteIdentifier("tblDigitalLibrary_DigitalContent{$Type}Index")}.DigitalContentID = tblDigitalLibrary_DigitalContent.ID {$joinquery} WHERE {$this->mdb2->quoteIdentifier("tblDigitalLibrary_DigitalContent{$Type}Index")}.{$this->mdb2->quoteIdentifier("{$Type}ID")} = ? $whereTmp ORDER BY tblDigitalLibrary_DigitalContent.Title";
                  $prepQuery->types = array_merge(array('integer'), $repositorytypes, $publicaccesstypes, $filetypetypes, $mediatypetypes);
                  $prepQuery->vars = array_merge(array($ID), $repositoryvars, $publicaccessvars, $filetypevars, $mediatypevars);
                  $arrPrepQueries[] = $prepQuery;
               }
            }
         }
      }

      if(!empty($arrPrepQueries))
      {
         foreach($arrPrepQueries as $prepQuery)
         {
            if($prepQuery->query)
            {
               call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
               $prep = $this->mdb2->prepare($prepQuery->query, $prepQuery->types, MDB2_PREPARE_RESULT);
               if(PEAR::isError($prep))
               {
                  echo($prepQuery->query);
                  trigger_error($prep->getMessage(), E_USER_ERROR);
               }
               $result = $prep->execute($prepQuery->vars);
               if(PEAR::isError($result))
               {
                  trigger_error($result->getMessage(), E_USER_ERROR);
               }

               while($row = $result->fetchRow())
               {
                  $arrDigitalContent[$row['ID']] = New DigitalContent($row);
               }
               $result->free();
               $prep->free();
            }
         }
      }

      return $arrDigitalContent;
   }

   /**
    * Searches the FileType database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return FileType[]
    */
   public function searchFileTypes($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblDigitalLibrary_FileTypes', array('FileType', 'FileExtensions'), 'FileType', 'FileType', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

   public function searchNewFiles($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $newFiles = $this->getNewFileList();
      $arrNewFiles = array();
      foreach($newFiles as $ID => $filename)
      {

         if(!$SearchQuery || stripos($filename, $SearchQuery) !== false)
         {
            $file = New File($ID);
            $file->Filename = $filename;
            $arrNewFiles[] = $file;
         }
      }
      return $arrNewFiles;
   }

   public function searchFiles($SearchQuery, $SearchFlags, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $arrFiles = array();

      $wherequery = '';
      $wheretypes = array();
      $wherevars = array();

      if($SearchFlags == 0)
      {
         $SearchFlags = SEARCH_FILES_ALL;
      }

      switch($SearchFlags)
      {
         case(SEARCH_FILES_ALL):
            $wherequery .= '1=1 ';
            break;
         case(SEARCH_FILES_UNLINKED):
            $wherequery .= 'DigitalContentID = ? ';
            $wheretypes[] = 'integer';
            $wherevars[] = -1;
            break;
         case(SEARCH_FILES_LINKED):
            $wherequery .= 'DigitalContentID > ? ';
            $wheretypes[] = 'integer';
            $wherevars[] = 0;
            break;
      }


      $arrWords = $this->createSearchWordArray($SearchQuery);

      $Fields = array('Filename', 'Title');

      if(!empty($arrWords))
      {
         foreach($arrWords as $word)
         {
            $i++;
            if($word{0} == '-')
            {
               $word = encoding_substr($word, 1, encoding_strlen($word) - 1);
               $textquery .= '(';

               foreach($Fields as $key => $Field)
               {
                  $textquery .= "{$this->mdb2->quoteIdentifier($Field)} NOT LIKE ?";
                  $wheretypes[] = 'text';
                  $wherevars[] = "%$word%";

                  if($key + 1 < count($Fields))
                  {
                     $textquery .= ' AND ';
                  }
               }

               $textquery .= ')';
            }
            else
            {
               $textquery .= '(';
               foreach($Fields as $key => $Field)
               {
                  $textquery .= "{$this->mdb2->quoteIdentifier($Field)} LIKE ?";
                  $wheretypes[] = 'text';
                  $wherevars[] = "%$word%";

                  if($key + 1 < count($Fields))
                  {
                     $textquery .= ' OR ';
                  }
               }

               $textquery .= ')';
            }

            if($i < count($arrWords))
            {
               $textquery .= " AND ";
            }
         }
      }
      else
      {
         $textquery .= '1 = 1';
      }

      $wherequery .= ' AND ' . $textquery;

      // If our query is just a number, try to match it
      // directly to an ID from the table.
      if(is_natural($SearchQuery) && $SearchQuery > 0)
      {
         $wherequery .= " OR ID = ?";
         $wheretypes[] = 'integer';
         $wherevars[] = $SearchQuery;
      }


      if((is_natural($Offset) && $Offset > 0) && (is_natural($Limit) && $Limit > 0))
      {
         $limitparams = array($Limit, $Offset);
      }
      elseif(is_natural($Offset) && $Offset > 0)
      {
         $limitparams = array(4294967295, $Offset);
      }
      elseif(is_natural($Limit) && $Limit > 0)
      {
         $limitparams = array($Limit);
      }
      else
      {
         $limitparams = array(4294967295);
      }


      $query = "SELECT ID,DefaultAccessLevel,DigitalContentID,Title,Filename,FileTypeID,Size,DisplayOrder FROM tblDigitalLibrary_Files WHERE $wherequery ORDER BY Filename";

      call_user_func_array(array($this->mdb2, 'setLimit'), $limitparams);
      $prep = $this->mdb2->prepare($query, $wheretypes, MDB2_PREPARE_RESULT);
      if(PEAR::isError($prep))
      {
         echo($query);
         trigger_error($prep->getMessage(), E_USER_ERROR);
      }
      $result = $prep->execute($wherevars);
      if(PEAR::isError($result))
      {
         trigger_error($result->getMessage(), E_USER_ERROR);
      }

      while($row = $result->fetchRow())
      {
         $arrFiles[$row['ID']] = New File($row);
      }
      $result->free();
      $prep->free();

      return $arrFiles;
   }

   public function getFileSearchOptions()
   {
      $objSearchAllFilesPhrase = Phrase::getPhrase('searchallfiles', PACKAGE_DIGITALLIBRARY, MODULE_FILEMANAGER, PHRASETYPE_ADMIN);
      $strSearchAllFiles = $objSearchAllFilesPhrase ? $objSearchAllFilesPhrase->getPhraseValue(ENCODE_HTML) : 'Search All Files';
      $objSearchUnlinkedFilesPhrase = Phrase::getPhrase('searchunlinkedfiles', PACKAGE_DIGITALLIBRARY, MODULE_FILEMANAGER, PHRASETYPE_ADMIN);
      $strSearchUnlinkedFiles = $objSearchUnlinkedFilesPhrase ? $objSearchUnlinkedFilesPhrase->getPhraseValue(ENCODE_HTML) : 'Search Unlinked Files';
      $objSearchLinkedFilesPhrase = Phrase::getPhrase('searchlinkedfiles', PACKAGE_DIGITALLIBRARY, MODULE_FILEMANAGER, PHRASETYPE_ADMIN);
      $strSearchLinkedFiles = $objSearchLinkedFilesPhrase ? $objSearchLinkedFilesPhrase->getPhraseValue(ENCODE_HTML) : 'Search Linked Files';

      return array(
          SEARCH_FILES_ALL => $strSearchAllFiles,
          SEARCH_FILES_UNLINKED => $strSearchUnlinkedFiles,
          SEARCH_FILES_LINKED => $strSearchLinkedFiles
      );
   }

}

$_ARCHON->mixClasses('Archon', 'DigitalLibrary_Archon');
?>
