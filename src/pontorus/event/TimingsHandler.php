<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\event;

use pontorus\command\defaults\TimingsCommand;
use pontorus\entity\Living;
use pontorus\plugin\PluginManager;
use pontorus\Server;

class TimingsHandler{

	/** @var TimingsHandler[] */
	private static $HANDLERS = [];

	private $name;
	/** @var TimingsHandler */
	private $parent = null;

	private $count = 0;
	private $curCount = 0;
	private $start = 0;
	private $timingDepth = 0;
	private $totalTime = 0;
	private $curTickTotal = 0;
	private $violations = 0;

	/**
	 * @param string         $name
	 * @param TimingsHandler $parent
	 */
	public function __construct($name, TimingsHandler $parent = null){
		$this->name = $name;
		if($parent !== null){
			$this->parent = $parent;
		}

		self::$HANDLERS[spl_object_hash($this)] = $this;
	}

	public static function printTimings($fp){
		fwrite($fp, "Minecraft" . PHP_EOL);

		foreach(self::$HANDLERS as $timings){
			$time = $timings->totalTime;
			$count = $timings->count;
			if($count === 0){
				continue;
			}

			$avg = $time / $count;

			fwrite($fp, "    " . $timings->name . " Time: " . round($time * 1000000000) . " Count: " . $count . " Avg: " . round($avg * 1000000000) . " Violations: " . $timings->violations . PHP_EOL);
		}

		fwrite($fp, "# Version " . Server::getInstance()->getVersion() . PHP_EOL);
		fwrite($fp, "# " . Server::getInstance()->getName() . " " . Server::getInstance()->getpontorusVersion() . PHP_EOL);

		$entities = 0;
		$livingEntities = 0;

		fwrite($fp, "# Entities " . $entities . PHP_EOL);
		fwrite($fp, "# LivingEntities " . $livingEntities . PHP_EOL);
	}

	public static function reload(){
		if(Server::getInstance()->getPluginManager()->useTimings()){
			foreach(self::$HANDLERS as $timings){
				$timings->reset();
			}
			TimingsCommand::$timingStart = microtime(true);
		}
	}

	public static function tick($measure = true){
		if(PluginManager::$useTimings){
			if($measure){
				foreach(self::$HANDLERS as $timings){
					if($timings->curTickTotal > 0.05){
						$timings->violations += round($timings->curTickTotal / 0.05);
					}
					$timings->curTickTotal = 0;
					$timings->curCount = 0;
					$timings->timingDepth = 0;
				}
			}else{
				foreach(self::$HANDLERS as $timings){
					$timings->totalTime -= $timings->curTickTotal;
					$timings->count -= $timings->curCount;

					$timings->curTickTotal = 0;
					$timings->curCount = 0;
					$timings->timingDepth = 0;
				}
			}
		}
	}

	public function startTiming(){
		if(PluginManager::$useTimings and ++$this->timingDepth === 1){
			$this->start = microtime(true);
			if($this->parent !== null and ++$this->parent->timingDepth === 1){
				$this->parent->start = $this->start;
			}
		}
	}

	public function stopTiming(){
		if(PluginManager::$useTimings){
			if(--$this->timingDepth !== 0 or $this->start === 0){
				return;
			}

			$diff = microtime(true) - $this->start;
			$this->totalTime += $diff;
			$this->curTickTotal += $diff;
			++$this->curCount;
			++$this->count;
			$this->start = 0;
			if($this->parent !== null){
				$this->parent->stopTiming();
			}
		}
	}

	public function reset(){
		$this->count = 0;
		$this->curCount = 0;
		$this->violations = 0;
		$this->curTickTotal = 0;
		$this->totalTime = 0;
		$this->start = 0;
		$this->timingDepth = 0;
	}

	public function remove(){
		unset(self::$HANDLERS[spl_object_hash($this)]);
	}

}