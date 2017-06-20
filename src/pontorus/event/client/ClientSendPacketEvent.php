<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\event\client;

use pontorus\Client;
use pontorus\event\Cancellable;
use pontorus\network\protocol\spp\DataPacket;

class ClientSendPacketEvent extends ClientEvent implements Cancellable{
	public static $handlerList = null;

	/** @var DataPacket */
	private $packet;

	public function __construct(Client $client, DataPacket $packet){
		parent::__construct($client);
		$this->packet = $packet;
	}

	public function getPacket() : DataPacket{
		return $this->packet;
	}
}