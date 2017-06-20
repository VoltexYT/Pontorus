<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

 
namespace pontorus\network\protocol\spp;

use pontorus\utils\UUID;

class TransferPacket extends DataPacket{
	const NETWORK_ID = Info::TRANSFER_PACKET;

	/** @var UUID */
	public $uuid;
	public $clientHash;

	public function encode(){
		$this->reset();
		$this->putUUID($this->uuid);
		$this->putString($this->clientHash);
	}

	public function decode(){
		$this->uuid = $this->getUUID();
		$this->clientHash = $this->getString();
	}
}