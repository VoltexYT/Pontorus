<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\event\client;

use pontorus\Client;

class ClientDisconnectEvent extends ClientEvent{
	public static $handlerList = null;

	private $reason;
	private $type;

	public function __construct(Client $client, string $reason, int $type){
		parent::__construct($client);
		$this->reason = $reason;
		$this->type = $type;
	}

	public function getReason() : string{
		return $this->reason;
	}

	public function setReason(string $reason){
		$this->reason = $reason;
	}

	public function getType() : int{
		return $this->type;
	}
}