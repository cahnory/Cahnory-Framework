<?php

	class Module_AppInfos	extends	Module
	{
		public	function	index()
		{
			//	Home
			$home	=	array('route' => $this->system->router->getRoute('*'));
			if($home['route'] === NULL)	$home['route']	=	'Index';
			$home['module']	=	$this->system->module->get($home['route']);
			
			
			//	Helpers
			$helpersPaths	=	$this->system->load->getAllDir('Helper');
			$helpers		=	array();
			foreach($helpersPaths as $path) {
				$helpers	=	array_merge($helpers, $this->system->File->listFiles($path, '*.php'));
			}
			$helpers		=	array_unique($helpers);
			foreach($helpers as &$helper) {
				$helper		=	preg_replace('#\.php$#', '', $helper);
			}
			
			//	Modules
			$modulesPaths	=	$this->system->load->getAllDir('modules','Module');
			$modules		=	array();
			foreach($modulesPaths as $path) {
				$modules	=	array_merge($modules, $this->system->File->listFiles($path, '*{.php,/controller.php}'));
			}
			$modules		=	array_unique($modules);
			foreach($modules as &$module) {
				$module		=	preg_replace('#(/controller)?\.php$#', '', $module);
			}
			
			$this->system->view->put('<module>index', compact('home','helpers','modules'));
		}
	}

?>