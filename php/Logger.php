<?php

class Logger {

	 private $file;
	 private $handler;

	 function __construct( $file ) {
	 	$this->file = $file;
	 }

	 public function open() {
	 	if ( $this->handler)
	 		$this->closeLog();

	 	$this->handler = fopen($this->file, 'a');
	 }

	public function lock() {
		if ( $this->handler) 
			flock ($this->handler,LOCK_EX);
	}
	
	public function close() {
		if ($this->handler) {
		  fflush($this->handler);
		  flock($this->handler, LOCK_UN);
		  fclose($this->handler);
		}
	}

	public function write($msg) {
		if ( $this->handler )
			fwrite($this->handler, date("Y-m-d H:i:s").' '.$msg."\n");
	}

}