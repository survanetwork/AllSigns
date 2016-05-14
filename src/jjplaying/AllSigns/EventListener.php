<?php
/**
 * Created by PhpStorm.
 * User: jjplaying
 * Date: 14.05.16
 * Time: 12:01
 */

namespace jjplaying\AllSigns;

use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;

class EventListener extends PluginBase implements Listener {
    protected $plugin;

    public function __construct(AllSigns $plugin) {
        $this->plugin = $plugin;
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($block->getId() == Block::SIGN_POST OR $block->getId() == Block::WALL_SIGN) {
            $tile = $block->getLevel()->getTile($block);

            if($tile instanceof Sign) {
                $text = $tile->getText();

                switch($text[0]) {
                    case $this->plugin->getConfig()->get("world"):
                        $level = $this->plugin->getServer()->getLevelByName($text[1]);

                        if($level instanceof Level) {
                            $tile->setText($this->plugin->getConfig()->get("worldtext"), $text[1], $text[2], count($level->getPlayers()) . " " . $this->plugin->getConfig()->get("players"));
                        } else {
                            $block->getLevel()->setBlock($block, Block::AIR);
                            $player->sendMessage($this->plugin->getConfig()->get("noworld"));
                        }
                        return true;
                    case $this->plugin->getConfig()->get("command"):
                        $tile->setText($this->plugin->getConfig()->get("commandtext"), $text[1], $text[2], $text[3]);
                        return true;
                    case $this->plugin->getConfig()->get("worldtext"):
                        $level = $this->plugin->getServer()->getLevelByName($text[1]);

                        if($level instanceof Level) {
                            $player->teleport($level->getSafeSpawn());
                        } else {
                            $player->sendMessage($this->plugin->getConfig()->get("noworld"));
                        }
                        return true;
                    case $this->plugin->getConfig()->get("commandtext"):
                        $this->plugin->getServer()->dispatchCommand($player, $text[2] . $text[3]);
                        return true;
                }
            }
        }
    }
}