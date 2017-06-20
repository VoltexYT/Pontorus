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


class StopCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pontorus.command.stop.description",
			"%commands.stop.usage"
		);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		$sender->getServer()->displayTranslation(new TranslationContainer("commands.stop.start"));

		$sender->getServer()->shutdown();

		return true;
	}
}