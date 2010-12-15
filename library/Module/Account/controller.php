<?php

	class Module_Account extends Module
	{
		protected	$config	=	array(
			'hash'	=>	'sha512'
		);
		
		public	function	c_preload(array $config = array())
		{
			//	Helpers
			$this->system->Session;
			$this->system->Redirection;
			$this->system->User;
			
			//	I18n
			$this->system->I18n->load('<module>');
			
			//	Model
			$this->system->Doctrine->db()->export->exportSql(dirname(__FILE__).'/Model');
			
			if($this->system->Redirection->bind('login') === NULL) {
				$this->system->Redirection->bind('login', $this->system->module->route().'/login');
			}
		}
		
		public	function	index()
		{
			//	Home
			return	$this->login();
		}
		
		public	function	login()
		{
			$login	=	NULL;
			$this->system->Redirection->register(false);
			if($this->system->request->isMethod('post')) {
				$login		=	$this->system->request->post('login');
				$password	=	hash($this->config['hash'], $this->system->request->post('password'));
				$column		=	$this->system->Validate->email($login) ? 'email' : 'username';
								
				$table		=	$this->system->Doctrine->getTable('Model_Account')->createQuery('u')
  								->where('u.'.$column.' = ?', $login)
  								->andWhere('u.password = ?', $password);
  								
				if($user = $table->fetchOne()) {
					$user	=	$user->toArray();
					unset($user['password']);
					$this->system->User->data($user);
					$this->system->User->logged(true);
					$this->system->Redirection->history();
				}
			}
			$this->system->view->put('<module>login', compact('login'));
		}
		
		public	function	logout()
		{
			$this->system->User->clear();
			$this->system->Redirection->register(false);
			$this->system->Redirection->history();
		}
		
		public	function	lost()
		{
			$unknownLogin	=	false;
			$this->system->Redirection->register(false);
			
			if($this->system->request->isMethod('post')) {
				$login		=	$this->system->request->post('login');
				$column		=	$this->system->Validate->email($login) ? 'email' : 'username';							
				$table	=	$this->system->Doctrine->getTable('Model_Account')->createQuery('u')
								 ->where('u.'.$column.' = ?', $login);
				
				if($user = $table->fetchOne()) {
					//	Lost account creation
					$lost	=	new	Model_LostAccount;
					$lost->accountID	=	$user['id'];
					$lost->hash			=	hash($this->config['hash'], uniqid(rand(), true));
					$lost->save();
				
					/*/	Reset mail
					$mail	=	new	PHPMailer;
					$mail->isSMTP();					
					$mail->Host	=	'ssl://smtp.gmail.com';
		    		$mail->Mailer   = "smtp";                     
		    		$mail->WordWrap = 75;
		    		$mail->Port = "465";
		    		$mail->SMTPAuth = true;		    		
					$mail->Body	=	'Test';
					$mail->AddAddress('cahnory@gmail.com');
					$mail->send();/* */
				} else {
					$unknownLogin	=	true;
				}
			}
			$this->system->view->put('<module>lost', array('unknownLogin' => $unknownLogin));
		}
		
		public	function	reset()
		{
			$this->system->Redirection->register(false);
			
			if($hash = $this->system->request->post('hash')) {
				$login	=	$this->system->request->post('login');
				$column	=	$this->system->Validate->email($login) ? 'email' : 'username';							
				$table	=	$this->system->Doctrine->getTable('Model_Account')->createQuery('u')
								 ->where('u.'.$column.' = ?', $login);
				
				if($user = $table->fetchOne()) {
					$lost	=	new	Model_LostAccount;
					$lost->accountID	=	$user['id'];
					$lost->hash			=	hash($this->config['hash'], uniqid(rand(), true));
					$lost->find();
					// test mot de pass
					echo 'ok';
				}
				
				$this->system->view->put('<module>reset', array(
					'hash'	=>	$hash,
					'login'	=>	$this->system->request->post('login')
				));
			} elseif($hash = $this->system->request->get('hash')) {
				$this->system->view->put('<module>reset', array('hash' => $hash));
			} else {
				return	false;
			}
		}
		
		public	function	set()
		{
			if($this->system->User->logged()) {
				$this->system->User->setAccess('account');
			}
			$this->system->Redirection->history();
		}
		
		private	function	_login($login, $password)
		{
			$password	=	hash($this->config['hash'], $password);
			$column		=	$this->system->Validate->email($login) ? 'email' : 'username';
							
			$table		=	$this->system->Doctrine->getTable('Model_Account')->createQuery('u')
								->where('u.'.$column.' = ?', $login)
								->andWhere('u.password = ?', $password);
								
			if($user = $table->fetchOne()) {
				$user	=	$user->toArray();
				unset($user['password']);
				$this->system->User->data($user);
				$this->system->User->logged(true);
				return	true;
			}
			return	false;
		}
	}

?>