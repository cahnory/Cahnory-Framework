<?php

	class CahnoryORM_Database
	{
		private	$_access;
		private	$_tables	=	array();
		
		public	function	__get($name)
		{
			return	$this->getTable($name);
		}
		
		public	function	getAccess()
		{
			return	$this->_access;
		}
		
		public	function	getTable($name)
		{
			if(isset($this->_tables[$name])) {
				$record	=	clone($this->_tables[$name]);
				$record->define();
				return	$record;
			}
		}
		
		public	function	setAccess($config = array())
		{
			$this->_access	=	new CahnoryORM_Access($config);
		}
		
		public	function	setRecord($class)
		{
			if(array_key_exists('CahnoryORM_Record', class_parents($class))) {
				$model	=	new $class($this);
				$record	=	clone($model);
				$record->define();
				$this->_tables[$record->getName()]	=	$model;				
				return	$record;
			}
		}
	}

?>