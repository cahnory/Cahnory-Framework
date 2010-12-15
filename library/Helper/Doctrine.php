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
	class	Helper_Doctrine
	{
		private	$system;
		private	$_cfg	=	array(
			'driver'	=>	'mysql',
			'host'		=>	'localhost',
			'port'		=>	NULL,
			'database'	=>	NULL,
			'user'		=>	NULL,
			'pass'		=>	NULL,
			'options'	=>	array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')
		);
		private	$_pdo;
		private	$_db;
		
		public	function	__construct(cahnory $system, $config = array())
		{
			$this->_cfg		=	array_merge($this->_cfg, $config);
			$this->system	=	$system;
			spl_autoload_register(array('Doctrine', 'autoload'));
		}
		
		public	function	__call($name, $args)
		{
			if($this->_db)	return	call_user_func_array(array($this->_db, $name), $args);
		}
		
		public	function	__get($name)
		{
			if($this->_db)	return	$this->_db->$name;
		}
		
		public	function	connect()
		{
			//	Création du dsn
			$dsn	=	array_key_exists('dsn', $this->_cfg)
						//	DSN fourni
					?	$this->_cfg['dsn']
					:	(	array_key_exists('uri', $this->_cfg)
						//	URI fournie
						?	$this->getUriDSN($this->_cfg['uri'])
						//	DSN par paramètres
						:	$this->getDriverDSN($this->_cfg['driver'], $this->_cfg['database'], $this->_cfg['host'], $this->_cfg['port'])
					);
			
			$this->_pdo	=	new PDO(
				$dsn,
				$this->_cfg['user'],
				$this->_cfg['pass'],
				$this->_cfg['options']
			);
			
			$this->_db	=	Doctrine_Manager::connection($this->_pdo);
			
			return	$this->_db;
		}
		
		public	function	db()
		{
			return	$this->_db;
		}
		
		public	function	getDriverDSN($driver, $db, $host = 'localhost', $port = NULL)
		{
			return	$driver.':dbname='.$db.';host='.$host
					.($port === NULL ? NULL : ';port='.$port);
		}
		
		public	function	getUriDSN($uri)
		{
			return	'uri:'.$uri;
		}
	}

?>