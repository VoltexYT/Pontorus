<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


/**
 * Minecraft: PE multiplayer protocol implementation
 */
namespace pontorus\network\protocol\mcpe;


interface Info{

	/**
	 * Actual Minecraft: PE protocol version
	 */
	const CURRENT_PROTOCOL = 81;
	const ACCEPTED_PROTOCOLS = [81];

	const LOGIN_PACKET = 0x01;
	const PLAY_STATUS_PACKET = 0x02;
	const DISCONNECT_PACKET = 0x05;
	const BATCH_PACKET = 0x06;
	const TEXT_PACKET = 0x07;
	const CHANGE_DIMENSION_PACKET = 0x36;
	const PLAYER_LIST_PACKET = 0x38;
}











