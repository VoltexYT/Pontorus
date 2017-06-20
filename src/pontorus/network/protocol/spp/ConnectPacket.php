<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

 
namespace pontorus\network\protocol\spp;

class ConnectPacket extends DataPacket{
	const NETWORK_ID = Info::CONNECT_PACKET;

	public $protocol = Info::CURRENT_PROTOCOL;
	public $maxPlayers;
	public $isMainServer;
	public $description;
	public $password;

	public function encode(){
		$this->reset();
		$this->putInt($this->protocol);
		$this->putInt($this->maxPlayers);
		$this->putByte($this->isMainServer ? 1 : 0);
		$this->putString($this->description);
		$this->putString($this->password);
	}

	public function decode(){
		$this->protocol = $this->getInt();
		$this->maxPlayers = $this->getInt();
		$this->isMainServer = ($this->getByte() == 1) ? true : false;
		$this->description = $this->getString();
		$this->password = $this->getString();
	}

}