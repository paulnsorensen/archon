<?php

abstract class Accessions_Archon
{

   /**
    * Retrieves all Processing Priorities from the database
    *
    * The returned array of ProcessingPriority objects
    * is sorted by ProcessingPriority and has IDs as keys.
    *
    * @return ProcessingPriority[]
    */
   public function getAllProcessingPriorities()
   {
      return $this->loadTable("tblAccessions_ProcessingPriorities", "ProcessingPriority", "DisplayOrder, ProcessingPriority");
   }

   /**
    * Searches the ProcessingPriority database
    *
    * @param string $SearchQuery
    * @param integer $SearchFlags
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ProcessingPriority[]
    */
   public function searchAccessions($SearchQuery, $SearchFlags = SEARCH_ACCESSIONS, $ClassificationID = 0, $CollectionID = 0, $SubjectID = 0, $CreatorID = 0, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      $arrAccessions = array();
      $arrPrepQueries = array();

      if(!$this->Security->verifyPermissions(MODULE_ACCESSIONS, READ))
      {
         $SearchFlags &= ~ (SEARCH_DISABLED_ACCESSIONS);
      }

      if(!($SearchFlags & SEARCH_ACCESSIONS))
      {
         return $arrAccessions;
      }

      $enabledquery = " AND (";
      if($SearchFlags & SEARCH_ENABLED_ACCESSIONS)
      {
         $enabledquery .= "tblAccessions_Accessions.Enabled = '1'";

         if($SearchFlags & SEARCH_DISABLED_ACCESSIONS)
         {
            $enabledquery .= " OR tblAccessions_Accessions.Enabled = '0'";
         }
      }
      else
      {
         $enabledquery = "tblAccessions_Accessions.Enabled = '0'";
      }
      $enabledquery .= ")";
      $enabledtypes = array();
      $enabledvars = array();

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


      if($SubjectID && is_natural($SubjectID))
      {
         $arrIndexSearch['Subject'] = array($SubjectID => NULL);
      }
      elseif($CreatorID && is_natural($CreatorID))
      {
         $arrIndexSearch['Creator'] = array($CreatorID => NULL);
      }
      elseif($ClassificationID && is_natural($ClassificationID))
      {
         $arrIndexSearch['Classification'] = array($ClassificationID => NULL);
      }
      elseif($CollectionID && is_natural($CollectionID))
      {
         $arrIndexSearch['Collection'] = array($CollectionID => NULL);
      }
      else
      {
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
                  $textquery .= "(tblAccessions_Accessions.Title NOT LIKE ? AND tblAccessions_Accessions.AccessionDate NOT LIKE ? AND tblAccessions_Accessions.ScopeContent NOT LIKE ? AND tblAccessions_Accessions.PhysicalDescription NOT LIKE ? AND tblAccessions_Accessions.Identifier NOT LIKE ? AND tblAccessions_Accessions.Donor NOT LIKE ? AND tblAccessions_Accessions.Comments NOT LIKE ?)";
                  array_push($texttypes, 'text', 'text', 'text', 'text', 'text', 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%", "%$word%", "%$word%", "%$word%", "%$word%", "%$word%");
               }
               else
               {
                  $textquery .= "(tblAccessions_Accessions.Title LIKE ? OR tblAccessions_Accessions.AccessionDate LIKE ? OR tblAccessions_Accessions.ScopeContent LIKE ? OR tblAccessions_Accessions.PhysicalDescription LIKE ? OR tblAccessions_Accessions.Identifier LIKE ? OR tblAccessions_Accessions.Donor LIKE ? OR tblAccessions_Accessions.Comments LIKE ?)";
                  array_push($texttypes, 'text', 'text', 'text', 'text', 'text', 'text', 'text');
                  array_push($textvars, "%$word%", "%$word%", "%$word%", "%$word%", "%$word%", "%$word%", "%$word%");
               }

               if($i < count($arrWords))
               {
                  $textquery .= " AND ";
               }
            }
         }
         else
         {
//                $textquery = "tblAccessions_Accessions.Title LIKE '%%'";
            $textquery = "1=1";
         }

         // If our query is just a number, try to match it
         // directly to an ID from the table.
         if(is_natural($SearchQuery) && $SearchQuery > 0)
         {
            $textquery .= " OR ID = ?";
            $texttypes[] = 'integer';
            $textvars[] = $SearchQuery;
         }

         if($textquery || $enabledquery)
         {
            $wherequery = "WHERE $textquery $enabledquery";
            $wheretypes = array_merge($texttypes, $enabledtypes);
            $wherevars = array_merge($textvars, $enabledvars);
         }
         else
         {
            $wherequery = '';
            $wheretypes = array();
            $wherevars = array();
         }

         $prepQuery->query = "SELECT tblAccessions_Accessions.* FROM tblAccessions_Accessions $wherequery ORDER BY tblAccessions_Accessions.Title, tblAccessions_Accessions.Identifier";
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
                  if($Type != 'Classification')
                  {
                     $prepQuery->query = "SELECT tblAccessions_Accessions.* FROM tblAccessions_Accessions JOIN {$this->mdb2->quoteIdentifier("tblAccessions_Accession{$Type}Index")} ON {$this->mdb2->quoteIdentifier("tblAccessions_Accession{$Type}Index")}.AccessionID = tblAccessions_Accessions.ID WHERE {$this->mdb2->quoteIdentifier("tblAccessions_Accession{$Type}Index")}.{$this->mdb2->quoteIdentifier("{$Type}ID")} = ?$enabledquery ORDER BY tblAccessions_Accessions.Title, tblAccessions_Accessions.Identifier";
                     $prepQuery->types = array_merge(array('integer'), $enabledtypes);
                     $prepQuery->vars = array_merge(array($ID), $enabledvars);
                     $arrPrepQueries[] = $prepQuery;

                     // $arrQueries[] = "SELECT tblAccessions_Accessions.* FROM tblAccessions_Accessions JOIN tblAccessions_{$Type}Index ON tblAccessions_{$Type}Index.AccessionID = tblAccessions_Accessions.ID WHERE tblAccessions_{$Type}Index.{$Type}ID = '$ID'$enabledquery ORDER BY tblAccessions_Accessions.Title $limitquery";
                  }
                  else
                  {
                     $prepQuery->query = "SELECT tblAccessions_Accessions.* FROM tblAccessions_Accessions JOIN tblAccessions_AccessionCollectionIndex ON tblAccessions_AccessionCollectionIndex.AccessionID = tblAccessions_Accessions.ID WHERE tblAccessions_AccessionCollectionIndex.ClassificationID = ?$enabledquery ORDER BY tblAccessions_Accessions.Title, tblAccessions_Accessions.Identifier";
                     $prepQuery->types = array_merge(array('integer'), $enabledtypes);
                     $prepQuery->vars = array_merge(array($ID), $enabledvars);
                     $arrPrepQueries[] = $prepQuery;

                     // $arrQueries[] = "SELECT tblAccessions_Accessions.* FROM tblAccessions_Accessions JOIN tblAccessions_AccessionCollectionIndex ON tblAccessions_AccessionCollectionIndex.AccessionID = tblAccessions_Accessions.ID WHERE tblAccessions_AccessionCollectionIndex.ClassificationID = '$ID'$enabledquery ORDER BY tblAccessions_Accessions.Title $limitquery";
                  }
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
               $result = $prep->execute($prepQuery->vars);
               if(PEAR::isError($result))
               {
                  trigger_error($result->getMessage(), E_USER_ERROR);
               }

               while($row = $result->fetchRow())
               {
                  $arrAccessions[$row['ID']] = New Accession($row);
               }
               $result->free();
               $prep->free();
            }
         }
      }

      return $arrAccessions;
   }

   /**
    * Searches the ProcessingPriority database
    *
    * @param string $SearchQuery
    * @param integer $Limit[optional]
    * @param integer $Offset[optional]
    * @return ProcessingPriority[]
    */
   public function searchProcessingPriorities($SearchQuery, $Limit = CONFIG_CORE_SEARCH_RESULTS_LIMIT, $Offset = 0)
   {
      return $this->searchTable($SearchQuery, 'tblAccessions_ProcessingPriorities', 'ProcessingPriority', 'ProcessingPriority', 'DisplayOrder, ProcessingPriority', NULL, array(), array(), NULL, array(), array(), $Limit, $Offset);
   }

}

$_ARCHON->mixClasses('Archon', 'Accessions_Archon');
?>