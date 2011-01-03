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
	 * @subpackage Library
	 * @category   Helper
	 * @author     François 'cahnory' Germain <cahnory@gmail.com>
	 * @copyright  2010 François Germain
	 * @license    http://www.opensource.org/licenses/mit-license.php
	 */
	class Helper_File
	{
		static	private	$_root;
		static	private	$_base;
		
		public	function __construct(Cahnory $system)
		{
			self::$_root	=	$system->appPath();
			self::$_base	=	trim(substr($system->appPath(), strlen($system->rootPath())), DIRECTORY_SEPARATOR);
		}
		
		static	public	function exists($filename)
		{
			return @fopen($filename,"r") !== false;
		}
		
		static	public	function create($filename, $erase = true)
		{
			if((is_file($filename) || is_dir($filename)) && $erase)
			{
				self::delete($filename);
			}
			if(substr($filename, -1) == '/'){
				mkdir($filename, 0755);
				chmod($filename, 0755);
			}else{
				return fopen($filename, 'w+');
			}
		}
		
		static	public	function createDir($path)
		{
			$path	=	explode('/', rtrim($path,'/'));
			$dir	=	NULL;
			while($path){
				$dir	.=	current(array_splice($path,0,1)).'/';
				if(!is_dir($dir)){
					mkdir($dir, 0755);
					chmod($dir, 0755);
				}
			}
			return true;
		}
		
		static	public	function save($filename, $content)
		{
			if(!is_dir(dirname($filename))) self::createDir(dirname($filename));
			$file = fopen($filename, "w+");
			fwrite($file, $content);
			fclose($file);
			return true;
		}
		
		static	public	function rename($filename, $newname, $use_same_extension = false, $smash = false)
		{
			if(!is_file($filename) && !is_dir($filename))	return false;
			$fullname	=	$filename;
			$dirname	=	dirname($filename);
			$filename	=	basename($filename);
			$newfile	=	$dirname.'/'.$newname;
			if($use_same_extension){
				$newfile .= '.'.self::getExtension($filename);
			}
			if(is_file($newfile) || is_dir($newfile)){
				if($smash){
					unlink($newfile);
				}else{
					return false;
				}
			}
			return rename($fullname, $newfile);
		}
		
		/*public	function	delete($filename)
		{
			if(is_file($filename)){
				unlink($filename);
				return	true;
			}else{
				rmdir($filename);
				return	true;
			}
			return	false;
		}*/
		
		static	public	function delete($dir) {
			if(is_file($dir)){
				unlink($dir);
				return true;
			}
			$files = glob( $dir . '*', GLOB_MARK ); 
			foreach( $files as $file ){ 
				if( substr( $file, -1 ) == '/' ){
					self::delete( $file ); 
				}else{
					unlink( $file ); 
				}			
			}
			if (is_dir($dir)) rmdir( $dir ); 
			return true;
		} 
		
		static	public	function copy($from, $to, $smash = false, $dirSmash = false)
		{
			if(!$smash && is_file($to))	return	false;
			if(is_file($from)){
				if(is_file($to) && $smash) {
					unlink($to);
				}
				copy($from, $to);
				return	true;
			}else if(is_dir($from)){
				$from		=	rtrim($from,'/').'/';
				$to			=	rtrim($to,'/').'/';
				if(!is_dir($to)) {
					mkdir($to, 0755);
				} elseif($dirSmash) {
					rmdir($to);
					mkdir($to, 0755);
				}
				chmod($to, 0755);
				$files		=	self::listAll($from);
				$succeed	=	true;
				foreach($files as $file){
					if(!self::copy($from.$file,$to.$file,$smash,$dirSmash))	$succeed	=	false;
				}
				return $succeed;
			} else if(filter_var($from, FILTER_VALIDATE_URL) && $str = file_get_contents($from)) {
				self::save($to, $str);
				return true;
			} else {
				return	false;
			}
		}
		
		static	public	function slice($filename, $content)
		{
			$file = fopen($filename, "r+");
			fputs($file, $content);
			fclose($file);
			return true;
		}
		
		static	public	function put($filename, $content)
		{
			$file = fopen($filename, "a+");
			fputs($file, $content);
			fclose($file);
			return true;
		}
		
		static	public	function listFolders($path, $pattern = '*')
		{
			$path	=	rtrim($path,'/').'/';
			$output	=	array();
			$start	=	strlen($path);
			
			foreach(glob($path.$pattern, GLOB_ONLYDIR) as $filename){
				$output[]	=	substr($filename, $start);
			}
			return $output;
		}
		
		static	public	function listFiles($path, $pattern = '*', $flag = GLOB_BRACE)
		{
			$path	=	rtrim($path,'/').'/';
			$output	=	array();
			$start	=	strlen($path);
			
			foreach(glob($path.$pattern, $flag) as $filename){
				if(is_file($filename)){
					$output[]	=	substr($filename, $start);
				}
			}
			return $output;
		}
		
		static	public	function listAll($path, $pattern = '*', $flag = GLOB_BRACE)
		{
			$path	=	rtrim($path,'/').'/';
			$output	=	array();
			$start	=	strlen($path);
			
			foreach(glob($path.$pattern, $flag) as $filename){
				$output[]	=	substr($filename, $start);
			}
			return $output;
		}
		
		static	public	function nextFreeFilename($filename, $glue = '-')
		{
			if(is_file($filename)) {
				$extension	=	self::getExtension($filename);
				if($extension === '') {
					$extension	=	'.'.$extension;
				}
				$base		=	self::removeExtension($filename);
				$i			=	1;
				while(is_file($filename)) {
					$filename	=	$base.$glue.$i.$extension;
					$i++;
				}
			}
			return	self::removePath($filename);
		}
		
		static	public	function removeExtension($filename)
		{
			return preg_replace('#(.)(\.[^.]+$)#','$1', $filename);
		}
		
		static	public	function removePath($filename)
		{
			return preg_replace('#(.+/)?([^/]+$)#','$2', $filename);
		}
		
		static	public	function getExtension($filename)
		{
			if(strpos($filename,'.') === false)
				return	NULL;
			return	preg_replace('#^(.*)\.([^.]+)$#','$2', $filename);
		}
		
		static	public	function relativize($link)
		{
			$link	=	preg_replace('#^'.preg_quote(self::$_root).'#','',$link);
			$link	=	explode('/', trim($link,'/'));
			$base	=	explode('/', self::$_base);
			for($i = 0; isset($base[$i]) && isset($link[$i]) && $base[$i] === $link[$i]; $i++);
			return str_repeat('../', sizeof($base) - $i).implode('/',array_slice($link,$i));
		}
	}

?>