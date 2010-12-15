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
	abstract class View
	{
		protected	$_config	=	array();
		protected	$_mime;
		protected	$_type;
		
		private		$_charset	=	'utf-8';
		private		$_data;
		private		$_content;
		
		public	function __construct(array $config = array(), $data = array())
		{
			$this->_config	=	array_merge($this->_config, $config);
			$this->_data	=	$data;
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
		final	public	function data($name = NULL, $value = NULL)
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
					:	NULL;
			}
		}
		
		/*
			Renvoit le type de la vue (html, json...)
			@return		string
		*/
		final	public	function type()
		{
			return	$this->_type;
		}
		
		/*
			Renvoit le type mime de la vue (text/html, application/json...)
			@return		string
		*/
		final	public	function mime()
		{
			return	$this->_mime;
		}
		
		/*
			Définit/renvoit le contenu de la vue
			@param		string		$content	Le contenu de la vue
			@return		string
		*/
		final	public	function content($content = NULL)
		{
			if(!is_null($content))
				$this->_content	=	(string)$content;
			return	$this->_content;
		}
		
		/*
			Définit/renvoit le charset de la vue
			@param		string		$charset	Le charset de la vue
			@return		string
		*/
		final	public	function charset($charset = NULL)
		{
			if(!is_null($charset))
				$this->_charset	=	(string)$charset;
			return	$this->_charset;
		}
		
		/*
			Affiche la vue
			@return		void
		*/
		final	public	function render()
		{
			header('content-type: '.$this->_mime.'; charset='.$this->_charset);
			echo	$this->_content;
		}
	}

?>