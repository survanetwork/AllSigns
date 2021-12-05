<?php
/**
 * AllSigns | teleport sign class
 */

namespace surva\allsigns\sign;

use pocketmine\level\Position;
use pocketmine\Player;
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

        if (!$this->allSigns->getServer()->isLevelLoaded($worldName)) {
            $this->allSigns->getServer()->loadLevel($worldName);
        }

        $level = $this->allSigns->getServer()->getLevelByName($worldName);

        if ($level === null) {
            return;
        }

        if ($x !== "" && $y !== "" && $z !== "") {
            $player->teleport(new Position(floatval($x), floatval($y), floatval($z), $level));
        } else {
            $player->teleport($level->getSafeSpawn());
        }
    }

    /**
     * @inheritDoc
     */
    public function createSign(array $signData, string $text, string $permission): bool
    {
        if (($lvl = $this->signBlock->getLevel()) === null) {
            return false;
        }

        $this->data = [
          "world"       => $lvl->getName(),
          "coordinates" => [
            "xc" => $this->signBlock->getX(),
            "yc" => $this->signBlock->getY(),
            "zc" => $this->signBlock->getZ(),
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

        return $this->createSignInternally($lvl, $text);
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
