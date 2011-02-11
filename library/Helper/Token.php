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
	class Helper_Token
	{
		private	$system;
		private	$_token;
		private	$_date;
		private	$_duration;
		private	$_config	=	array(
			'offsetPrefix'	=>	'helper_token',
			'offsetName'	=>	NULL,
			'duration'		=>	900
		);

		public	function	__construct($system, $config = array())
		{			
			if(!is_array($config))
				$config		=	array('offsetName' => $config);
								
			$this->_config	=	array_merge($this->_config, $config);
			$this->system	= $system;			
			$this->system->Session;	//	Session helper
			
			$this->_duration	=	$this->_config['duration'];
			
			//	Select session var
			$sessionOffset	=	$this->_config['offsetPrefix'].($this->_config['offsetName'] !== NULL
									?	'-'.$this->_config['offsetName']
									:	NULL);	
									
			//	Get vars from session
			if(!array_key_exists($sessionOffset, $_SESSION) || !is_array($_SESSION[$sessionOffset])) {
				$_SESSION[$sessionOffset]	=	array();
			} else {
				$this->_token		=	array_key_exists('token', $_SESSION[$sessionOffset])
									?	$_SESSION[$sessionOffset]['token'] : NULL;
				$this->_date		=	array_key_exists('date', $_SESSION[$sessionOffset])
									?	$_SESSION[$sessionOffset]['date'] : 0;
				$this->_duration	=	array_key_exists('duration', $_SESSION[$sessionOffset])
									?	$_SESSION[$sessionOffset]['duration'] : $this->_duration;
			}
			
			//	Put vars in session
			$_SESSION[$sessionOffset]	=	array(
				'token'		=>	&$this->_token,
				'date'		=>	&$this->_date,
				'duration'	=>	&$this->_duration
			);
		}
		
		public	function	create($config = array()) {
			return	new Helper_Token($this->system, $config);
		}
		
		/**
		 *	Set token duration
		 *	
		 *	You could check for expiration state at another
		 *	date than now using the $time argument.
		 *
		 *	@param int    $time time to check in seconds
		 *	@param string $unit the time unit (h:hours,m:minutes,s:seconds)
		 *
		 *	@return	boolean if token expired
		 */
		public	function	duration($time = 15, $unit = 'm')
		{
			if($time !== NULL) {
				if($unit == 'h') {
					$this->_duration	=	$time * 3600;
				} elseif($unit == 'm') {
					$this->_duration	=	$time * 60;
				} elseif($unit == 's') {
					$this->_duration	=	$time;
				}
			}
		}
		
		/**
		 *	Check if token is expired
		 *	
		 *	You could check for expiration state at another
		 *	date than now using the $time argument.
		 *
		 *	@param int    $time  time to check in seconds
		 *
		 *	@return	boolean if token expired
		 */
		public	function	expired($time = NULL)
		{
			if($time === NULL)
				$time	=	time();
				
			return	$this->expires() < $time;
		}
		
		/**
		 *	Return expiration date
		 *
		 *	@return	int expiration date in seconds
		 */
		public	function	expires()
		{
			return	$this->_date + $this->_duration;
		}
		
		/**
		 *	Check if input token match current token.
		 *
		 *	@param string $token the token to compare
		 *
		 *	@return	boolean if token match
		 */
		public	function	match($token)
		{
			return	$this->_token === $token;
		}
		
		/**
		 *	Return the token string and make new one if expired
		 *
		 *	@return	string the token string
		 */
		public	function	token()
		{
			if($this->_date + $this->_duration < time()) {
				$this->_token	=	uniqid(md5(rand()), true);
				$this->_date	=	time();
			}
			return	$this->_token;
		}
		
		/**
		 *	Check if input token match current token and if
		 *	current token isn't expired.
		 *
		 *	@param string $token the token to compare
		 *	@param int    $time  time to check in seconds
		 *
		 *	@return	boolean if token match and isn't expired
		 */
		public	function	valid($token, $time = NULL)
		{
			return	$this->match($token) && !$this->expired($time);
		}
	}

?>