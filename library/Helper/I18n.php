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
	class Helper_I18n
	{
		private	$system;
		private	$_parent;
		private	$_name;
		private	$_languages		=	array();
		private	$_dictionary	=	array();
		private	$_nameSpaces	=	array();

		public	function	__construct($system)
		{
			$this->system = $system;
			$this->system->load->help('I18n', array($this, 'load'));
		}
		
		public	function	__get($name)
		{
			if(!isset($this->_nameSpaces[$name])) {
				$this->_nameSpaces[$name]				=	new Helper_I18n($this->system);
				$this->_nameSpaces[$name]->_parent		=	$this;
				$this->_nameSpaces[$name]->_name		=	$name;
				$this->_nameSpaces[$name]->_languages	=&	$this->_languages;
			}
			return	$this->_nameSpaces[$name];
		}
		
		public	function	__toString()
		{
			if($this->_parent !== NULL) {
				return	(string)$this->_parent->get($this->_name);
			}
			return	'';
		}
		
		public	function	main($language)
		{
			$languages	=	func_get_args();
			$i			=	0;
			foreach($languages as $language) {
				if(($language = $this->_getLanguage($language)) !== NULL) {
					array_splice($this->_languages, array_search($language, $this->_languages), 1);
					array_splice($this->_languages, $i, 0, $language);
					$i++;
				}
			}
			return	$i > 0;
		}
		
		public	function	set($language, $name, $value)
		{
			if(($language = $this->_getLanguage($language)) !== false) {
				if(!isset($this->_dictionary[$language])) {
					$this->_dictionary[$language]	=	array();
				}
				$this->_dictionary[$language][$name]	=	$value;
				return	true;
			}
			return	false;
		}
		
		public	function	get($name, $language = NULL)
		{
			if($language !== NULL) {
				if(($language = $this->_getLanguage($language)) !== false) {
					if(isset($this->_dictionary[$language]) && array_key_exists($name, $this->_dictionary[$language])) {
						return	$this->_dictionary[$language][$name];
					}
				}
			} else {
				foreach($this->_languages as $language) {
					if(isset($this->_dictionary[$language]) && array_key_exists($name, $this->_dictionary[$language])) {
						return	$this->_dictionary[$language][$name];
					}
				}
			}
		}
		
		public	function	load($name)
		{
			$files	=	$this->getFile($name);
			if(is_array($files)) {
				foreach($files as $file) {
					include	$file;
				}
			} elseif($name !== NULL) {
				include $files;
			} else {
				return	false;
			}
			return	true;
		}
		
		public	function	getFile($filename)
		{
			$filename	.=	'.php';
			if(strstr($filename, '<module>')) {
				$files	=	array(
					'I18n/'.$filename,
					'I18n/'.str_replace('<module>', 'module.'.$this->system->module->name(), $filename),
					'I18n/'.str_replace('<module>', 'module.'.$this->system->module->route(), $filename)
				);
			} elseif(strstr($filename, '<widget>')) {
				$files	=	array(
					'I18n/'.$filename,
					'I18n/'.str_replace('<widget>', $this->system->Widget->name(), $filename),
					'I18n/'.str_replace('<widget>', 'widget.'.$this->system->Widget->name(), $filename)
				);
			} else {
				$files	=	'I18n/'.$filename;
			}
			$files = $this->system->load->getFile($files);
			return	$files;
		}
		
		private	function	_getLanguage($language)
		{
			$language	=	strtolower($language);
			if(!in_array($language, $this->_languages)) {
				if($language === NULL) {
					$language	=	sizeof($this->_languages)
								?	$this->_languages[0]
								:	false;
				} else {
					$this->_languages[]				=	$language;
				}
			}
			return	$language;
		}
	}

?>