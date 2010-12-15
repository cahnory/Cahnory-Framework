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
	 * @category   Widget
	 * @author     François 'cahnory' Germain <cahnory@gmail.com>
	 * @copyright  2010 François Germain
	 * @license    http://www.opensource.org/licenses/mit-license.php
	 */
	class Widget_Menu_Item implements Iterator, ArrayAccess
	{
		private	$_items		=	array();
		private	$_offset	=	0;
		private	$_itemLabels	=	array();
		public	$link;
		public	$label;
		
		public	function	__get($name)
		{			
			$keys	=	array_keys($this->_itemLabels, $name);
			if(sizeof($keys) === 1) {
				return	$this->_items[$keys[0]];
			} else {
				$items	=	array();
				foreach($keys as $key) {
					$items[]	=	$this->_items[$key];
				}
				return	$items;
			}
		}
		
		public	function	__set($label, $link)
		{
			return	$this->addItem($link, $label);
		}
		
		public	function	addItem($link = NULL, $label = NULL)
		{
			$item					=	new Widget_Menu_Item;
			$item->link				=	$link;
			$item->label			=	$label;
			$this->_items[]			=	$item;
			$this->_itemLabels[]	=	&$item->label;
			return	$item;
		}
		
		public	function	length()
		{
			return	sizeof($this->_items);
		}

		function rewind()
		{
			$this->_offset	=	0;
		}
		
		function current()
		{
			return $this->_items[$this->_offset];
		}
		
		function key()
		{
			return $this->_offset;
		}
		
		function next()
		{
			$this->_offset++;
		}
		
		function valid()
		{
			return array_key_exists($this->_offset, $this->_items);
		}
		
		public function offsetSet($offset, $value)
		{
			return	$this->addItem($value, $offset);
		}
		
		public function offsetExists($offset)
		{
			return in_array($offset, $this->_itemLabels);
		}
		
		public function offsetUnset($offset)
		{
			$keys	=	array_keys($this->_itemLabels, $offset);
			foreach($keys as $key) {
				unset($this->_items[$key]);
				unset($this->_itemLabels[$key]);
			}
		}
		
		public function offsetGet($offset)
		{
			return $this->__get($offset);
		}
	}

?>