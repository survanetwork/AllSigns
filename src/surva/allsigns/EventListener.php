<?php

/**
 * AllSigns | EventListener, listen for sign interaction or
 * create/change events
 */

namespace surva\allsigns;

use pocketmine\block\BaseSign;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use surva\allsigns\form\SelectTypeForm;
use surva\allsigns\util\AllSignsGeneral;
use surva\allsigns\util\Messages;

class EventListener implements Listener
{
    private AllSigns $allSigns;

    public function __construct(AllSigns $allSigns)
    {
        $this->allSigns = $allSigns;
    }

    /**
     * Monitor when signs are changing their content to create new magic signs,
     * prevent changing existing MagicSigns
     *
     * @param SignChangeEvent $ev
     *
     * @return void
     */
    public function onSignChange(SignChangeEvent $ev): void
    {
        $pl = $ev->getPlayer();
        $signBlock = $ev->getSign();
        $newText = $ev->getNewText();

        $firstLine = strtolower($newText->getLine(0));

        if (
            $firstLine === AllSignsGeneral::ID_SEPARATOR . "allsigns"
            || $firstLine === AllSignsGeneral::ID_SEPARATOR . "as"
        ) {
            $messages = new Messages($this->allSigns, $pl);

            if (!$pl->hasPermission("allsigns.create")) {
                $pl->sendMessage($messages->getMessage("form.nopermission"));

                return;
            }

            $selectTypeForm = new SelectTypeForm($this->allSigns, $signBlock, $messages);
            $pl->sendForm($selectTypeForm);
        }
    }

    /**
     * Check if a player interacts with a magic sign,
     * run sign action if one exists or edit the sign if player is
     * holding a golden pickaxe
     *
     * @param PlayerInteractEvent $ev
     *
     * @return void
     */
    public function onPlayerInteract(PlayerInteractEvent $ev): void
    {
        $pl = $ev->getPlayer();
        $item = $ev->getItem();
        $block = $ev->getBlock();

        if (!($block instanceof BaseSign)) {
            return;
        }

        if (($sign = $this->allSigns->getMagicSignByBlock($block)) === null) {
            return;
        }

        $mode = $item->getTypeId() === VanillaItems::GOLDEN_PICKAXE()->getTypeId()
          ? AllSignsGeneral::EDIT_MODE
          : AllSignsGeneral::INTERACT_MODE;

        if ($mode === AllSignsGeneral::EDIT_MODE) {
            if (!$pl->hasPermission("allsigns.create")) {
                $this->allSigns->sendMessage($pl, "form.nopermission");

                return;
            }
        } elseif (!$pl->hasPermission("allsigns.use")) {
            $this->allSigns->sendMessage($pl, "form.nousepermission");

            return;
        }

        $sign->handleSignInteraction($pl, $mode);
    }

    /**
     * Check if a player breaks a magic sign and
     * prevent if they don't have permission to do so
     *
     * @param BlockBreakEvent $ev
     *
     * @return void
     */
    public function onBlockBreak(BlockBreakEvent $ev): void
    {
        $pl = $ev->getPlayer();
        $block = $ev->getBlock();

        if (!($block instanceof BaseSign)) {
            return;
        }

        if (($sign = $this->allSigns->getMagicSignByBlock($block)) === null) {
            return;
        }

        if (!$pl->hasPermission("allsigns.create")) {
            $this->allSigns->sendMessage($pl, "form.nopermission");

            $ev->cancel();
            return;
        }

        $sign->remove();
    }
}
