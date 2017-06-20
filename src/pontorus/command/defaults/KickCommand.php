<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\command\defaults;

use pontorus\command\Command;
use pontorus\command\CommandSender;
use pontorus\event\TranslationContainer;
use pontorus\Player;
use pontorus\utils\TextFormat;

class KickCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pontorus.command.kick.description",
			"%commands.kick.usage"
		);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$name = array_shift($args);
		$reason = array_shift($args);

		if(($player = $sender->getServer()->getPlayer($name)) instanceof Player){
			$player->close($reason);
			if(strlen($reason) >= 1){
				$sender->sendMessage(new TranslationContainer("commands.kick.success.reason", [$player->getName(), $reason]));
			}else{
				$sender->sendMessage(new TranslationContainer("commands.kick.success", [$player->getName()]));
			}
		}else{
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
		}


		return true;
	}
}
