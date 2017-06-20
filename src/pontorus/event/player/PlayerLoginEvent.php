<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\event\player;

use pontorus\event\Cancellable;
use pontorus\Player;

class PlayerLoginEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	private $kickMessage;
	private $clientHash;

	public function __construct(Player $player, string $kickMessage, string $clientHash){
		parent::__construct($player);
		$this->clientHash = $clientHash;
	}

	public function setClientHash(string $clientHash){
		$this->clientHash = $clientHash;
	}

	public function getClientHash() : string{
		return $this->clientHash;
	}

	public function setKickMessage(string $kickMessage){
		$this->kickMessage = $kickMessage;
	}

	public function getKickMessage() : string{
		return $this->kickMessage;
	}
}