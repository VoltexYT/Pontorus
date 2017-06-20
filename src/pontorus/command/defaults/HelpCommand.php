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
use pontorus\command\ConsoleCommandSender;
use pontorus\event\TranslationContainer;
use pontorus\utils\TextFormat;

class HelpCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pontorus.command.help.description",
			"%commands.help.usage",
			["?"]
		);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){

		if(count($args) === 0){
			$command = "";
			$pageNumber = 1;
		}elseif(is_numeric($args[count($args) - 1])){
			$pageNumber = (int) array_pop($args);
			if($pageNumber <= 0){
				$pageNumber = 1;
			}
			$command = implode(" ", $args);
		}else{
			$command = implode(" ", $args);
			$pageNumber = 1;
		}

		if($sender instanceof ConsoleCommandSender){
			$pageHeight = PHP_INT_MAX;
		}else{
			$pageHeight = 5;
		}

		if($command === ""){
			/** @var Command[][] $commands */
			$commands = [];
			foreach($sender->getServer()->getCommandMap()->getCommands() as $command){
				$commands[$command->getName()] = $command;
			}
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			$commands = array_chunk($commands, $pageHeight);
			$pageNumber = (int) min(count($commands), $pageNumber);
			if($pageNumber < 1){
				$pageNumber = 1;
			}
			$sender->sendMessage(new TranslationContainer("commands.help.header", [$pageNumber, count($commands)]));
			if(isset($commands[$pageNumber - 1])){
				foreach($commands[$pageNumber - 1] as $command){
					$sender->sendMessage(TextFormat::DARK_GREEN . "/" . $command->getName() . ": " . TextFormat::WHITE . $command->getDescription());
				}
			}

			return true;
		}else{
			if(($cmd = $sender->getServer()->getCommandMap()->getCommand(strtolower($command))) instanceof Command){
				$message = TextFormat::YELLOW . "--------- " . TextFormat::WHITE . " Help: /" . $cmd->getName() . TextFormat::YELLOW . " ---------\n";
				$message .= TextFormat::GOLD . "Description: " . TextFormat::WHITE . $cmd->getDescription() . "\n";
				$message .= TextFormat::GOLD . "Usage: " . TextFormat::WHITE . implode("\n" . TextFormat::WHITE, explode("\n", $cmd->getUsage())) . "\n";
				$sender->sendMessage($message);

				return true;
			}
			$sender->sendMessage(TextFormat::RED . "No help for " . strtolower($command));

			return true;
		}
	}

}