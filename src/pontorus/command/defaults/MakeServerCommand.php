<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */

namespace pontorus\command\defaults;

use pontorus\command\CommandSender;
use pontorus\Server;
use pontorus\network\protocol\mcpe\Info;

class MakeServerCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Creates a PocketMine Phar",
			"/makeserver (nogz)"
		);
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){

		$server = $sender->getServer();
		$pharPath = Server::getInstance()->getPluginPath() . DIRECTORY_SEPARATOR . "pontorus" . DIRECTORY_SEPARATOR . $server->getName() . "_" . $server->getpontorusVersion() . ".phar";
		if(file_exists($pharPath)){
			$sender->sendMessage("Phar file already exists, overwriting...");
			@unlink($pharPath);
		}
		$phar = new \Phar($pharPath);
		$phar->setMetadata([
			"name" => $server->getName(),
			"version" => $server->getpontorusVersion(),
			"api" => $server->getApiVersion(),
			"minecraft" => $server->getVersion(),
			"protocol" => Info::CURRENT_PROTOCOL,
			"creator" => "pontorus MakeServerCommand",
			"creationDate" => time()
		]);
		$phar->setStub('<?php define("pontorus\\\\PATH", "phar://". __FILE__ ."/"); require_once("phar://". __FILE__ ."/src/pontorus/pontorus.php");  __HALT_COMPILER();');
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$phar->startBuffering();

		$filePath = substr(\pontorus\PATH, 0, 7) === "phar://" ? \pontorus\PATH : realpath(\pontorus\PATH) . "/";
		$filePath = rtrim(str_replace("\\", "/", $filePath), "/") . "/";
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath . "src")) as $file){
			$path = ltrim(str_replace(["\\", $filePath], ["/", ""], $file), "/");
			if($path{0} === "." or strpos($path, "/.") !== false or substr($path, 0, 4) !== "src/"){
				continue;
			}
			$phar->addFile($file, $path);
			$sender->sendMessage("[pontorus] Adding $path");
		}
		foreach($phar as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				$finfo->compress(\Phar::GZ);
			}
		}
		if(!isset($args[0]) or (isset($args[0]) and $args[0] != "nogz")){
			$phar->compressFiles(\Phar::GZ);
		}
		$phar->stopBuffering();

		$sender->sendMessage($server->getName() . " " . $server->getpontorusVersion() . " Phar file has been created on " . $pharPath);

		return true;
	}
}
