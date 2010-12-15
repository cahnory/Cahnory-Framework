<?php

	class Cache
	{
		private	$_path;
		private	$_extension;
		
		public	function setPath($dirname)
		{
			$this->_path	=	trim($dirname,'/').'/';
			return $this;
		}
		
		public	function save($id, $content)
		{
			$file = fopen($this->getFileName($id), "w+");
			fwrite($file, $content);
			fclose($file);
			return $this;
		}
		
		public	function	isCached($id)
		{
			return is_file($this->getFileName($id));
		}
		
		public	function getFile($id)
		{
			$filename	=	$this->getFileName($id);
			if(is_file($filename))
				return $filename;
			return false;
		}
		
		public	function getFileName($id)
		{
			$filename	=	$this->_path.$id;
			$filename	.=	$this->_extension && $this->_extension !== 0 ? '.'.$this->_extension : NULL;
			return $filename;
		}
		
		public	function setExtension($extension)
		{
			$this->_extension	=	$extension;
			return $this;
		}
		
		public	function getExtension($extension)
		{
			return $this->_extension;
		}
	}

?>