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
	class Helper_HTML
	{
		static	private	$_writingConventions	=	array(
			'fr'	=>	array(
				'surroundingSpace'	=>	'!|\?|:|;',
				'leadingSpace'		=>	';',
				'trailingSpace'		=>	'\.|…|,'
			)
		);
		
		static	public	function	absolutize($html, $base)
		{
			$base   =	rtrim($base, '/').'/';
			
			//	href, src, background attributes
			$html   =	preg_replace('#(<[^>]+(href|src|background)=")(?![a-z]+:)/?([^"]+)"#is', '$1'.$base.'$3"', $html);
			//	style attributes url(), src()
			$html   =	preg_replace('#(<[^>]+style="[^"]*(url|src)\([\s\']*)(?![a-z]+:)/?([^)]+)\)#is', '$1'.$base.'$3)', $html);
			
			//	style tag
			preg_match_all('#<style[^>]*>([^<]+)#is', $html, $styles);
			foreach($styles[1] as $style){
			    $html   =	str_replace(
		            $style, 
		            preg_replace('#((url|src)\([\s\']*)(?![a-z]+:)/?([^)]+)\)#is', '$1'.$base.'$3)', $style),
		            $html
				);
			};
			
			return  $html;
		}
		
		static	public	function	escapeAttribute($string, $quote = ENT_QUOTES, $encoding = 'UTF-8')
		{
			return	htmlspecialchars(html_entity_decode($string, $quote, $encoding), $quote, $encoding);
		}
		
		static	public	function	relativize($html, $base)
		{
			$base   =	rtrim($base, '/');
			
			//	href, src, background attributes
			$html   =	preg_replace('#(<[^>]+(href|src|background)=")('.preg_quote($base, '#').'/?)#is', '$1', $html);
			//	style attributes url(), src()
			$html   =	preg_replace('#(<[^>]+style="[^"]*(url|src)\([\s\']*)'.preg_quote($base, '#').'/?#is', '$1', $html);
			
			//	style tag
			preg_match_all('#<style[^>]*>([^<]+)#is', $html, $styles);
			foreach($styles[1] as $style){
			    $html   =	str_replace(
		            $style, 
		            preg_replace('#((url|src)\([\s\']*)'.preg_quote($base, '#').'/?#is', '$1', $style),
		            $html
				);
			};
			
			return  $html;
		}
		
		static	public	function	format($string, $convention = 'fr', $encoding = 'UTF-8')
		{
			$string	=	trim($string);
			if(mb_strlen($string, $encoding)) {
				$string	=	preg_replace('#[ \t]+#', ' ', $string);
				//	Language conventions (experimental)
				if(isset(self::$_writingConventions[$convention])) {
					$rules	=	self::$_writingConventions[$convention];
					//	Leading space
					//$string			=	preg_replace('#([\s]*('.$rules['leadingSpace'].'))#', '&nbsp;$2', $string);
					//	Trailing space
					//$string			=	preg_replace('#(('.$rules['trailingSpace'].')[\s]*)#', '$2%%%', $string);
					$string	=	preg_replace_callback(
						'#([ \t]*('.$rules['surroundingSpace'].')[ \t]*)|([ \t]*('.$rules['leadingSpace'].'))|(('.$rules['trailingSpace'].')[ \t]*)#',
						array('Helper_HTML','_breakingSpaceReplace'),
						$string
					);
				}
				//	Special chars
				$string	=	htmlspecialchars($string, ENT_COMPAT, $encoding, false);
				//	Newlines
				//$string	=	preg_replace('#(\n\r|\n|\r)#', '<br/>', $string);
				$string	=	nl2br($string);
				//	Paragraphs
				$string	=	'<p>'.preg_replace('#(\<br[\s]*/\>[\r\n\s]*){2,}#', '</p><p>', $string).'</p>';
			}
			return	$string;
		}
		
		static	private	function	_breakingSpaceReplace($m)
		{			
			if(isset($m[6])) {
				return	$m[6].'&nbsp;';
			} elseif(isset($m[4])) {
				return	'&nbsp;'.$m[4];
			} else {
				return	'&nbsp;'.$m[2].'&nbsp;';
			}
		}
	}

?>