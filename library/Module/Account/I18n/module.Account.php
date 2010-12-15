<?php

	$space	=	$this->module->account;
	
	$space->message->set('FR', 'lost',			'Un lien va vous être envoyé par email. Il conduit à une page vous invitant à renouveler votre mot de passe.');
	$space->message->set('FR', 'unknownLogin',	'Nous ne parvenons pas à retrouver votre compte, veuillez vérifier votre nom d\'utilisateur/email.');
	$space->label->set('FR', 'login',		'Nom d\'utilisateur ou email');
	$space->label->set('FR', 'password',	'Mot de passe');
	$space->label->set('FR', 'connection',	'Connexion');
	$space->label->set('FR', 'reset',		'Renouveler');
	
	$space->message->set('EN', 'lost',			'A link will be sent to your mailbox...');
	$space->message->set('EN', 'unknownLogin',	'Your account was not found, please check your username/email.');
	$space->label->set('EN', 'login',		'Username or email');
	$space->label->set('EN', 'password',	'Password');
	$space->label->set('EN', 'connection',	'Connection');
	$space->label->set('EN', 'reset',		'Reset password');

?>