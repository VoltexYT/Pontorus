<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\utils;

class ReversePriorityQueue extends \SplPriorityQueue{

	public function compare($priority1, $priority2){
		return (int) -($priority1 - $priority2);
	}
}