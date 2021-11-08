<?php
/**
 * AllSigns | EventListener
 */

namespace surva\allsigns;

use pocketmine\block\BaseSign;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemIds;
use surva\allsigns\form\SelectTypeForm;
use surva\allsigns\util\AllSignsGeneral;

class EventListener implements Listener
{

    private AllSigns $allSigns;

    public function __construct(AllSigns $allSigns)
    {
        $this->allSigns = $allSigns;
    }

    /**
     * Monitor when signs are changing their content to create new magic signs
     *
     * @param  \pocketmine\event\block\SignChangeEvent  $ev
     */
    public function onSignChange(SignChangeEvent $ev): void
    {
        $pl        = $ev->getPlayer();
        $signBlock = $ev->getSign();
        $newText   = $ev->getNewText();

        $firstLine = strtolower($newText->getLine(0));

        if ($firstLine === AllSignsGeneral::ID_SEPARATOR . "allsigns"
            || $firstLine === AllSignsGeneral::ID_SEPARATOR . "as"
        ) {
            if (!$pl->hasPermission("allsigns.create")) {
                $pl->sendMessage($this->allSigns->getMessage("form.nopermission"));

                return;
            }

            $selectTypeForm = new SelectTypeForm($this->allSigns, $signBlock);
            $pl->sendForm($selectTypeForm);
        }
    }

    /**
     * Check if a player interacts with a magic sign
     *
     * @param  \pocketmine\event\player\PlayerInteractEvent  $ev
     */
    public function onPlayerInteract(PlayerInteractEvent $ev): void
    {
        $pl    = $ev->getPlayer();
        $item  = $ev->getItem();
        $block = $ev->getBlock();

        if (!($block instanceof BaseSign)) {
            return;
        }

        if (($sign = $this->allSigns->getMagicSignByBlock($block)) === null) {
            return;
        }

        $mode = $item->getId() === ItemIds::GOLD_PICKAXE ? AllSignsGeneral::EDIT_MODE : AllSignsGeneral::INTERACT_MODE;

        if ($mode === AllSignsGeneral::EDIT_MODE) {
            if (!$pl->hasPermission("allsigns.create")) {
                $pl->sendMessage($this->allSigns->getMessage("form.nopermission"));

                return;
            }
        } elseif (!$pl->hasPermission("allsigns.use")) {
            $pl->sendMessage($this->allSigns->getMessage("form.nousepermission"));

            return;
        }

        $sign->handleSignInteraction($pl, $mode);
    }

    /**
     * Check if a player breaks a magic sign
     *
     * @param  \pocketmine\event\block\BlockBreakEvent  $ev
     */
    public function onBlockBreak(BlockBreakEvent $ev): void
    {
        $pl    = $ev->getPlayer();
        $block = $ev->getBlock();

        if (!($block instanceof BaseSign)) {
            return;
        }

        if (($sign = $this->allSigns->getMagicSignByBlock($block)) === null) {
            return;
        }

        if (!$pl->hasPermission("allsigns.create")) {
            $pl->sendMessage($this->allSigns->getMessage("form.nopermission"));

            $ev->cancel();
            return;
        }

        $sign->remove();
    }

}
