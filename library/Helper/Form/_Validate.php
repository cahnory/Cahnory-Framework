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
	class Helper_Form_Validate
	{
		static	public	function email($value)
		{
			/*	Tests sur la composition de l'email	*/
			return	filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
		}
		
		static	public	function	int($value)
		{
			return	(boolean)preg_match('#^[0-9]+$#', $value);
		}
		
		static	public	function	float($value, $strict = false)
		{
			if($strict) {
				return	(boolean)preg_match('#^([0-9]+)?\.[0-9]+$#', $value);
			} else {
				return	(boolean)preg_match('#(^[0-9]+(\.[0-9]+)?$)|(^([0-9]+)?\.[0-9]+$)#', $value);
			}
		}
		
		static	public	function	modulo($value, $modulo)
		{
			return	$value % $modulo === 0;
		}
		
		static	public	function	maxLength($value, $length = 255)
		{
			return	strlen($value) <= $length;
		}
		
		static	public	function	minLength($value, $length = 1)
		{
			return	strlen($value) >= $length;
		}
		
		static	public	function	min($value, $min = 1)
		{
			return	$value <= $min;
		}
		
		static	public	function	max($value, $max = 1)
		{
			return	$value <= $max;
		}
	}

?>