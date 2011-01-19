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
	class Helper_Browser
	{
		private	$_userAgent;
		private	$_name;
		private	$_engine;
		private	$_version;
		
		public	function	__construct($system, $options = array())
		{
			$this->_userAgent	=	isset($options['userAgent'])
								?	$options['userAgent']
								:	$_SERVER['HTTP_USER_AGENT'];
		}
		
		public	function	engine()
		{
			if($this->_engine === NULL)
				$this->_engine	=	self::getEngineFromUA($this->_userAgent);
			return	$this->_engine;
		}
		
		public	function	name()
		{
			if($this->_name === NULL)
				$this->_name	=	self::getNameFromUA($this->_userAgent);
			return	$this->_name;
		}
		
		public	function	version()
		{
			if($this->_version === NULL)
				$this->_version	=	self::getVersionFromUA($this->_userAgent);
			return	$this->_version;
		}
		
		static	public	function	getEngineFromUA($ua)
		{
			if(preg_match('#MSIE(?!.+Mac_PowerPC)#', $ua))
				return	'Trident';
			if(preg_match('#Firefox|Netscape#', $ua))
				return	'Gecko';
			if(preg_match('#Safari|Chrome#', $ua))
				return	'webCore';
			if(strstr($ua, 'Opera'))
				return	'Presto';
			if(strstr($ua, 'Konqueror'))
				return	'KHTML';
			if(preg_match('#MSIE.+Mac_PowerPC#', $ua))
				return	'Tasman';
		}
		
		static	public	function	getNameFromUA($ua)
		{
			if(strstr($ua, 'MSIE'))
				return	'Internet Explorer';
			if(strstr($ua, 'Firefox'))
				return	'Firefox';
			if(strstr($ua, 'Chrome'))
				return	'Chrome';
			if(strstr($ua, 'Safari'))
				return	'Safari';
			if(strstr($ua, 'Opera'))
				return	'Opera';
			if(strstr($ua, 'Konqueror'))
				return	'Konqueror';
			if(preg_match('#Netscape|Navigator/[0-9]+#', $ua))
				return	'Netscape';
		}
		
		static	public	function	getVersionFromUA($ua)
		{
			if(preg_match('#MSIE ([0-9.]+)#', $ua, $m))
				return	$m[1];
			if(preg_match('#(Firefox|Chrome|Opera|Konqueror|Netscape|Navigator)(/| )(?=[0-9])([^; ]+)#', $ua, $m))
				return	$m[3];
			if(strstr($ua, 'Safari')) {
				if(preg_match('#Version/([0-9.]+)#', $ua, $m))
					return	$m[1];
				if(preg_match('#Safari/([0-9.]+)#', $ua, $m)) {
					if($m[1] > 400)
						return	2;
					else
						return	1;
				}
			}
		}
	}

?>