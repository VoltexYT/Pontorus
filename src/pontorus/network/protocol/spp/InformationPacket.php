<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

 
namespace pontorus\network\protocol\spp;

class InformationPacket extends DataPacket{
	const NETWORK_ID = Info::INFORMATION_PACKET;

	const TYPE_LOGIN = 0;
	const TYPE_CLIENT_DATA = 1;

	const INFO_LOGIN_SUCCESS = "success";
	const INFO_LOGIN_FAILED = "failed";

	public $type;
	public $message;

	public function encode(){
		$this->reset();
		$this->putByte($this->type);
		$this->putString($this->message);
	}

	public function decode(){
		$this->type = $this->getByte();
		$this->message = $this->getString();
	}
}