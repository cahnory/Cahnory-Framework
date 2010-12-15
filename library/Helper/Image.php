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
	class	Helper_Image
	{
		static	public	function	crop(&$image, $width, $height = NULL, $x = 0, $y = 0)
		{
			if($height === NULL) {
				$height	=	$width;
			}
			$newImage	=	imagecreatetruecolor($width, $height);
			if(imagecopyresampled($newImage, $image, 0, 0, $x, $y, $width, $height, $width, $height)) {
				$image	=	$newImage;
				return	true;
			} else {
				return	false;
			}
		}
		
		static	public	function	cropHeight(&$image, $height, $y = 0)
		{
			return	self::crop($image, imagesx($image), $height, 0, $y);
		}
		
		static	public	function	cropWidth(&$image, $width, $x = 0)
		{
			return	self::crop($image, $width, imagesy($image), $x, 0);
		}
		
		static	public	function	centerCrop(&$image, $width, $height)
		{
			return	self::crop($image, $width, $height, round((imagesx($image) - $width) / 2), round((imagesy($image) - $height) / 2));
		}
		
		static	public	function	getDataURI($image, $encoding = 'base64', $type = NULL)
		{
			$data	=	'data:';
			//	Image type and content
			if(is_string($image)) {
				//	Using filename
				if(!is_file($image))	return	false;
				$content	=	file_get_contents($image);
				if($type === NULL) {
					$type	=	exif_imagetype($image);
					switch($type) {
						case	1:
							$type	=	'gif';
							break;
						case	3:
							$type	=	'png';
							break;
						case	15:
							$type	=	'wbmp';
							break;
						case	16:
							$type	=	'xbm';
							break;
						default:
							$type	=	'jpeg';
							break;
					}
				}
			} else {
				//	Using ressource
				ob_start();
				switch($type) {
					case	'gif':
						$type	=	'gif';
						imagegif($image);
						break;
					case	'png':
						$type	=	'png';
						imagepng($image);
						break;
					case	'wbmp':
						$type	=	'wbmp';
						imagewbmp($image);
						break;
					case	'xbm':
						$type	=	'xbm';
						imagexbm($image);
						break;
					default:
						$type	=	'jpeg';
						imagejpeg($image);
						break;
				}
				$content	=	ob_get_clean();
			}
			
			$data	.=	'image/'.$type;
			
			//	Encoding
			switch($encoding) {
				case	'base64':
					$content	=	base64_encode($content);
					$data		.=	';base64';
					break;
			}
			
			return	$data.','.$content;
		}
		
		static	public	function	load($filename, $object = true)
		{
			//	Le fichier est introuvable
			if(!is_file($filename)) return	false;
			
			//	Chargement de l'image selon son type
			$type	=	exif_imagetype($filename);
			switch($type) {
				case	1:
					$image	=	imagecreatefromgif($filename);
					$type	=	'gif';
					break;
				case	2:
					$image	=	imagecreatefromjpeg($filename);
					$type	=	'jpeg';
					break;
				case	3:
					$image	=	imagecreatefrompng($filename);
					$type	=	'png';
					break;
				case	15:
					$image	=	imagecreatefromwbmp($filename);
					$type	=	'wbmp';
					break;
				case	16:
					$image	=	imagecreatefromxbm($filename);
					$type	=	'xbm';
					break;
			}
			
			//	Type d'image non pris en charge
			if(!isset($image))	return	false;
			
			if($object) {
				$image	=	Helper_Image_Object::createFromResource($image);
				$image->defaultType($type);
			}
			
			return	$image;			
		}
		
		static	public	function	resize(&$image, $width, $height)
		{
			$newImage	=	imagecreatetruecolor($width, $height);
			if(imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image))) {
				$image	=	$newImage;
				return	true;
			} else {
				return	false;
			}
		}
		
		static	public	function	sizeLimit(&$image, $width, $height)
		{
			$coefX	=	$width / imagesx($image);
			$coefY	=	$height / imagesy($image);
			$coef	=	$coefX < $coefY ? $coefX : $coefY;
			return	self::resize($image, imagesx($image) * $coef, imagesy($image) * $coef);
		}
		
		static	public	function	scale(&$image, $coef, $type = NULL)
		{
			switch($type) {
				case 'width':
					$width	=	$coef;
					$height	=	round(imagesy($image) * ($coef / imagesx($image)));
					break;
				case 'height':
					$width	=	round(imagesx($image) * ($coef / imagesy($image)));
					$height	=	$coef;
					break;
				default:
					$width	=	round(imagesx($image) * $coef);
					$height	=	round(imagesy($image) * $coef);
					break;
			}
			return	self::resize($image, $width, $height);
		}
		
		static	public	function	rotate(&$image, $angle, $backgroundColor = 0, $ignoreAlpha = 0)
		{
			if($newImage = imagerotate($image, -$angle%360, $backgroundColor, $ignoreAlpha)) {
				$image	=	$newImage;
				return	true;
			} else {
				return	false;
			}
		}
		
		static	public	function	save($image, $filename)
		{
			if(preg_match('#(?<=[^./]\.)[^./]+$#', $filename, $extension)) {
				$extension	=	strtolower($extension[0]);
			} else {
				$extension	=	'jpeg';
			}
			switch($extension) {
				case 'gif':
					if(imagegif($image, $filename))	return	'gif';
					break;
				case 'png':
					$args	=	func_get_args();
					if(call_user_func_array('imagepng', $args))		return	'png';
					break;
				case 'wbmp':
					$args	=	func_get_args();
					if(call_user_func_array('imagewbmp', $args))	return	'wbmp';
					break;
				case 'xbm':
					$args	=	func_get_args();
					if(call_user_func_array('imagexbm', $args))		return	'xbm';
					break;
				default:
					$args	=	func_get_args();
					if(call_user_func_array('imagejpeg', $args))	return	'jpeg';
					break;
			}
			return	false;
		}
	}
	
	/*
	 * @package    Cahnory
	 * @subpackage Library
	 * @category   Helper
	 * @author     François 'cahnory' Germain <cahnory@gmail.com>
	 * @copyright  2010 François Germain
	 * @license    http://www.opensource.org/licenses/mit-license.php
	 */
	class	Helper_Image_Object
	{
		private	$_defaultType;
		private	$_resource;
		
		private	function	__construct($resource)
		{
			$this->_resource	=	$resource;
		}
		
		public	function	height()
		{
			return	imagesy($this->_resource);
		}
		
		public	function	pixels()
		{
			return	imagesx($this->_resource) * imagesy($this->_resource);
		}
		
		public	function	width()
		{
			return	imagesx($this->_resource);
		}
		
		static	public	function	createFromResource($resource)
		{
			if(is_resource($resource) && get_resource_type($resource) == 'gd') {
				return	new	self($resource);
			} else {
				return false;
			}
		}
		
		static	public	function	createFromFile($filename)
		{
			return	Helper_Image::load($filename);
		}
		
		public	function	crop($width, $height = NULL, $x = 0, $y = 0)
		{
			return	Helper_Image::crop($this->_resource, $width, $height, $x, $y);
		}
		
		public	function	cropWidth($width, $x = 0)
		{
			return	Helper_Image::cropWidth($this->_resource, $width);
		}
		
		public	function	cropHeight($height, $y = 0)
		{
			return	Helper_Image::cropHeight($this->_resource, $height);
		}
		
		public	function	centerCrop($width, $height)
		{
			return	Helper_Image::centerCrop($this->_resource, $width, $height);	
		}
		
		public	function	defaultType()
		{
			if($type = func_get_arg(0)) {
				$this->_defaultType	=	$type;
			}
			return	$this->_defaultType;
		}
		
		public	function	getDataURI($encoding = 'base64', $type = NULL)
		{
			if($type === NULL)	$type	=	$this->_defaultType;
			return	Helper_Image::getDataURI($this->_resource, $encoding, $type);
		}
		
		public	function	resize($width, $height)
		{
			return	Helper_Image::resize($this->_resource, $width, $height);
		}
		
		public	function	scale($coef, $type = NULL)
		{
			return	Helper_Image::scale($this->_resource, $coef, $type);
		}
		
		public	function	rotate($angle, $backgroundColor = 0, $ignoreAlpha = 0)
		{
			return	Helper_Image::rotate($this->_resource, $angle, $backgroundColor, $ignoreAlpha);
		}
		
		public	function	save($filename)
		{
			if($type = Helper_Image::save($this->_resource, $filename)) {
				$this->_defaultType	=	$type;
			}
			return	$type;
		}
		public	function	sizeLimit($width, $height)
		{
			return	Helper_Image::sizeLimit($this->_resource, $width, $height);
		}
	}

?>