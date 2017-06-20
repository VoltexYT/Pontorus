<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\plugin;

use pontorus\event\Event;
use pontorus\event\Listener;

class MethodEventExecutor implements EventExecutor{

	private $method;

	public function __construct($method){
		$this->method = $method;
	}

	public function execute(Listener $listener, Event $event){
		$listener->{$this->getMethod()}($event);
	}

	public function getMethod(){
		return $this->method;
	}
}