<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\plugin;

use pontorus\event\Cancellable;
use pontorus\event\Event;
use pontorus\event\Listener;
use pontorus\event\TimingsHandler;

class RegisteredListener{

	/** @var Listener */
	private $listener;

	/** @var int */
	private $priority;

	/** @var Plugin */
	private $plugin;

	/** @var EventExecutor */
	private $executor;

	/** @var bool */
	private $ignoreCancelled;

	/** @var TimingsHandler */
	private $timings;


	/**
	 * @param Listener       $listener
	 * @param EventExecutor  $executor
	 * @param int            $priority
	 * @param Plugin         $plugin
	 * @param boolean        $ignoreCancelled
	 * @param TimingsHandler $timings
	 */
	public function __construct(Listener $listener, EventExecutor $executor, $priority, Plugin $plugin, $ignoreCancelled, TimingsHandler $timings){
		$this->listener = $listener;
		$this->priority = $priority;
		$this->plugin = $plugin;
		$this->executor = $executor;
		$this->ignoreCancelled = $ignoreCancelled;
		$this->timings = $timings;
	}

	/**
	 * @return Listener
	 */
	public function getListener(){
		return $this->listener;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(){
		return $this->plugin;
	}

	/**
	 * @return int
	 */
	public function getPriority(){
		return $this->priority;
	}

	/**
	 * @param Event $event
	 */
	public function callEvent(Event $event){
		if($event instanceof Cancellable and $event->isCancelled() and $this->isIgnoringCancelled()){
			return;
		}
		$this->timings->startTiming();
		$this->executor->execute($this->listener, $event);
		$this->timings->stopTiming();
	}

	public function __destruct(){
		$this->timings->remove();
	}

	/**
	 * @return bool
	 */
	public function isIgnoringCancelled(){
		return $this->ignoreCancelled === true;
	}
}