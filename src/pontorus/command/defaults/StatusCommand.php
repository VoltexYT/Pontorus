<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\command\defaults;

use pontorus\command\CommandSender;
use pontorus\utils\TextFormat;
use pontorus\utils\Utils;

class StatusCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pontorus.command.status.description",
			"%pontorus.command.status.usage"
		);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		$mUsage = Utils::getMemoryUsage(true);
		$rUsage = Utils::getRealMemoryUsage();

		$server = $sender->getServer();
		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "%pontorus.command.status.title" . TextFormat::GREEN . " ----");
		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.player" . TextFormat::GREEN ." ". \count($sender->getServer()->getOnlinePlayers()) . "/" . $sender->getServer()->getMaxPlayers());

		$time = microtime(true) - \pontorus\START_TIME;

		$seconds = floor($time % 60);
		$minutes = null;
		$hours = null;
		$days = null;

		if($time >= 60){
			$minutes = floor(($time % 3600) / 60);
			if($time >= 3600){
				$hours = floor(($time % (3600 * 24)) / 3600);
				if($time >= 3600 * 24){
					$days = floor($time / (3600 * 24));
				}
			}
		}

		$uptime = ($minutes !== null ?
				($hours !== null ?
					($days !== null ?
						"$days %pontorus.command.status.days "
						: "") . "$hours %pontorus.command.status.hours "
					: "") . "$minutes %pontorus.command.status.minutes "
				: "") . "$seconds %pontorus.command.status.seconds";

		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.uptime " . TextFormat::RED . $uptime);

		$tpsColor = TextFormat::GREEN;
		if($server->getTicksPerSecondAverage() < 10){
			$tpsColor = TextFormat::GOLD;
		}elseif($server->getTicksPerSecondAverage() < 1){
			$tpsColor = TextFormat::RED;
		}

		$tpsColour = TextFormat::GREEN;
		if($server->getTicksPerSecond() < 10){
			$tpsColour = TextFormat::GOLD;
		}elseif($server->getTicksPerSecond() < 1){
			$tpsColour = TextFormat::RED;
		}

		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.AverageTPS " . $tpsColor . $server->getTicksPerSecondAverage() . " (" . $server->getTickUsageAverage() . "%)");
		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.CurrentTPS " . $tpsColour . $server->getTicksPerSecond() . " (" . $server->getTickUsage() . "%)");

		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Networkupload " . TextFormat::RED . \round($server->getNetwork()->getUpload() / 1024, 2) . " kB/s");
		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Networkdownload " . TextFormat::RED . \round($server->getNetwork()->getDownload() / 1024, 2) . " kB/s");

		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Threadcount " . TextFormat::RED . Utils::getThreadCount());

		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Mainmemory " . TextFormat::RED . number_format(round(($mUsage[0] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Totalmemory " . TextFormat::RED . number_format(round(($mUsage[1] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Totalvirtualmemory " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Heapmemory " . TextFormat::RED . number_format(round(($rUsage[0] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "%pontorus.command.status.Maxmemorysystem " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");

		return true;
	}
}
