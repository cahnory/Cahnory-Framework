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
	class Helper_Persist
	{
		private	$system;
		private	$_data		=	array();
		private	$_filename	=	'Persist';

		public	function	__construct($system)
		{
			$this->system = $system;
			$this->_load();
			$this->system->bind('viewSent',		array($this, 'save'));
			$this->system->bind('redirection',	array($this, 'save'));
		}
		
		public	function	__get($name)
		{
			return	$this->get($name);
		}
		
		public	function	__set($name, $value)
		{
			return	$this->set($name, $value);
		}
		
		public	function	get($name)
		{
			return	array_key_exists($name, $this->_data)
				?	$this->_data[$name]
				:	NULL;
		}
		
		public	function	set($name, $value)
		{
			$this->_data[$name]	=	$value;
			$this->save();
		}
		
		private	function	_load()
		{
			if(is_file($this->_filename)) {
				$data	=	unserialize(file_get_contents($this->_filename));
				if(is_array($data)) {
					$this->_data	=	array_merge($data, $this->_data);
				}
			}
		}
		
		public	function	save()
		{
			$file = fopen($this->_filename, "w+");
			fwrite($file, serialize($this->_data));
			fclose($file);
		}
	}

?>