<?php
/**
 * Created by PhpStorm.
 * User: jjplaying
 * Date: 14.05.16
 * Time: 12:01
 */

namespace jjplaying\AllSigns;

use jjplaying\AllSigns\Tasks\SignUpdate;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class AllSigns extends PluginBase {
    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new SignUpdate($this), 60);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch(strtolower($command->getName())) {
            case "allsigns":
                $sender->sendMessage("§7This server is using §eAllSigns §7by §bjjplaying §7(https://github.com/jjplaying)");
                return true;
        }
    }
}