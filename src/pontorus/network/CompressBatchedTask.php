<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\network;

use pontorus\Player;
use pontorus\scheduler\AsyncTask;
use pontorus\Server;

class CompressBatchedTask extends AsyncTask{

	public $level = 7;
	public $data;
	public $final;
	public $targets;

	public function __construct($data, array $targets, $level = 7){
		$this->data = $data;
		$this->targets = serialize($targets);
		$this->level = $level;
	}

	public function onRun(){
		try{
			$this->final = zlib_encode($this->data, ZLIB_ENCODING_DEFLATE, $this->level);
			$this->data = null;
		}catch(\Throwable $e){

		}
	}

	public function onCompletion(Server $server){
		$server->broadcastPacketsCallback($this->final, unserialize($this->targets));
	}
}