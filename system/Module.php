<?php

	/**
	 * LICENSE: Copyright (c) 2010 François 'cahnory' Germain
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 * 
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 * 
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 * that is available through the world-wide-web at the following URI:
	 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
	 * the PHP License and are unable to obtain it through the web, please
	 * send a note to license@php.net so we can mail you a copy immediately.
	 *
	 * @package    Cahnory
	 * @author     François 'cahnory' Germain <cahnory@gmail.com>
	 * @copyright  2010 François Germain
	 * @license    http://www.opensource.org/licenses/mit-license.php
	 */
	class Cahnory_Module
	{
		protected	$system;
		
		private	$_autoLoad		=	true;
		private	$_loaded		=	array();
		
		//	Preloaded	Modules
		private	$_modules		=	array();
		private	$_modulesFiles	=	array();
		private	$_modulesNames	=	array();
		
		//	Dispatching module
		private	$_module;
		private	$_moduleFile;
		private	$_moduleName;
		private	$_moduleRoute;
		private	$_modulePackage;
		
		public	function	__construct(Cahnory $system)
		{
			$this->system	=	$system;
			$this->system->view->setFilenameModifier(array($this, 'modifyViewFilename'));
		}
		
		/*	Enable or disable the autoLoad
		 *
		 *	@param bool $auto the new boolean autoLoad state
		 *
		 *	@return bool the current boolean autoLoad state
		 *
		 *	@acces public
		 *	@see Cahnory_Module::$_autoLoad
		 */
		public	function	autoLoad($auto = NULL)
		{
			if($auto !== NULL) {
				$this->_autoLoad	=	$auto;
			}
			
			return	$this->_autoLoad;
		}
		
		/*	Loads and execute the module linked to a given route
		 *
		 *	@param string $route
		 *
		 *	@return mixed module dispatch return
		 *
		 *	@acces public
		 */
		public	function	dispatch($route)
		{
			//	Aucune module ne correspond
			if(!$module = $this->load($route)) {
				return	false;
			}
			
			$result	=	$module->c_dispatch();
			
			return	$result;
		}
		
		/*	Return the loaded module's name
		 *
		 *	@return string the loaded module's name
		 *
		 *	@acces public
		 *	@see Cahnory_Module::$_moduleName
		 */
		public	function	name($module = NULL)
		{
			if($module !== NULL) {
				if(($route = array_search($module, $this->_modulesNames)) === false)
					return	NULL;
				
				return	$this->_modulesNames[$route];
			}
			return	$this->_moduleName;
		}
		
		/*	Return the loaded module's route
		 *
		 *	@return string the loaded module's route
		 *
		 *	@acces public
		 *	@see Cahnory_Module::$_moduleRoute
		 */
		public	function	route($module = NULL)
		{
			if($module !== NULL) {
				if(($route = array_search($module, $this->_modules)) === false)
					return	NULL;
				return	$route;
			}
			return	$this->_moduleRoute;
		}
		
		/*	Return the loaded module's filename
		 *
		 *	@return string the loaded module's filename
		 *
		 *	@acces public
		 *	@see Cahnory_Module::$_moduleFile
		 */
		public	function	file()
		{
			if($module !== NULL) {
				if(($route = array_search($module, $this->_modules)) === false)
					return	NULL;
				
				return	$this->_moduleFiles[$route];
			}
			return	$this->_moduleFile;
		}
		
		/*	Return the loading state
		 *
		 *	@return bool the loading state
		 *
		 *	@acces public
		 *	@see Cahnory_Module::_loaded
		 */
		public	function isLoaded()
		{
			return	$this->_loaded;
		}
		
		/*	Return the module linked to a given route
		 *
		 *	@param string $route
		 *
		 *	@return Module the linked module if exists
		 *
		 *	@acces public
		 *	@see Cahnory_Module::_modules
		 *	@see Cahnory_Module::_modulesNames
		 */
		public	function get($route)
		{
			return	array_key_exists($route, $this->_modules)
				?	$this->_modules[$route]
				:	false;
		}
		
		/*	Link a module to a given route
		 *
		 *	@param string $module the module name
		 *	@param string $route the route to link the module to
		 *	@param array  $config the config passed to the module
		 *
		 *	@return bool if the module could be linked
		 *
		 *	@acces public
		 */
		public	function set($name, $route = NULL, array $config = array())
		{
			if(is_array($route)) {
				$config	=	$route;
				$route	=	$name;
			}
			
			//	Use module name as route
			if(is_null($route)) {
				$route	=	$name;
			}
			
			$filename	=	$this->system->load->getFile(array(
				'Module'.DIRECTORY_SEPARATOR.$name.'.php',
				'Module'.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.'controller.php'
			));
			
			//	Module not found
			if($filename === NULL)	return false;
			
			return	$this->_preload($filename, $route, $name, $config);
		}
		
		/*	Load a module by its route
		 *
		 *	@param string $route
		 *
		 *	@return Module the linked module if exists
		 *
		 *	@acces public
		 */
		public	function load(&$route = NULL)
		{
			//	Module en cours de dispatch
			if($route === NULL && $this->_module) {
				return	$this->_module();
			}
			
			//	Module défini
			if(isset($this->_modules[$route])) {
				$this->_setFocus($route);
				return	$this->_modules[$route];
				
			//	AutoLoad
			} elseif($this->_autoLoad) {
				if($route === NULL) $route = 'index';
				$filename	=	'Module'.DIRECTORY_SEPARATOR.$route.'.php';
				if(!is_file($filename)) {
					$filename	=	'Module'.DIRECTORY_SEPARATOR.$route.DIRECTORY_SEPARATOR.'controller.php';
					if(!is_file($filename)) {
						return	false;
					}
				}
				return	$this->_preload($filename, $route, $route);
			}
		}
		
		public	function	unload()
		{
			$this->_unsetFocus();
		}
		
		private	function	_preload($filename, $route, $name, array $config = array()) {
			require_once	$filename;
			
			$class	=	'Module_'.$name;
			if(!class_exists($class)) {
				return false;
			}
			
			$module	=	new $class($this->system, $config);
			
			if(!$module instanceof Module) {
				return false;
			}
			
			$this->_modules[$route]			=	$module;
			$this->_modulesFiles[$route]	=	$filename;
			$this->_modulesNames[$route]	=	$name;
			
			$this->_setFocus($route);
			$module->c_preload($config);
			
			return	$module;
		}
		
		public	function	modifyViewFilename($filename)
		{
			if(preg_match('#\<module(?:\:([a-zA-Z_0-9]+))?>#', $filename, $m)) {
				if(isset($m[1])) {
					$route	=	$m[1];
					$name	=	$this->system->module->name($m[1]);
				} else {
					$route	=	$this->system->module->route();
					$name	=	$this->system->module->name();
				}				
				return	array(
					str_replace(array($m[0].'.',$m[0]), '+'.$route.'.', $filename),
					str_replace(array($m[0].'.',$m[0]), '+module.'.$route.'.', $filename),
					str_replace(array($m[0].'.',$m[0]), '+module.'.$route.DIRECTORY_SEPARATOR, $filename),
					str_replace(array($m[0].'.',$m[0]), 'module'.DIRECTORY_SEPARATOR.'+'.$route.'.', $filename),
					str_replace(array($m[0].'.',$m[0]), 'module'.DIRECTORY_SEPARATOR.'+'.$route.DIRECTORY_SEPARATOR, $filename),
					str_replace(array($m[0].'.',$m[0]), $name.'.', $filename),
					str_replace(array($m[0].'.',$m[0]), 'module.'.$name.'.', $filename),
					str_replace(array($m[0].'.',$m[0]), 'module.'.$name.DIRECTORY_SEPARATOR, $filename),
					str_replace(array($m[0].'.',$m[0]), 'module'.DIRECTORY_SEPARATOR.$name.'.', $filename),
					str_replace(array($m[0].'.',$m[0]), 'module'.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR, $filename)
				);
			}
		}
		
		private	function	_setFocus($route)
		{
			$this->_module			=	$this->_modules[$route];
			$this->_moduleFile		=	$this->_modulesFiles[$route];
			$this->_moduleName		=	$this->_modulesNames[$route];
			$this->_moduleRoute		=	$route;
			$this->_loaded			=	true;
			$this->_modulePackage	=	basename(dirname($this->_moduleFile)) == $this->_moduleName && basename($this->_moduleFile) == 'controller.php'
									?	dirname($this->_moduleFile).DIRECTORY_SEPARATOR
									:	false;
			
			if($this->_modulePackage !== false) {
				$this->system->load->addPathAfter($this->system->appPath(), $this->_modulePackage);
			}
		}
		
		private	function	_unsetFocus()
		{
			if($this->_modulePackage !== false) {
				$this->system->load->removePath($this->_modulePackage);
			}		
			$this->_module		=	NULL;
			$this->_moduleFile	=	NULL;
			$this->_moduleName	=	NULL;
			$this->_moduleRoute	=	NULL;			
			$this->_loaded		=	false;
		}
	}

?>