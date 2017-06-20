<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


/**
 * Network-related classes
 */
namespace pontorus\network;

use pontorus\network\protocol\mcpe\DataPacket;
use pontorus\Player;

/**
 * Classes that implement this interface will be able to be attached to players
 */
interface SourceInterface{

	/**
	 * Sends a DataPacket to the interface, returns an unique identifier for the packet if $needACK is true
	 *
	 * @param Player     $player
	 * @param DataPacket $packet
	 * @param bool       $needACK
	 * @param bool       $immediate
	 *
	 * @return int
	 */
	public function putPacket(Player $player, DataPacket $packet, $needACK = false, $immediate = true);

	/**
	 * Terminates the connection
	 *
	 * @param Player $player
	 * @param string $reason
	 *
	 */
	public function close(Player $player, $reason = "unknown reason");

	/**
	 * @param string $name
	 */
	public function setName($name);

	/**
	 * @return bool
	 */
	public function process();

	public function shutdown();

	public function emergencyShutdown();

}