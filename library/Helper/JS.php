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
	class Helper_JS
	{
		private	$system;
		
		public	function	__construct(cahnory $system)
		{
			$this->system	=	$system;
		}
		
		public	function	create()
		{
			return	new	Helper_JS_Object($this->system);
		}
	}
	
	class	Helper_JS_Object
	{
		private	$system;
		private	$_filename;
		private	$_string;
		
		private	$_cache		=	true;
		private	$_minify	=	true;
		private	$_fork		=	false;
		
		private	$_parts	=	array();
		
		public	function	__construct(cahnory $system)
		{
			$this->system	=	$system;
		}
		
		private	function	_addPart($string, $filename)
		{
			$this->_parts[]	=	array(
				'string'	=>	$string,
				'filename'	=>	$filename
			);
			$this->_string	=	NULL;
		}
		
		public	function	cache($bool = NULL)
		{
			if(func_num_args() > 0) {
				$this->_cache	=	$bool;
			}
			return	$this->_cache;
		}
		
		public	function	fork($bool = NULL)
		{
			if(func_num_args() > 0) {
				$this->_fork	=	$bool;
			}
			return	$this->_fork;
		}
		
		public	function	minify($bool = NULL)
		{
			if(func_num_args() > 0) {
				$this->_minify	=	$bool;
			}
			return	$this->_minify;
		}
		
		public	function	addFile($filename)
		{
			if(is_file($file = $this->system->load->getFile($filename))) {
				$this->_addPart(NULL, $file);
				return	true;
			} else {
				if($string = file_get_contents($filename)) {
					$this->_addPart($string, $filename);
					return	true;
				}
			}
			return	false;
		}
		
		public	function	addString($string)
		{
			$this->_addPart($string, NULL);
			return	$this;
		}
		
		public	function	getString($filename = NULL)
		{
			if($this->_string !== NULL && $this->_filename === $filename) {
				return	$this->_string;
			}
			
			$string	=	NULL;
			foreach($this->_parts as $part) {	
				//	String or file
				if($part['string'] === NULL && $part['filename'] !== NULL) {
					$string	.=	$part['string']	=	file_get_contents($part['filename']);
				} else {
					$string	.=	$part['string'];
				}
			}
			//	Minify
			if($this->_minify) {
				$string	=	JSMin::minify($string);
			}
			$this->_filename	=	$filename;
			$this->_string		=	$string;
			return	$string;
		}
		
		public	function	save($filename)
		{
			if($this->_fork) {
				if(preg_match('#^(.+)(?<!/)\.([^./]+)$#', $filename, $match)) {
					$filename	=	$match[1].'-'.md5(serialize($this->_parts)).'.'.$match[2];
				} else {
					$filename	=	$filename.'-'.md5(serialize($this->_parts));
				}
			}
			if($this->_cache && is_file($filename)) {
				$res	=	true;
			} else {
				$string	=	$this->getString($filename);
				$file	=	fopen($filename, "w+");
				$res	=	fwrite($file, $string);
				fclose($file);
			}
			return $res ? $filename : false;
		}	
	}

?>