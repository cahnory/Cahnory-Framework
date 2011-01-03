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
	class Helper_URI
	{		
		static	public	function	arrayToQuery(array $array, $add = '&')
		{
			return	http_build_query($array, '', $add);
		}
		
		static	public	function	clearQuery($uri)
		{
			return	preg_replace('#\?[^?\#]*#', '', $uri);
		}
		
		//	Source : http://julp.developpez.com/php/curl/#L3.1.2
		function fetch($url, $timeout = 10, $userpwd = '')
		{
		    $cURL	=	curl_init($url);
		    curl_setopt($cURL, CURLOPT_TIMEOUT,			$timeout);
		    curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT,	$timeout);
		    curl_setopt($cURL, CURLOPT_RETURNTRANSFER,	true);
		    if ($userpwd) {
		        curl_setopt($cURL, CURLOPT_USERPWD,	$userpwd);
		    }
		    $data	=	curl_exec($cURL);
		    curl_close($cURL);
		
		    return $data;
		}
		
		static	public	function	queryToArray($query)
		{
			$array	=	array();
			parse_str($query, $array);
			return	$array;
		}
		
		static	public	function	SEO($string, $add = '-')
		{
			$string	=	str_replace(
				array(
					'À','Á','Â','Ã','Ä','Å',
					'à','á','â','ã','ä','å',
					'Ò','Ó','Ô','Õ','Ö','Ø',
					'ò','ó','ô','õ','ö','ø',
					'È','É','Ê','Ë',
					'é','è','ê','ë',
					'Ç','ç',
					'Ì','Í','Î','Ï',
					'ì','í','î','ï',
					'Ù','Ú','Û','Ü',
					'ù','ú','û','ü',
					'ÿ','Ñ','ñ'
				),
				array(
					'A','A','A','A','A','A',
					'a','a','a','a','a','a',
					'O','O','O','O','O','O',
					'o','o','o','o','o','o',
					'E','E','E','E','e','e',
					'e','e',
					'C','c',
					'I','I','I','I',
					'i','i','i','i',
					'U','U','U','U',
					'u','u','u','u',
					'y','N','n'
				),
				$string);
			$string	=	strtolower($string);
			$string	=	preg_replace('#(\.|,)+#', '$1', $string);
			$string	=	preg_replace('#([^a-z0-9]+)#', ' ', $string);
			$string	=	str_replace(' ', $add, trim($string, ' ,.'));
			return $string;
		}
		
		static	public	function	setQuery($uri, $values, $value = NULL, $add = '&')
		{
			if(!is_array($values)) {
				$values		=	array($values => $value);
			} else {
				$add	=	$value;
			}
			if($add === NULL) {
				$add	=	'&';
			}
			
			preg_match('#^([^?]*)\??(.+)$#', $uri, $match);
			$url	=	$match[1];
			$query	=	$match[2];
			if(strlen($query) !== 0) {
				parse_str($query, $query);
				$query	=	http_build_query(array_merge($query, $values), '', $add);
			} else {
				$query	=	http_build_query($query, '', $add);
			}
			
			return	$url.'?'.$query;
		}
		
		static	public	function	unsetQuery($uri, $offsets, $add = '&')
		{
			if(!is_array($offsets)) {
				$offsets	=	array($offsets);
			}
			
			preg_match('#^([^?]*)\??(.+)$#', $uri, $match);
			$url	=	$match[1];
			$query	=	$match[2];
			if(strlen($query) !== 0) {
				parse_str($query, $query);
				foreach($offsets as $offset) {
					if(isset($query[$offset])) {
						unset($query[$offset]);
					}
				}
				$query	=	http_build_query($query, '', $add);
				if(strlen($query) !== 0) {
					$uri	=	$url.'?'.$query;
				}
			}
			return	$uri;
		}
	}

?>