<?php

	class CahnoryDB_Record
	{
		private	$_db;
		static	private	$_table;
		static	private	$_primary	=	'id';
		static	private	$_columns	=	array('id' => array('int', 11));
		
		private	$_selectString;
		private	$_insertString;
		
		private	$_values	=	array();
		private	$_saved		=	false;
		
		private	$_filters	=	array();
		private	$_params	=	array();
		
		final	public	function	__construct(CahnoryDB $db)
		{
			$this->_db				=	$db;
			$this->setDefinition();
			
			$inlineColumns			=	implode(',', array_keys($this->_columns));
			$this->_selectString	=	'SELECT '.$inlineColumns.' FROM '.$this->_table;
			$this->_insertString	=	'INSERT INTO '.$this->_table.'('.$inlineColumns.')'
									.	' VALUES('.str_repeat('?,', sizeof($this->_columns) - 1).'?)';
			
			$this->_values			=	array_combine(
											array_keys($this->_columns),
											array_pad(array(), sizeof($this->_columns), NULL));
		}
		
		public	function	__set($name, $value)
		{
			if(!isset($this->_columns[$name])) {
				$this->_values[$name]	=	$value;
			}
		}
		
		public	function	find($primary = NULL)
		{
			$query		=	$this->_selectString;
			$filter		=	implode(' OR ', $this->_filters);
			$params		=	$this->_params;
			if($primary !== NULL) {
				$filter	=	$filter
						?	'('.$filter.') AND '.$this->_primary.' = ?'
						:	$this->_primary.' = ?';
				array_unshift($params, $primary);
			}
			if($filter) {
				$query	.=	' WHERE '.$filter;
			}
			var_dump($query, $params);
		}
		
		protected	function	hasColumn($name, $type = NULL, $param = NULL)
		{
			self::$_columns[$name]	=	array($type, $param);
		}
		
		protected	function	hasOne($name, $alias = array(), $config = NULL)
		{
			if(!is_array($alias)) {
				$config	=	is_array($config) ? $config : array();
			} else {
				$config	=	$alias;
				$alias	=	NULL;
			}
		}
		
		public	function	save()
		{
			if(!$this->_saved) {
				$query	=	$this->_insertString;
				$params	=	$this->_values;
				var_dump($query, $params);
			}
		}
		
		public	function	setDefinition() {}
		
		protected	function	setPrimary($column)
		{
			self::$_primary	=	$column;
		}
		
		protected	function	setTable($name)
		{
			self::$_table	=	$name;
		}
	}

?>