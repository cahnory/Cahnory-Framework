<?php

	require_once	dirname(__FILE__).'/system/Loader.php';
	require_once	dirname(__FILE__).'/system/Request.php';
	require_once	dirname(__FILE__).'/system/Router.php';
	require_once	dirname(__FILE__).'/system/Module.php';
	require_once	dirname(__FILE__).'/system/View.php';

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
	class Cahnory
	{
		/**
	     *	All the Cahnory instances
	     *
	     *	@var array
	     *	@access	private
	     */
		static	private	$_instances	=	array();
		
		/**
	     *	The app path
	     *
	     *	The path where all app files are.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_appPath		=	'.';
		
		/**
	     *	The root path (www/htdocs)
	     *
	     *	The path where all app files are.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_rootPath		=	'.';
		
		/**
	     *	The system path
	     *
	     *	The path to this file
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_systemPath;
		
		/**
	     *	The public path
	     *
	     *	The only path wich allow direct file access by users.
	     *	[!] This is not in use for the moment.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_publicPath	=	'files';
		
		/**
	     *	The binded callback
	     *
	     *	Callback are stored in arrays stored by event name.
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_binds	=	array();
		
		/**
	     *	The reserved events
	     *
	     *	Reserved events could not be triggerd publicly
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_reservedEvents	=	array(
			'dispatchEnd',
			'dispatchError',
			'dispatchStart',
			'moduleDispatched',
			'viewError',
			'viewLoaded',
			'viewSent'
		);
		
		/**
	     *	The assigned helpers
	     *
	     *	Assigned helpers by access name
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_helpers			=	array();
		
		/**
	     *	The locked helpers
	     *
	     *	Locked helpers couldn't be assigned/replaced/removed
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_lockedHelpers		=	array(
			'load', 'request', 'router', 'module', 'view'
		);
		
		/**
	     *	The request modifiers
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_requestModifiers	=	array(
			'server' => array(), 'get' => NULL, 'post' => NULL, 'files' => NULL, 'args' => NULL
		);
		
		public	function	__construct()
		{
			$this->_systemPath	=	dirname(__FILE__);
			self::$_instances[]	=	$this;
			
			if(isset($_SERVER['SHELL'])) {
				$this->_initCLI();
			} else {
				$this->_initGUI();
			}
			
			//	Hide errors by default
			ini_set('display_errors', 0);
			error_reporting(0);
			
			//	Loader
			$this->_helpers['load']		=	new Cahnory_Loader();
			$this->_helpers['load']->addPath($this->_systemPath.DIRECTORY_SEPARATOR.'library');
			$this->_helpers['load']->addPath($this->_appPath);
			spl_autoload_register(array($this->_helpers['load'], 'autoLoad'));
			
			//	Request & route
			$this->_helpers['request']	=	new Cahnory_Request(
				$this->_requestModifiers['server'],
				$this->_requestModifiers['get'],
				$this->_requestModifiers['post'],
				$this->_requestModifiers['files'],
				$this->_requestModifiers['args']
			);
			$this->_helpers['router']	=	new Cahnory_Router($this->_helpers['request']->getRoute());
			
			//	Module & view
			$this->_helpers['view']		=	new Cahnory_View($this);
			$this->_helpers['module']	=	new Cahnory_Module($this);
		}
		
		public	function	__get($name)
		{
			if(!isset($this->_helpers[$name])) {
				if(!$this->setHelper($name)) {
					return false;
				}
			}
			return	$this->_helpers[$name];
		}
		
		/*	Getter/Setter for the app path
		 *
		 *	@param string $path the new path
		 *
		 *	@return	mixed if $path is null, the app path is returned
		 *				  else if new path exists, true is returned
		 *				  else false
		 *
		 *	@acces public
		 */
		public	function	appPath($path = NULL)
		{
			if($path === NULL)	return	$this->_appPath;
			
			if(is_dir($path)) {
				$path	=	realpath($path);
				$this->_helpers['load']->swapPath($this->_appPath, $path);
				$this->_appPath	=	$path;
				if($this->_helpers['request']->isCLI()) {
					chdir($this->_appPath);
				}
				return	true;
			} else {
				return	false;
			}
		}
		
		/*	Getter for the root path
		 *
		 *	@return	string the root path is returned
		 *
		 *	@acces public
		 */
		public	function	rootPath()
		{
			return	$this->_rootPath;
		}
		
		/*	Link a function to an event
		 *
		 *	@param string $event    the event name
		 *	@param string $callback function to call
		 *
		 *	@acces public
		 */
		public	function	bind($event, $callback)
		{
			$this->_binds[$event][]	=	$callback;
		}
		
		/*	Launch the view and module
		 *
		 *	@acces public
		 */
		public	function	dispatch()
		{
			$this->_trigger('dispatchStart');
			//	View
			$viewType	=	$this->_helpers['request']->getFormat();
			if($viewType === NULL) {
				$viewType	=	$this->_helpers['view']->defaultType();
			}
			if(!$this->_helpers['view']->load($viewType)) {
				$this->_trigger('viewError', false);
			} else {			
				$this->_trigger('viewLoaded');
				
				//	Module
				$moduleName	=	$this->_helpers['router']->getRoutePath(0,1);
				if($this->module->dispatch($moduleName) === false) {
					$this->_trigger('dispatchError', false);
				} else {
					$this->_trigger('moduleDispatched', false);
				}		
				$this->module->unload();		
			}
			$this->_trigger('dispatchEnd', false);
				
			$this->_helpers['view']->render();			
			$this->_trigger('viewSent', false);			
		}
		
		/*	Returns the nth Cahnory instance from the last
		 *
		 *	@param int $i instance to return since the last created
		 *
		 *	@return	Cahnory
		 *
		 *	@acces public
		 *	@static
		 */
		static	public	function	instance($i = 0)
		{
			if(isset(self::$_instances[$i]))
				return	self::$_instances[$i];
		}
		
		/*	Lock a helper to a name
		 *
		 *	Once it's locked, the helper could not be removed and/or
		 *	replaced by another.
		 *
		 *	@param string $name   the name the helper was assigned to
		 *
		 *	@acces public
		 */
		public	function	lockHelper($name)
		{
			$names	=	func_get_args();
			$this->_lockedHelpers	=	array_merge($this->_lockedHelpers, $names);
		}
		
		public	function	_initCLI()
		{	
			if(isset($_SERVER['argv'][1])) {
				$string	=	$_SERVER['argv'][1];
				
				//	Parsing de la chaine de paramètres '[<param:value>[<param2:value>]]'
				preg_match_all(
					'#<[\s]*([^:>]+)[\s]*:[\s]*(((?<!\\\)\\\(\\\\\\\)*>|.(?!>))+.)[\s]*>#',
					$string,
					$match
				);
				
				//	Param array ou route string
				if(sizeof($match[2])) {
					$params	=	array_combine($match[1], $match[2]);
				} else {
					$params	=	array('route'	=>	$string);
				}
					
				if(array_key_exists('protocol', $params))
					$this->_requestModifiers['server']['SERVER_PROTOCOL']	=	$params['protocol'];
					
				if(array_key_exists('host', $params))
					$this->_requestModifiers['server']['HTTP_HOST']			=	$params['host'];
				else
					$this->_requestModifiers['server']['HTTP_HOST']			=	'localhost';
					
				if(array_key_exists('base', $params))
					$this->_requestModifiers['server']['SCRIPT_NAME']		=	DIRECTORY_SEPARATOR.trim($params['base'],'\/').DIRECTORY_SEPARATOR;
				else
					$this->_requestModifiers['server']['SCRIPT_NAME']		=	DIRECTORY_SEPARATOR;
					
				if(array_key_exists('route', $params))
					$this->_requestModifiers['server']['REDIRECT_URL']		=	$this->_requestModifiers['server']['SCRIPT_NAME'].trim($params['route'],'/');
				
				$this->_requestModifiers['server']['SCRIPT_NAME']	.=	'index.php';
			}
			
			//	Search for the script real filename
			if(isset($_SERVER['SCRIPT_NAME']))
				$this->_appPath	=	dirname($_SERVER['SCRIPT_NAME']);	//	cli
			elseif(isset($_SERVER['PWD']))								//	cgi
				$this->_appPath	=	DIRECTORY_SEPARATOR.trim(
						isset($_SERVER['OLDPWD'])
						?	trim($_SERVER['PWD'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
						:	NULL
					.	trim(dirname(DIRECTORY_SEPARATOR.$_SERVER['argv'][0]), DIRECTORY_SEPARATOR)
					,	DIRECTORY_SEPARATOR);
					
			chdir($this->_appPath);
			
			$this->_rootPath	=	substr($this->_appPath,0, strlen($this->_appPath) - strlen(dirname($this->_requestModifiers['server']['SCRIPT_NAME'])));
		}
		
		public	function	_initGUI()
		{
			$this->_rootPath	=	$_SERVER['DOCUMENT_ROOT'];
			$this->_appPath		=	isset($_SERVER['SCRIPT_FILENAME'])
								?	dirname($_SERVER['SCRIPT_FILENAME'])
								:	dirname($this->_rootPath.$_SERVER['SCRIPT_NAME']);
		}
		
		/*	Load and assign a helper
		 *
		 *	@param string $name   the name to assign the helper
		 *	@param string $helper the helper name
		 *	@param array  $config the helper config
		 *
		 *	@acces public
		 */
		public	function	setHelper($name, $helper = NULL, array $config = array())
		{
			//	Reserved helper
			if(!in_array($name, $this->_lockedHelpers)) {
				if($helper === NULL) {
					$helper	=	$name;
				} elseif(is_array($helper)) {
					$config	=	$helper;
					$helper =	$name;
				}
				$class	=	'Helper_'.$helper;
				if(!class_exists($class)) {
					return false;
				}
				$this->_helpers[$name]	=	new $class($this, $config);
				return	true;
			}
		}
		
		/*	Returns the system path (where this file is)
		 *
		 *	@return string the system path
		 *
		 *	@acces public
		 */
		public	function	systemPath()
		{
			return	$this->_systemPath;
		}
		
		/*	Trigger a non reserved event
		 *
		 *	@param string $event the event name
		 *	@param bool   $asc   call function in an ascendant way (by binding)
		 *
		 *	@acces public
		 */
		public	function	trigger($event, $asc = true)
		{
			if(!in_array($event, $this->_reservedEvents)) {
				if(func_num_args() > 2) {
					$args	=	func_get_args();
					$this->_trigger($event, $asc, array_splice($args, 2));
				} else {
					$this->_trigger($event, $asc);
				}
				return	true;
			}
			return	false;
		}		
		
		/*	Trigger an event
		 *
		 *	@param string $event the event name
		 *	@param bool   $asc   call function in an ascendant way (by binding)
		 *	@param array  $args  the arguments to pass to functions
		 *
		 *	@acces private
		 */
		private	function	_trigger($event, $asc = true, array $args = array())
		{
			if(!isset($this->_binds[$event]))	return false;
			
			$callbacks	=	$this->_binds[$event];
			if(!$asc) {
				$callbacks	=	array_reverse($callbacks);
			}
			foreach($callbacks as $callback) {
				call_user_func_array($callback, $args);
			}
		}
		
		/*	UnLink a function from an event
		 *
		 *	@param string $event    the event name
		 *	@param string $callback function to unbind
		 *
		 *	@return bool if function was found and unset
		 *
		 *	@acces public
		 */
		public	function	unbind($event, $callback)
		{
			if($keys = array_keys($this->_binds[$event], $callback)) {
				foreach($keys as $key) {
					unset($this->_binds[$event][$key]);
				}
				return	true;
			}
			return	false;
		}
	}
	
	//	Last instance shortcut (deprecated, func name is subject to change)
	function	C($i = 0) { return Cahnory::instance($i); }

?>