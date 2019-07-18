<?php
namespace PAMA;
use pocketmine\level\Position;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
class Countdown extends Task
{
    /** @var int $time */
    public $time = 10; //sets the countdown to 10 seconds
    public $plugin;
    public $username;
    public $configs;
    public $path;
    public function __construct(Main $plugin, $username)
    {
        $this->plugin = $plugin;
        $this->username = $username;
        $this->path=$this->plugin->getDataFolder();
        $this->configs = new Config($this->path."config.yml", Config::YAML,array());
    }

    public function onRun(int $currentTick): void
    {
        $this->time--;
        if ($this->configs->get($this->username)==null){
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
            return;
        }else{
            Server::getInstance()->getPlayer($this->username)->sendTip(TextFormat::GREEN."剩余时间>>".TextFormat::RED.$this->time."分");
            if($this->time == 0){
                $this->configs->set($this->username,null);
                $in=$this->configs->save();
                $this->configs->reload();
                if (!$in){
                    Server::getInstance()->getPlayer($this->username)->sendMessage("错误！");
                }

                Server::getInstance()->getPlayer($this->username)->teleport(new Position(226,69,317,Server::getInstance()->getLevelByName("lob")));
                Server::getInstance()->getPlayer($this->username)->sendMessage("10分钟到！您被传送回主城！");
                $this->plugin->getScheduler()->cancelTask($this->getTaskId());

            }
        }

    }
}