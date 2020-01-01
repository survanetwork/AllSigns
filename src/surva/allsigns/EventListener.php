<?php
/**
 * AllSigns | EventListener
 */

namespace surva\allsigns;

use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;

class EventListener implements Listener {
    /* @var AllSigns */
    private $allSigns;

    public function __construct(AllSigns $allSigns) {
        $this->allSigns = $allSigns;
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $action = $event->getAction();
        $block = $event->getBlock();

        if(
            (
                $block->getId() === Block::SIGN_POST OR
                $block->getId() === Block::WALL_SIGN
            ) AND
            $action === PlayerInteractEvent::RIGHT_CLICK_BLOCK
        ) {
            $tile = $block->getLevel()->getTile($block);

            if($tile instanceof Sign) {
                $text = $tile->getText();

                $worldIdentifier = $this->allSigns->getConfig()->getNested("world.identifier", "world");
                $worldText = $this->allSigns->getConfig()->getNested("world.text", "§9World");

                $commandIdentifier = $this->allSigns->getConfig()->getNested("command.identifier", "command");
                $commandText = $this->allSigns->getConfig()->getNested("command.text", "§aCommand");

                switch($text[0]) {
                    case $worldIdentifier:
                        if($this->allSigns->getServer()->loadLevel($text[1])) {
                            $this->allSigns->updateWorldSign($tile, $text, $worldText);
                        } else {
                            $block->getLevel()->setBlock($block, Block::get(Block::AIR));

                            $player->sendMessage($this->allSigns->getMessage("noworld"));
                        }
                        break;
                    case $commandIdentifier:
                        $tile->setText($commandText, $text[1], $text[2], $text[3]);
                        break;
                    case $worldText:
                        if($this->allSigns->getServer()->loadLevel($text[1])) {
                            if($level = $this->allSigns->getServer()->getLevelByName($text[1])) {
                                $player->teleport($level->getSafeSpawn());
                            } else {
                                $player->sendMessage($this->allSigns->getMessage("noworld"));
                            }
                        } else {
                            $block->getLevel()->setBlock($block, Block::get(Block::AIR));

                            $player->sendMessage($this->allSigns->getMessage("noworld"));
                        }
                        break;
                    case $commandText:
                        $this->allSigns->getServer()->dispatchCommand($player, $text[2] . $text[3]);
                        break;
                }
            }
        }
    }
}
