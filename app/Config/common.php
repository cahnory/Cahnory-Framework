<?php
	
	$system->Constant->themeMode	=	'fast';
	$system->Constant->online		=	$_SERVER['SERVER_PORT'] != 80 || $_SERVER['SERVER_ADDR'] == '127.0.0.1';
	
	//	Debug
	$system->Debug->handleError();
	if($system->request->isCLI()) {
		$system->Debug->addRegistrar(new Helper_Debug_FileRegistrar('Debug/cli.log'));
	} else {
		$system->Debug->addRegistrar(new Helper_Debug_FileRegistrar('Debug/apache.log'));
	}
	$system->Debug->displayFatalError(true);
	
	//	Server dependant conf
	require_once	$system->Constant->online ?	'Config/offline.php' : 'Config/online.php';

	//	Page d'accueil
	$system->module->set('AppInfos');
	$system->router->bind('AppInfos');

	//	Présentation
	$system->router->mainFormat	=	'html';
	$system->view->set('html');
	$system->view->data('title', 			'Nouvelle application');
	$system->view->data('name',  			'Cahnory framework');

	//	Useful helpers
	$system->Error;
	$system->Theme->setTheme('default');

?>