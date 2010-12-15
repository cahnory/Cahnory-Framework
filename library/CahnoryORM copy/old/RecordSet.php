<?php

	abstract class CahnoryDB_RecordSet
	{
		private	$_records;
		
		public	function	__construct(array $records)
		{
			$this->_records	=	$records;
		}
		/*
		public	function	save()
		{
			foreach($this->_records as $record) {
				$record->save();
			}
		}
	
	    function rewind() {
	        $this->_offset = 0;
	    }
	
	    function current() {
	        return $this->_array[$this->_offset];
	    }
	
	    function key() {
	        return $this->_offset;
	    }
	
	    function next() {
	        ++$this->_offset;
	    }
	
	    function valid() {
	        return isset($this->_array[$this->_offset]);
	    }*/
	}

?>