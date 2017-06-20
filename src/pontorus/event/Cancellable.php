<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\event;


/**
 * Events that can be cancelled must use the interface Cancellable
 */
interface Cancellable{
	public function isCancelled();

	public function setCancelled($forceCancel = false);
}