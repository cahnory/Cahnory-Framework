<?php

	class Model_Account extends Doctrine_Record
	{		
	    public function setTableDefinition()
	    {
			$this->setTableName('accounts');
			
			$this->hasColumn('username',	'varchar', 255);
			$this->hasColumn('password',	'varchar', 255);
			$this->hasColumn('email',		'varchar', 255);
	    }
	}

?>