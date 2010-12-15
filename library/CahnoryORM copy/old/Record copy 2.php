<?php

	abstract class CahnoryDB_Record
	{
		private	$_db;
		private	$_table;
		private	$_alias;
		private	$_compiledAlias;
		private	$_primary	=	'id';
		private	$_columns	=	array('id' => array('int', 11));
		private	$_relations	=	array();
		
		private	$_compiled	=	false;
		private	$_compilation;
		
		private	$_SQLColumns;
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
			
			foreach($this->_columns as $name => $col) {				
				//	Record default values
				$this->_defaultValues[$name]	=	$col['default'];
				$this->_publicValues[$name]		=	$col['default'];
			}
		}
		
		public	function	__get($name)
		{
			$this->getValue($name);
		}
		
		public	function	__set($name, $value)
		{
			$this->setValue($name, $value);
		}
		
		private	function	_compil(&$aliasID = 0, &$cmp = NULL)
		{
			if($cmp === NULL) {
				$this->_compiled	=	true;
				$cmp	=&	$this->_compilation;
				$cmp	=	array(
					'columns'	=>	array(),
					'from'		=>	array($this->_table.' a'.$aliasID),
					'fetch'		=>	array(),
					'stack'		=>	array()
				);
			}
			$id	=	$aliasID;
			$this->_compiledAlias	=	'a'.$aliasID;
			$cmp['stack'][]	=	$this->_alias;
			foreach($this->_columns as $name => $column) {
				$cmp['columns'][]	=	'a'.$aliasID.'.'.$name;
			}
			$cmp['fetch'][]	=	array(
				'record'	=>	$this,
				'path'		=>	implode('->', $cmp['stack']),
				'split'		=>	sizeof($cmp['columns'])
			);
			foreach($this->_relations as $name => $rel) {
				$aliasID++;
				$cmp['from'][]	=	' LEFT JOIN '.$rel['record']->_table.' a'.$aliasID
								.	' ON a'.$aliasID.'.'.(isset($rel['foreign']) ? $rel['foreign'] : $rel['record']->_primary)
								.	' = a'.$id.'.'.(isset($rel['local']) ? $rel['local'] : $this->_primary);
				$rel['record']->_compil(&$aliasID, &$cmp);
			}
			array_pop($cmp['stack']);
		}
		
		private	function	_fetch($row, $obj = NULL)
		{
			$last	=	0;
			if($obj === NULL)	$obj = array($this->_db);
			foreach($this->_compilation['fetch'] as $fetch) {
				var_dump('$obj->'.$fetch['path'], ${'$obj->'.$fetch['path']}, $obj->tests, $this->_table, $this->_alias);
				exit();
				/*$obj[0]->fill(array_slice($row,$last,$fetch['split'] - $last));
				$fetch['record']->fill(array_slice($row,$last,$fetch['split'] - $last));
				$last	=	$fetch['split'];*/
			}
			return	$row;
			return	$obj;
		}
		
		public	function	count()
		{
			$access	=	$this->_db->getAccess();
			return	$access->query('SELECT COUNT('.$this->_primary.') as numRecords FROM '.$this->_table)->fetchObject()->numRecords;
		}
		
		public	function	fill($values)
		{
			/*if(is_a($values, CahnoryDB_Record)) {
				$
			}*/
			foreach($values as $name => $value) {
				$this->setValue($name, $value);
			}
		}
		
		public	function	find($primary = NULL)
		{
			if(!$this->_compiled)	$this->_compil();
			$query	=	'SELECT '.implode(',',$this->_compilation['columns'])
					.	' FROM '.implode('',$this->_compilation['from']);
			$filter		=	implode(' OR ', $this->_filters);
			$params		=	$this->_params;
			
			//	Search on primary Key, 0 to 1 row
			if($primary !== NULL) {
				$objectFilter	=	$this->_compiledAlias.'.'.$this->_primary.' = ?';
				$params[]		=	$primary;
				
			//	Mixed search unlimited rows
			} else {
				$objectFilter	=	NULL;
				$i	=	0;
				foreach($this->_values as $key => $value) {
					if($value !== NULL) {
						if($i > 1)	$objectFilter	.=	' AND';
						$objectFilter	.=	$this->_compiledAlias.'.'.$key.' = ?';
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
			
			$access		=	$this->_db->getAccess();
			$access->query($query, array_values($params));
			var_dump($query, $params);
			
			if($primary !== NULL) {
				return	$this->_fetch($access->fetch());
			} else {
				$obj	=	array();
				while($row = $access->fetch()) {
					$obj[]	=	$this->_fetch($row);
				}
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
				$record->setAlias($name);
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