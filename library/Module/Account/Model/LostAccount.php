<?php

	class Model_LostAccount extends Doctrine_Record
	{		
	    public function setTableDefinition()
	    {
			$this->setTableName('lostAccounts');
			
			$this->hasColumn('accountID',	'int', 11);
			$this->hasColumn('date',		'timestamp', 255);
			$this->hasColumn('hash',		'varchar', 128);
	    }
	}

?>