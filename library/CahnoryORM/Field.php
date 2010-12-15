<?php

	class CahnoryORM_Field
	{
		private	$_default;
		private	$_name;
		private	$_option;
		private	$_record;
		private	$_type;
		private	$_value;
		
		public	function	__construct(CahnoryORM_Record $record, $name, $type = NULL, $option = NULL, $default = NULL)
		{
			//$this->_record	=	$record;
			$this->_name	=	$name;
			$this->_type	=	$type;
			$this->_option	=	$option;
			$this->_default	=	$default;
		}
		
		public	function	__toString()
		{
			return	(string)$this->_value;
		}
		
		/**
		 *	Sets the field value
		 *
		 *	@param mixde $value the new field value
		 *
		 *	@access public
		 */	
		public	function	setValue($value)
		{
			$this->_value	=	$value;
		}
		
		/**
		 *	Returns the field value
		 *
		 *	@return mixed
		 *
		 *	@access public
		 */		
		public	function	getValue()
		{
			return	$this->_value;
		}
	}

?>