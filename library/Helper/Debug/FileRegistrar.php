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
	 * @category   Helper_Debug
	 * @author     François 'cahnory' Germain <cahnory@gmail.com>
	 * @copyright  2010 François Germain
	 * @license    http://www.opensource.org/licenses/mit-license.php
	 */
	class	Helper_Debug_FileRegistrar	implements	Helper_Debug_Registrar
	{
		private	$_format;
		private	$_filename;
		private	$_xdebugDump;
		
		public	function	__construct($filename, $format = 'comfort')
		{
			$this->_format		=	$format;
			$this->_filename	=	$filename;
			
			date_default_timezone_set(date_default_timezone_get());
		}
		
		public	function	registerError($level, $string, $file, $line)
		{
			return	$this->_register($this->_format($level, $string, $file, $line));
		}
		
		public	function	registerFatalError($level, $string, $file, $line)
		{
			//	Nothing to do, can't write on files
		}
		
		public	function	trace()
		{
			$args	=	func_get_args();
			ob_start();
			foreach($args as $argument) {
				var_dump($argument);
			}
			$dump	=	ob_get_clean();
			if($this->_xdebugDump || $this->_xdebugDump = (substr($dump, 12, 15) == 'xdebug-var-dump')) {
				$dump	=	strip_tags($dump);
			}			
			return	$this->_register($dump."\r\n");
		}
		
		private	function	_register($string)
		{
			$handle	=	fopen($this->_filename, "a+");
			
			//	Ouverture/création du fichier impossible
			if(!$handle)	return false;
						
			$output	=	fwrite($handle, $string);
			fclose($handle);
			return	$output !== false;
		}
		
		private	function	_format($level, $string, $file, $line)
		{
			switch($this->_format) {
				case 'json':
					return	$this->_jsonFormat($level, $string, $file, $line);
					break;
				case 'comfort':
					return	$this->_comfortFormat($level, $string, $file, $line);
					break;
			}
			return	$this->_logFormat($level, $string, $file, $line);
		}
		
		private	function	_jsonFormat($level, $string, $file, $line)
		{
			return	json_encode(array(
				'date'		=>	date(DATE_W3C),
				'level'		=>	$level,
				'string'	=>	$string,
				'file'		=>	$file,
				'line'		=>	$line
			))."\r\n";
		}
		
		private	function	_logFormat($level, $string, $file, $line)
		{
			return	date(DATE_W3C).' Level='.$level.' file='.$file.' line='.$line.' string='.$string."\r\n";
		}
		
		private	function	_comfortFormat($level, $string, $file, $line)
		{
			return	date(DATE_W3C)."\r\nLevel=".$level."\r\nfile=".$file."\r\nline=".$line."\r\nstring=".$string."\r\n\r\n";
		}
	}

?>