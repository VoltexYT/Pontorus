<?php
/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */
namespace pontorus {
    use pontorus\utils\Binary;
    use pontorus\utils\MainLogger;
    use pontorus\utils\Terminal;
    use pontorus\utils\Utils;
    const VERSION = "1.0";
    const API_VERSION = "1.0.0";
    const CODENAME = "Encryptus";
    const MINECRAFT_VERSION = "v1.1.x Omega";
    const MINECRAFT_VERSION_NETWORK = "1.1.x";
    if(\Phar::running(true) !== ""){
        @define('pontorus\PATH', \Phar::running(true) . "/");
    }else{
        @define('pontorus\PATH', \getcwd() . DIRECTORY_SEPARATOR);
    }
    if(version_compare("7.0", PHP_VERSION) > 0){
        echo "[CRITICAL] You must use PHP >= 7.0" . PHP_EOL;
        echo "[CRITICAL] Please use the installer provided on the homepage." . PHP_EOL;
        exit(1);
    }
    if(!extension_loaded("pthreads")){
        echo "[CRITICAL] Unable to find the pthreads extension." . PHP_EOL;
        echo "[CRITICAL] Please use the installer provided on the homepage." . PHP_EOL;
        exit(1);
    }
    if(!class_exists("ClassLoader", false)){
        require_once(\pontorus\PATH . "src/spl/ClassLoader.php");
        require_once(\pontorus\PATH . "src/spl/BaseClassLoader.php");
        require_once(\pontorus\PATH . "src/pontorus/CompatibleClassLoader.php");
    }
    $autoloader = new CompatibleClassLoader();
    $autoloader->addPath(\pontorus\PATH . "src");
    $autoloader->addPath(\pontorus\PATH . "src" . DIRECTORY_SEPARATOR . "spl");
    $autoloader->register(true);
    set_time_limit(0); //Who set it to 30 seconds?!?!
    gc_enable();
    error_reporting(-1);
    ini_set("allow_url_fopen", 1);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    ini_set("default_charset", "utf-8");
    ini_set("memory_limit", -1);
    define('pontorus\START_TIME', microtime(true));
    $opts = getopt("", ["data:", "plugins:"]);
    define('pontorus\DATA', isset($opts["data"]) ? $opts["data"] . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR);
    define('pontorus\PLUGIN_PATH', isset($opts["plugins"]) ? $opts["plugins"] . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR);
    Terminal::init();
    define('pontorus\ANSI', Terminal::hasFormattingCodes());
    if(!file_exists(\pontorus\DATA)){
        mkdir(\pontorus\DATA, 0777, true);
    }
    //Logger has a dependency on timezone, so we'll set it to UTC until we can get the actual timezone.
    date_default_timezone_set("UTC");
    $logger = new MainLogger(\pontorus\DATA . "server.log", true);
    if(!ini_get("date.timezone")){
        if(($timezone = detect_system_timezone()) and date_default_timezone_set($timezone)){
            //Success! Timezone has already been set and validated in the if statement.
            //This here is just for redundancy just in case some program wants to read timezone data from the ini.
            ini_set("date.timezone", $timezone);
        }else{
            //If system timezone detection fails or timezone is an invalid value.
            if($response = Utils::getURL("http://ip-api.com/json")
                and $ip_geolocation_data = json_decode($response, true)
                and $ip_geolocation_data['status'] != 'fail'
                and date_default_timezone_set($ip_geolocation_data['timezone'])
            ){
                //Again, for redundancy.
                ini_set("date.timezone", $ip_geolocation_data['timezone']);
            }else{
                ini_set("date.timezone", "UTC");
                date_default_timezone_set("UTC");
                $logger->warning("Timezone could not be automatically determined. An incorrect timezone will result in incorrect timestamps on console logs. It has been set to \"UTC\" by default. You can change it on the php.ini file.");
            }
        }
    }else{
        /*
         * This is here so that people don't come to us complaining and fill up the issue tracker when they put
         * an incorrect timezone abbreviation in php.ini apparently.
         */
        $timezone = ini_get("date.timezone");
        if(strpos($timezone, "/") === false){
            $default_timezone = timezone_name_from_abbr($timezone);
            ini_set("date.timezone", $default_timezone);
            date_default_timezone_set($default_timezone);
        } else {
            date_default_timezone_set($timezone);
        }
    }
    function detect_system_timezone(){
        switch(Utils::getOS()){
            case 'win':
                $regex = '/(UTC)(\+*\-*\d*\d*\:*\d*\d*)/';
                /*
                 * wmic timezone get Caption
                 * Get the timezone offset
                 *
                 * Sample Output var_dump
                 * array(3) {
                 *	  [0] =>
                 *	  string(7) "Caption"
                 *	  [1] =>
                 *	  string(20) "(UTC+09:30) Adelaide"
                 *	  [2] =>
                 *	  string(0) ""
                 *	}
                 */
                exec("wmic timezone get Caption", $output);
                $string = trim(implode("\n", $output));
                //Detect the Time Zone string
                preg_match($regex, $string, $matches);
                if(!isset($matches[2])){
                    return false;
                }
                $offset = $matches[2];
                if($offset == ""){
                    return "UTC";
                }
                return parse_offset($offset);
                break;
            case 'linux':
                // Ubuntu / Debian.
                if(file_exists('/etc/timezone')){
                    $data = file_get_contents('/etc/timezone');
                    if($data){
                        return trim($data);
                    }
                }
                // RHEL / CentOS
                if(file_exists('/etc/sysconfig/clock')){
                    $data = parse_ini_file('/etc/sysconfig/clock');
                    if(!empty($data['ZONE'])){
                        return trim($data['ZONE']);
                    }
                }
                //Portable method for incompatible linux distributions.
                $offset = trim(exec('date +%:z'));
                if($offset == "+00:00"){
                    return "UTC";
                }
                return parse_offset($offset);
                break;
            case 'mac':
                if(is_link('/etc/localtime')){
                    $filename = readlink('/etc/localtime');
                    if(strpos($filename, '/usr/share/zoneinfo/') === 0){
                        $timezone = substr($filename, 20);
                        return trim($timezone);
                    }
                }
                return false;
                break;
            default:
                return false;
                break;
        }
    }
    /**
     * @param string $offset In the format of +09:00, +02:00, -04:00 etc.
     *
     * @return string
     */
    function parse_offset($offset){
        //Make signed offsets unsigned for date_parse
        if(strpos($offset, '-') !== false){
            $negative_offset = true;
            $offset = str_replace('-', '', $offset);
        }else{
            if(strpos($offset, '+') !== false){
                $negative_offset = false;
                $offset = str_replace('+', '', $offset);
            }else{
                return false;
            }
        }
        $parsed = date_parse($offset);
        $offset = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
        //After date_parse is done, put the sign back
        if($negative_offset == true){
            $offset = -abs($offset);
        }
        //And then, look the offset up.
        //timezone_name_from_abbr is not used because it returns false on some(most) offsets because it's mapping function is weird.
        //That's been a bug in PHP since 2008!
        foreach(timezone_abbreviations_list() as $zones){
            foreach($zones as $timezone){
                if($timezone['offset'] == $offset){
                    return $timezone['timezone_id'];
                }
            }
        }
        return false;
    }
    if(isset($opts["enable-profiler"])){
        if(function_exists("profiler_enable")){
            \profiler_enable();
            $logger->notice("Execution is being profiled");
        }else{
            $logger->notice("No profiler found. Please install https://github.com/krakjoe/profiler");
        }
    }
    function kill($pid){
        switch(Utils::getOS()){
            case "win":
                exec("taskkill.exe /F /PID " . ((int) $pid) . " > NUL");
                break;
            case "mac":
            case "linux":
            default:
                if(function_exists("posix_kill")){
                    posix_kill($pid, SIGKILL);
                }else{
                    exec("kill -9 " . ((int)$pid) . " > /dev/null 2>&1");
                }
        }
    }
    /**
     * @param object $value
     * @param bool   $includeCurrent
     *
     * @return int
     */
    function getReferenceCount($value, $includeCurrent = true){
        ob_start();
        debug_zval_dump($value);
        $ret = explode("\n", ob_get_contents());
        ob_end_clean();
        if(count($ret) >= 1 and preg_match('/^.* refcount\\(([0-9]+)\\)\\{$/', trim($ret[0]), $m) > 0){
            return ((int) $m[1]) - ($includeCurrent ? 3 : 4); //$value + zval call + extra call
        }
        return -1;
    }
    function getTrace($start = 1, $trace = null){
        if($trace === null){
            if(function_exists("xdebug_get_function_stack")){
                $trace = array_reverse(xdebug_get_function_stack());
            }else{
                $e = new \Exception();
                $trace = $e->getTrace();
            }
        }
        $messages = [];
        $j = 0;
        for($i = (int) $start; isset($trace[$i]); ++$i, ++$j){
            $params = "";
            if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
                if(isset($trace[$i]["args"])){
                    $args = $trace[$i]["args"];
                }else{
                    $args = $trace[$i]["params"];
                }
                foreach($args as $name => $value){
                    $params .= (is_object($value) ? get_class($value) . " " . (method_exists($value, "__toString") ? $value->__toString() : "object") : gettype($value) . " " . (is_array($value) ? "Array()" : Utils::printable(@strval($value)))) . ", ";
                }
            }
            $messages[] = "#$j " . (isset($trace[$i]["file"]) ? cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Utils::printable(substr($params, 0, -2)) . ")";
        }
        return $messages;
    }
    function cleanPath($path){
        return rtrim(str_replace(["\\", ".php", "phar://", rtrim(str_replace(["\\", "phar://"], ["/", ""], \pontorus\PATH), "/"), rtrim(str_replace(["\\", "phar://"], ["/", ""], \pontorus\PLUGIN_PATH), "/")], ["/", "", "", "", ""], $path), "/");
    }
    $errors = 0;
    if(php_sapi_name() !== "cli"){
        $logger->critical("You must run pontorus-MP using the CLI.");
        ++$errors;
    }
    if(!extension_loaded("sockets")){
        $logger->critical("Unable to find the Socket extension.");
        ++$errors;
    }
    $pthreads_version = phpversion("pthreads");
    if(substr_count($pthreads_version, ".") < 2){
        $pthreads_version = "0.$pthreads_version";
    }
    if(version_compare($pthreads_version, "3.1.5") < 0){
        $logger->critical("pthreads >= 3.1.5 is required, while you have $pthreads_version.");
        ++$errors;
    }
    if(!extension_loaded("curl")){
        $logger->critical("Unable to find the cURL extension.");
        ++$errors;
    }
    if(!extension_loaded("yaml")){
        $logger->critical("Unable to find the YAML extension.");
        ++$errors;
    }
    if(!extension_loaded("sqlite3")){
        $logger->critical("Unable to find the SQLite3 extension.");
        ++$errors;
    }
    if(!extension_loaded("zlib")){
        $logger->critical("Unable to find the Zlib extension.");
        ++$errors;
    }
    if($errors > 0){
        $logger->critical("Please use the installer provided on the homepage, or recompile PHP again.");
        $logger->shutdown();
        $logger->join();
        exit(1); //Exit with error
    }
    if(file_exists(\pontorus\PATH . ".git/refs/heads/master")){ //Found Git information!
        define('pontorus\GIT_COMMIT', strtolower(trim(file_get_contents(\pontorus\PATH . ".git/refs/heads/master"))));
    }else{ //Unknown :(
        define('pontorus\GIT_COMMIT', str_repeat("00", 20));
    }
    @define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
    @define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);
    @ini_set("opcache.mmap_base", bin2hex(Utils::getRandomBytes(8, false))); //Fix OPCache address errors
    ThreadManager::init();
    $server = new Server($autoloader, $logger, \pontorus\PATH, \pontorus\DATA, \pontorus\PLUGIN_PATH);
    $logger->info("Stopping other threads");
    foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
        $logger->debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
        $thread->quit();
    }
    $logger->shutdown();
    $logger->join();
    echo "Server has stopped" . Terminal::$FORMAT_RESET . "\n";
    exit(0);
}