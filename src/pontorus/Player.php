<?php
/*____             _
|  _ \ ___  _ __ | |_ ___  _ __ _   _ ___
| |_) / _ \| '_ \| __/ _ \| '__| | | / __|
|  __/ (_) | | | | || (_) | |  | |_| \__ \
|_|   \___/|_| |_|\__\___/|_|   \__,_|___/
 */
namespace pontorus;
use pontorus\event\player\PlayerLoginEvent;
use pontorus\event\player\PlayerLogoutEvent;
use pontorus\event\player\PlayerTransferEvent;
use pontorus\event\Timings;
use pontorus\network\protocol\mcpe\BatchPacket;
use pontorus\network\protocol\mcpe\DataPacket;
use pontorus\network\protocol\mcpe\DisconnectPacket;
use pontorus\network\protocol\mcpe\Info;
use pontorus\network\protocol\mcpe\PlayerListPacket;
use pontorus\network\protocol\mcpe\TextPacket;
use pontorus\network\protocol\spp\PlayerLoginPacket;
use pontorus\network\protocol\spp\PlayerLogoutPacket;
use pontorus\network\protocol\spp\RedirectPacket;
use pontorus\network\SourceInterface;
use pontorus\utils\UUID;
use pontorus\utils\TextFormat;
use pontorus\event\TextContainer;
use pontorus\event\TranslationContainer;
class Player{
    /** @var DataPacket */
    private $cachedLoginPacket = null;
    private $name;
    private $ip;
    private $port;
    private $clientId;
    private $randomClientId;
    private $protocol;
    /** @var UUID */
    private $uuid;
    /** @var SourceInterface */
    private $interface;
    /** @var Client */
    private $client;
    /** @var Server */
    private $server;
    private $rawUUID;
    private $isFirstTimeLogin = true;
    private $lastUpdate;
    private $closed = false;
    public function __construct(SourceInterface $interface, $clientId, $ip, int $port){
        $this->interface = $interface;
        $this->clientId = $clientId;
        $this->ip = $ip;
        $this->port = $port;
        $this->name = "Unknown";
        $this->server = Server::getInstance();
        $this->lastUpdate = microtime(true);
    }
    public function getClientId(){
        return $this->randomClientId;
    }
    public function getRawUUID(){
        return $this->rawUUID;
    }
    public function getServer() : Server{
        return $this->server;
    }
    public function handleDataPacket(DataPacket $pk){
        if($this->closed){
            return;
        }
        $timings = Timings::getPlayerReceiveDataPacketTimings($pk);
        $timings->startTiming();
        $this->lastUpdate = microtime(true);
        switch($pk::NETWORK_ID){
            case Info::BATCH_PACKET:
                if($this->cachedLoginPacket == null){
                    /** @var BatchPacket $pk */
                    $this->getServer()->getNetwork()->processBatch($pk, $this);
                }else{
                    $this->redirectPacket($pk->buffer);
                }
                break;
            case Info::LOGIN_PACKET:
                $this->cachedLoginPacket = $pk->buffer;
                $this->name = $pk->username;
                $this->uuid = UUID::fromString($pk->clientUUID);
                $this->rawUUID = $this->uuid->toBinary();
                $this->randomClientId = $pk->clientId;
                $this->protocol = $pk->protocol;
                $this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pontorus.player.logIn", [
                    TextFormat::AQUA . $this->name . TextFormat::WHITE,
                    $this->ip,
                    $this->port,
                    TextFormat::GREEN . $this->randomClientId . TextFormat::WHITE,
                ]));
                $c = $this->server->getMainClients();
                if(count($c) > 0){
                    $clientHash = array_rand($c);
                }else{
                    $clientHash = "";
                }
                $this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin Reason", $clientHash));
                if($ev->isCancelled()){
                    $this->close($ev->getKickMessage());
                    break;
                }
                if(!isset($this->server->getClients()[$ev->getClientHash()])){
                    $this->close("pontorus Server: " . TextFormat::RED . "No server online!");
                    break;
                }
                $this->transfer($this->server->getClients()[$ev->getClientHash()]);
                break;
            default:
                $this->redirectPacket($pk->buffer);
        }
        $timings->stopTiming();
    }
    public function redirectPacket(string $buffer){
        $packet = new RedirectPacket();
        $packet->uuid = $this->uuid;
        $packet->direct = false;
        $packet->mcpeBuffer = $buffer;
        $this->client->sendDataPacket($packet);
    }
    public function getIp(){
        return $this->ip;
    }
    public function getPort() : int{
        return $this->port;
    }
    public function getUUID(){
        return $this->uuid;
    }
    public function getName() : string{
        return $this->name;
    }

    public function onUpdate($currentTick){
        if((microtime(true) - $this->lastUpdate) >= 5 * 60){//5 minutes timeout
            $this->close("timeout");
        }
    }
    public function removeAllPlayer(){
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_REMOVE;
        foreach($this->client->getPlayers() as $p){
            $pk->entries[] = [$p->getUUID()];
        }
        $this->sendDataPacket($pk);
    }
    public function transfer(Client $client, bool $needDisconnect = false){
        $this->server->getPluginManager()->callEvent($ev = new PlayerTransferEvent($this, $client, $needDisconnect));
        if(!$ev->isCancelled()){
            if($this->client instanceof Client and $needDisconnect){
                $pk = new PlayerLogoutPacket();
                $pk->uuid = $this->uuid;
                $pk->reason = "Player has been transferred";
                $this->client->sendDataPacket($pk);
                $this->client->removePlayer($this);
                $this->removeAllPlayer();
            }
            $this->client = $ev->getTargetClient();
            $this->client->addPlayer($this);
            $pk = new PlayerLoginPacket();
            $pk->uuid = $this->uuid;
            $pk->address = $this->ip;
            $pk->port = $this->port;
            $pk->isFirstTime = $this->isFirstTimeLogin;
            $pk->cachedLoginPacket = $this->cachedLoginPacket;
            $this->client->sendDataPacket($pk);
            $this->isFirstTimeLogin = false;
            $this->server->getLogger()->info("{$this->name} has been transferred to {$this->client->getIp()}:{$this->client->getPort()}");
        }
    }
    public function sendDataPacket(DataPacket $pk, $direct = false, $needACK = false){
        if(!$this->closed){
            $timings = Timings::getPlayerSendDataPacketTimings($pk);
            $timings->startTiming();
            $this->interface->putPacket($this, $pk, $needACK, $direct);
            $timings->stopTiming();
        }
    }
    public function close(string $reason = "Generic reason", bool $notify = true){
        if(!$this->closed){
            if($notify and strlen($reason) > 0){
                $pk = new DisconnectPacket();
                $pk->message = $reason;
                $this->sendDataPacket($pk, true);
            }
            $this->server->getPluginManager()->callEvent(new PlayerLogoutEvent($this));
            $this->closed = true;
            if($this->client instanceof Client){
                $pk = new PlayerLogoutPacket();
                $pk->uuid = $this->uuid;
                $pk->reason = $reason;
                $this->client->sendDataPacket($pk);
                $this->client->removePlayer($this);
            }
            $this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pontorus.player.logOut", [
                TextFormat::AQUA . $this->getName() . TextFormat::WHITE,
                $this->ip,
                $this->port,
                $this->getServer()->getLanguage()->translateString($reason)
            ]));
            $this->interface->close($this, $notify ? $reason : "");
            $this->getServer()->removePlayer($this);
        }
    }

    /**
     * Sends a direct chat message to a player
     *
     * @param string|TextContainer $message
     */
    public function sendMessage($message){
        if($message instanceof TextContainer){
            if($message instanceof TranslationContainer){
                $this->sendTranslation($message->getText(), $message->getParameters());
                return;
            }
            $message = $message->getText();
        }
        $mes = explode("\n", $this->server->getLanguage()->translateString($message));
        foreach($mes as $m){
            if($m !== ""){
                $pk = new TextPacket();
                $pk->type = TextPacket::TYPE_RAW;
                $pk->message = $m;
                $this->sendDataPacket($pk);
            }
        }
    }
    public function sendTranslation($message, array $parameters = []){
        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_TRANSLATION;
        $pk->message = $this->server->getLanguage()->translateString($message, $parameters, "pontorus.");
        foreach($parameters as $i => $p){
            $parameters[$i] = $this->server->getLanguage()->translateString($p, $parameters, "pontorus.");
        }
        $pk->parameters = $parameters;
        $this->sendDataPacket($pk);
    }
    public function sendPopup($message, $subtitle = ""){
        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_POPUP;
        $pk->source = $message;
        $pk->message = $subtitle;
        $this->sendDataPacket($pk);
    }
    public function sendTip($message){
        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_TIP;
        $pk->message = $message;
        $this->sendDataPacket($pk);
    }
}