<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\network;

use pontorus\Client;
use pontorus\network\protocol\spp\DisconnectPacket;
use pontorus\network\protocol\spp\ConnectPacket;
use pontorus\network\protocol\spp\DataPacket;
use pontorus\network\protocol\spp\HeartbeatPacket;
use pontorus\network\protocol\spp\Info;
use pontorus\network\protocol\spp\InformationPacket;
use pontorus\network\protocol\spp\PlayerLoginPacket;
use pontorus\network\protocol\spp\PlayerLogoutPacket;
use pontorus\network\protocol\spp\RedirectPacket;
use pontorus\network\protocol\spp\TransferPacket;
use pontorus\network\synlib\pontorusServer;
use pontorus\Server;

class pontorusInterface{
	private $server;
	private $ip;
	private $port;
	/** @var Client[] */
	private $clients;
	/** @var DataPacket[] */
	private $packetPool = [];
	/** @var pontorusServer */
	private $interface;

	public function __construct(Server $server, $ip, int $port){
		$this->server = $server;
		$this->ip = $ip;
		$this->port = $port;
		$this->registerPackets();
		$this->interface = new pontorusServer($server->getLogger(), $this, $server->getLoader(), $port, $ip);
	}

	public function getServer(){
		return $this->server;
	}

	public function addClient($ip, $port){
		$this->clients[$ip . ":" . $port] = new Client($this, $ip, $port);
	}

	public function removeClient(Client $client){
		$this->interface->addExternalClientCloseRequest($client->getHash());
		unset($this->clients[$client->getHash()]);
	}

	public function putPacket(Client $client, DataPacket $pk){
		if(!$pk->isEncoded){
			$pk->encode();
		}
		$this->interface->pushMainToThreadPacket($client->getHash() . "|" . $pk->buffer);
	}

	public function process(){
		while(strlen($data = $this->interface->getClientOpenRequest()) > 0){
			$tmp = explode(":", $data);
			$this->addClient($tmp[0], $tmp[1]);
		}
		while(strlen($data = $this->interface->readThreadToMainPacket()) > 0){
			$tmp = explode("|", $data, 2);
			if(count($tmp) == 2){
				$this->handlePacket($tmp[0], $tmp[1]);
			}
		}
		while(strlen($data = $this->interface->getInternalClientCloseRequest()) > 0){
			$this->clients[$data]->closeAllPlayers();
			$this->server->removeClient($this->clients[$data]);
			unset($this->clients[$data]);
		}
	}

	/**
	 * @param $buffer
	 *
	 * @return DataPacket
	 */
	public function getPacket($buffer){
		$pid = ord($buffer{0});
		/** @var DataPacket $class */
		$class = $this->packetPool[$pid];
		if($class !== null){
			$pk = clone $class;
			$pk->setBuffer($buffer, 1);
			return $pk;
		}
		return null;
	}

	public function handlePacket($hash, $buffer){
		if(!isset($this->clients[$hash])){
			return;
		}

		$client = $this->clients[$hash];

		if(($pk = $this->getPacket($buffer)) != null){
			$pk->decode();
			$client->handleDataPacket($pk);
		}else{
			$this->server->getLogger()->critical("Error packet: 0x" . dechex(ord($buffer{0})) . " $buffer");
		}
	}

	/**
	 * @param int        $id 0-255
	 * @param DataPacket $class
	 */
	public function registerPacket($id, $class){
		$this->packetPool[$id] = new $class;
	}


	private function registerPackets(){
		$this->packetPool = new \SplFixedArray(256);

		$this->registerPacket(Info::HEARTBEAT_PACKET, HeartbeatPacket::class);
		$this->registerPacket(Info::CONNECT_PACKET, ConnectPacket::class);
		$this->registerPacket(Info::DISCONNECT_PACKET, DisconnectPacket::class);
		$this->registerPacket(Info::REDIRECT_PACKET, RedirectPacket::class);
		$this->registerPacket(Info::PLAYER_LOGIN_PACKET, PlayerLoginPacket::class);
		$this->registerPacket(Info::PLAYER_LOGOUT_PACKET, PlayerLogoutPacket::class);
		$this->registerPacket(Info::INFORMATION_PACKET, InformationPacket::class);
		$this->registerPacket(Info::TRANSFER_PACKET, TransferPacket::class);
	}
}