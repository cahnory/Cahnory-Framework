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
	class Cahnory_View
	{
		protected	$system;
		
		private	$_defaultType	=	'html';
		private	$_views;
		private	$_view;
		private	$_format;
		private	$_type;
		private	$_data;
		private	$_filenameModifiers	=	array();
		private	$_executing			=	array();
		
		public	function __construct(cahnory $system)
		{
			$this->_views	=	array();
			$this->_data	=	array();
			$this->system	=	$system;
			$this->system->load->help('view', array($this, 'getFile'));
		}
		
		/*
			Définit/renvoit une donnée de vue. La définition peut se faire
			par tableau indexé.
			Les données de vue peuvent par exemple être utilisé pour stocker
			les meta d'une vue html.
			@param		string/array	$name	Le nom de la donnée ou un tableau indexé
			@param		string			$value	La valeur de la donnée $name
			@return		string/bool
		*/
		private	function _data($name = NULL, $value = NULL)
		{
			if(is_null($name))
				return	$this->_data;
			
			if(is_array($name)) {
				$this->_data		=	array_merge($this->_data, $name);
			} else if(!is_null($value)) {
				$this->_data[$name]	=	$value;
			} else {
				return	array_key_exists($name, $this->_data)
					?	$this->_data[$name]
					:	false;
			}
		}
		
		/*	Set/get the default view type
		 *
		 *	@param string $type the new default view type
		 *
		 *	@return string the default type
		 *
		 *	@acces public
		 */
		public	function	defaultType($type = NULL)
		{
			if($type !== NULL) {
				$this->_defaultType	=	$type;
			}
			return	$this->_defaultType;
		}
		
		public	function	execute($viewName, array $data = array(), $autoType = true)
		{
			if($viewFile = $this->getFile($viewName, $autoType)) {
				return	$this->executeFile($viewFile, $data);
			}
			return	false;
		}
		
		public	function	executeFile($file, array $data = array())
		{
			if($this->_view) {
				array_unshift($this->_executing, $file);
				extract($data);
				ob_start();
				include	$this->_executing[0];
				$output	=	ob_get_clean();
				array_shift($this->_executing);
				return	$output;
			}
			return	false;
		}
		
		public	function	executeString($string, array $data = array())
		{
			if($this->_view) {
				array_unshift($this->_executing, $string);	// Attention au loop plus profond que 1
				extract($data);
				ob_start();
				eval('?>'.$this->_executing[0]);
				$output	=	ob_get_clean();
				array_shift($this->_executing);
				return	$output;
			}
			return	false;
		}
		
		public	function format()
		{
			return	$this->_format;
		}
		
		public	function	getFile($filename, $autoType = true)
		{
			$filename	=	'View'.DIRECTORY_SEPARATOR.$filename.($autoType ? '.'.$this->_type : NULL);
			$files	=	array($filename);
			foreach($this->_filenameModifiers as $modifier) {
				if(is_array($file = call_user_func($modifier, $filename))) {
					$files		=	array_merge($files, $file);
				} else if($file !== NULL) {
					$files[]	=	$file;
				}
			}
			$files = $this->system->load->getFile($files);
			return	$files;
		}
		
		public	function	isLoaded()
		{
			return	$this->_view !== NULL;
		}
		
		/*
			Charge la vue associée à un format.
			@param		string		$format		Le format d'url
			@return		View
		*/
		public	function load($format)
		{
			if(!array_key_exists($format, $this->_views))
				return	false;
			
			extract($this->_views[$format]);
			
			$class	=	'View_'.$type;
			if(!$this->system->load->class($class)) {
				return	false;
			}
			$view	=	new	$class($config, $this->_data);
			if(!$view instanceof View) {
				return false;
			}
			
			$this->_view	=	$view;
			$this->_type	=	$type;
			$this->_format	=	$format;
			return	$this->_view;
		}
		
		public	function	loop($back = 0, array $data = array())
		{
			if(is_array($back)) {
				$data	=	$back;
				$back	=	0;
			}
			if(isset($this->_executing[$back])) {
				extract($data);
				ob_start();
				include	$this->_executing[$back];
				return	ob_get_clean();
			}
			return	false;
		}
		
		public	function	put($viewName, array $data = array(), $autoType = true)
		{
			if(($result = $this->execute($viewName, $data, $autoType)) !== false) {
				var_dump($result);
				$this->content($this->content().$result);
				return	true;
			} else {
				return false;
			}
		}
		
		public	function	putString($string)
		{
			$this->content($this->content().$string);
			return	true;
		}
		
		/*
			Associe un type de vue à un format d'url. Le format d'url correspond à
			l'extension du fichier/page demandé sans le point le précédant.
			@param		string		$type		html, json, xml...
			@param		string		$format		Le format d'url associé (après le dernier .)
			@param		array		$config		Tableau de configuration
			@return		this
		*/
		public	function set($type, $format = NULL, array $config = array())
		{
			if(is_array($format)) {
				$config	=	$format;
				$format	=	$type;
			}
			
			if(is_null($format))
				$format	=	$type;
			
			$this->_views[(string)$format]	=	array(
				'type'		=>	$type,
				'config'	=>	$config
			);
			return	$this;
		}
		
		public	function	setFilenameModifier($callback)
		{
			$this->_filenameModifiers[]	=	$callback;
		}
		
		public	function	unsetFilenameModifier($callback)
		{
			if($keys = array_keys($this->_filenameModifiers, $callback)) {
				foreach($keys as $key) {
					unset($this->_filenameModifiers[$key]);
				}
				return	true;
			}
			return false;
		}
		
		/* !Follow */
		public	function charset($charset = NULL)
		{
			if($this->_view)
				return	$this->_view->charset($charset);
			else
				return	false;
		}
		public	function content($content = NULL)
		{
			if($this->_view)
				return	$this->_view->content($content);
			else
				return	false;
		}
		public	function data($name = NULL, $value = NULL)
		{
			if($this->_view)
				return	$this->_view->data($name, $value);
			else
				return	$this->_data($name, $value);
		}
		public	function mime()
		{
			if($this->_view)
				return	$this->_view->mime();
			else
				return	false;
		}
		public	function render()
		{
			if($this->_view)
				return	$this->_view->render();
			else
				return	false;
		}
		public	function type()
		{
			if($this->_view)
				return	$this->_view->type();
			else
				return	false;
		}
	}

?>