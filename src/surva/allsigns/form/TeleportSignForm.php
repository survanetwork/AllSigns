<?php
/**
 * AllSigns | create/edit teleport sign form
 */

namespace surva\allsigns\form;

use pocketmine\form\Form;
use pocketmine\Player;
use surva\allsigns\AllSigns;
use surva\allsigns\sign\TeleportSign;

class TeleportSignForm implements Form
{

    private AllSigns $allSigns;

    private TeleportSign $sign;

    private string $type = "custom_form";

    private string $title;

    private array $content;

    /**
     * @param  \surva\allsigns\AllSigns  $allSigns
     * @param  \surva\allsigns\sign\TeleportSign  $teleportSign
     */
    public function __construct(AllSigns $allSigns, TeleportSign $teleportSign)
    {
        $this->allSigns = $allSigns;
        $this->sign     = $teleportSign;

        $existingData = $this->sign->getData();

        $defaultWorld = "world";

        if (($lvl = $this->sign->getSignBlock()->getLevel()) !== null) {
            $defaultWorld = $lvl->getName();
        }

        $this->title   = $allSigns->getMessage("form.teleportsign.title");
        $this->content = [
          [
            "type"    => "input",
            "text"    => $allSigns->getMessage("form.teleportsign.world"),
            "default" => $existingData !== null ? $existingData["settings"]["world"] : $defaultWorld,
          ],
          [
            "type"    => "input",
            "text"    => $allSigns->getMessage("form.teleportsign.xc"),
            "default" => $existingData !== null ? $existingData["settings"]["xc"] : "",
          ],
          [
            "type"    => "input",
            "text"    => $allSigns->getMessage("form.teleportsign.yc"),
            "default" => $existingData !== null ? $existingData["settings"]["yc"] : "",
          ],
          [
            "type"    => "input",
            "text"    => $allSigns->getMessage("form.teleportsign.zc"),
            "default" => $existingData !== null ? $existingData["settings"]["zc"] : "",
          ],
          [
            "type"    => "input",
            "text"    => $allSigns->getMessage("form.commandsign.text"),
            "default" => $existingData !== null ? $existingData["settings"]["text"] : "",
          ],
          [
            "type"    => "input",
            "text"    => $allSigns->getMessage("form.commandsign.permission"),
            "default" => $existingData !== null ? $existingData["settings"]["permission"] : "",
          ],
        ];
    }

    /**
     * Getting a response from the client form
     *
     * @param  \pocketmine\Player  $player
     * @param  mixed  $data
     */
    public function handleResponse(Player $player, $data): void
    {
        if (!is_array($data)) {
            return;
        }

        if (count($data) !== 6) {
            return;
        }

        $signData = [
          "world" => $data[0],
          "xc"    => $data[1],
          "yc"    => $data[2],
          "zc"    => $data[3],
        ];

        $text       = $data[4];
        $permission = $data[5];

        if ($this->sign->createSign($signData, $text, $permission)) {
            $player->sendMessage($this->allSigns->getMessage("form.teleportsign.success"));
        } else {
            $player->sendMessage($this->allSigns->getMessage("form.teleportsign.error"));
        }
    }

    /**
     * Return JSON data of the form
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
          "type"    => $this->type,
          "title"   => $this->title,
          "content" => $this->content,
        ];
    }

}
