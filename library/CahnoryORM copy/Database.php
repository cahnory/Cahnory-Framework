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
				return	clone($this->_tables[$name]);
			}
		}
		
		public	function	setAccess($config = array())
		{
			$this->_access	=	new CahnoryORM_Access($config);
		}
		
		public	function	setTable($class, $alias = NULL)
		{
			if(array_key_exists('CahnoryORM_Record', class_parents($class)) || array_key_exists('CahnoryORM_Record2', class_parents($class))) {
				if($alias === NULL)	$alias	==	$class;
				$this->_tables[$alias]	=	new $class($this, $alias, $class);
			}
		}
	}

?>