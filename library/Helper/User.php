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
	class Helper_User
	{
		private	$system;
		private	$_config	=	array(
			'sessionOffset'	=>	'helper_user-user',
			'persistOffset'	=>	'helper_user-user',
			'primaryKey'	=>	'id'
		);
		
		private	$_persist;
		private	$_token;
		private	$_data		=	array();
		private	$_logged	=	false;
		private	$_level		=	0;
		private	$_access	=	array();

		public	function	__construct($system, $config)
		{
			$this->system 	=	$system;
			
			$this->_persist	=	$this->system->Persist->get($this->_config['persistOffset']);
			if(!isset($this->_persist['logged']))	$this->_persist['logged']	=	array();
			
			$this->system->bind('viewSent',		array($this, 'save'));
			$this->system->bind('redirection',	array($this, 'save'));
			
			$this->_load();
		}
		
		public	function	__get($name)
		{
			return	array_key_exists($name, $this->_data) ? $this->_data[$name] : NULL;
		}
		
		public	function	__set($name, $value)
		{
			$this->_data[$name]	=	$value;
		}
		
		private	function	_load()
		{
			/*if(!$this->_token = $this->system->Cookie->token->value()) {
				$this->_token					=	md5(uniqid(rand(), true));
				$this->system->Cookie->token	=	$this->_token;
				$this->system->Cookie->token->encrypt();
			}*/
			if(isset($_SESSION[$this->_config['sessionOffset']])) { //&& isset($_SESSION[$this->_config['sessionOffset']]['token'])) {
				$save			=	$_SESSION[$this->_config['sessionOffset']];
				$this->_data	=	array_key_exists('data', $save)   ? $save['data']   : array();
				$this->_level	=	array_key_exists('level', $save)  ? $save['level']  : 0;
				$this->_logged	=	array_key_exists('logged', $save) ? $save['logged'] : false;
				$this->_access	=	array_key_exists('access', $save) ? $save['access'] : array();
			} else {
				$this->_token	=	md5(uniqid(rand(), true));
			}
		}
		
		public	function	clear()
		{
			$this->_data	=	array();
			$this->_logged	=	false;
			$this->_level	=	0;
			$this->_access	=	array();
		}
		
		public	function	data($name, $value = NULL)
		{
			if(is_array($name)) {
				$this->_data	=	array_merge($this->_data, $name);
			} else {
				$this->_data[$name]	=	$value;
			}
		}
		
		public	function	logged($logged = false)
		{
			if(func_num_args()) {
				$this->_logged	=	(boolean)$logged;
				
				//	Group logged user sessions
				$key	=	$this->__get($this->_config['primaryKey']);
				$id		=	session_id();
				if($this->_logged) {
					if(!isset($this->_persist['logged'][$key])) {
						$this->_persist['logged'][$key]	=	array($id);
					} elseif(!in_array($id, $this->_persist['logged'][$key])) {
						$this->_persist['logged'][$key][]	=	$id;
					}
				} elseif(isset($this->_persist['logged'][$key])&& $offset = array_search($id, $this->_persist['logged'][$key])) {
					unset($this->_persist['logged'][$key][$offset]);
				}
			}
			return	$this->_logged;
		}
		
		public	function	hasAccess($access, $auth = true)
		{
			return	in_array($access, $this->_access);
		}
		
		public	function	setAccess($access, $auth = true)
		{
			if(!in_array($access, $this->_access)) {
				if($auth) {
					$this->_access[]	=	$access;
				}
			} elseif(!$auth) {
				unset($this->_access[array_search($access, $this->_access)]);
			}
		}
		
		public	function	save()
		{
			$this->system->Persist->set($this->_config['persistOffset'], $this->_persist);
			$_SESSION[$this->_config['sessionOffset']]	=	array(
				//'token'		=>	$this->_token,
				'data'		=>	$this->_data,
				'level'		=>	$this->_level,
				'logged'	=>	$this->_logged,
				'access'	=>	$this->_access
			);	
		}
	}

?>