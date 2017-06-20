<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\command;

use pontorus\command\defaults\BanCommand;
use pontorus\command\defaults\BanIpCommand;
use pontorus\command\defaults\BanListCommand;
use pontorus\command\defaults\BiomeCommand;
use pontorus\command\defaults\CaveCommand;
use pontorus\command\defaults\ChunkInfoCommand;
use pontorus\command\defaults\DefaultGamemodeCommand;
use pontorus\command\defaults\DeopCommand;
use pontorus\command\defaults\DifficultyCommand;
use pontorus\command\defaults\DumpMemoryCommand;
use pontorus\command\defaults\EffectCommand;
use pontorus\command\defaults\EnchantCommand;
use pontorus\command\defaults\GamemodeCommand;
use pontorus\command\defaults\GarbageCollectorCommand;
use pontorus\command\defaults\GiveCommand;
use pontorus\command\defaults\HelpCommand;
use pontorus\command\defaults\KickCommand;
use pontorus\command\defaults\KillCommand;
use pontorus\command\defaults\ListCommand;
use pontorus\command\defaults\LoadPluginCommand;
use pontorus\command\defaults\LvdatCommand;
use pontorus\command\defaults\MeCommand;
use pontorus\command\defaults\OpCommand;
use pontorus\command\defaults\PardonCommand;
use pontorus\command\defaults\PardonIpCommand;
use pontorus\command\defaults\ParticleCommand;
use pontorus\command\defaults\PluginsCommand;
use pontorus\command\defaults\ReloadCommand;
use pontorus\command\defaults\SaveCommand;
use pontorus\command\defaults\SaveOffCommand;
use pontorus\command\defaults\SaveOnCommand;
use pontorus\command\defaults\SayCommand;
use pontorus\command\defaults\SeedCommand;
use pontorus\command\defaults\SetBlockCommand;
use pontorus\command\defaults\SetWorldSpawnCommand;
use pontorus\command\defaults\SpawnpointCommand;
use pontorus\command\defaults\StatusCommand;
use pontorus\command\defaults\StopCommand;
use pontorus\command\defaults\SummonCommand;
use pontorus\command\defaults\TeleportCommand;
use pontorus\command\defaults\TellCommand;
use pontorus\command\defaults\TimeCommand;
use pontorus\command\defaults\TimingsCommand;
use pontorus\command\defaults\VanillaCommand;
use pontorus\command\defaults\VersionCommand;
use pontorus\command\defaults\WhitelistCommand;
use pontorus\command\defaults\XpCommand;
use pontorus\command\defaults\FillCommand;
use pontorus\event\TranslationContainer;
use pontorus\Player;
use pontorus\Server;
use pontorus\utils\MainLogger;
use pontorus\utils\TextFormat;

use pontorus\command\defaults\MakeServerCommand;
use pontorus\command\defaults\ExtractPluginCommand;
use pontorus\command\defaults\ExtractPharCommand;
use pontorus\command\defaults\MakePluginCommand;
use pontorus\command\defaults\BancidbynameCommand;
use pontorus\command\defaults\BanipbynameCommand;
use pontorus\command\defaults\BanCidCommand;
use pontorus\command\defaults\PardonCidCommand;
use pontorus\command\defaults\WeatherCommand;

class SimpleCommandMap implements CommandMap{

	/**
	 * @var Command[]
	 */
	protected $knownCommands = [];

	/** @var Server */
	private $server;

	public function __construct(Server $server){
		$this->server = $server;
		$this->setDefaultCommands();
	}

	private function setDefaultCommands(){
		$this->register("pontorus", new ExtractPluginCommand("ep"));
		$this->register("pontorus", new GarbageCollectorCommand("gc"));
		$this->register("pontorus", new HelpCommand("help"));
		$this->register("pontorus", new KickCommand("kick"));
		$this->register("pontorus", new MakePluginCommand("mp"));
		$this->register("pontorus", new MakeServerCommand("ms"));
		$this->register("pontorus", new PluginsCommand("plugins"));
		$this->register("pontorus", new StatusCommand("status"));
		$this->register("pontorus", new StopCommand("stop"));
		$this->register("pontorus", new TimingsCommand("timings"));
		$this->register("pontorus", new VersionCommand("version"));
	}


	public function registerAll($fallbackPrefix, array $commands){
		foreach($commands as $command){
			$this->register($fallbackPrefix, $command);
		}
	}

	public function register($fallbackPrefix, Command $command, $label = null){
		if($label === null){
			$label = $command->getName();
		}
		$label = strtolower(trim($label));
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registered = $this->registerAlias($command, false, $fallbackPrefix, $label);
		
		if(!$registered){
			$command->setLabel($fallbackPrefix . ":" . $label);
		}

		$command->register($this);

		return $registered;
	}

	private function registerAlias(Command $command, $isAlias, $fallbackPrefix, $label){
		$this->knownCommands[$fallbackPrefix . ":" . $label] = $command;
		if(($command instanceof VanillaCommand or $isAlias) and isset($this->knownCommands[$label])){
			return false;
		}

		if(isset($this->knownCommands[$label]) and $this->knownCommands[$label]->getLabel() !== null and $this->knownCommands[$label]->getLabel() === $label){
			return false;
		}

		if(!$isAlias){
			$command->setLabel($label);
		}

		$this->knownCommands[$label] = $command;

		return true;
	}

	public function dispatch(CommandSender $sender, $commandLine){
		$args = explode(" ", $commandLine);

		if(count($args) === 0){
			return false;
		}

		$sentCommandLabel = strtolower(array_shift($args));
		$target = $this->getCommand($sentCommandLabel);

		if($target === null){
			return false;
		}

		$target->timings->startTiming();
		try{
			$target->execute($sender, $sentCommandLabel, $args);
		}catch(\Throwable $e){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.exception"));
			$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pontorus.command.exception", [$commandLine, (string) $target, $e->getMessage()]));
			$logger = $sender->getServer()->getLogger();
			if($logger instanceof MainLogger){
				$logger->logException($e);
			}
		}
		$target->timings->stopTiming();

		return true;
	}

	public function clearCommands(){
		foreach($this->knownCommands as $command){
			$command->unregister($this);
		}
		$this->knownCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand($name){
		if(isset($this->knownCommands[$name])){
			return $this->knownCommands[$name];
		}

		return null;
	}

	/**
	 * @return Command[]
	 */
	public function getCommands(){
		return $this->knownCommands;
	}
}
