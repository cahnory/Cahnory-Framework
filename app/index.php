<?php
	
	//	Chargement et instanciation du system
	require_once	'/Users/studio2/Dropbox/Server/cahnory/cahnory.php';
	$system	=	new Cahnory();
	
	//	Chargement de la configuration
	require_once	'Config/common.php';
	
	//	Lancement du traitement
	$system->dispatch();

?>