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
	class Cahnory_Router
	{
		private	$_binds				=	array();
		private	$_route;
		private	$_routeLength;
		public	$mainFormat;

		/*
		
			Pattern URL with format :		.*(?=(\.([^./]+)$))
			Pattern URL without format :	(.(?!(\.(([^./]+)$))|/$|$))*.
		*/
		public	function	__construct($route)
		{
			$route	=	trim($route, '/');
			$this->_route		=	$route != '' ? $route : '*/index';
			$this->_routeLength	=	substr_count($this->_route, '/') + 1;
			if($this->_routeLength == 1) {
				$this->_route	.=	'/index';
				$this->_routeLength++;
			}
		}
		
		public	function	bind($bind, $route = NULL)
		{
			if($route === NULL)
				$this->_binds['*']		=	$bind;
			else
				$this->_binds[$bind]	=	$route;
			return	$this;
		}
		
		public	function	getRouteLength()
		{
			return	$this->_routeLength;
		}
		
		public	function	getURL($url = NULL, $format = NULL)
		{
			if($url === NULL) {
				$url	=	$this->_route;
			}
			if($format === NULL)	$format	=	$this->mainFormat;
			$url	=	trim($url, '/');
			if(preg_match('#^([^?]*)\.([^./]+)(\?.*)?#',$url,$m)) {
				$url	=	$this->getBind($m[1]);
				$format	=	$m[2];
			}
			$url	=	$this->getBind($url);
			if($format || $format === 0) {
				$url	.=	'.'.$format;
			}
			return	$url;
		}
		
		public	function	getSubURL($url, $format = NULL)
		{
			if($format === NULL)	$format	=	$this->mainFormat;
			return	$this->getURL($this->getRoutePath().'/'.ltrim($url,'/'), $format);
		}
		
		public	function	getSupURL($format = NULL, $back = 1, $url = NULL)
		{
			if($url !== NULL) {
				$url	=	'/'.ltrim($url,'/');
			}
			if($format === NULL)	$format	=	$this->mainFormat;
			return	$this->getURL($this->getRoutePath(0, $this->_routeLength - $back).$url, $format);
		}
		
		public	function	getLevelURL($url = NULL, $format = NULL)
		{
			if($url !== NULL) {
				$url	=	'/'.ltrim($url,'/');
			}
			if($format === NULL)	$format	=	$this->mainFormat;
			return	$this->getURL($this->getRoutePath(0, $this->_routeLength - 1).$url, $format);
		}
		
		public	function	getModuleURL($url = NULL, $format = NULL)
		{
			if($url !== NULL) {
				$url	=	'/'.ltrim($url,'/');
			}
			if($format === NULL)	$format	=	$this->mainFormat;
			return	$this->getURL($this->getRoutePath(0, 1).$url, $format);
		}
		
		public	function	getBind($route = NULL)
		{
			if($route === NULL)
				$route	=	$this->_route;
				
			if($binds = array_keys($this->_binds, $route)) {
				foreach($binds as $key => $bind) {
					if($bind != '*')	break;
				}
				return	$this->getBind($bind);
			}
			if($split = strrpos($route, '/')) {
				return	$this->getBind(current(str_split($route, $split))).substr($route, $split);
			}
			if($route == '*') {
				$route = array_key_exists('*',$this->_binds) ? $this->_binds['*'] : NULL;
			}
			return	$route;
		}
		
		public	function	getBindCrumb($start = NULL, $length = NULL)
		{
			$bindCrumbs	=	explode('/', $this->getRoute());
			if($start !== NULL)
				$bindCrumbs	=	array_slice($bindCrumbs, $start, $length);
			return	$bindCrumbs;
		}
		
		public	function	getRoute($bind = NULL, $tested = array())
		{
			if($bind === NULL)
				$bind	=	$this->_route;
			$tested[]	=	$bind;
			if(isset($this->_binds[$bind])){
				return	$this->getRoute($this->_binds[$bind], $tested);
				
			}
			if($split = strrpos($bind, '/'))
				$bind	=	$this->getRoute(current(str_split($bind, $split)), $tested).substr($bind, $split);
			if(!in_array($bind, $tested))
				$bind	=	$this->getRoute($bind, $tested);				
			if($bind == '*')
				$bind	=	NULL;
			return	$bind;
		}
		
		public	function	getRouteCrumb($start = NULL, $length = NULL)
		{
			$route			=	$this->getRoute();
			if($route === NULL)	return	NULL;
			
			$routeCrumbs	=	explode('/', $this->getRoute());
			if($start !== NULL)
				$routeCrumbs	=	array_slice($routeCrumbs, $start, $length);
			return	$routeCrumbs;
		}
		
		public	function	getRoutePath($start = NULL, $length = NULL)
		{
			$crumbs	=	$this->getRouteCrumb();
			if($crumbs === NULL || $start >= sizeof($crumbs))	return	NULL;
			if($start !== NULL)
				$crumbs	=	array_slice($crumbs, $start, $length);
			return	implode('/', $crumbs);
		}
	}

?>