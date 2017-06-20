<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\command;


interface CommandSender{

	/**
	 * @param string $message
	 */
	public function sendMessage($message);

	/**
	 * @return \pontorus\Server
	 */
	public function getServer();

	/**
	 * @return string
	 */
	public function getName();


}