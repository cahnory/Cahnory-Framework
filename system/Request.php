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
	class Cahnory_Request
	{
		private	$_uri;
		private	$_base;
		private	$_host;
		private	$_protocol;
		
		private	$_root;
		private	$_route;
		private	$_format;
		private	$_isAjax;
		private	$_isCLI;
		private	$_method;
		
		private	$_get;
		private	$_post;
		private	$_files;
		private	$_input;
		private	$_args;	//	CLI arguments
		
		public	function	__construct($server = NULL, $get = NULL, $post = NULL, $files = NULL, $args = NULL)
		{
			$server			=	$server	=== NULL ? $_SERVER	: array_merge($_SERVER, $server);
			$get			=	$get	=== NULL ? $_GET	: $get;
			$post			=	$post	=== NULL ? $_POST	: $post;
			$files			=	$files	=== NULL ? $_FILES	: $files;
			$this->_args	=	$args	=== NULL ? $server['argv']	: $args;
			
			$this->_isAjax	=	isset($server['HTTP_X_REQUESTED_WITH'])
					       	&&	$server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
					       	
			$this->_isCLI	=	php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']);
			
			//$this->_uri			=	urldecode($server['REQUEST_URI']);
			$this->_uri			=	'/'.trim(isset($server['REDIRECT_URL']) ? $server['REDIRECT_URL'] : $server['REQUEST_URI'], '/');//On test
			if($this->_isCLI) {
				$this->_base	=	'/';
			} else {
				$this->_base	=	trim(dirname($server['SCRIPT_NAME']),'/');
				if(strlen($this->_base))	$this->_base	.=	'/';
			}
			$this->_host		=	$server['HTTP_HOST'];
			$this->_protocol	=	(substr($server['SERVER_PROTOCOL'], 0, 5) == 'https' ? 'https' : 'http').'://';
			$this->_method		=	$server['REQUEST_METHOD'];			
			if(get_magic_quotes_gpc()) {
				$this->_get		=	self::removeMagicQuotes($get);
				$this->_post	=	self::removeMagicQuotes($post);
			} else {
				$this->_get		=	$get;
				$this->_post	=	$post;
			}
			$this->_files	=	self::normalizeFilesArray($files);
			$this->_input	=	array_merge($this->_get,$this->_post,$this->_files);
	        $this->_parseURI();
		}
		
		/*
			Définie le chemin de l'application
		*/
		public	function	setBase($base)
		{
			$this->_base	=	$base;
	        $this->_parseURI();
			return	$this;
		}
		
		/*
			Renvoie le chemin de l'application
		*/
		public	function	getBase($full = false)
		{
			return	$full ? $this->_protocol.$this->_host.'/'.$this->_base : $this->_base;
		}
		
		/*
			Renvoie la méthode
		*/
		public	function	getMethod()
		{
			return	$this->_method;
		}
		
		/*
			Renvoie le chemin de l'uri
		*/
		public	function	getRoute()
		{
			return	$this->_route;
		}
		
		/*
			Renvoie le format de l'uri
		*/
		public	function	getFormat($alt = NULL)
		{
			return	$this->_format === NULL ? $alt : $this->_format;
		}
		
		public	function	getRoot()
		{
			return	$this->_root;
		}
		
		/*
			Renvoie le chemin de l'application
		*/
		public	function	getURI()
		{
			return	$this->_uri;
		}
		
		/*
			Renvoie l'état ajax
		*/
		public	function	isAjax()
		{
			return	$this->_isAjax;
		}
		
		/*
			Détermine si php s'exécute en Ligne de commande
		*/
		public	function	isCLI()
		{
			return	$this->_isCLI;
		}
		
		/*
			Renvoie true si la méthode est la bonne
		*/
		public	function	isMethod($method)
		{
			return	$this->_method == strtoupper($method);
		}
		
		/*
			Renvoie la valeur associé à un sélecteur dans le tableau get
		*/
		public	function	&get($selectors = NULL, $useNext = true, $create = true)
		{
			return	self::_arrayThru($selectors, $this->_get, $useNext);
		}
		
		/*
			Renvoie la valeur associé à un sélecteur dans le tableau post
		*/
		public	function	&post($selectors = NULL, $useNext = true, $create = true)
		{
			return	self::_arrayThru($selectors, $this->_post, $useNext);
		}
		
		/*
			Renvoie la valeur associé à un sélecteur dans le tableau file
		*/
		public	function	&file($selectors = NULL, $useNext = true, $create = true)
		{
			return	self::_arrayThru($selectors, $this->_files, $useNext);
		}
		
		/*
			Renvoie la valeur associé à un sélecteur dans le tableau input
		*/
		public	function	&input($selectors = NULL, $useNext = true, $create = true)
		{
			return	self::_arrayThru($selectors, $this->_input, $useNext);
		}
		
		private	function	_parseURI()
		{
			$uri	=	explode('?', $this->_uri, 2);
			$uri	=	substr($uri[0], strlen($this->_base));
			if(strlen($uri) === 0)
				return	array(
					'route'		=>	NULL,
					'format'	=>	NULL
				);
			preg_match(
				'#^(.*(?=(\.([^./]+)$))|(.(?!(\.(([^./]+)$))|/$|$))*.)#',
				$uri,
				$m
			);
			$this->_route	=	trim($m[1] || $m[1] === 0	?	$m[1]	:	NULL, '/');
			$this->_format	=	$m[3] || $m[3] === 0	?	$m[3]	:	NULL;
		}
		
		/*
			Retire les slashs de toute les valeurs d'un array récursivement
		*/
		static	private	function	removeMagicQuotes($array) {
			if(!is_array($array))	return	stripslashes($array);
			foreach($array as $key => $value) {
				$array[$key]	=	self::removeMagicQuotes($value);
			}
			return	$array;
		}
		
		/*
			Armonise l'arborescence du tableau $_FILES
			sur le model de $_POST, $_GET
		*/
		private	static	function normalizeFilesArray() {		
			if(empty($_FILES))	return	$_FILES;
				
			$output	=	array();		
			foreach($_FILES as $parent => $file) {
				$output[$parent]	=	array();
				foreach($file as $attr => $tree) {
					$cursor				=	&$output[$parent];
					while(is_array($tree)) {
						$key	=	key($tree);
						$tree	=	$tree[$key];
						if(!array_key_exists($key, $cursor)) {
							$cursor[$key]	=	array();
						}
						$cursor			=	&$cursor[$key];
					}
					$cursor[$attr]	=	$tree;
				}
			}
			return	$output;
		}
		
		/*
			Parcour un tableau à l'aide de sélecteur correspondant aux valeurs
			possibles de l'attribut html name
			Si useNext vaut true la recherche se poursuivra aux enfant des
			tableaux non indexés.
			@param	$selectors	string		Sélecteur
			@param	$a			array		le tableau à traverser
			@param	$useNext	boolean	
			@param	$create		boolean		Crée automatiquement les clé recherchées
		*/
		private	static	function &_arrayThru($selectors, &$a, $useNext = true, $create = true) 
		{ 
			//	passage de name[name2] vers array('name','name2'); 
			if(is_string($selectors)) 
				$selectors	=	explode('[',str_replace(']','',$selectors)); 
			
			if(!sizeof($selectors)) 
				return	$a; 
			
			//	On récupère la première clé 
			$selector	=	array_shift($selectors); 
			
			if(isset($a[$selector])) 
				return	self::_arrayThru($selectors, $a[$selector], $useNext, $create); 
			
			//	Recherche dans les sous array name[0][name2], name[1][name2]... 
			$set	 =	null; 
			if($useNext && isset($a[0][$selector])){ 
				$set	=	array(); 
				foreach($a as $k => $v){ 
					$e	=&	self::_arrayThru($selectors, $a[$k][$selector], $useNext, $create); 
					if(!is_array($e)){ 
						$set[]	=	&$e; 
						continue; 	
					} 
					foreach($e as $n => $v){
						$set[]	=	&$e[$n]; 
					} 
				} 
			} elseif($create) {
				$a[$selector]	=	sizeof($selectors) === 0
								?	NULL
								:	array();
				return	self::_arrayThru($selectors, $a[$selector], $useNext, $create);
			}
			
			return	$set; 
		}
	}

?>