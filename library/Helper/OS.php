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
	class Helper_OS
	{
		private	$_name;
		private	$_version;
		
		public	function	name()
		{
			if($this->_name === NULL)
				$this->_name	=	self::getNameFromUA($_SERVER['HTTP_USER_AGENT']);
			return	$this->_name;
		}
		
		public	function	version()
		{
			if($this->_version === NULL)
				$this->_version	=	self::getVersionFromUA($_SERVER['HTTP_USER_AGENT']);
			return	$this->_version;
		}
		
		static	public	function	getNameFromUA($ua)
		{
			if(strstr($ua, 'Windows'))
				return	'Windows';
			if(strstr($ua, 'Mac OS'))
				return	'Mac OS';
			if(strstr($ua, 'Ubuntu'))
				return	'Ubuntu';
			if(strstr($ua, 'gentoo'))
				return	'Gentoo';
			if(strstr($ua, 'Linux'))
				return	'Linux';
			if(strstr($ua, 'FreeBSD'))
				return	'FreeBSD';
			if(strstr($ua, 'BeOS'))
				return	'BeOS';
			if(strstr($ua, 'IRIX'))
				return	'Irix';
		}
		
		static	public	function	getVersionFromUA($ua)
		{
			if(strstr($ua, 'Windows')){
				if(strstr($ua, 'Windows NT 5.1'))
					return	'XP';
				if(strstr($ua, 'Windows NT 6.0'))
					return	'Vista';
				if(strstr($ua, 'Windows NT 6.1'))
					return	'7';
			}
			if(preg_match('#Mac OS X ([0-9_.]+)#', $ua, $m))
				return	str_replace('_', '.', $m[1]);
			if(strstr($ua, 'Mac OS X'))
				return	10;
			if(preg_match('#Mac OS ([0-9_.]+)#', $ua, $m))
				return	str_replace('_', '.', $m[1]);
		}
	}

?>