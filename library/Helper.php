<?php

	class	Helper
	{
		protected	$system;
		protected	$_config;
		
		public	function	__construct(cahnory $system, $config = array())
		{
			$this->_config	=	array_merge($this->_config, $config);
			$this->system	=	$system;
		}
	}

?>