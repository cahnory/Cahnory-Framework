<?php

	class Cahnory_Library
	{
		private	$_path		=	array();
		private	$_helpers	=	array();
		
		public	function	__call($name, $args)
		{
			if($name == 'class') {
				return	call_user_func_array(array($this, 'autoLoad'), $args);
			}
			if(isset($this->_helpers[$name])) {
				return	call_user_func_array($this->_helpers[$name], $args);
			}
		}
		
		public	function	help($name, $function)
		{
			$this->_helpers[$name]	=	$function;
		}
		
		public	function	autoLoad($name, $base = NULL, $prefix = NULL)
		{
			$output	=	true;
			if(is_array($name)) {
				$names	=	$name;
				$output	=	array();
				foreach($names as $name) {
					if(!$this->autoLoad($name, $base)) {
						$ouput	=	false;
					}
				}
			} else {
				$name	=	$prefix.$name;
				if(!class_exists($name)) {
					if($file = $this->getFile($base.(str_replace('_', DIRECTORY_SEPARATOR, $name)).'.php')) {
						require_once	$file;
						$output	=	class_exists($name);
					} else {
						$output	=	false;
					}
				}
			}
			return	$output;
		}
		
		public	function	getAllDir($dirnames)
		{
			if(!is_array($dirnames)) {
				$dirnames	=	func_get_args();
			}
			$output	=	array();
			foreach($this->_path as $path) {
				foreach($dirnames as $dirname) {
					if(is_dir($path.$dirname)) {
						$output[]	=	$path.$dirname;
					}
				}
			}
			return	$output;
		}
		
		public	function	getDir($dirname)
		{
			$output	=	NULL;
			if(func_num_args() > 1) {
				$dirnames	=	func_get_args();
				$output		=	array();
				foreach($dirnames as $dirname) {
					$output[]	=	$this->getFile($dirname);
				}
			} else {
				$dirnames	=	(array)$dirname;
				foreach($dirnames as $key => $dirname) {
					$dirname[$key]	=	ltrim($dirname, DIRECTORY_SEPARATOR);
				}
				foreach($this->_path as $path) {
					foreach($dirnames as $dirname) {
						if(is_dir($path.$dirname)) {
							$output	=	$path.$dirname;
							break	2;
						}
					}
				}
				if($output === NULL) {
					foreach($dirnames as $dirname) {
						if(is_dir($dirname)) {
							$output	=	$dirname;
							break;
						}
					}
				}
			}
			return	$output;
		}
		
		public	function	getFile($filename)
		{
			$output	=	NULL;
			if(func_num_args() > 1) {
				$filenames	=	func_get_args();
				$output		=	array();
				foreach($filenames as $filename) {
					$output[]	=	$this->getFile($filename);
				}
			} else {
				$filenames	=	(array)$filename;
				foreach($filenames as $key => $filename) {
					$filename[$key]	=	ltrim($filename, DIRECTORY_SEPARATOR);
				}
				foreach($this->_path as $path) {
					foreach($filenames as $filename) {
						if(is_file($path.$filename)) {
							$output	=	$path.$filename;
							break	2;
						}
					}
				}
				if($output === NULL) {
					foreach($filenames as $filename) {
						if(is_file($filename)) {
							$output	=	$filename;
							break;
						}
					}
				}
			}
			return	$output;
		}
		
		public	function	getFile($filename)
		{
			$filename	=	ltrim($filename,DIRECTORY_SEPARATOR);
			foreach($this->_path as $path) {
				if(is_file($path.$filename)) {
					return	$path.$filename;
				}
			}
			return	is_file($filename) ? $filename : NULL;
		}
		
		public	function	getFiles($filename)
		{
			$output		=	array();
			$filenames	=	func_get_args();
			foreach($filenames as $k => $filename) {
				$filenames[$k]	=	$this->getFile($filename);
			}
			return	$filenames;
		}
		
		public	function	addPath($path)
		{
			$path	=	rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			
			//	Remove same path
			$this->removePath($path);
			
			//	Put path in first position
			if(is_dir($path)) {
				array_unshift($this->_path, $path);
				return true;
			}
			return	false;
		}
		
		public	function	removePath($path)
		{
			$path	=	rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			
			if(($key = array_search($path, $this->_path)) !== false) {
				array_splice($this->_path, $key, 1);
				return	true;
			}
			return	false;
		}
		
		public	function	swapPath($old, $new)
		{
			$old	=	rtrim($old, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			$new	=	rtrim($new, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			
			if(($oldKey = array_search($old, $this->_path)) !== false) {
				$simKey	=	array_search($new, $this->_path);
				$this->_path[$oldKey]	=	$new;
				if($simKey !== false) {
					array_splice($this->_path, $simKey, 1);
				}
				return	true;
			}
			return	false;
		}
		
		public	function	addPathAfter($ref, $path) {
			$ref	=	rtrim($ref, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			$path	=	rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			
			if(($refKey = array_search($ref, $this->_path)) !== false) {
				array_splice($this->_path, $refKey + 1, 0, $path);
				return true;
			}
			return	false;
		}
		
		public	function	addPathBefore($ref, $path) {
			$ref	=	rtrim($ref, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			$path	=	rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			
			if(($refKey = array_search($ref, $this->_path)) !== false) {
				array_splice($this->_path, $refKey, 0, $path);
				return true;
			}
			return	false;
		}
		
		/* !Specials */		
		public	function	helper($names)
		{
			return	$this->autoLoad($names, 'library'.DIRECTORY_SEPARATOR, 'Helper_');
		}
	}

?>