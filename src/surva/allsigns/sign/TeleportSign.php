<?php

/**
 * AllSigns | teleport sign class
 */

namespace surva\allsigns\sign;

use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use surva\allsigns\form\TeleportSignForm;
use surva\allsigns\util\SignType;

class TeleportSign extends MagicSign
{
    /**
     * @inheritDoc
     */
    protected function internallyHandleSignInteraction(Player $player): void
    {
        $permission = $this->data["settings"]["permission"];
        $worldName  = $this->data["settings"]["world"];
        $x          = $this->data["settings"]["xc"];
        $y          = $this->data["settings"]["yc"];
        $z          = $this->data["settings"]["zc"];

        if ($permission !== "" && !$player->hasPermission($permission)) {
            $player->sendMessage($this->allSigns->getMessage("form.nousepermission"));

            return;
        }

        if (!$this->allSigns->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
            $this->allSigns->getServer()->getWorldManager()->loadWorld($worldName);
        }

        $pmWorld = $this->allSigns->getServer()->getWorldManager()->getWorldByName($worldName);

        if ($pmWorld === null) {
            return;
        }

        if ($x !== "" && $y !== "" && $z !== "") {
            $player->teleport(new Position(floatval($x), floatval($y), floatval($z), $pmWorld));
        } else {
            $player->teleport($pmWorld->getSafeSpawn());
        }
    }

    /**
     * @inheritDoc
     */
    public function createSign(array $signData, string $text, string $permission): bool
    {
        try {
            $wld = $this->signBlock->getPosition()->getWorld();
        } catch (AssumptionFailedError $e) {
            return false;
        }

        $this->data = [
          "world"       => $wld->getFolderName(),
          "coordinates" => [
            "xc" => $this->signBlock->getPosition()->getX(),
            "yc" => $this->signBlock->getPosition()->getY(),
            "zc" => $this->signBlock->getPosition()->getZ(),
          ],
          "type"        => SignType::TELEPORT_SIGN,
          "settings"    => [
            "world"      => $signData["world"],
            "xc"         => $signData["xc"],
            "yc"         => $signData["yc"],
            "zc"         => $signData["zc"],
            "text"       => $text,
            "permission" => $permission,
          ],
        ];

        return $this->createSignInternally($text);
    }

    /**
     * @inheritDoc
     */
    public function sendCreateForm(Player $player, ?array $existingData = null): void
    {
        $form = new TeleportSignForm($this->allSigns, $this);
        $player->sendForm($form);
    }
}
