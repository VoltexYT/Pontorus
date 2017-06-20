<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\command\defaults;

use pontorus\command\CommandSender;
use pontorus\event\Timings;
use pontorus\scheduler\GarbageCollectionTask;
use pontorus\utils\TextFormat;


class GarbageCollectorCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pontorus.command.gc.description",
			"%pontorus.command.gc.usage"
		);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		Timings::$garbageCollectorTimer->startTiming();

		$size = $sender->getServer()->getScheduler()->getAsyncTaskPoolSize();
		for($i = 0; $i < $size; ++$i){
			$sender->getServer()->getScheduler()->scheduleAsyncTaskToWorker(new GarbageCollectionTask(), $i);
		}

		$sender->sendMessage(TextFormat::GOLD . "Collected cycles: " . gc_collect_cycles());

		Timings::$garbageCollectorTimer->stopTiming();
		return true;
	}
}
