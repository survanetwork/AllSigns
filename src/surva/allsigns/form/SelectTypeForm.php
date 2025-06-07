<?php

/**
 * AllSigns | select sign type form
 */

namespace surva\allsigns\form;

use pocketmine\block\BaseSign;
use pocketmine\form\Form;
use pocketmine\player\Player;
use surva\allsigns\AllSigns;
use surva\allsigns\sign\CommandSign;
use surva\allsigns\sign\TeleportSign;
use surva\allsigns\util\SignType;
use surva\allsigns\util\Messages;

class SelectTypeForm implements Form
{
    private AllSigns $allSigns;
    private BaseSign $signBlock;

    private string $type = "custom_form";
    private string $title;

    /**
     * @var mixed[]
     */
    private array $content;

    /**
     * @param  \surva\allsigns\AllSigns  $allSigns
     * @param  \pocketmine\block\BaseSign  $signBlock
     * @param  \surva\allsigns\util\Messages  $messages
     */
    public function __construct(AllSigns $allSigns, BaseSign $signBlock, Messages $messages)
    {
        $this->allSigns  = $allSigns;
        $this->signBlock = $signBlock;

        $this->title   = $messages->getMessage("form.typeselect.title");
        $this->content = [
          [
            "type"    => "dropdown",
            "text"    => $messages->getMessage("form.typeselect.select"),
            "options" => [
              $messages->getMessage("signtypes.command"),
              $messages->getMessage("signtypes.teleport"),
            ],
            "default" => SignType::COMMAND_SIGN,
          ],
        ];
    }

    /**
     * Getting a response from the client form
     *
     * @param  \pocketmine\player\Player  $player
     * @param  mixed  $data
     */
    public function handleResponse(Player $player, $data): void
    {
        if (!is_array($data)) {
            return;
        }

        if (count($data) !== 1) {
            return;
        }

        $type = $data[0];

        switch ($type) {
            case SignType::COMMAND_SIGN:
                $sign = new CommandSign($this->allSigns, $this->signBlock);
                break;
            case SignType::TELEPORT_SIGN:
                $sign = new TeleportSign($this->allSigns, $this->signBlock);
                break;
            default:
                return;
        }

        $sign->sendCreateForm($player);
    }

    /**
     * Return JSON data of the form
     *
     * @return array<string, mixed>
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
