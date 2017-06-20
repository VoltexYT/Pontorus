<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */
 
namespace pontorus\event\client;

use pontorus\Client;
use pontorus\event\Event;

abstract class ClientEvent extends Event{
	/** @var Client */
	private $client;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function getClient() : Client{
		return $this->client;
	}
}