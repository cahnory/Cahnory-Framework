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
	class	Helper_PDO
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
		private	$_connexion;
		private	$_lastQuery;
		private	$_preparedQuerys	=	array();
		
		public	function	__construct(cahnory $system, $config = array())
		{
			$this->_cfg		=	array_merge($this->_cfg, $config);
			$this->system	=	$system;
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
			
			$this->_connexion	=	new PDO(
				$dsn,
				$this->_cfg['user'],
				$this->_cfg['pass'],
				$this->_cfg['options']
			);
			
			return	(bool)$this->_connexion;
		}
		
		public	function query($query, $parameters	=	array())
		{
			//	Aucune connexion et connexion impossible
			if(!$this->_connexion && !$this->connect())	return false;
			
			//	Préparation de la requête
			$prepared	=	isset($this->_preparedQuerys[$query])
						?	$this->_preparedQuerys[$query]
						:	$this->_connexion->prepare($query);
			
			//	Exécution de la requête
			$prepared->execute($parameters);
			$this->_lastQuery	=	$prepared;
			
			return	$this->_lastQuery;
		}
		
		public	function	columnCount()
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->columnCount();
		}
		
		public	function	errorCode()
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->errorCode();
		}
		
		public	function	errorInfo()
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->errorInfo();
		}
		
		public	function	fetch($style = PDO::FETCH_BOTH, $orientation = PDO::FETCH_ORI_NEXT, $offset = 0)
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->fetch($style, $orientation, $offset);
		}
		
		public	function	fetchAll($style = PDO::FETCH_BOTH, $index = 0, array $args = array())
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->fetch($style, $index, $args);
		}
		
		public	function	fetchColumn($number = 0)
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->fetchColumn($number);
		}
		
		public	function	fetchObject($class = 'stdClass', array $args = NULL)
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->fetchObject($class, $args);
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
		
		public	function	lastInsertId()
		{
			//	Aucune connexion
			if(!$this->_connexion)	return false;
			return	$this->_connexion->lastInsertId();
		}
		
		public	function	rowCount()
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->rowCount();
		}
	}

?>