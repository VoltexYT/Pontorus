<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

 
namespace pontorus\network\protocol\spp;

use pontorus\utils\UUID;

class RedirectPacket extends DataPacket{
	const NETWORK_ID = Info::REDIRECT_PACKET;

	/** @var UUID */
	public $uuid;
	public $direct;
	public $mcpeBuffer;

	public function encode(){
		$this->reset();
		$this->putUUID($this->uuid);
		$this->putByte($this->direct ? 1 : 0);
		$this->putString($this->mcpeBuffer);
	}

	public function decode(){
		$this->uuid = $this->getUUID();
		$this->direct = ($this->getByte() == 1) ? true : false;
		$this->mcpeBuffer = $this->getString();
	}
}