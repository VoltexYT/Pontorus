<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\network\synlib;


class SessionManager{
	protected $shutdown = false;

	/** @var pontorusServer */
	protected $server;
	/** @var pontorusSocket */
	protected $socket;
	/** @var Session[] */
	private $sessions = [];

	public function __construct(pontorusServer $server, pontorusSocket $socket){
		$this->server = $server;
		$this->socket = $socket;
		$this->run();
	}

	public function run(){
		$this->tickProcessor();
	}

	private function tickProcessor(){
		while(!$this->server->isShutdown()){
			$start = microtime(true);
			$this->tick();
			$time = microtime(true);
			if($time - $start < 0.01){
				@time_sleep_until($time + 0.01 - ($time - $start));
			}
		}
		$this->tick();
		foreach($this->sessions as $client){
			$client->close();
		}
		$this->socket->close();
	}

	public function getClients(){
		return $this->sessions;
	}

	public function getServer(){
		return $this->server;
	}

	private function tick(){
		try{
			while(($socket = $this->socket->getClient())){
				$session = new Session($this, $socket);
				$this->sessions[$session->getHash()] = $session;
				$this->server->addClientOpenRequest($session->getHash());
			}

			while(strlen($data = $this->server->readMainToThreadPacket()) > 0){
				$tmp = explode("|", $data, 2);
				if(count($tmp) == 2){
					if(isset($this->sessions[$tmp[0]])){
						$this->sessions[$tmp[0]]->writePacket($tmp[1]);
					}
				}
			}

			foreach($this->sessions as $session){
				if($session->update()){
					while(($data = $session->readPacket()) !== null){
						$this->server->pushThreadToMainPacket($session->getHash() . "|" . $data);
					}
				}else{
					$session->close();
					$this->server->addInternalClientCloseRequest($session->getHash());
					unset($this->sessions[$session->getHash()]);
				}
			}
			
			while(strlen($data = $this->server->getExternalClientCloseRequest()) > 0){
				$this->sessions[$data]->close();
				unset($this->sessions[$data]);
			}
		}catch(\Throwable $e){
			$this->server->getLogger()->logException($e);
		}
	}
}