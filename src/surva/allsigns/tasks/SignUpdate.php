<?php
/**
 * AllSigns | sign update task
 */

namespace surva\allsigns\tasks;

use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;
use surva\allsigns\AllSigns;

class SignUpdate extends Task
{

    /* @var AllSigns */
    private $allSigns;

    /**
     * SignUpdate constructor
     *
     * @param  AllSigns  $allSigns
     */
    public function __construct(AllSigns $allSigns)
    {
        $this->allSigns = $allSigns;
    }

    /**
     * Task run
     *
     * @param  int  $currentTick
     */
    public function onRun(int $currentTick): void
    {
        foreach ($this->allSigns->getServer()->getLevels() as $level) {
            foreach ($level->getTiles() as $tile) {
                if ($tile instanceof Sign) {
                    $text = $tile->getText();

                    $worldText = $this->allSigns->getConfig()->getNested("world.text");

                    if ($text[0] === $worldText) {
                        if ($this->allSigns->getServer()->isLevelGenerated($text[1])) {
                            $this->allSigns->updateWorldSign($tile, $text, $worldText);
                        } else {
                            $tile->setText($text[0], $text[1], $text[2], $this->allSigns->getMessage("error"));
                        }
                    }
                }
            }
        }
    }

}
