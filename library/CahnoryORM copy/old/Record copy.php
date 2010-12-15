<?php

	abstract class CahnoryDB_Record
	{
		private	$_db;
		private	$_table;
		private	$_alias;
		private	$_primary	=	'id';
		private	$_columns	=	array('id' => array('int', 11));
		private	$_relations	=	array();
		
		private	$_columnsNames			=	array();
		private	$_columnsTableSplits	=	array();
		
		private	$_SQLColumns;
		private	$_SQLRelations;
		private	$_SQLRelationsColumns;
		private	$_SQLUpdates;
		private	$_SQLValues;
		
		private	$_defaultValues	=	array();
		private	$_publicValues	=	array();
		private	$_values		=	array();
		
		private	$_saved		=	false;
		
		private	$_filters	=	array();
		private	$_params	=	array();
		
		final	public	function	__construct(CahnoryDB $db)
		{
			$this->_db	=	$db;
			$this->setDefinition();
			
			if($this->_alias === NULL) {
				$this->_alias	=	$this->_table;
			}
			
			foreach($this->_columns as $key => $col) {
				//	Prepared pieces of sql query
				if($this->_SQLColumns != NULL) {
					$this->_SQLColumns	.=	',';
					$this->_SQLUpdates	.=	',';
					$this->_SQLValues	.=	',';
				}
				$this->_SQLColumns	.=	$this->_alias.'.'.$key;
				$this->_SQLUpdates	.=	$this->_alias.'.'.$key.' = VALUES('.$this->_alias.'.'.$key.')';
				$this->_SQLValues	.=	'?';
				
				//	Record default values
				$this->_defaultValues[$key]		=	$col['default'];
				$this->_publicValues[$key]		=	$col['default'];
				$i++;
			}
			foreach($this->_relations as $key => $rel) {
				if($this->_SQLRelationsColumns !== NULL) {
					$this->_SQLRelationsColumns	.=	',';
				}
				//foreach($rel['record'] as $d);
				$this->_SQLRelationsColumns	=	$key.'.*';
				$this->_defaultValues[$key]	=	$rel['record'];
				$local		=	isset($rel['local']) ? $rel['local'] : $this->_primary;
				$foreign	=	isset($rel['foreign']) ? $rel['foreign'] : $rel['record']->_primary;
				$this->_SQLRelations		=	' LEFT JOIN '.$rel['record']->_table.' '.$key
											.	' ON '.$this->_alias.'.'.$local.' = '.$key.'.'.$foreign.'';
				$i++;
			}
		}
		
		private	function	defineRelations()
		{			
			$this->_columnsNames		=	array_keys($this->_columns);
			$this->_columnsTableSplits	=	array(sizeof($this->_columnsNames));
			foreach($this->_relations as $key => $rel) {
				if(!$rel['record']->_relationsDefined)	$rel['record']->defineRelations();
				$this->_columnsNames		=	array_merge($this->_columnsNames, array_keys($rel['record']->_columns));
				$this->_columnsTableSplits[]	=	sizeof($this->_columnsNames);
			}
			$this->_relationsDefined	=	true;
		}
		
		public	function	__get($name)
		{
			$this->getValue($name);
		}
		
		public	function	__set($name, $value)
		{
			$this->setValue($name, $value);
		}
		
		public	function	count()
		{
			$access	=	$this->_db->getAccess();
			return	$access->query('SELECT COUNT('.$this->_primary.') as numRecords FROM '.$this->_table)->fetchObject()->numRecords;
		}
		
		public	function	fill(array $values)
		{			
			foreach($values as $name => $value) {
				$this->setValue($name, $value);
			}
		}
		
		public	function	find($primary = NULL)
		{
			$access		=	$this->_db->getAccess();
			$query		=	'SELECT '.$this->_SQLColumns;
			if($this->_SQLColumns && $this->_SQLRelationsColumns) {
				$query	.=	',';
			}
			if($this->_SQLRelationsColumns) {
				$query	.=	$this->_SQLRelationsColumns;
			}
			$query		.=	' FROM '.$this->_table.' '.$this->_alias.$this->_SQLRelations;
			$filter		=	implode(' OR ', $this->_filters);
			$params		=	$this->_params;
			
			//	Search on primary Key, 0 to 1 row
			if($primary !== NULL) {
				$objectFilter	=	$this->_primary.' = ?';
				$params[]		=	$primary;
				
			//	Mixed search unlimited rows
			} else {
				$objectFilter	=	NULL;
				$i	=	0;
				foreach($this->_values as $key => $value) {
					if($value !== NULL) {
						if($i > 1)	$objectFilter	.=	' AND';
						$objectFilter	.=	' '.$key.' = ?';
						$params[]		=	$value;
						$i++;
					}
				}
			}
			
			if($objectFilter !== NULL) {
				$query	.=	$filter
						?	' WHERE ('.$filter.') AND '.$objectFilter
						:	' WHERE '.$objectFilter;
			} elseif($filter) {
				$query	.=	' WHERE '.$filter;
			}
			$access->query($query, array_values($params));
			var_dump($query, $params);
			
			if($primary !== NULL) {
				$obj	=	$access->fetch();
			} else {
				$obj	=	$access->fetchAll();
			}
			return	$obj;
		}
		
		public	function	getValue($name)
		{
			if(isset($this->_publicValues[$key])) {
				return	$this->_publicValues[$key];
			}
		}
		
		protected	function	hasColumn($name, $type = NULL, $value = NULL, $default = NULL)
		{
			if(is_array($type)) {
				$this->_columns[$name]	=	array(
					'type'		=>	isset($type['type'])	? $type['type']		: NULL,
					'value'		=>	isset($type['value'])	? $type['value']	: NULL,
					'default'	=>	isset($type['default'])	? $type['default']	: NULL
				);
			} else {
				$this->_columns[$name]	=	array(
					'type'		=>	$type,
					'value'		=>	$value,
					'default'	=>	$default
				);
			}
		}
		
		protected	function	hasOne($name, $alias = array(), $config = NULL)
		{
			if(!is_array($alias)) {
				$config	=	is_array($config) ? $config : array();
			} else {
				$config	=	$alias;
				$alias	=	$name;
			}
			if($record = $this->_db->getTable($name)) {
				$config['record']			=	$record;
				$this->_relations[$alias]	=	$config;
			}
		}
		
		public	function	isSaved()
		{
			return	$this->_saved;
		}
		
		public	function	setAlias($alias)
		{
			if(!$this->_db->$alias) {
				$this->_alias	=	$alias;
				return	true;
			}
			return false;
		}
		
		public	function	save()
		{
			if(!$this->_saved) {
				$access	=	$this->_db->getAccess();
				$params	=	$this->_publicValues;
				$query	=	'INSERT INTO '.$this->_table.'('.$this->_SQLColumns.')'
						.	' VALUES('.$this->_SQLValues.')'
						.	' ON DUPLICATE KEY UPDATE '.$this->_SQLUpdates;
						
				$access->query($query, array_values($params));
				
				if($this->id === NULL && $access->errorCode() == '00000') {
					$this->id		=	$access->lastInsertId();
					$this->_saved	=	true;
				}
			}
		}
		
		public	function	setDefinition() {}
		
		protected	function	setPrimary($column)
		{
			$this->_primary	=	$column;
		}
		
		protected	function	setTable($name)
		{
			$this->_table	=	$name;
		}
		
		private	function	setValue($name, $value)
		{
			if(isset($this->_publicValues[$name])) {
				if(!array_key_exists($name, $this->_values) || $this->_values[$name] != $value) {
					$this->_publicValues[$name]	=	$value;
					$this->_values[$name]		=	$value;
					$this->_saved				=	false;
				}
			}
		}
		
		public	function	value()
		{
			$args	=	func_get_args();
			if(sizeof($args) == 0) {
				return	$this->_publicValues;
			} elseif(is_array($args[0])) {
				$this->fill($args[0]);
			} elseif(isset($this->_publicValues[$key])) {
				if(sizeof($args) > 1) {
					$this->setValue($args[0], $args[1]);
				} else {
					return	$this->getValue($args[0]);
				}
			}
		}
	}

?>