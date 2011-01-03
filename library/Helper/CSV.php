<?php

	/**
	 * LICENSE: Copyright (c) 2010 François 'cahnory' Germain
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 * 
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 * 
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 * that is available through the world-wide-web at the following URI:
	 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
	 * the PHP License and are unable to obtain it through the web, please
	 * send a note to license@php.net so we can mail you a copy immediately.
	 *
	 * @package    Cahnory
	 * @subpackage Library
	 * @category   Helper
	 * @author     François 'cahnory' Germain <cahnory@gmail.com>
	 * @copyright  2010 François Germain
	 * @license    http://www.opensource.org/licenses/mit-license.php
	 */
	class Helper_CSV
	{
		public	function	load($filename, $delimiter = ';', $enclosure = '"', $escape = '\\', $length = 0)
		{
			return	Helper_CSV_Object::createFromFile($filename, $delimiter, $enclosure, $escape, $length);
		}
		public	function	create($string, $delimiter = ';', $enclosure = '"', $escape = '\\')
		{
			return	Helper_CSV_Object::createFromString($string, $delimiter, $enclosure, $escape);
		}
	}
	
	/*
	 * @package    Cahnory
	 * @subpackage Library
	 * @category   Helper
	 * @author     François 'cahnory' Germain <cahnory@gmail.com>
	 * @copyright  2010 François Germain
	 * @license    http://www.opensource.org/licenses/mit-license.php
	 */
	class Helper_CSV_Object
	{
		private	$_resource;
		private	$_delimiter;
		private	$_enclosure;
		private	$_escape; //PHP 5 only
		
		//	Fget
		private	$_length;
		private	$_filename;
		
		private	$_array;
		private	$_closed;
		
		private	function	__construct($resource, $delimiter, $enclosure, $escape)
		{
			$this->_resource	=	$resource;
			$this->_delimiter	=	$delimiter;
			$this->_enclosure	=	$enclosure;
			$this->_escape		=	$escape;
		}
		
		public	function	addCol($value, $col)
		{
			$col	=	(int)$col;
			for($i = 0; $row = $this->getRow($i); $i++) {
				if(is_array($value)) {
					$cell	=	array_key_exists($i, $value) ? $value[$i] : '';
				} else {
					$cell	=	$value;
				}
				if($col >= sizeof($row)) {
					$row		=	array_pad($row, $col, NULL);
					$row[$col]	=	$cell;
				} else {
					array_splice($row, $col, 0, $cell);
				}
				$this->setRow($i, $row);
			}
		}
		
		public	function	addRow($value, $row = NULL)
		{
			if($row === NULL) {
				//	Chargement des lignes non utilisées
				if(!$this->_closed)	$this->getRow(-1);
				$this->_array[]	=	(array)$value;
			} else {
				if($this->getRow($row) === false) {
					$this->_array		=	array_pad($this->_array, $row, array(NULL));
					$this->_array[$row]	=	(array)$value;
				} else {
					array_splice($this->_array, $row, 0, (array)$value);
				}
			}
		}
		
		static	public	function	createFromFile($filename, $delimiter = ';', $enclosure = '"', $escape = '\\', $length = 0)
		{
			//	Le fichier est introuvable
			if(!is_file($filename)) return	false;
			
			//	Création de l'objet
			$csv	=	new Helper_CSV_Object(fopen($filename, 'r'), $delimiter, $enclosure, $escape);
			
			//	Paramètres spécifiques à la lecture de fichier
			$csv->_length	=	$length;			
			$csv->_filename	=	$filename;
			
			return	$csv;
		}
		
		static	public	function	createFromString($string, $delimiter = ';', $enclosure = '"', $escape = '\\')
		{
			//	Création de l'objet
			$csv	=	new Helper_CSV_Object($string, $delimiter, $enclosure, $escape);
			return	$csv;
		}
		
		public	function	getArray()
		{
			//	Chargement des lignes non utilisées
			if(!$this->_closed)	$this->getRow(-1);
			return	$this->_array;
		}
		
		public	function	getCell($row, $col)
		{
			if(($row = $this->getRow($row)) === false)	return	false;
			return	array_key_exists($col, $row) ? $row[$col] : false;
		}
		
		public	function	getCol($col, $start = 0, $length = -1)
		{
			$output	=	array();
			$end	=	$start + $length;
			while($start != $end) {
				if(($row = $this->getRow($start)) !== false) {
					$output[]	=	array_key_exists($col, $row) ? $row[$col] : NULL;
					$start++;
				} else {
					break;
				}
			}
			return	$output;
		}
		
		public	function	getRow($row)
		{
			//	Unknown row
			if($row >= sizeof($this->_array) || $row < 0) {
				if($this->_closed)	return	false;
				//	Read from file
				if($this->_filename !== NULL) {
					for($i = sizeof($this->_array); $i <= $row || $row < 0; $i++) {
						if($value = fgetcsv($this->_resource, $this->_length, $this->_delimiter, $this->_enclosure)) {
							$this->_array[]	=	$value;
						} else {
							$this->_closed	=	true;
							return	false;
						}
					}
				
				//	Read from string
				} else {
					for($i = sizeof($this->_array); $i <= $row || $row < 0; $i++) {
						if($value = str_getcsv($this->_resource, $this->_delimiter, $this->_enclosure)) {
							$this->_array[]	=	$value;
						} else {
							$this->_closed	=	true;
							return	false;
						}
					}
				}
			}
			return	$this->_array[$row];
		}
		
		public	function	save($filename = NULL)
		{
			if($filename === NULL)	$filename	=	$this->_filename;
			if($filename === NULL)	return	false;
			
			//	Chargement des lignes non utilisées
			if(!$this->_closed)	$this->getRow(-1);
			
			//	Remplissage du fichier
			$handle	=	fopen($filename, 'w+');
			foreach($this->_array as $key => $row) {
				if(fputcsv($handle, $row, $this->_delimiter, $this->_enclosure) === false)	return false;
			}
			fclose($handle);
			return	true;
		}
		
		public	function	setCell($row, $col, $value)
		{
			if($this->getRow($row) === false) {
				$this->_array	=	array_pad($this->_array, $n, array(NULL));
			}
			if(!array_key_exists($col, $this->_array[$row])) {
				$this->_array	=	array_pad($this->_array[$row], $col, NULL);
			}
			$this->_array[$row][$col]	=	$value;
		}
		
		public	function	setCol($col, $value, $start = 0, $length = -1)
		{			
			$end	=	$start + $length;
			while($start != $end) {
				if(($cell = $this->getRow($start)) !== false) {
					$this->_array[$start][$col]	=	$value;
					$start++;
				} elseif($length > 0) {
					$this->_array[$start]		=	array_pad(array(), $col, NULL);
					$this->_array[$start][$col]	=	$value;
 				} else {
					break;
				}
			}
		}
		
		public	function	setRow($row, $value)
		{
			if($this->getRow($row) === false) {
				$this->_array	=	array_pad($this->_array, $row, array(NULL));
			}
			$this->_array[$row]	=	(array)$value;
		}
		
		public	function	toString()
		{
			$string		=	NULL;
			$pattern	=	'#['.preg_quote($this->_enclosure.$this->_escape, '#').']#';
			for($i = 0; $row = $this->getRow($i); $i++) {
				if($i !== 0)	$string	.=	"\r\n";
				foreach($row as $k => $col) {
					if($k !== 0)	$string	.=	$this->_delimiter;
					$string	.=	$this->_enclosure.preg_replace($pattern, $this->_escape.'$0', $col).$this->_enclosure;
				}
			}
			return	$string;
		}
	}

?>