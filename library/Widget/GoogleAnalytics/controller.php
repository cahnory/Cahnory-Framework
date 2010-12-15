<?php

	class Widget_GoogleAnalytics
	{
		private	$_view;
		
		public	function	__construct(Cahnory $system, $id)
		{
			$this->_view	=	$system->view->execute('<widget>', array('id' => $id));
		}
		
		public	function	__toString()
		{
			return	$this->_view;
		}
	}

?>