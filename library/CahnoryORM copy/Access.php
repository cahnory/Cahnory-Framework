<?php

	class CahnoryORM_Access
	{
		private	$_db;
		private	$_cfg	=	array(
			'driver'	=>	'mysql',
			'host'		=>	'localhost',
			'port'		=>	NULL,
			'database'	=>	NULL,
			'user'		=>	NULL,
			'pass'		=>	NULL,
			'options'	=>	array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')
		);
		private	$_lastQuery;
		private	$_preparedQuerys	=	array();
		
		public	function	__construct($config = array())
		{
			$this->_cfg		=	array_merge($this->_cfg, $config);
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
			
			$this->_db	=	new PDO(
				$dsn,
				$this->_cfg['user'],
				$this->_cfg['pass'],
				$this->_cfg['options']
			);
			
			return	(bool)$this->_db;
		}
		
		public	function query($query, $parameters	=	array())
		{
			//	Aucune connexion et connexion impossible
			if(!$this->_db && !$this->connect())	return false;
			
			//	Préparation de la requête
			$prepared	=	isset($this->_preparedQuerys[$query])
						?	$this->_preparedQuerys[$query]
						:	$this->prepare($query);
			
			//	Exécution de la requête
			$prepared->execute($parameters);
			$this->_lastQuery	=	$prepared;
			Cahnory::instance()->Debug->trace($query);
			
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
		
		public	function	fetch($style = PDO::FETCH_NUM)
		{
			if(!$this->_lastQuery)	return	false;
			if(func_num_args() > 1) {
				$args	=	func_get_args();
				return	call_user_func_array(array($this->_lastQuery, 'fetch'), $args);
			} else {
				return	$this->_lastQuery->fetch($style);
			}
		}
		
		public	function	fetchAll($style = PDO::FETCH_NUM)
		{
			if(!$this->_lastQuery)	return	false;
			if(func_num_args() > 1) {
				$args	=	func_get_args();
				return	call_user_func_array(array($this->_lastQuery, 'fetchAll'), $args);
			} else {
				return	$this->_lastQuery->fetchAll($style);
			}
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
			if(!$this->_db)	return false;
			return	$this->_db->lastInsertId();
		}
		
		public	function	prepare($query)
		{
			return	$this->_preparedQuerys[$query]	=	$this->_db->prepare($query);
		}
		
		public	function	rowCount()
		{
			if(!$this->_lastQuery)	return	false;
			return	$this->_lastQuery->rowCount();
		}
	}

?>