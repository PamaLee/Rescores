<?php
/**
 * Created by LFDEVS
 * Maker: COOKIE
 * website： www.lfdevs.com
 * Date: 2019/2/4 0004
 * Time: 11:20
 */
namespace PAMA;
use killer549\timerplus\tasks;
use function PHPSTORM_META\elementType;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;

class Main extends PluginBase implements Listener
{

    public $db;
    public $config;
    public $id;
    public $ca;
    public $one;


    const success=C::GREEN."(*)";
    const error=C::RED."(!)";

    public function onLoad()
    {
        $this->config= new Config($this->getDataFolder()."config.yml");
    }

    public function onEnable()
    {
      $this->getServer()->getPluginManager()->registerEvents($this,$this);
      $this->getLogger()->info("ok");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $cmd = $command->getName();
        if ($cmd == 'zy') {
            if ($sender instanceof Player) {
                if (!isset($args[0])){
                    $sender->sendMessage("§e-----------");
                    $sender->sendMessage("§a>>§d1000金币-10分钟");
                    $sender->sendMessage("§e>>/zy join - 加入资源区");
                    return false;
                }
                if ($args[0]=='join'){
                if ($this->getMoney($sender->getName()) == false) {
                    $sender->sendMessage(self::error."您没有足够的钱！");
                    return false;
                }
                    if ($this->intozy($sender->getName())==false){
                        $sender->sendMessage(self::error."您现在可以进入资源区！！！");
                        return false;
                    }
                    $sender->sendMessage(self::success."成功！请进入资源区！");
                    $this->task($sender->getName());
                    $sender->teleport(new Position(226,69,317,$this->getServer()->getLevelByName("lob")));
                    return true;
                }

            }
            return true;
        }
        return true;
    }

    function onJoin(PlayerJoinEvent $event){
        $player=$event->getPlayer();
        $levelName=$event->getPlayer()->getLevel()->getFolderName();
        if ($levelName=='zy'){
            $player->teleport(new Position(226,69,317,$this->getServer()->getLevelByName("lob")));
            $player->sendMessage(self::error."您已被拉回主城！");
        }
    }


    function onQuit(PlayerQuitEvent $event){
        if ($this->config->get($event->getPlayer()->getName())!=null){
            $this->getScheduler()->cancelTask($this->id);
            $this->config->set($event->getPlayer()->getName(),null);
            $this->config->save();
        }

    }




    function onWorldChange(EntityLevelChangeEvent $event){
        $world_name=$event->getTarget()->getFolderName();

        $player=$event->getEntity();
        if ($player instanceof Player){
            $username=$player->getPlayer()->getName();
            if ($this->ca==true){
                $this->config->set($username,null);
                $this->config->save();
            }
            if ($world_name=="zy"){
                $this->config->reload();
                if ($this->config->get($username)==null){
                    $player->sendMessage(self::error."您没有权限进入！请输入/zy 获得进入许可");
                    $event->setCancelled();

                }else{
                    $player->sendMessage(self::success."欢迎进入！开始您的挖掘之旅吧！");
                }
        }
        }
    }

    public function onMove(PlayerMoveEvent $event):void {

            $player=$event->getPlayer();
            $username=$player->getName();
            $level=$player->getLevel()->getFolderName();
            if ($this->ca==true){
                $this->config->set($username,null);
                $this->config->save();
            }
            if ($level=="zy"){
               $en= $this->config->get($username);
                if ($en!=null){
                    return;
                }else{
                    $player->sendMessage("非法进入！");
                    $player->teleport(new Position(226,69,317,$this->getServer()->getLevelByName("lob")));
                    $event->setCancelled();
                }
            }
        }



    function getMoney($username){
        $mysql=mysqli_connect("localhost","game","1249072779","game");
        $rows=mysqli_query($mysql,"select * from user where username='$username'");
        $money=mysqli_fetch_row($rows);
        print_r($money);
        if ($money[4]<1000){
           return false;
        }
        else{
            $update=mysqli_query($mysql,"update user set coin=coin-1000 where username='$username'");
            if ($update){
                return true;
            }else{
                return false;
            }
        }


    }
    function intozy($username){
        if ($this->config->get($username)!=null){
            return false;
        }
        $this->config->set($username);
        $this->config->save();
        return true;
    }

    function task($username){
      $this->one=$this->getScheduler()->scheduleRepeatingTask(new Countdown($this, $username), 20*60);
      $this->id=$this->one->getTaskId();
      $this->ca=$this->one->isCancelled();
    }


    }

