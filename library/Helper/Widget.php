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
	class Helper_Widget
	{
		protected	$system;
		
		//	Loading widget
		private	$_widgetFile;
		private	$_widgetName;
		
		public	function	__construct(Cahnory $system)
		{
			$this->system	=	$system;
			$this->system->view->setFilenameModifier(array($this, 'modifyViewFilename'));
		}
		
		public	function	name()
		{
			return	$this->_widgetName;
		}
		
		public	function	file()
		{
			return	$this->_widgetFile;
		}
		
		/*
			Associe un module à une route. La route correspond à
			la première portion du path de l'url (ex: blog dans host.dom/blog/cat/2.html).
			@param		string		$module		Le nom du module
			@param		string		$route		Le format d'url associé (après le dernier .)
			@param		array		$config		Tableau de configuration
			@return		this
		*/
		public	function get($name, $config = NULL)
		{
			$filename	=	$this->system->load->getFile(array(
				'Widget/'.$name.'.php',
				'Widget/'.$name.'/controller.php'
			));
			
			//	Widget introuvable
			if($filename === NULL)	return false;
			
			require_once	$filename;
			$class	=	'Widget_'.$name;
			
			//Widget introuvable
			if(!class_exists($class))	return false;
			
			$path	=	dirname($filename).'/';
			$this->system->load->addPathAfter($this->system->appPath(), $path);
			$this->_widgetFile	=	$filename;
			$this->_widgetName	=	$name;
			
			$widget	=	new $class($this->system, $config);
			
			$this->_widgetFile	=	NULL;
			$this->_widgetName	=	NULL;
			$this->system->load->removePath($path);
			
			return	$widget;
		}
		
		public	function	modifyViewFilename($filename)
		{
			if(strstr($filename, '<widget>')) {
				$name	=	$this->system->Widget->name();
				return	array(
					str_replace(array('<widget>.','<widget>'), $name, $filename),
					str_replace(array('<widget>.','<widget>'), 'widget.'.$name, $filename),
					str_replace(array('<widget>.','<widget>'), 'widget/'.$name, $filename)
				);
			}
		}
	}

?>