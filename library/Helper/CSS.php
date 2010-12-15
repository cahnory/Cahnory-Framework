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
	class Helper_CSS
	{
		private	$system;
		
		public	function	__construct(cahnory $system)
		{
			$this->system	=	$system;
		}
		
		static	public	function	absolutize($string, $path, $base)
		{
			// Bug à cause des ":"
			$path !== NULL && ($path = rtrim($path, '/')) !== '' ? $path .= '/' : NULL;
			$string	=	preg_replace('#((url|src)\([\s]*\'?"?)(?!\'|"|[^/:)]+:|\./|\#/|/)([^)]+\))#is', '$1'.$path.'$3', $string);
			$string	=	preg_replace('#((url|src)\([\s]*\'?"?)\./([^)]+\))#is', '$1'.$base.'$3', $string);
			$string	=	preg_replace('#((url|src)\([\s]*\'?"?)\\#/([^)]+\))#is', '$1'.'$3', $string);
			return	$string;
		}
		
		public	function	create()
		{
			return	new	Helper_CSS_Object($this->system);
		}

		static	public function pathLink($pathA, $pathB)
		{
			if($pathA == $pathB) return	NULL;
			
			//	Chemin complet (base commune)
			if(!$path1 = realpath($pathA)) {
				$path1	=	realpath('./').'/'.ltrim($pathA,'/');
			}
			if(!$path2 = realpath($pathB)) {
				$path2	=	realpath('./').'/'.ltrim($pathB,'/');
			}
			
			//	Dossier parent si fichier
			if(is_file($path2))	$path2	=	dirname($path2);
			if(is_file($path1))	$path1	=	dirname($path1);
			//	Séparation en dossier pour la comparaison
			$path2	= explode('/',trim($path2, '/'));
			$path1	= explode('/',trim($path1, '/'));
			
			$path	=	NULL;
			foreach($path2 as $key => $value)
			{
				//	Aucune séparation, second chemin plus court
				if(!isset($path1[$key])) {
					$path	=	implode('/', array_slice($path2, $key));
					$key	=	NULL;
					break;
				//	Séparation des chemins
				} else if($value != $path1[$key]) {
					$path	=	str_repeat('../', sizeof($path1) - $key)
							.	implode('/', array_slice($path2, $key));
					$key	=	NULL;
					break;
				}
			}
			//	Aucune séparation, second chemin plus long
			if($key !== NULL && $key < sizeof($path1) - 1) {
				$path	=	str_repeat('../', sizeof($path1) - $key - 1);
			}
			
			return	$path;
		}
	}
	
	class	Helper_CSS_Object
	{
		private	$system;
		private	$_filename;
		private	$_string;
		
		private	$_cache		=	true;
		private	$_minify	=	true;
		private	$_less		=	false;
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
		
		public	function	useLESS($bool = NULL)
		{
			if(func_num_args() > 0) {
				$this->_less	=	$bool;
			}
			return	$this->_less;
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
		
		/*public	function	**eval($bool = NULL)
		{
			if(func_num_args() > 0) {
				$this->_eval	=	$bool;
			}
			return	$this->_eval;
		}*/
		
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
					$part['string']	=	file_get_contents($part['filename']);
				}
				if($filename === NULL || $part['filename'] === NULL) {
					$string	.=	$part['string'];
				} else {
					$link	=	Helper_CSS::pathLink(dirname($filename), dirname($part['filename']));
					$base	=	Helper_CSS::pathLink(dirname($filename), './');
					$string	.=	Helper_CSS::absolutize($part['string'], $link, $base);
				}
			}
			//	Use less
			if($this->_less) {
				$less	=	new lessc();
				$string	=	$less->parse($string);
			}
			//	Minify
			if($this->_minify) {
				$string	=	CSSMin::minify($string);
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