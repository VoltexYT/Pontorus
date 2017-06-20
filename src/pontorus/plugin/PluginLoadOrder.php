<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\plugin;


abstract class PluginLoadOrder{
	/*
	 * The plugin will be loaded at startup
	 */
	const STARTUP = 0;

	/*
	 * The plugin will be loaded after the first world has been loaded/created.
	 */
	const POSTWORLD = 1;
}