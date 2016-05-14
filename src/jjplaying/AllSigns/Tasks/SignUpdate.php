<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 14.05.16
 * Time: 12:23
 */

namespace jjplaying\AllSigns\Tasks;

use jjplaying\AllSigns\AllSigns;
use pocketmine\level\Level;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Sign;

class SignUpdate extends PluginTask {
    private $plugin;

    public function __construct(AllSigns $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun($currentTick) {
        foreach($this->plugin->getServer()->getLevels() as $level) {
            foreach($level->getTiles() as $tile) {
                if($tile instanceof Sign) {
                    $text = $tile->getText();

                    switch($text[0]) {
                        case $this->plugin->getConfig()->get("worldtext"):
                            $level = $this->plugin->getServer()->getLevelByName($text[1]);

                            if($level instanceof Level) {
                                $tile->setText($text[0], $text[1], $text[2], count($level->getPlayers()) . " " . $this->plugin->getConfig()->get("players"));
                            } else {
                                $tile->setText($text[0], $text[1], $text[2], $this->plugin->getConfig()->get("error"));
                            }
                            return true;
                    }
                }
            }
        }
    }
}