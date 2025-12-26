<?php

/**
 * AllSigns | create/edit teleport sign form
 */

namespace surva\allsigns\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use surva\allsigns\sign\TeleportSign;
use surva\allsigns\util\Messages;

class TeleportSignForm implements Form
{
    private TeleportSign $sign;
    private Messages $messages;

    private string $type = "custom_form";
    private string $title;
    private array $content;

    public function __construct(TeleportSign $teleportSign, Messages $messages)
    {
        $this->sign = $teleportSign;
        $this->messages = $messages;

        $existingData = $this->sign->getData();

        $wld = $this->sign->getSignBlock()->getPosition()->getWorld();
        $defaultWorld = $wld->getFolderName();

        $this->title = $messages->getMessage("form.teleportsign.title");
        $this->content = [
          [
            "type" => "input",
            "text" => $messages->getMessage("form.teleportsign.world"),
            "default" => $existingData !== null ? $existingData["settings"]["world"] : $defaultWorld,
          ],
          [
            "type" => "input",
            "text" => $messages->getMessage("form.teleportsign.xc"),
            "default" => $existingData !== null ? $existingData["settings"]["xc"] : "",
          ],
          [
            "type" => "input",
            "text" => $messages->getMessage("form.teleportsign.yc"),
            "default" => $existingData !== null ? $existingData["settings"]["yc"] : "",
          ],
          [
            "type" => "input",
            "text" => $messages->getMessage("form.teleportsign.zc"),
            "default" => $existingData !== null ? $existingData["settings"]["zc"] : "",
          ],
          [
            "type" => "input",
            "text" => $messages->getMessage("form.commandsign.text"),
            "default" => $existingData !== null ? $existingData["settings"]["text"] : "",
          ],
          [
            "type" => "input",
            "text" => $messages->getMessage("form.commandsign.permission"),
            "default" => $existingData !== null ? $existingData["settings"]["permission"] : "",
          ],
        ];
    }

    /**
     * Handle form response from client, check if
     * data is valid and create the sign
     *
     * @param Player $player
     * @param $data
     *
     * @return void
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
          "xc" => $data[1],
          "yc" => $data[2],
          "zc" => $data[3],
        ];

        $text = $data[4];
        $permission = $data[5];

        if ($this->sign->createSign($signData, $text, $permission)) {
            $player->sendMessage($this->messages->getMessage("form.teleportsign.success"));
        } else {
            $player->sendMessage($this->messages->getMessage("form.teleportsign.error"));
        }
    }

    /**
     * Return JSON data of the form
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
          "type" => $this->type,
          "title" => $this->title,
          "content" => $this->content,
        ];
    }
}
