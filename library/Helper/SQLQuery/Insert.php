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
	class	Helper_SQLQuery_Insert
	{
		private	$_into;
		private	$_fields;
		private	$_nbFields;
		
		private	$_values	=	array();
		
		public	function	__toString()
		{
			return	$this->getQueryString();
		}
		
		public	function	getQuery()
		{
			return	array(
				$this->getQueryString(),
				$this->getQueryParameters()
			);
		}
		
		public	function	getQueryString()
		{
			$query	=	'INSERT INTO '.$this->_into.
					($this->_nbFields
						?	' ('.$this->_fields.')'
						:	NULL
					);
			if($this->_nbFields && !empty($this->_values)) {
				$query	.=	' VALUES '.str_repeat('(?'.str_repeat(', ?', $this->_nbFields - 1).')', sizeof($this->_values));
			}
			return	$query;
		}
		
		public	function	getQueryParameters()
		{
			$parameters	=	array();
			foreach($this->_values as $value) {
				$parameters	=	array_merge($parameters, $value);
			}
			return	$parameters;
		}
		
		public	function	into($tables, $fields)
		{
			$this->_into		=	$tables;
			$this->_fields		=	$fields;
			$this->_nbFields	=	substr_count($this->_fields) + 1;
			return	$this;
		}
		
		public	function	values($values)
		{
			if(!is_array($values))	$values	=	func_get_args();
			$this->_values[]	=	$values;
			return	$this;
		}
	}

?>