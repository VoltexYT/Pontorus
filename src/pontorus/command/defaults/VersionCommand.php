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
use pontorus\network\protocol\mcpe\Info;
use pontorus\plugin\Plugin;
use pontorus\utils\TextFormat;

class VersionCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pontorus.command.version.description",
			"%pontorus.command.version.usage",
			["ver", "about"]
		);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){

		if(\count($args) === 0){
			$sender->sendMessage(new TranslationContainer("pontorus.server.info.extended", [
				$sender->getServer()->getName(),
				$sender->getServer()->getpontorusVersion(),
				$sender->getServer()->getCodename(),
				$sender->getServer()->getApiVersion(),
				$sender->getServer()->getVersion(),
				Info::CURRENT_PROTOCOL,
			]));
		}else{
			$pluginName = \implode(" ", $args);
			$exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

			if($exactPlugin instanceof Plugin){
				$this->describeToSender($exactPlugin, $sender);

				return \true;
			}

			$found = \false;
			$pluginName = \strtolower($pluginName);
			foreach($sender->getServer()->getPluginManager()->getPlugins() as $plugin){
				if(\stripos($plugin->getName(), $pluginName) !== \false){
					$this->describeToSender($plugin, $sender);
					$found = \true;
				}
			}

			if(!$found){
				$sender->sendMessage(new TranslationContainer("pontorus.command.version.noSuchPlugin"));
			}
		}

		return true;
	}

	private function describeToSender(Plugin $plugin, CommandSender $sender){
		$desc = $plugin->getDescription();
		$sender->sendMessage(TextFormat::DARK_GREEN . $desc->getName() . TextFormat::WHITE . " version " . TextFormat::DARK_GREEN . $desc->getVersion());

		if($desc->getDescription() != \null){
			$sender->sendMessage($desc->getDescription());
		}

		if($desc->getWebsite() != \null){
			$sender->sendMessage("Website: " . $desc->getWebsite());
		}

		if(count($authors = $desc->getAuthors()) > 0){
			if(count($authors) === 1){
				$sender->sendMessage("Author: " . implode(", ", $authors));
			}else{
				$sender->sendMessage("Authors: " . implode(", ", $authors));
			}
		}
	}
}