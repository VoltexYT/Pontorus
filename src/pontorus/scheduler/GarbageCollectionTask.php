<?php

/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */


namespace pontorus\scheduler;

class GarbageCollectionTask extends AsyncTask{

	public function onRun(){
		gc_enable();
		gc_collect_cycles();
	}
}
