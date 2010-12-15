<?php

	class Helper_Theme
	{
		private	$_system;
		private	$_metaNames	=	array(
			'keywords','description','author','contact','copyright','robots','googlebot','copyright'
		);
		private	$_metaEquiv	=	array(
			'Content-Type','charset','expires','last-modified','cache-control','pragma','content-language','content-type'
		);
		
		private	$_path		=	'Theme/';
		private	$_themes	=	array();
		private	$_patterns	=	array();
		private	$_css		=	array();
		private	$_js		=	array(
			'body'	=>	array(),
			'head'	=>	array()
		);
		private	$_enabled	=	true;
		
		public	function	__construct($system)
		{
			$this->system	=	$system;
			$this->system->bind('dispatchEnd',		array($this, 'onDispatchEnd'));
			$this->system->bind('viewError',		array($this, 'onViewError'));
		}
		
		public	function	disable()
		{
			if($this->_enabled) {				
				$this->system->unbind('dispatchEnd', array($this, 'onDispatchEnd'));
			}
			$this->_enabled	=	false;
		}
		
		public	function	enable()
		{
			if(!$this->_enabled) {
				$this->system->bind('dispatchEnd', array($this, 'onDispatchEnd'));
			}
			$this->_enabled	=	true;
		}
		
		/* !View */
		public	function	getMeta()
		{
			$metas	=	array();
			//	16,6
			$data	=	$this->system->view->data();
			foreach($this->_metaNames as $name) {
				if(isset($data[$name])) {
					$metas[]	=	array(
						'http-equiv'	=>	NULL,
						'name'			=>	$name,
						'content'		=>	$data[$name]
					);
				}
			}
			foreach($this->_metaEquiv as $equiv) {
				if(isset($data[$equiv])) {
					$metas[]	=	array(
						'http-equiv'	=>	$equiv,
						'name'			=>	NULL,
						'content'		=>	$data[$equiv]
					);
				} else if($equiv == 'Content-Type') {
					$metas[]	=	array(
						'http-equiv'	=>	$equiv,
						'name'			=>	NULL,
						'content'		=>	$this->system->view->mime().'; charset='.$this->system->view->charset()
					);
				}
			}
			return	$metas;
		}
		
		public	function	getTitle()
		{
			$name	=	$this->system->view->data('name');
			$title	=	$this->system->view->data('title');
			$split	=	$this->system->view->data('title_separator');
			if($title != $name) {
				if(empty($name) || empty($title)) {
					$split	=	NULL;
				} elseif($split === NULL) {
					$split	=	' - ';
				}
				
				if($this->system->view->data('home')) {
					$title	=	$name.$split.$title;
				} else {
					$title	=	$title.$split.$name;
				}
			}
			
			return $title;
		}
		
		public	function	getView($content)
		{
			if($this->system->request->isCLI()) {
				$pattern	=	isset($this->_patterns['cli']) ? $this->_patterns['cli'] : NULL;
			} else if($this->system->request->isAjax()) {
				$pattern	=	isset($this->_patterns['ajax']) ? $this->_patterns['ajax'] : NULL;
			} else {
				$pattern	=	isset($this->_patterns['.']) ? $this->_patterns['.'] : NULL;
			}
			
			$theme			=	array(
				'title'		=>	$this->getTitle(),
				'meta'		=>	$this->getMeta(),
				'base'		=>	$this->system->request->getBase(true),
				'css'		=>	$this->_css,
				'js'		=>	$this->_js,
				'path'		=>	$this->_path.end($this->_themes).'/',
				'module'	=>	$content
			);
			
			if(is_file($pattern)) {
				return	$this->system->view->executeFile($pattern, array('theme' => $theme));
			} else {
				return	$this->system->view->executeString($pattern, array('theme' => $theme));
			}
		}
		
		/* !Patterns */
		public	function	getPattern($namespace = '.')
		{
			return	isset($this->_patterns[$namespace]) ? $this->_patterns[$namespace] : NULL;
		}
		
		public	function	setPattern($filename, $namespace = '.')
		{
			return	$this->_patterns[$namespace]	=	$this->system->load->view($filename);
		}
		
		public	function	unsetPattern($namespace = '.')
		{
			if(isset($this->_patterns[$namespace])) {
				unset($this->_patterns[$namespace]);
			}
		}
		
		/* !Themes */		
		public	function	addTheme($name)
		{
			$name	=	rtrim($name, '/').'/';
			if(is_dir($this->_path.$name)) {
				$this->system->load->addPath($this->_path.$name);
				$this->_themes[]	=	$this->_path.$name;
			}
			return $this;
		}
		
		/* !Includes */
		public	function	addCSS($filename, $media = 'all')
		{
			$this->_css[]	=	array(
				'filename'	=>	$filename,
				'media'		=>	$media
			);
		}

		public	function	addJS($filename, $namespace = 'body')
		{
			if(!isset($this->_js[$namespace])) {
				$this->_js[$namespace]	=	array();
			}
			$this->_js[$namespace][]	=	array(
				'filename'	=>	$filename
			);
		}

		public	function	setTheme($name)
		{
			foreach($this->_themes as $theme) {
				$this->system->load->removePath($theme);
			}
			$name	=	trim($name, '/').'/';
			if(is_dir($this->_path.$name)) {
				$this->system->load->addPath($this->_path.$name);
				$this->_themes	=	array($this->_path.$name);
			}
			return $this;
		}

		/* !Events */
		public	function onViewLoaded()
		{
			/*if($this->system->view->type() === 'html') {
				foreach($this->_themes as $theme) {
					if(is_file($filename = $theme.'theme.php')) {
						require	$filename;
					}
				}
			}*/
		}

		/*public	function onViewError()
		{
			//if(!$this->system->view->isLoaded())
			if($this->system->view->isLoaded()
			||($this->system->view->defaultType() === 'html' && $this->system->view->load($this->system->view->defaultType()))) {
				foreach($this->_themes as $theme) {
					$this->system->load->addPath($theme);
					if(is_file($filename = $theme.'theme.php')) {
						require	$filename;
					}
				}
			}
		}*/

		public	function onViewError()
		{
			if(!$this->system->view->isLoaded() && ($this->system->view->defaultType() === 'html')) {
				$this->system->view->load($this->system->view->defaultType());
			}
		}

		public	function onDispatchEnd()
		{
			if($this->system->view->type() === 'html') {
				foreach($this->_themes as $theme) {
					if(is_file($filename = $theme.'theme.php')) {
						require	$filename;
					}
				}
			}
			$this->system->view->content($this->getView($this->system->view->content()));
		}
	}

?>