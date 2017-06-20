<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\command\defaults;

use pontorus\command\CommandSender;
use pontorus\event\TranslationContainer;
use pontorus\utils\TextFormat;

class PluginsCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pontorus.command.plugins.description",
			"%pontorus.command.plugins.usage",
			["pl"]
		);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		$this->sendPluginList($sender);
		return true;
	}

	private function sendPluginList(CommandSender $sender){
		$list = "";
		foreach(($plugins = $sender->getServer()->getPluginManager()->getPlugins()) as $plugin){
			if(strlen($list) > 0){
				$list .= TextFormat::WHITE . ", ";
			}
			$list .= $plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED;
			$list .= $plugin->getDescription()->getFullName();
		}

		$sender->sendMessage(new TranslationContainer("pontorus.command.plugins.success", [count($plugins), $list]));
	}
}
