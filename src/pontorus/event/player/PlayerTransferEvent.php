<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\event\player;

use pontorus\Client;
use pontorus\event\Cancellable;
use pontorus\Player;

class PlayerTransferEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	/** @var Client */
	private $targetClient;
	private $needDisconnect;

	public function __construct(Player $player, Client $client, bool $needDisconnect){
		parent::__construct($player);
		$this->targetClient = $client;
		$this->needDisconnect = $needDisconnect;
	}

	public function needDisconnect() : bool{
		return $this->needDisconnect;
	}

	public function getTargetClient() : Client{
		return $this->targetClient;
	}

	public function setTargetClient(Client $client){
		$this->targetClient = $client;
	}
}