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
	class Helper_Session
	{
		static private $_poped = true;
		private	$system;
		private	$_filename	=	'Sessions';

		public	function	__construct($system, $config)
		{
			$this->system = $system;			
			
			//	Ouverture de session
			session_name(md5($this->system->appPath()));
			/*session_set_save_handler(
				array($this, 'sessionOpen'),
				array($this, 'sessionClose'),
				array($this, 'sessionRead'),
				array($this, 'sessionWrite'),
				array($this, 'sessionDestroy'),
				array($this, 'sessionGarbageCollect')
			);*/
			session_start();
			header('P3P: CP="CAO PSA OUR"');
			
			$this->popOut();
			$this->system->bind('viewSent', array($this, 'popIn'));
		}
		
		public	function	getSession($id)
		{
			$filename	=	session_save_path().'/sess_'.$id;
			if(is_file($filename)) {
				$session	=	preg_replace('#^Helper_Session\|#', '', file_get_contents($filename));
				return	unserialize($session);
			}
		}
		
		public	function	popOut()
		{
			if(self::$_poped AND isset($_SESSION['Helper_Session'])) {
				$_SESSION		=	$_SESSION['Helper_Session'];
			}
			self::$_poped	=	false;
		}
		
		public	function	popIn()
		{
			if(!self::$_poped) {
				$_SESSION		=	array('Helper_Session' => $_SESSION);
				self::$_poped	=	true;
			}
		}
		
		public function sessionOpen($sess_path, $sess_name) {
	        return true;
	    }
	
	    public function sessionClose() {
	        return true;
	    }
	
	    public function sessionRead($sess_id) {
	        return '';
	    }
	
	    public function sessionWrite($sess_id, $data) {
	        return true;
	    }
	
	    public function sessionDestroy($sess_id) {
	        return true;
	    }
	
	    public function sessionGarbageCollect($sess_maxlifetime) {
	        return true;
	    }
	}

?>