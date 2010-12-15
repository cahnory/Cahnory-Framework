<?php

	class Helper_Redirection
	{
		private	$system;
		private	$_history;
		private	$_registered;
		private	$_config	=	array(
			'sessionOffset'	=>	'helper_redirection-history'
		);
		private	$_binds		=	array();
		
		public	function	__construct(cahnory $system, $config = array())
		{
			$this->_config	=	array_merge($this->_config, $config);
			$this->system	=	$system;
			$this->system->bind('viewSent', array($this, 'register'));
			
			if(!isset($_SESSION[$this->_config['sessionOffset']])) {
				$this->_history	=	array();
			} else {
				$this->_history	=	(array)$_SESSION[$this->_config['sessionOffset']];
			}
			
			//	La page demandée est la dernière enregistrée
			$this->_registered	=	isset($this->_history[0]) && $this->_history[0] === $_SERVER['REQUEST_URI'];
		}
		
		public	function	alias($alias, $exit = true, $else = NULL)
		{
			$location	=	array_key_exists($alias, $this->_binds)
						?	$this->_binds[$alias]
						:	$else;
			$this->unknown($location, $exit);
		}
		
		public	function	application($route = NULL, $exit = true)
		{
			if(trim($route, '/') === '') {
				$route	=	'*';
			}
			$destination	=	$this->system->request->getBase(true)
							.	$this->system->router->getURL($route);
			$this->_redirect($destination, $exit);
		}
		
		public	function	bind($alias)
		{
			if(func_num_args() > 1) {
				$this->_binds[$alias]	=	func_get_arg(1);
			} else {
				return	array_key_exists($alias, $this->_binds)
					?	$this->_binds[$alias]
					:	NULL;
			}
		}
		
		public	function	foreign($location, $exit = true)
		{
			$this->_redirect($location, $exit);
		}
		
		public	function	history($back = 1, $default = NULL, $exit = true)
		{
			if(!is_numeric($back)) {
				$back		=	1;
				$default	=	$back;
				$exit		=	is_null($default) ? true : $default;
			}
			if($back === 1 && $this->_registered) $back++;
			if(array_key_exists($back-1, $this->_history)) {
				$this->_redirect($this->_history[$back-1], $exit);
			} else {
				$this->unknown($default, $exit);
			}
		}
		
		public	function	module($route = NULL, $exit = true)
		{
			$this->application($this->system->module->route().'/'.$route, $exit);
		}
		
		public	function	register($register = true)
		{
			if($register && !$this->_registered) {
				array_unshift($this->_history, $_SERVER['REQUEST_URI']);
				$this->_registered = true;
				$this->system->unbind('viewSent', array($this, 'register'));
			} elseif(!$register) {
				if($this->_registered) {
					array_shift($this->_history);
					$this->_registered = false;
				}
				$this->system->unbind('viewSent', array($this, 'register'));
			}
			$_SESSION[$this->_config['sessionOffset']]	=	$this->_history;
		}
		
		public	function	unknown($location, $exit = true)
		{
			if(!preg_match('#^[a-z0-9]+:#', $location)) {
				$this->application($location , $exit);
			} else {
				$this->foreign($location , $exit);
			}
		}
		
		private	function	_redirect($location, $exit = true)
		{
			$this->system->trigger('redirection');
			header('location: '.$location);
			if($exit)	exit();
		}
	}

?>