<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\network\synlib;


class pontorusSocket{
	private $socket;

	public function __construct(\ThreadedLogger $logger, $port = 10305, $interface = "0.0.0.0"){
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(@socket_bind($this->socket, $interface, $port) !== true){
			$logger->critical("**** FAILED TO BIND TO " . $interface . ":" . $port . "!");
			$logger->critical("Perhaps a server is already running on that port?");
			exit(1);
		}
		socket_listen($this->socket);
		$logger->info("pontorus is running on $interface:$port");
		socket_set_nonblock($this->socket);
	}

	public function getClient(){
		return socket_accept($this->socket);
	}

	public function getSocket(){
		return $this->socket;
	}

	public function close(){
		socket_close($this->socket);
	}
}