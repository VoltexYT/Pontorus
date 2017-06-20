<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\event;

use pontorus\network\protocol\mcpe\DataPacket;
use pontorus\plugin\PluginManager;
use pontorus\scheduler\PluginTask;
use pontorus\scheduler\TaskHandler;

abstract class Timings{

	/** @var TimingsHandler */
	public static $fullTickTimer;
	/** @var TimingsHandler */
	public static $serverTickTimer;
	/** @var TimingsHandler */
	public static $playerListTimer;
	/** @var TimingsHandler */
	public static $playerNetworkTimer;
	/** @var TimingsHandler */
	public static $playerNetworkReceiveTimer;
	/** @var TimingsHandler */
	public static $connectionTimer;
	/** @var TimingsHandler */
	public static $clientNetworkReceiveTimer;
	/** @var TimingsHandler */
	public static $clientNetworkSendTimer;
	/** @var TimingsHandler */
	public static $tickablesTimer;
	/** @var TimingsHandler */
	public static $schedulerTimer;
	/** @var TimingsHandler */
	public static $serverCommandTimer;

	/** @var TimingsHandler */
	public static $schedulerSyncTimer;
	/** @var TimingsHandler */
	public static $schedulerAsyncTimer;

	/** @var TimingsHandler[] */
	public static $entityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static $tileEntityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static $packetReceiveTimingMap = [];
	/** @var TimingsHandler[] */
	public static $packetSendTimingMap = [];
	/** @var TimingsHandler[] */
	public static $pluginTaskTimingMap = [];

	public static function init(){
		if(self::$serverTickTimer instanceof TimingsHandler){
			return;
		}

		self::$fullTickTimer = new TimingsHandler("Full Server Tick");
		self::$serverTickTimer = new TimingsHandler("** Full Server Tick", self::$fullTickTimer);
		self::$playerListTimer = new TimingsHandler("Player List");
		self::$playerNetworkTimer = new TimingsHandler("Player Network Send");
		self::$playerNetworkReceiveTimer = new TimingsHandler("Player Network Receive");
		self::$connectionTimer = new TimingsHandler("Connection Handler");
		self::$tickablesTimer = new TimingsHandler("Tickables");
		self::$schedulerTimer = new TimingsHandler("Scheduler");
		self::$serverCommandTimer = new TimingsHandler("Server Command");

		self::$schedulerSyncTimer = new TimingsHandler("** Scheduler - Sync Tasks", PluginManager::$pluginParentTimer);
		self::$schedulerAsyncTimer = new TimingsHandler("** Scheduler - Async Tasks");

	}

	/**
	 * @param TaskHandler $task
	 * @param             $period
	 *
	 * @return TimingsHandler
	 */
	public static function getPluginTaskTimings(TaskHandler $task, $period){
		$ftask = $task->getTask();
		if($ftask instanceof PluginTask and $ftask->getOwner() !== null){
			$plugin = $ftask->getOwner()->getDescription()->getFullName();
		}elseif($task->timingName !== null){
			$plugin = "Scheduler";
		}else{
			$plugin = "Unknown";
		}

		$taskname = $task->getTaskName();

		$name = "Task: " . $plugin . " Runnable: " . $taskname;

		if($period > 0){
			$name .= "(interval:" . $period . ")";
		}else{
			$name .= "(Single)";
		}

		if(!isset(self::$pluginTaskTimingMap[$name])){
			self::$pluginTaskTimingMap[$name] = new TimingsHandler($name, self::$schedulerSyncTimer);
		}

		return self::$pluginTaskTimingMap[$name];
	}

	/**
	 * @param DataPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getPlayerReceiveDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetReceiveTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetReceiveTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** playerReceivePacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkReceiveTimer);
		}

		return self::$packetReceiveTimingMap[$pk::NETWORK_ID];
	}


	/**
	 * @param DataPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getPlayerSendDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetSendTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetSendTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** playerSendPacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkTimer);
		}

		return self::$packetSendTimingMap[$pk::NETWORK_ID];
	}

	/**
	 * @param DataPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getClientReceiveDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetReceiveTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetReceiveTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** clientReceivePacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$clientNetworkReceiveTimer);
		}

		return self::$packetReceiveTimingMap[$pk::NETWORK_ID];
	}


	/**
	 * @param DataPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getClientSendDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetSendTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetSendTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** clientSendPacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$clientNetworkSendTimer);
		}

		return self::$packetSendTimingMap[$pk::NETWORK_ID];
	}


}