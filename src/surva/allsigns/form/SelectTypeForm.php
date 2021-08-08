<?php
/**
 * AllSigns | select sign type form
 */

namespace surva\allsigns\form;

use pocketmine\block\Block;
use pocketmine\form\Form;
use pocketmine\Player;
use surva\allsigns\AllSigns;
use surva\allsigns\sign\CommandSign;
use surva\allsigns\sign\TeleportSign;
use surva\allsigns\util\SignType;

class SelectTypeForm implements Form
{

    /**
     * @var \surva\allsigns\AllSigns
     */
    private $allSigns;

    /**
     * @var Block
     */
    private $signBlock;

    /**
     * @var string
     */
    private $type = "custom_form";

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * SelectTypeForm constructor.
     *
     * @param  \surva\allsigns\AllSigns  $allSigns
     * @param  \pocketmine\block\Block  $signBlock
     */
    public function __construct(AllSigns $allSigns, Block $signBlock)
    {
        $this->allSigns  = $allSigns;
        $this->signBlock = $signBlock;

        $this->title   = $allSigns->getMessage("form.typeselect.title");
        $this->content = [
          [
            "type"    => "dropdown",
            "text"    => $allSigns->getMessage("form.typeselect.select"),
            "options" => [
              $allSigns->getMessage("signtypes.command"),
              $allSigns->getMessage("signtypes.teleport"),
            ],
            "default" => SignType::COMMAND_SIGN,
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
