<?php

	class CahnoryORM_Filter
	{
		private	$_querys		=	array();
		private	$_parameters	=	array();
		
		public	function __construct($columns = NULL, $value = false)
		{
			if(!is_null($columns)){
				if(!is_array($columns)) {
					$columns	=	array($columns => $value);
				}
				foreach($columns as $column => $value) {
					$this->is($column, $value);
				}
			}
		}
		
		public	function isEmpty()
		{
			return	sizeof($this->_querys) === 0;
		}
		
		public	function getQuery()
		{
			return implode(' AND ', $this->_querys);
		}
		
		public	function getParameters()
		{
			return	$this->_parameters;
		}
		
		public	function merge(CahnoryORM_Filter $filter)
		{
			foreach($filter->_querys as $k => $v) {
				$this->_querys[]		=	$v;
				$this->_parameters[]	=	$filter->_parameters[$k];
			}
			return	$this;
		}
		
		public	function between($column, $a, $b)
		{
			$this->_querys[]		=	$column.' BETWEEN ? AND ?';
			$this->_parameters[]	=	$a;
			$this->_parameters[]	=	$b;
			return	$this;
		}
		
		public	function exists($string, array $parameters = array())
		{
			$this->_querys[]	=	'EXISTS('.$string.')';
			$this->_parameters	=	array_merge($this->_parameters, array_values($parameters));
		}
		
		public	function gt($column, $value)
		{
			$this->_querys[]		=	$column.' > ?';
			$this->_parameters[]	=	$value;
			return	$this;
		}
		
		public	function gte($column, $value)
		{
			$this->_querys[]		=	$column.' >= ?';
			$this->_parameters[]	=	$value;
			return	$this;
		}
		
		public	function is($column, $value)
		{
			$this->_querys[]		=	$column.' = ?';
			$this->_parameters[]	=	$value;
			return	$this;
		}
		
		public	function isNull($column)
		{
			$this->_querys[]		=	$column.' IS NULL';
			return	$this;
		}
		
		public	function lt($column, $value)
		{
			$this->_querys[]		=	$column.' < ?';
			$this->_parameters[]	=	$value;
			return	$this;
		}
		
		public	function lte($column, $value)
		{
			$this->_querys[]		=	$column.' <= ?';
			$this->_parameters[]	=	$value;
			return	$this;
		}
		
		public	function not($column, $value)
		{
			$this->_querys[]		=	$column.' != ?';
			$this->_parameters[]	=	$value;
			return	$this;
		}
		
		public	function notBetween($column, $a, $b)
		{
			$this->_querys[]		=	$column.' NOT BETWEEN ? AND ?';
			$this->_parameters[]	=	$a;
			$this->_parameters[]	=	$b;
			return	$this;
		}
		
		public	function notExists($string, array $parameters = array())
		{
			$this->_querys[]	=	'NOT EXISTS('.$string.')';
			$this->_parameters	=	array_merge($this->_parameters, array_values($parameters));
		}
		
		public	function notNull($column)
		{
			$this->_querys[]		=	$column.' IS NOT NULL';
			return	$this;
		}
	}

?>