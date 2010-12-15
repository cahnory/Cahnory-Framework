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
	class	Helper_Cookie
	{
		private	$_defined	=	array();
		private	$_cookies;
		private	$system;
		private	$_cfg	=	array(
			'key'		=>	NULL
		);
		
		public	function	__construct(cahnory $system, $config = array(), $cookie = NULL)
		{
			$this->_cfg		=	array_merge($this->_cfg, $config);
			$this->system	=	$system;
			
			//	Encryption key
			if($this->_cfg['key'] === NULL) {
				$this->_cfg['key']	=	md5($this->system->appPath());
			}
			
			//	Cookie var
			if($cookie === NULL) {
				if(get_magic_quotes_gpc()) {
					$this->_cookies	=	self::removeMagicQuotes($_COOKIE);
				} else {
					$this->_cookies	=	$_COOKIE;
				}
			} else {
				$this->_cookies	=	$cookie;
			}
		}
		
		public	function	set($name, array $config = array())
		{
			$this->_defined[$name]	=	new Helper_Cookie_Object(
				$name,
				(array_key_exists($name, $this->_cookies) ? $this->_cookies[$name] : NULL),
				array_merge($this->_cfg, $config)
			);
			return	$this->_defined[$name];
		}
		
		public	function	__get($name)
		{
			if(!isset($this->_defined[$name])) {
				$this->_defined[$name]	=	new Helper_Cookie_Object(
					$name,
					(array_key_exists($name, $this->_cookies) ? $this->_cookies[$name] : NULL),
					$this->_cfg
				);
			}
			return	$this->_defined[$name];
		}
		
		public	function	__set($name, $value)
		{
			if(!isset($this->_defined[$name])) {
				$this->_defined[$name]	=	new Helper_Cookie_Object(
					$name,
					(array_key_exists($name, $this->_cookies) ? $this->_cookies[$name] : NULL),
					$this->_cfg
				);
			}
			$this->_defined[$name]->value($value);
		}
		
		/*
			Retire les slashs de toute les valeurs d'un array récursivement
		*/
		static	private	function	removeMagicQuotes($array) {
			if(!is_array($array))	return	stripslashes($array);
			foreach($array as $key => $value) {
				$array[$key]	=	self::removeMagicQuotes($value);
			}
			return	$array;
		}
	}
	
	class	Helper_Cookie_Object
	{
		private	$_name;
		private	$_value;
		private	$_altered	=	false;
		
		private	$_cfg	=	array(
			'duration'	=>	0,
			'expire'	=>	NULL,
			'path'		=>	NULL,
			'domain'	=>	NULL,
			'secure'	=>	false,
			'httpOnly'	=>	false,
			
			'encrypt'	=>	false,
			'key'		=>	NULL,
			'cipher'	=>	MCRYPT_RIJNDAEL_256,
			'mode'		=>	MCRYPT_MODE_CBC,
			'rand'		=>	MCRYPT_RAND			
		);
		
		public	function	__construct($name, $value = NULL, array $cfg = array())
		{
			$this->_name	=	$name;
			$this->_value	=	$value;
			$this->_cfg		=	array_merge($this->_cfg, $cfg);
		}
		
		private	function	_decrypt($value)
		{
			return	trim(mcrypt_decrypt($this->_cfg['cipher'], $this->_cfg['key'], base64_decode($value), $this->_cfg['mode'], mcrypt_create_iv(mcrypt_get_iv_size($this->_cfg['cipher'], $this->_cfg['mode']), MCRYPT_RAND)));
		}
		
		private	function	_encrypt($value)
		{
			return	trim(base64_encode(mcrypt_encrypt($this->_cfg['cipher'], $this->_cfg['key'], $value, $this->_cfg['mode'], mcrypt_create_iv(mcrypt_get_iv_size($this->_cfg['cipher'], $this->_cfg['mode']), MCRYPT_RAND))));
		}
		
		public	function	encrypt($cipher = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC, $rand = MCRYPT_RAND)
		{
			if($cipher === false) {
				$this->_cfg['encrypt']	=	false;
			} else {
				$this->_cfg['encrypt']	=	true;
				$this->_cfg['cipher']	=	$cipher;
				$this->_cfg['mode']		=	$mode;
				$this->_cfg['rand']		=	$rand;
			}
		}
		
		public	function	duration($length, $unit = 'day')
		{
			switch($unit) {
				case 'minute':
					$length *= 60;
					break;
				case 'hour':
					$length *= 3600;
					break;
				case 'day':
					$length *= 86400;
					break;
				case 'week':
					$length *= 604800;
					break;
				case 'month':
					$length *= 2592000;
					break;
				case 'quarter':
					$length *= 7862400;
					break;
				case 'year':
					$length	*= 31536000;
					break;
			}
			$this->_cfg['expire']	=	NULL;
			$this->_cfg['duration']	=	$length;
		}
		
		public	function	save()
		{
			//	Value
			$value	=	$this->_value;
			if($this->_cfg['encrypt'] && $this->_altered) {
				$value	=	$this->_encrypt($value);
			}
			//	Expiration timestamp
			if($this->_cfg['expire'] === NULL) {
				if($this->_cfg['duration'] == 0) {
					$expire	=	0;
				} else {
					$expire	=	time() + $this->_cfg['duration'];
				}
			} else {
				$expire	=	$this->_cfg['expire'];
			}
			//	Cookie setting
			$res	=	setcookie(
				$this->_name,
				$value,
				$expire,
				$this->_cfg['path'],
				$this->_cfg['domain'],
				$this->_cfg['secure'],
				$this->_cfg['httpOnly']
			);
		}
		
		public	function	value($value = NULL)
		{
			if(func_num_args() > 0) {
				$this->_value	=	$value;
				$this->_altered	=	true;
				$this->save();
			} elseif($this->_cfg['encrypt'] && !$this->_altered) {
				$this->_value	=	$this->_decrypt($value);
			}
			return	$this->_value;
		}
	}

?>