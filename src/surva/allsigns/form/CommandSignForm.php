<?php

/**
 * AllSigns | create/edit command sign form
 */

namespace surva\allsigns\form;

use pocketmine\form\Form;
use pocketmine\player\Player;
use surva\allsigns\sign\CommandSign;
use surva\allsigns\util\ExecutionContext;
use surva\allsigns\util\Messages;

class CommandSignForm implements Form
{
    private CommandSign $sign;

    private string $type = "custom_form";
    private string $title;

    /**
     * @var mixed[]
     */
    private array $content;

    private Messages $messages;

    /**
     * @param  \surva\allsigns\sign\CommandSign  $commandSign
     * @param  \surva\allsigns\util\Messages  $messages
     */
    public function __construct(CommandSign $commandSign, Messages $messages)
    {
        $this->sign     = $commandSign;
        $this->messages = $messages;

        $existingData = $this->sign->getData();

        $this->title   = $messages->getMessage("form.commandsign.title");
        $this->content = [
          [
            "type"    => "input",
            "text"    => $messages->getMessage("form.commandsign.command"),
            "default" => $existingData !== null ? $existingData["settings"]["command"] : "",
          ],
          [
            "type"    => "dropdown",
            "text"    => $messages->getMessage("form.commandsign.context"),
            "options" => [
              $messages->getMessage("form.commandsign.player"),
              $messages->getMessage("form.commandsign.server"),
            ],
            "default" => $existingData !== null ? $existingData["settings"]["context"]
              : ExecutionContext::CONTEXT_PLAYER,
          ],
          [
            "type"    => "input",
            "text"    => $messages->getMessage("form.commandsign.text"),
            "default" => $existingData !== null ? $existingData["settings"]["text"] : "",
          ],
          [
            "type"    => "input",
            "text"    => $messages->getMessage("form.commandsign.permission"),
            "default" => $existingData !== null ? $existingData["settings"]["permission"] : "",
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

        if (count($data) !== 4) {
            return;
        }

        $signData = [
          "command" => $data[0],
          "context" => $data[1],
        ];

        $text       = $data[2];
        $permission = $data[3];

        if ($this->sign->createSign($signData, $text, $permission)) {
            $player->sendMessage($this->messages->getMessage("form.commandsign.success"));
        } else {
            $player->sendMessage($this->messages->getMessage("form.commandsign.error"));
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
          "type"    => $this->type,
          "title"   => $this->title,
          "content" => $this->content,
        ];
    }
}
