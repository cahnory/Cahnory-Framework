<?php

	$mode	=	$this->system->Constant->themeMode;
	$path	=	'files/themes/default/';
	
	$this->setPattern('pattern/html5');
	$this->system->view->data('favicon',			$path.'favicon.ico');
	$this->system->view->data('apple-touch-icon',	$path.'apple-touch-icon.png');
	
	if($mode != 'fast' || true) {
		//	Copie des images, fonts... dans le dossier
		$this->system->File->createDir($path);
		$this->system->File->copy(
			dirname(__FILE__).'/public/',
			$path,
			true	//	Écrase le dossier s'il existe
		);
	
		//	Feuille de style principale
		$css	=	$this->system->CSS->create();
		$css->cache(false);
		$css->minify(false);
		$css->useLESS(true);
		
		$css->addFile('css/css3.less');
		$css->addFile('css/application.css');
		
		$this->addCSS($css->save($path.'main.css'));
	} else {
		//	Fast mode, when files were cached
		$this->addCSS($path.'main.css');
	}

?>