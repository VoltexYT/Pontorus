<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\network\protocol\mcpe;

#include <rules/DataPacket.h>


class BatchPacket extends DataPacket{
	const NETWORK_ID = Info::BATCH_PACKET;

	public $payload;

	public function decode(){
		$size = $this->getInt();
		$this->payload = $this->get($size);
	}

	public function encode(){
		$this->reset();
		$this->putInt(strlen($this->payload));
		$this->put($this->payload);
	}

}