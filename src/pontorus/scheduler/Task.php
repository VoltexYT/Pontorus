<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\scheduler;

abstract class Task{

	/** @var TaskHandler */
	private $taskHandler = null;

	/**
	 * @return TaskHandler
	 */
	public final function getHandler(){
		return $this->taskHandler;
	}

	/**
	 * @return int
	 */
	public final function getTaskId(){
		if($this->taskHandler !== null){
			return $this->taskHandler->getTaskId();
		}

		return -1;
	}

	/**
	 * @param TaskHandler $taskHandler
	 */
	public final function setHandler($taskHandler){
		if($this->taskHandler === null or $taskHandler === null){
			$this->taskHandler = $taskHandler;
		}
	}

	/**
	 * Actions to execute when run
	 *
	 * @param $currentTick
	 *
	 * @return void
	 */
	public abstract function onRun($currentTick);

	/**
	 * Actions to execute if the Task is cancelled
	 */
	public function onCancel(){

	}

}
