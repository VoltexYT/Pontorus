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

interface EventExecutor{

	/**
	 * @param Listener $listener
	 * @param Event    $event
	 *
	 * @return void
	 */
	public function execute(Listener $listener, Event $event);
}