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
	class Helper_Array
	{
		static	public	function	deepMerge(array $a, array $b)
		{
			$arrays	=	func_get_args();
			$first	=	array_shift($arrays);
			foreach($arrays as $array) {
				foreach($array as $k => $v) {
					if(isset($first[$k]) && is_array($first[$k])) {
						$first[$k]	=	self::deepMerge($first[$k], (array)$v);
					} else {
						$first[$k]	=	$v;
					}
				}
			}
			return	$first;
		}
		
		static	public	function	fromRow($row, $splits = '&=')
		{
			$row	=	trim($row, $splits);
			$parts	=	preg_split('#['.preg_quote($splits, '#').']+#', $row);
			$array	=	array();
			$size	=	sizeof($parts);
			for($i = 0; $i < $size; $i+=2) {
				$array[$parts[$i]]	=	isset($parts[$i+1]) ? $parts[$i+1] : NULL;
			}
			return	$array;
		}
		
		function sortBy($array, $on, $order = 'ASC')
		{
		    $new_array = array();
		    $sortable_array = array();
		
		    if (count($array) > 0) {
		        foreach ($array as $k => $v) {
		            if (is_array($v)) {
		                foreach ($v as $k2 => $v2) {
		                    if ($k2 == $on) {
		                        $sortable_array[$k] = $v2;
		                    }
		                }
		            } else {
		                $sortable_array[$k] = $v;
		            }
		        }
		
		        if ($order == 'DESC') {
					arsort($sortable_array);
				} else {
					asort($sortable_array);
		        }		
		        foreach ($sortable_array as $k => $v) {
		            $new_array[$k] = $array[$k];
		        }
		    }
		
		    return $new_array;
		}
		
		/*
			Parcour un tableau à l'aide de sélecteur correspondant aux valeurs
			possibles de l'attribut html name
			Si useNext vaut true la recherche se poursuivra aux enfant des
			tableaux non indexés.
			@param	$selectors	Mixed		Sélecteur
			@param	$a			array		le tableau à traverser
			@param	$useNext	boolean	
			@param	$create		boolean		Crée automatiquement les clé recherchées
		*/
		static	public	function &find($selectors, &$a, $useNext = true, $create = true) 
		{ 
			//	passage de name[name2] vers array('name','name2'); 
			if(is_string($selectors)) 
				$selectors	=	explode('[',str_replace(']','',$selectors)); 
			
			if(!sizeof($selectors)) 
				return	$a; 
			
			//	On récupère la première clé 
			$selector	=	array_shift($selectors); 
			
			if(isset($a[$selector])) 
				return	self::find($selectors, $a[$selector], $useNext, $create); 
			
			//	Recherche dans les sous array name[0][name2], name[1][name2]... 
			$set	 =	null; 
			if($useNext && isset($a[0][$selector])){ 
				$set	=	array(); 
				foreach($a as $k => $v){ 
					$e	=&	self::find($selectors, $a[$k][$selector], $useNext, $create); 
					if(!is_array($e)){ 
						$set[]	=	&$e; 
						continue; 	
					} 
					foreach($e as $n => $v){
						$set[]	=	&$e[$n]; 
					} 
				} 
			} elseif($create) {
				$a[$selector]	=	sizeof($selectors) === 0
								?	NULL
								:	array();
				return	self::find($selectors, $a[$selector], $useNext, $create);
			}
			
			return	$set; 
		}
	}

?>