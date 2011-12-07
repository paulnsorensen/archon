<?php

/**
 * @package PHP_MARC
 * @version 0.2-dev
 */

//-----------------------------------------------------------------------------
//
// Copyright (C) 2003-2005 Oy Realnode Ab
//
//-----------------------------------------------------------------------------
//
// php-marc.php
//     Part of the Emilda Project (http://www.emilda.org/)
//
// Description
//     MARC Record parser. Syntatically and logically identical to
//     the Perl library MARC::Record. MARC parsing rules have been
//     checked up from MARC::Record.
//
// Authors
//     Christoffer Landtman <landtman (at) realnode com>
//
//-----------------------------------------------------------------------------
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
//-----------------------------------------------------------------------------
//
// $Id: php-marc.php,v 1.6 2005/09/22 12:27:40 lanttis Exp $
// $Revision: 1.6 $
//
//-----------------------------------------------------------------------------

/**
 * Hexadecimal value for Subfield indicator
 * @global hex SUBFIELD_INDICATOR
 */
define("SUBFIELD_INDICATOR", "\x1F");
/**
 * Hexadecimal value for End of Field
 * @global hex END_OF_FIELD
 */
define("END_OF_FIELD", "\x1E");
/**
 * Hexadecimal value for End of Record
 * @global hex END_OF_RECORD
 */
define("END_OF_RECORD", "\x1D");
/**
 * Length of the Directory
 * @global integer DIRECTORY_ENTRY_LEN
 */
define("DIRECTORY_ENTRY_LEN", 12);
/**
 * Length of the Leader
 * @global integer LEADER_LEN
 */
define("LEADER_LEN", 24);

/**
 * Class PHP_MARC_File
 * Class to read MARC records from file(s)
 */
Class PHP_MARC_File {
	
	/**
	 * ========== VARIABLE DECLARATIONS ==========
	 */
	
	/**
	 * Array containing raw records
	 * @var array
	 */
	var $raw;
	/**
	 * Array of warnings
	 * @var array
	 */
	var $warn;
	/**
	 * Current position in the array of records
	 * @var integer
	 */
	var $pointer;
	/**
	 * Debug status
	 * @var int
	 */
	var $_DEBUG = 0;
	
	/**
	 * ========== ERROR FUNCTIONS ==========
	 */
	
	/**
	 * Croaking function
	 *
	 * Similar to Perl's croak function, which ends parsing and raises an
	 * user error with a descriptive message.
	 * @param string The message to display
	 */
	function _croak($msg) {
		trigger_error($msg, E_USER_ERROR);
	}
	
	/**
	 * Fuction to issue warnings
	 *
	 * Warnings will not be displayed unless explicitly accessed, but all
	 * warnings issued during parse will be stored
	 * @param string Warning
	 * @return string Last added warning
	 */
	function _warn($msg) {
		$this->warn[] = $msg;
		return $msg;
	}
	
	/**
	 * Get warning(s)
	 *
	 * Get either all warnings or a specific warning ID
	 * @param integer ID of the warning
	 * @return array|string Return either Array of all warnings or specific warning
	 */
	function warnings($id = "") {
		if(!$id) {
			return $this->warn;
		} else {
			if(array_key_exists($id, $this->warn)) {
				return $this->warn[$id];
			} else {
				return "Invalid warning ID: $id";
			}
		}
	}

	/**
	 * ========== PROCESSING FUNCTIONS ==========
	 */
	
	/**
	 * Return the next raw MARC record
	 * 
	 * Returns th nexts raw MARC record from the read file, unless all
	 * records already have been read.
	 * @return string|false Either a raw record or False
	 */
	function _next() {
		/**
		 * Exit if we are at the end of the file
		 */
		if ($this->pointer >= count($this->raw)) {
			return FALSE;
		}
		
		/**
		 * Read next line
		 */
		$usmarc = $this->raw[$this->pointer++];
	
		// remove illegal stuff that sometimes occurs between records
		// preg_replace does not know what to do with \x00, thus omitted.
		$usmarc = preg_replace("/^[\x0a\x0d]+/u", "", $usmarc);
	
		/**
		 * Record validation
		 */
		if ( strlen($usmarc) < 5 ) {
			$this->_warn( "Could not find record length" );
		}
		$reclen = substr($usmarc,0,5);
		if ( preg_match("/^\d{5}$/u", $reclen) || $reclen != strlen($usmarc) ) {
			$this->_warn( "Invalid record length \"$reclen\"" );
		}
	
		return $usmarc;
	}
	
	/**
	 * Read in MARC record file
	 *
	 * This function will read in MARC record files that either
	 * contain a single MARC record, or numerous records.
	 * @param string Name of the file
	 * @return string Returns warning if issued during read
	 */
	function php_marc_file($in) {
		if(file_exists($in)) {
			$input = file($in);
			$recs = explode(END_OF_RECORD, join("", $input));
			// Append END_OF_RECORD as we lost it when splitting
			// Last is not record, as it is empty because every record ends
			// with END_OF_RECORD.
			for ($i = 0; $i < (count($recs)-1); $i++) {
				$this->raw[] = $recs[$i].END_OF_RECORD;
			}
			$this->pointer = 0;
		} else {
			return $this->_warn("Invalid input file: $i");
		}
	}
	
	/**
	 * Return next PHP_MARC-object
	 *
	 * Decode the next raw MARC record and return
	 * @return Record A PHP_MARC object
	 */
	function next() {
		if($raw = $this->_next()) {
			return $this->decode($raw);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decode a given raw MARC record
	 *
	 * "Port" of Andy Lesters MARC::File::USMARC->decode() function into PHP. Ideas and
	 * "rules" have been used from USMARC::decode().
	 *
	 * @param string Raw MARC record
	 * @return Record Decoded MARC PHP_MARC object
	 */
	function decode($text) {
		if(!preg_match("/^(\d{5})/u", $text, $matches)) {
			$this->_warn('Record length "'.substr( $text, 0, 5 ).'" is not numeric');
		}
		
		$marc = new PHP_MARC;
		
		// Store record length
		$reclen = $matches[1];
		
		if($reclen != strlen($text)) {
			$this->_warn("Invalid record length: Leader says $reclen bytes, but it's actually ".strlen($text));
		}
		
		if (substr($text, -1, 1) != END_OF_RECORD)
			$this->_warn("Invalid record terminator");
			
	    // Store leader
		$marc->leader(substr( $text, 0, LEADER_LEN ));
		
		// bytes 12 - 16 of leader give offset to the body of the record
		$data_start = 0 + substr( $text, 12, 5 );
	
		// immediately after the leader comes the directory (no separator)
		$dir = substr( $text, LEADER_LEN, $data_start - LEADER_LEN - 1 );  // -1 to allow for \x1e at end of directory
		
		// character after the directory must be \x1e
		if (substr($text, $data_start-1, 1) != END_OF_FIELD) {
			$this->_warn("No directory found");
		}
		
		// All directory entries 12 bytes long, so length % 12 must be 0
		if (strlen($dir) % DIRECTORY_ENTRY_LEN != 0) {
			$this->_warn("Invalid directory length");
		}
		
		// go through all the fields
		$nfields = strlen($dir) / DIRECTORY_ENTRY_LEN;
		for ($n=0; $n<$nfields; $n++) {
			// As pack returns to key 1, leave place 0 in list empty
			list(, $tagno) = unpack("A3", substr($dir, $n*DIRECTORY_ENTRY_LEN, DIRECTORY_ENTRY_LEN));
			list(, $len) = unpack("A3/A4", substr($dir, $n*DIRECTORY_ENTRY_LEN, DIRECTORY_ENTRY_LEN));
			list(, $offset) = unpack("A3/A4/A5", substr($dir, $n*DIRECTORY_ENTRY_LEN, DIRECTORY_ENTRY_LEN));
			
			// Check directory validity
			if (!preg_match("/^[0-9A-Za-z]{3}$/u", $tagno)) {
				$this->_warn("Invalid tag in directory: \"$tagno\"");
			}
			if (!preg_match("/^\d{4}$/u", $len)) {
				$this->_warn("Invalid length in directory, tag $tagno: \"$len\"");
			}
			if (!preg_match("/^\d{5}$/u", $offset)) {
				$this->_warn("Invalid offset in directory, tag $tagno: \"$offset\"");
			}
			if ($offset + $len > $reclen) {
				$this->_warn("Directory entry runs off the end of the record tag $tagno");
			}
			
			$tagdata = substr( $text, $data_start + $offset, $len );
			
			if ( substr($tagdata, -1, 1) == END_OF_FIELD ) {
				# get rid of the end-of-tag character
				$tagdata = substr($tagdata, 0, -1);
				--$len;
			} else {
				$this->_warn("field does not end in end of field character in tag $tagno");
			}
	
			if ( preg_match("/^\d+$/u", $tagno) && ($tagno < 10) ) {
				$marc->append_fields(new PHP_MARC_Field($tagno, $tagdata));
			} else {
				$subfields = split(SUBFIELD_INDICATOR, $tagdata);
				$indicators = array_shift($subfields);
	
				if ( strlen($indicators) > 2 || strlen( $indicators ) == 0 ) {
					$this->_warn("Invalid indicators \"$indicators\" forced to blanks for tag $tagno");
					list($ind1,$ind2) = array(" ", " ");
				} else {
					$ind1 = substr( $indicators, 0, 1 );
					$ind2 = substr( $indicators, 1, 1 );
				}
	
				// Split the subfield data into subfield name and data pairs
				$subfield_data = array();
				foreach ($subfields as $subfield) {
					if ( strlen($subfield) > 0 ) {
						$subfield_data[substr($subfield, 0, 1)] = substr($subfield, 1);
					} else {
						$this->_warn( "Entirely empty subfield found in tag $tagno" );
					}
				}
	
				if (!isset($subfield_data)) {
					$this->_warn( "No subfield data found $location for tag $tagno" );
				}
	
				$marc->append_fields(new PHP_MARC_Field($tagno, $ind1, $ind2, $subfield_data ));
			}
		}
		
		if($this->_DEBUG && !empty($this->warn)) {
			print "<pre>";
			print "MARC DECODE WARNINGS:\n\n";
			foreach($this->warn as $w) {
				print "-->\t$w\n";
			}
			print "</pre>";
		}
		
		return $marc;
	}
	
	/**
	 * Get the number of records available in this Record
	 * @return int The number of records
	 */
	function num_records() {
		return count($this->raw);
	}
}

/**
 * PHP_MARC_USMARC Class
 * Extension class to File class, which allows passing of raw MARC string
 * instead of filename
 */
Class PHP_MARC_USMARC Extends PHP_MARC_File {
	/**
	 * Read raw MARC string for decoding
	 * @param string Raw MARC
	*/
	function php_marc_usmarc($string) {
		$this->raw[] = $string;
		$this->pointer = 0;
	}
}

/**
 * PHP_MARC Class
 * Create a PHP_MARC class
 */
Class PHP_MARC {
	
	/**
	 * ========== VARIABLE DECLARATIONS ==========
	 */
	
	/**
	 * Contain all @link Field objects of the Record
	 * @var array
	 */
	var $fields;
	/**
	 * Leader of the Record
	 * @var string
	 */
	var $ldr;
	/**
	 * Array of warnings
	 * @var array
	 */
	var $warn;
	
	/**
	 * ========== ERROR FUNCTIONS ==========
	 */
	
	/**
	 * Croaking function
	 *
	 * Similar to Perl's croak function, which ends parsing and raises an
	 * user error with a descriptive message.
	 * @param string The message to display
	 */
	function _croak($msg) {
		trigger_error($msg, E_USER_ERROR);
	}
	
	/**
	 * Fuction to issue warnings
	 *
	 * Warnings will not be displayed unless explicitly accessed, but all
	 * warnings issued during parse will be stored
	 * @param string Warning
	 * @return string Last added warning
	 */
	function _warn($msg) {
		$this->warn[] = $msg;
		return $msg;
	}
	
	/**
	 * Return an array of warnings
	 */
	function warnings() {
		return $this->warn;
	}
	
	/**
	 * ========== PROCESSING FUNCTIONS ==========
	 */
	
	/**
	 * Start function
	 *
	 * Set all variables to defaults to create new PHP_MARC object
	 */
	function php_marc() {
		$this->fields = array();
		$this->ldr = str_repeat(' ', 24);
	}
	
	/**
	 * Get/Set Leader
	 *
	 * If argument specified, sets leader, otherwise gets leader. No validation
	 * on the specified leader is performed
	 * @param string Leader
	 * @return string|null Return leader in case requested.
	 */
	function leader($ldr = "") {
		if($ldr) {
			$this->ldr = $ldr;
		} else {
			return $this->ldr;
		}
	}
	
	/**
	 * Append field to existing
	 *
	 * Given Field object will be appended to the existing list of fields. Field will be
	 * appended last and not in its "correct" location.
	 * @param Field The field to append
	 */
	function append_fields($field) {
		if(strtolower(get_class($field)) == "php_marc_field") {
			$this->fields[$field->tagno][] = $field;
		} else {
			$this->_croak(sprintf("Given argument must be PHP_MARC_Field object, but was '%s'", get_class($field)));
		}
	}
	
	/**
	 * Build Record Directory
	 *
	 * Generate the directory of the Record according to existing data.
	 * @return array Array ( $fields, $directory, $total, $baseaddress )
	 */
	function _build_dir() {
        // Vars
		$fields = array();
        $directory = array();

        $dataend = 0;
        foreach ($this->fields as $field_group ) {
			foreach ($field_group as $field) {
				// No empty fields allowed
				if(!$field->is_empty()) {
					// Get data in raw format
					$str = $field->raw();
					$fields[] = $str;
	
					// Create directory entry
					$len = strlen($str);
					$direntry = sprintf( "%03s%04d%05d", $field->tagno(), $len, $dataend );
					$directory[] = $direntry;
					$dataend += $len;
				}
			}
        }

		/**
		 * Rules from MARC::Record::USMARC
		 */
        $baseaddress =
                LEADER_LEN +    // better be 24
                ( count($directory) * DIRECTORY_ENTRY_LEN ) +
                                // all the directory entries
                1;              // end-of-field marker


        $total =
                $baseaddress +  // stuff before first field
                $dataend +      // Length of the fields
                1;              // End-of-record marker



        return array($fields, $directory, $total, $baseaddress);
	}
	
	/**
	 * Set Leader lengths
	 *
	 * Set the Leader lengths of the record according to defaults specified in
	 * http://www.loc.gov/marc/bibliographic/ecbdldrd.html
	 */
	function leader_lengths($reclen, $baseaddr) {
		$this->ldr = substr_replace($this->ldr, sprintf("%05d", $reclen), 0, 5);
		$this->ldr = substr_replace($this->ldr, sprintf("%05d", $baseaddr), 12, 5);
		$this->ldr = substr_replace($this->ldr, '22', 10, 2);
		$this->ldr = substr_replace($this->ldr, '4500', 20, 4);
	}
	
	/**
	 * Return all PHP_MARC_Field objects matching criteria. If the criteria is omitted
	 * all fields will be returned
	 *
	 * @param string Criteria
	 * @return array Array of PHP_MARC_Field objects
	 */
	function fields($spec = "") {
		if($spec) {
			if(array_key_exists($spec, $this->fields)) {
				return $this->fields[$spec];
			} else {
				return false;
			}
		} else {
			return $this->fields;
		}
	}
	
	/**
	 * Get specific field
	 *
	 * Search for field in Record fields based on field name, e.g. 020
	 * @param string Field name
	 * @return PHP_MARC_Field|false Return PHP_MARC_Field if found, otherwise false
	 */
	function field($spec) {
		if(array_key_exists($spec, $this->fields)) {
			return $this->fields[$spec][0];
		} else {
			return false;
		}
	}
	
	/**
	 * Get subfield of PHP_MARC_Field object
	 *
	 * Returns the value of a specific subfield of a given Field object
	 * @param string Name of field
	 * @param string Name of subfield
	 * @return PHP_MARC_Subfield|false Return value of subfield if Field exists, otherwise false
	 */
	function subfield($field, $subfield) {
		if(!$field = $this->field($field)) {
			return false;
		} else {
			return $field->subfield($subfield);
		}
	}
	
	/**
	 * Delete Field
	 *
	 * Delete the first occurance of the given field.
	 * @param PHP_MARC_Field The field to be deleted
	 */
	function delete_field($field) {
		if(count($this->fields[$field->tagno]) > 1) {
			array_shift($this->fields[$field->tagno]);
		} else {
			$this->delete_all($field);
		}
	}
	
	/**
	 * Delete field
	 *
	 * Delete all occurances of a field
	 * @param PHP_MARC_Field Field to be deleted
	 */
	function delete_all($field) {
		if(array_key_exists($field->tagno, $this->fields)) {
			unset($this->fields[$field->tagno]);
		}
	}
	
	/**
	 * Clone record
	 *
	 * Clone a record with all its Fields and subfields
	 * @return Record Clone record
	 */
	function make_clone() {
		$clone = new PHP_MARC;
		$clone->leader($this->ldr);

		foreach ($this->fields() as $data) {
			foreach ($data as $field) {
				$clone->append_fields($field);
			}
		}

		return $clone;
	}
	
	/**
	 * ========== OUTPUT FUNCTIONS ==========
	 */
	
	/**
	 * Formatted representation of PHP_MARC_Field
	 *
	 * Format a Field with a sprintf()-like formatting syntax. The formatting
	 * codes are the names of the subfields of the Field.
	 * @param string Field name
	 * @param string Format string
	 * @return string|false Return formatted string if Field exists, otherwise False
	 */
	function ffield($tag, $format) {
		$result = "";
		if($field = $this->field($tag)) {
			for ($i=0; $i<strlen($format); $i++) {
				$curr = $format[$i];
				if($curr != "%") {
					$result[] = $curr;
				} else {
					$i++;
					$curr = $format[$i];
					if($curr == "%") {
						$result[] = $curr;
					} else {
						$subfield = $this->subfield($tag, $curr);
						$result[] = $subfield->value();
					}
				}
			}
			return implode("", $result);
		} else {
			return false;
		}
	}
	
	/**
	 * Return Raw
	 *
	 * Return the Record in raw MARC format.
	 * @return string Raw MARC data
	 */
	function raw() {
		// First sort fields
		$this->_sort_fields();
		// Build
		list ($fields, $directory, $reclen, $baseaddress) = $this->_build_dir();
		$this->leader_lengths($reclen, $baseaddress);
	
		/**
		 * Glue together all parts
		 */
		return $this->ldr.implode("", $directory).END_OF_FIELD.implode("", $fields).END_OF_RECORD;
	}
    
	/**
	 * Return formatted
	 *
	 * Return the Record in a formatted fashion. Similar to the output
	 * of the formatted() function in MARC::Record in Perl
	 * @return string Formatted representation of MARC record
	 */
	function formatted() {
		// Sort fields before output
		$this->_sort_fields();
		// Begin output
		$formatted = "LDR ".$this->ldr."\n";
		foreach ($this->fields as $field_group) {
			foreach($field_group as $field) {
				$formatted .= $field->formatted()."\n";
			}
		}
		return $formatted;
	}
	
	/**
	 * Sort fields
	 *
	 * Sort all fields in record in appropriate manner. By default this is in
	 * numerically ascending order.
	 */
	function _sort_fields() {
		ksort($this->fields);
	}
}

/**
 * PHP_MARC_Field Class
 * Create a MARC Field object
 */
Class PHP_MARC_Field {
	
	/**
	 * ========== VARIABLE DECLARATIONS ==========
	 */
	
	/**
	 * The tag name of the Field
	 * @var string
	 */
	var $tagno;
	/**
	 * Value of the first indicator
	 * @var string
	 */ 
	var $ind1;
	/**
	 * Value of the second indicator
	 * @var string
	 */
	var $ind2;
	/**
	 * Array of subfields
	 * @var array
	 */
	var $subfields = array();
	/**
	 * Specify if the Field is a Control field
	 * @var bool
	 */
	var $is_control;
	/**
	 * Array of warnings
	 * @var array
	 */
	var $warn;
	/**
	 * Value of field, if field is a Control field
	 * @var string
	 */
	var $data;
	
	/**
	 * ========== ERROR FUNCTIONS ==========
	 */
	
	/**
	 * Croaking function
	 *
	 * Similar to Perl's croak function, which ends parsing and raises an
	 * user error with a descriptive message.
	 * @param string The message to display
	 */
	function _croak($msg) {
		trigger_error($msg, E_USER_ERROR);
	}
	
	/**
	 * Fuction to issue warnings
	 *
	 * Warnings will not be displayed unless explicitly accessed, but all
	 * warnings issued during parse will be stored
	 * @param string Warning
	 * @return string Last added warning
	 */
	function _warn($msg) {
		$this->warn[] = $msg;
		return $msg;
	}
	
	/**
	 * Return an array of warnings
	 */
	function warnings() {
		return $this->warn;
	}
	
	/**
	 * ========== PROCESSING FUNCTIONS ==========
	 */
	
	/**
	 * Field init function
	 *
	 * Create a new Field object from passed arguments
	 * @param array Array ( tagno, ind1, ind2, subfield_data )
	 * @return string Returns warnings if any issued during parse
	 */
	function php_marc_field() {
		$args = func_get_args();
		
		$tagno = array_shift($args);
		$this->tagno = $tagno;
		
		// Check if valid tag
		if(!preg_match("/^[0-9A-Za-z]{3}$/u", $tagno)) {
			return $this->_warn("Tag \"$tagno\" is not a valid tag.");
		}
		
		// Check if field is Control field
		$this->is_control = (preg_match("/^\d+$/u", $tagno) && $tagno < 10);
		if($this->is_control) {
			$this->data = array_shift($args);
		} else {
			foreach (array("ind1", "ind2") as $indcode) {
				$indicator = array_shift($args);
				if(!preg_match("/^[0-9A-Za-z ]$/u", $indicator)) {
					if($indicator != "") {
						$this->_warn("Illegal indicator '$indicator' in field '$tagno' forced to blank");
					}
					$indicator = " ";
				}
				$this->$indcode = $indicator;
			}
			
			$subfields = array_shift($args);
			
			if(count($subfields) < 1) {
				return $this->_warn("Field $tagno must have at least one subfield");
			} else {
				// Add subfields
				$this->add_subfields($subfields);
			}
		}
	}
	
	/**
	 * Add subfield
	 *
	 * Appends subfields to existing fields last, not in "correct" place
	 * @param array Subfield data
	 * @return string Returns warnings if issued during parse.
	 */
	function add_subfields() {
		// Process arguments
		$args = func_get_args();
		if(count($args) == 1 && is_array($args[0])) {
			$args = $args[0];
		}
		// Add subfields, is appropriate
		if ($this->is_control) {
			return $this->_warn("Subfields allowed only for tags bigger or equal to 10");
		} else {
			foreach($args as $tag => $value) {
				if($subfield = $this->subfield($tag)) {
					$subfield->append($value);
				} else {
					$this->subfields[$tag] = new PHP_MARC_Subfield($tag, $value);
				}
			}
		}
		
		return count($args)/2;
	}
	
	/**
	 * Return Tag number of Field
	 */
	function tagno() {
		return $this->tagno;
	}
	
	/**
	 * Set/Get Data of Control field
	 *
	 * Sets the Data if argument given, otherwise Data returned
	 * @param string Data to be set
	 * @return string Data of Control field if argument not given
	 */
	function data($data = "") {
		if(!$this->is_control) {
			$this->_croak("data() is only allowed for tags bigger or equal to 10");
		}
		if($data) {
			$this->data = $data;
		} else {
			return $this->data;
		}
	}
	
	/**
	 * Get values of indicators
	 *
	 * @param string Indicator number
	 * @return string Indicator value
	 */
	function indicator($ind) {
		if($ind == 1) {
			return $this->ind1;
		} elseif ($ind == 2) {
			return $this->ind2;
		} else {
			$this->_warn("Invalid indicator: $ind");
		}
	}
	
	/**
	 * Check if Field is Control field
	 *
	 * @return bool True or False
	 */
	function is_control() {
		return $this->is_control;
	}
	
	/**
	 * Get the value of a subfield
	 *
	 * Return of the value of the given subfield, if exists
	 * @param string Name of subfield
	 * @return PHP_MARC_Subfield|false Value of the subfield if exists, otherwise false
	 */
	function subfield($code) {
		if(array_key_exists($code, $this->subfields)) {
			return $this->subfields[$code];
		} else {
			return false;
		}
	}
	
	/**
	 * Return array of subfields
	 *
	 * @return array Array of PHP_MARC_Subfield objects
	 */
	function subfields() {
		return $this->subfields;
	}
	
	/**
	 * Update Field
	 *
	 * Update Field with given array of arguments.
	 * @param array Array of key->value pairs of data
	 */
	function update() {
		// Process arguments
		$args = func_get_args();
		if(count($args) == 1 && is_array($args[0])) {
			$args = $args[0];
		}
		if($this->is_control) {
			$this->data = array_shift($args);
		} else {
			foreach ($args as $subfield => $value) {
				if($subfield == "ind1") {
					$this->ind1 = $value;
				} elseif ($subfield == "ind2") {
					$this->ind2 = $value;
				} else {
					$this->subfields[$subfield] = new PHP_MARC_Subfield($subfield, $value);
				}
			}
		}
	}
	
	/**
	 * Replace Field with given Field
	 *
	 * @param Field Field to replace with
	 */
	function replace_with($obj) {
		if(strtolower(get_class($obj)) == "php_marc_field") {
			$this->tagno = $obj->tagno;
			$this->ind1 = $obj->ind1;
			$this->ind2 = $obj->ind2;
			$this->subfields = $obj->subfields;
			$this->is_control = $obj->is_control;
			$this->warn = $obj->warn;
			$this->data = $obj->data;
		} else {
			$this->_croak(sprintf("Argument must be Field-object, but was '%s'", get_class($obj)));
		}
	}
	
	/**
	 * Clone Field
	 *
	 * @return Field Cloned Field object
	 */
	function make_clone() {
		if($this->is_control) {
			return new PHP_MARC_Field($this->tagno, $this->data);
		} else {
			// Create clone
			$clone = new PHP_MARC_Field($this->tagno, $this->ind1, $this->ind2);
			
			// Process subfields and add to clone
			$subfields = array();
			foreach($this->subfields as $subfield) {
				foreach($subfield->values() as $value) {
					$clone->add_subfields(array($subfield->tag() => $value));
				}
			}
			
			// Return clone
			return $clone;
		}
	}
	
	/**
	 * Is empty
	 *
	 * Checks if the field is empty. Criteria is that it has
	 * atleast one subfield with a set value
	 */
	function is_empty() {
		if($this->tagno > 10) {
			$empty = true;
			foreach($this->subfields as $subfield) {
				// Check if subfield has data
				if(!$subfield->is_empty()) {
					return false;
				}
			}
			// Its empty
			return true;
		} else {
			return ($this->data) ? false : true;
		}
	}
	
	/**
	 * ========== OUTPUT FUNCTIONS ==========
	 */
	
	/**
	 * Return Field formatted
	 *
	 * Return Field as string, formatted in a similar fashion to the
	 * MARC::Record formatted() function in Perl
	 *
	 * @return string Formatted output of Field
	 */
	function formatted() {
		// Variables
		$lines = array();
		// Process
		if($this->is_control) {
			return sprintf("%3s     %s", $this->tagno, $this->data);
		} else {
			$pre = sprintf("%3s %1s%1s", $this->tagno, $this->ind1, $this->ind2);
		}
		// Process subfields
		foreach ($this->subfields as $subfield) {
			foreach($subfield->values() as $value) {
				$lines[] = sprintf("%6s _%1s%s", $pre, $subfield->tag(), $value);
				$pre = "";
			}
		}
		
		return join("\n", $lines);
	}
	
	/**
	 * Return Field in Raw MARC
	 *
	 * Return the Field formatted in Raw MARC for saving into MARC files
	 *
	 * @return string Raw MARC
	 */
	function raw() {
		if($this->is_control) {
			return $this->data.END_OF_FIELD;
		} else {
			$subfields = array();
			foreach ($this->subfields as $subfield) {
				if(!$subfield->is_empty()) {
					$subfields[] = $subfield->raw();
				}
			}
			return $this->ind1.$this->ind2.implode("", $subfields).END_OF_FIELD;
		}
	}
	
	/**
	 * Return Field as String
	 *
	 * Return Field formatted as String
	 *
	 * @return string Formatted as String
	 */
	function string() {
		return implode(" ", $this->fields);
	}
	
}

/**
 * PHP_MARC_Subfield class
 *
 * Represents a subfield within a MARC field. This class also implements the possibility of
 * duplicate subfields within a single field, i.e. 650 _z Test1 _z Test2 for example. This class
 * also implements all management functions related to a single subfield.
 */
Class PHP_MARC_Subfield {
	/**
	 * Tag name of subfield, e.g. _a, _b
	 * @var string
	 */
	var $tag;
	
	/**
	 * Data array for all possible values
	 * @var array
	 */
	var $data = array();
	
	/**
	 * PHP_MARC_Subfield constructor
	 *
	 * Create a new Subfield
	 *
	 * @param string Subfield number
	 * @param string|array Either a single value for this subfield tag, or an array of values
	 */
	function php_marc_subfield() {
		$args = func_get_args();
		
		$this->tag = array_shift($args);
		
		$data = array_shift($args);		
		if(is_array($data)) {
			$this->data = $data;
		} else {
			$this->data[] = $data;
		}
	}
	
	/**
	 * Return tag name of subfield
	 *
	 * @return string Tag name
	 */
	function tag() {
		return $this->tag;
	}
	
	/**
	 * Return first value
	 *
	 * @return string First possible value
	 */
	function value() {
		if(count($this->data)) {
			return $this->data[0];
		} else {
			return false;
		}
	}
	
	/**
	 * Return all possbile values
	 *
	 * @return array Values
	 */
	function values() {
		return $this->data;
	}
	
	/**
	 * Append a value to the possible values
	 *
	 * @param string Value to append
	 */
	function append($data) {
		$this->data[] = $data;
	}
	
	/**
	 * Return string representation of field
	 *
	 * @return string String representation
	 */
	function string() {
	}
	
	/**
	 * Return the USMARC representation of the field
	 *
	 * @return string USMARC representation
	 */
	function raw() {
		$result = array();
		foreach($this->data as $data) {
			$result[] = SUBFIELD_INDICATOR.$this->tag.$data;
		}
		return implode("", $result);
	}
	
	/**
	 * Is empty
	 *
	 * Checks wether the subfield is empty or not
	 *
	 * @return bool True or false
	 */
	function is_empty() {
		foreach($this->data as $code => $value) {
			// There is a value
			if($value) {
				return false;
			}
		}
		
		// Its empty
		return true;
	}
}

/**
 * PHP_MARC_YAZ class
 *
 * This class is an extension of the PHP_MARC class specially designed to import its data from
 * the PHP/YAZ framework. As of version 1.0.3 of PHP/YAZ, it contains a special indexed data array,
 * named 'array3' which PHP_MARC_YAZ uses to read its data in. Thus, 'array3' needs to be present in
 * your PHP/YAZ installation if you want to use this class.
 */
Class PHP_MARC_YAZ extends PHP_MARC {
	/**
	 * PHP_MARC_YAZ constructor function
	 *
	 * Creates a PHP_MARC object from data retrieved from PHP/YAZ. You can either pass
	 * an 'array3' array as argument, alternatively a YAZ connection ID and a request position.
	 * @param Array PHP/YAZ 'array3' array
	 * @param int YAZ connection ID
	 * @param int Request position
	 */
	function php_marc_yaz() {
		$args = func_get_args();
		
		if(!empty($args)) {
			if(is_array($args[0])) {
				return $this->_parse($args[0]);
			} else {
				return $this->_parse(yaz_record($args[0], $args[1], "array3"));
			}
		}
	}
	
	/**
	 * Internal parsing function
	 *
	 * Does the parsing of the 'array3' array. Issued only internally.
	 */
	function _parse($array) {
		// Store leader
		$this->ldr = $array['leader'][0];
		unset($array['leader']);
		
		// Do the rest
		foreach($array as $field_no => $data) {
			foreach($data as $occurance) {
				if($field_no < 10) {
					$field = new PHP_MARC_Field($field_no, $occurance);
				} else {
					$field = new PHP_MARC_Field($field_no, array_shift($occurance), array_shift($occurance), $occurance);
				}
				$this->append_fields($field);
			}
		}
	}
}

/**
 * PHP_MARC_Index class
 *
 * Creates an extremely fast Index of a PHP-MARC object designed only for data access,
 * not data manipulation. This class should be used when information from a record only
 * is displayed, not edited. Using the Benchmark tool from examples/benchmark.php you can
 * see that data access is approximately 100 times faster with this class than with any other
 * data conversion method available in PHP-MARC
 *
 * To be able to modify data from a PHP_MARC_Index class, issue the convert() function which will
 * return a complete PHP-MARC class from the PHP_MARC_index class.
 */
Class PHP_MARC_Index {
	/**
	 * Index array. Containes entire index
	 * @var array
	 */
	var $array;
	
	/**
	 * PHP_MARC_Index constructor function
	 *
	 * Give either an 'array3' array retrieved from PHP/YAZ, alternatively
	 * the YAZ connection id and the position.
	 * @param Array PHP/YAZ 'array3' array
	 * @param int PHP/YAZ connection ID
	 * @param int Position in reqest
	 */
	function php_marc_index() {
		$args = func_get_args();
		
		if(!empty($args)) {
			if(is_array($args[0])) {
				$this->array = $args[0];
			} else {
				$this->array = yaz_record($args[0], $args[1], "array3");
			}
		}
	}
	
	/**
	 * Get Field array
	 *
	 * In contrast to PHP-MARC, this function returns an array of key-value-pairs
	 * of data, where keys are 'ind1', 'ind2' or any subfield name.
	 * @param int Field number
	 * @return array|false Array of field data if match, otherwise false
	 */
	function field($field_no) {
		if(array_key_exists($field_no, $this->array)) {
			return $this->array[$field_no][0];
		} else {
			return false;
		}
	}
	
	/**
	 * Return a specific subfield of a field
	 *
	 * Returns the string representation of a specific subfield if exists
	 * @param int Field number
	 * @param string Subfield number
	 * @return String|false Value of subfield if exists
	 */
	function subfield($field_no, $subfield) {
		if($field = $this->field($field_no)) {
			if(array_key_exists($subfield, $field)) {
				return $field[$subfield];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Formatted representation of a field
	 *
	 * Format a Field with a sprintf()-like formatting syntax. The formatting
	 * codes are the names of the subfields of the Field.
	 * @param string Field name
	 * @param string Format string
	 * @return string|false Return formatted string if Field exists, otherwise False
	 */
	function ffield($tag, $format) {
		$result = "";
		if($field = $this->field($tag)) {
			for ($i=0; $i<strlen($format); $i++) {
				$curr = $format[$i];
				if($curr != "%") {
					$result[] = $curr;
				} else {
					$i++;
					$curr = $format[$i];
					if($curr == "%") {
						$result[] = $curr;
					} else {
						$result[] = $this->subfield($tag, $curr);
					}
				}
			}
			return implode("", $result);
		} else {
			return false;
		}
	}
	
	/**
	 * Convert into PHP_MARC class
	 *
	 * Convert the current PHP_MARC_Index class into a @link PHP_MARC class. If you
	 * want to edit the data in this class, you must issue this command to be able to edit
	 * the data.
	 * @return PHP_MARC PHP_MARC conversion of current class
	 */
	function convert() {
		return new PHP_MARC_YAZ($this->array);
	}
}

?>