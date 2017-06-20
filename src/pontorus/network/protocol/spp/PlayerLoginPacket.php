<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

 
namespace pontorus\network\protocol\spp;

use pontorus\utils\UUID;

class PlayerLoginPacket extends DataPacket{
	const NETWORK_ID = Info::PLAYER_LOGIN_PACKET;

	/** @var UUID */
	public $uuid;
	public $address;
	public $port;
	public $isFirstTime;
	public $cachedLoginPacket;

	public function encode(){
		$this->reset();
		$this->putUUID($this->uuid);
		$this->putString($this->address);
		$this->putInt($this->port);
		$this->putByte($this->isFirstTime ? 1 : 0);
		$this->putString($this->cachedLoginPacket);
	}

	public function decode(){
		$this->uuid = $this->getUUID();
		$this->address = $this->getString();
		$this->port = $this->getInt();
		$this->isFirstTime = ($this->getByte() == 1) ? true : false;
		$this->cachedLoginPacket = $this->getString();
	}
}