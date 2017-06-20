<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


/**
 * Event related classes
 */
namespace pontorus\event;

abstract class Event{

	/**
	 * Any callable event must declare the static variable
	 *
	 * public static $handlerList = null;
	 * public static $eventPool = [];
	 * public static $nextEvent = 0;
	 *
	 * Not doing so will deny the proper event initialization
	 */

	protected $eventName = null;
	private $isCancelled = false;

	/**
	 * @return string
	 */
	final public function getEventName(){
		return $this->eventName === null ? get_class($this) : $this->eventName;
	}

	/**
	 * @return bool
	 *
	 * @throws \BadMethodCallException
	 */
	public function isCancelled(){
		if(!($this instanceof Cancellable)){
			throw new \BadMethodCallException("Event is not Cancellable");
		}

		/** @var Event $this */
		return $this->isCancelled === true;
	}

	/**
	 * @param bool $value
	 *
	 * @return bool
	 *
	 * @throws \BadMethodCallException
	 */
	public function setCancelled($value = true){
		if(!($this instanceof Cancellable)){
			throw new \BadMethodCallException("Event is not Cancellable");
		}

		/** @var Event $this */
		$this->isCancelled = (bool) $value;
	}

	/**
	 * @return HandlerList
	 */
	public function getHandlers(){
		if(static::$handlerList === null){
			static::$handlerList = new HandlerList();
		}

		return static::$handlerList;
	}

}