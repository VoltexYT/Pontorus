<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\network\synlib;

use pontorus\utils\Binary;

class Session{

	const MAGIC_BYTES = "\x35\xac\x66\xbf";

	private $receiveBuffer = "";
	private $sendBuffer = "";
	/** @var SessionManager */
	private $sessionManager;
	/** @var resource */
	private $socket;
	private $ip;
	private $port;

	public function __construct(SessionManager $sessionManager, $socket){
		$this->sessionManager = $sessionManager;
		$this->socket = $socket;
		socket_getpeername($this->socket, $address, $port);
		$this->ip = $address;
		$this->port = $port;
		$sessionManager->getServer()->getLogger()->notice("Client [$address:$port] has connected.");
	}

	public function getHash(){
		return $this->ip . ':' . $this->port;
	}

	public function getIp() : string {
		return $this->ip;
	}

	public function getPort() : int{
		return $this->port;
	}

	public function update(){
		$err = socket_last_error($this->socket);
		socket_clear_error($this->socket);
		if($err == 10057 or $err == 10054){
			$this->sessionManager->getServer()->getLogger()->error("pontorus client [$this->ip:$this->port] has disconnected unexpectedly");
			return false;
		}else{
			$data = @socket_read($this->socket, 65535, PHP_BINARY_READ);
			if($data != ""){
				$this->receiveBuffer .= $data;
			}
			if($this->sendBuffer != ""){
				socket_write($this->socket, $this->sendBuffer);
				$this->sendBuffer = "";
			}
			return true;
		}
	}

	public function getSocket(){
		return $this->socket;
	}

	public function close(){
		@socket_close($this->socket);
	}

	public function readPacket(){
		$end = explode(self::MAGIC_BYTES, $this->receiveBuffer, 2);
		if(count($end) <= 2){
			if(count($end) == 1){
				if(strstr($end[0], self::MAGIC_BYTES)){
					$this->receiveBuffer = "";
				}else{
					return null;
				}
			}else{
				$this->receiveBuffer = $end[1];
			}
			$buffer = $end[0];
			if(strlen($buffer) < 4){
				return null;
			}
			$len = Binary::readLInt(substr($buffer, 0, 4));
			$buffer = substr($buffer, 4);
			if($len != strlen($buffer)){
				throw new \Exception("Wrong packet 0x" . ord($buffer{0}) . ": $buffer");
			}
			return $buffer;
		}
		return null;
	}

	public function writePacket($data){
		$this->sendBuffer .= Binary::writeLInt(strlen($data)) . $data . self::MAGIC_BYTES;
	}
}