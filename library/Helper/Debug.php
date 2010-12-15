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
	class Helper_Debug
	{
		private	$_registrars	=	array();
		
		public	function	__construct()
		{
			ini_set('display_errors', 0);
		}
		
		public	function	addRegistrar(Helper_Debug_Registrar $registrar)
		{
			$this->_registrars[]	=	$registrar;
		}
		
		public	function	displayFatalError($display = true)
		{
			ini_set('display_errors', (int)$display);
		}
		
		public	function	errorHandler($level, $string, $file, $line)
		{
			foreach($this->_registrars as $registrar) {
				$registrar->registerError($level, $string, $file, $line);
			}
		}
		
		public	function	handleError($report = NULL)
		{
			error_reporting($report === NULL ? E_ALL | E_STRICT : $report);
			set_error_handler(array($this, 'errorHandler'));
			register_shutdown_function(array($this, 'fatalErrorHandler'));
		}
		
		public	function	fatalErrorHandler()
		{
		    if (($error = error_get_last()) && $error['type'] == 1) {
				foreach($this->_registrars as $registrar) {
					$registrar->registerFatalError($error['type'], $error['message'], $error['file'], $error['line']);
				}
				exit();
			}
		}
		
		public	function	trace()
		{
			foreach($this->_registrars as $registrar) {
				$datas	=	func_get_args();
				call_user_func_array(array($registrar, 'trace'), $datas);
			}
		}
		
		public	function	triggerError()
		{
			
		}
	}
	
	interface	Helper_Debug_Registrar
	{
		public	function	registerError($level, $string, $file, $line);		
		public	function	registerFatalError($level, $string, $file, $line);
		public	function	trace();
	}

?>