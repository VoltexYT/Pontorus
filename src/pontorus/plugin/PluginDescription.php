<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\plugin;

use pontorus\utils\PluginException;

class PluginDescription{
	private $name;
	private $main;
	private $api;
	private $depend = [];
	private $softDepend = [];
	private $loadBefore = [];
	private $version;
	private $commands = [];
	private $description = null;
	private $authors = [];
	private $website = null;
	private $prefix = null;

	/**
	 * @param string|array $yamlString
	 */
	public function __construct($yamlString){
		$this->loadMap(!is_array($yamlString) ? \yaml_parse($yamlString) : $yamlString);
	}

	/**
	 * @param array $plugin
	 *
	 * @throws PluginException
	 */
	private function loadMap(array $plugin){
		$this->name = preg_replace("[^A-Za-z0-9 _.-]", "", $plugin["name"]);
		if($this->name === ""){
			throw new PluginException("Invalid PluginDescription name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = $plugin["version"];
		$this->main = $plugin["main"];
		$this->api = !is_array($plugin["api"]) ? [$plugin["api"]] : $plugin["api"];

		if(stripos($this->main, "pontorus\\") === 0){
			throw new PluginException("Invalid PluginDescription main, cannot start within the PocketMine namespace");
		}

		if(isset($plugin["commands"]) and is_array($plugin["commands"])){
			$this->commands = $plugin["commands"];
		}

		if(isset($plugin["depend"])){
			$this->depend = (array) $plugin["depend"];
		}
		if(isset($plugin["softdepend"])){
			$this->softDepend = (array) $plugin["softdepend"];
		}
		if(isset($plugin["loadbefore"])){
			$this->loadBefore = (array) $plugin["loadbefore"];
		}

		if(isset($plugin["website"])){
			$this->website = $plugin["website"];
		}
		if(isset($plugin["description"])){
			$this->description = $plugin["description"];
		}
		if(isset($plugin["prefix"])){
			$this->prefix = $plugin["prefix"];
		}
		$this->authors = [];
		if(isset($plugin["author"])){
			$this->authors[] = $plugin["author"];
		}
		if(isset($plugin["authors"])){
			foreach($plugin["authors"] as $author){
				$this->authors[] = $author;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getFullName(){
		return $this->name . " v" . $this->version;
	}

	/**
	 * @return array
	 */
	public function getCompatibleApis(){
		return $this->api;
	}

	/**
	 * @return array
	 */
	public function getAuthors(){
		return $this->authors;
	}

	/**
	 * @return string
	 */
	public function getPrefix(){
		return $this->prefix;
	}

	/**
	 * @return array
	 */
	public function getCommands(){
		return $this->commands;
	}

	/**
	 * @return array
	 */
	public function getDepend(){
		return $this->depend;
	}

	/**
	 * @return string
	 */
	public function getDescription(){
		return $this->description;
	}

	/**
	 * @return array
	 */
	public function getLoadBefore(){
		return $this->loadBefore;
	}

	/**
	 * @return string
	 */
	public function getMain(){
		return $this->main;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getSoftDepend(){
		return $this->softDepend;
	}

	/**
	 * @return string
	 */
	public function getVersion(){
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getWebsite(){
		return $this->website;
	}
}