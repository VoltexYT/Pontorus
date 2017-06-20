<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */
 
namespace pontorus\event\client;

use pontorus\Client;

class ClientAuthEvent extends ClientEvent{
	public static $handlerList = null;

	private $password;

	public function __construct(Client $client, string $password){
		parent::__construct($client);
		$this->password = $password;
	}

	public function getPassword() : string{
		return $this->password;
	}
}