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
	class Helper_Constant
	{
		private	$_name;
		private	$_parent;
		private	$_parentLinked;
		private	$_constants	=	array();

		public	function	__construct($system, $constants = array(), Helper_Constant $parent = NULL, $name = NULL)
		{
			$this->_parent	=	$parent;
			$this->_name	=	$name;
			if(is_array($constants) && !empty($constants)) {
				$this->_constants	=	$constants;
				$this->_linkParent();
			}
		}
		
		public	function	__get($name)
		{
			return	array_key_exists($name, $this->_constants)
				?	$this->_constants[$name]
				:	new Helper_Constant(NULL, array(), $this, $name);
		}
		
		public	function	__set($name, $value)
		{
			if(!array_key_exists($name, $this->_constants)) {
				$this->_constants[$name]	=	$value;
				$this->_linkParent();
			}
		}
		
		private	function	_linkParent()
		{
			if($this->_parent && $this->_parentLinked !== true) {
				$this->_parent->__set($this->_name, $this);
				$this->_parentLinked	=	true;
			}
		}
		
		public	function	set($name, $value)
		{
			if(!array_key_exists($name, $this->_constants)) {
				$this->_constants[$name]	=	$value;
				return	true;
			}
			return	false;
		}
		
		public	function	defined()
		{
			return	!empty($_constants);
		}
	}

?>