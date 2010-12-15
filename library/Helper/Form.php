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
	class Helper_Form
	{
		private	$system;
		private	$_sanitize;

		public	function	__construct($system, $config)
		{
			$this->system		=	$system;
		}
		
		public	function	create()
		{
			return	new Helper_Form_Object($system);
		}
	}
	/*
	class Helper_Form_Object
	{
		private	$_tests			=	array();
		private	$_testsArgs		=	array();
		private	$_dependencies	=	array();
		private	$_errorMessages	=	array();
		
		private	$_input			=	array();
		private	$_result		=	array();
		private	$_nbError		=	0;
		
		public	function	__get($name)
		{
			return	isset($this->_result[$name]) ? $this->_result[$name] : array('value' => NULL, 'valid' => NULL, 'message' => NULL);
		}
		
		public	function	nbError()
		{
			return	$this->_nbError;
		}
		
		public	function	process($input)
		{
			$this->_input	=	$input;
			$this->_result	=	array();
			$this->_nbError	=	0;
			foreach($this->_tests as $fieldName => $tests) {
				$this->_result[$fieldName]	=	array(
					'valid'		=>	true,
					'message'	=>	NULL,
					'value'		=>	isset($input[$fieldName]) ? $input[$fieldName] : NULL
				);
				//	Dépendance non satisfaite
				if(isset($this->_dependencies[$fieldName])) {
					foreach($this->_dependencies[$fieldName] as $dependency) {
						if(!isset($this->_result[$dependency]) || !$this->_result[$dependency]['valid']) {
							$this->_result[$fieldName]['valid']	=	NULL;
							continue 2;
						}
					}
				}
				//	Do tests
				foreach($tests as $key => $test) {
					$args	=	$this->_testsArgs[$fieldName][$key];
					array_unshift($args, $this->_result[$fieldName]['value']);
					if(!$this->_result[$fieldName]['valid'] = call_user_func_array(array('Helper_Validate', $test), $args)) {
						//	Test failed
						if(isset($this->_errorMessages[$fieldName][$test])) {
							$this->_result[$fieldName]['message']	=	$this->_errorMessages[$fieldName][$test];
						} elseif(isset($this->_errorMessages[$fieldName]['*'])) {
							$this->_result[$fieldName]['message']	=	$this->_errorMessages[$fieldName]['*'];
						}
						break;
					}
				}
			}
		}
		
		public	function	setRule($field, $tests, $message = NULL, $dependencies = NULL)
		{
			$this->setTest($field, $tests);
			if($message !== NULL)
				$this->setErrorMessage($field, $message);
			if($dependencies !== NULL)
				$this->setDependency($field, $message);
		}
		
		public	function	setTest($field, $tests)
		{
			$tests	=	(array)$tests;
			if(!isset($this->_tests[$field])) {
				$this->_tests[$field]		=	array();
				$this->_testsArgs[$field]	=	array();
			}
			$args	=	func_get_args();
			array_splice($args, 0, 2);
			foreach($tests as $test) {
				if(!in_array($test, $this->_tests[$field])) {
					$this->_tests[$field][]		=	$test;
					$this->_testsArgs[$field][]	=	$args;
				}
			}
		}
		
		public	function	setErrorMessage($field, $message, $tests = '*')
		{
			$tests	=	(array)$tests;
			if(!isset($this->_errorMessages[$field])) {
				$this->_errorMessages[$field]	=	array();
			}
			foreach($tests as $test) {
				$this->_errorMessages[$field][$test]	=	$message;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       
			}
		}
		
		public	function	setDependency($field, $dependencies)
		{
			$dependencies	=	(array)$dependencies;
			if(!isset($this->_dependencies[$field])) {
				$this->_dependencies[$field]	=	array();
			}
			foreach($dependencies as $dependency) {
				if(!in_array($dependency, $this->_dependencies[$field])) {
					$this->_dependencies[$field][]	=	$dependency;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       
				}
			}
		}
	}*/

?>