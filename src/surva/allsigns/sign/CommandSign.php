<?php
/**
 * AllSigns | command sign class
 */

namespace surva\allsigns\sign;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use surva\allsigns\form\CommandSignForm;
use surva\allsigns\util\ExecutionContext;
use surva\allsigns\util\SignType;

class CommandSign extends MagicSign
{

    /**
     * @inheritDoc
     */
    protected function internallyHandleSignInteraction(Player $player): void
    {
        $permission = $this->data["settings"]["permission"];
        $command    = $this->data["settings"]["command"];
        $context    = $this->data["settings"]["context"];

        if ($permission !== "" && !$player->hasPermission($permission)) {
            $player->sendMessage($this->allSigns->getMessage("form.nousepermission"));

            return;
        }

        $sender = $player;

        if ($context === ExecutionContext::CONTEXT_SERVER) {
            $sender = new ConsoleCommandSender();
        }

        $command = $this->replaceVariables($command, $player);

        $this->allSigns->getServer()->dispatchCommand($sender, $command);
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
          "type"        => SignType::COMMAND_SIGN,
          "settings"    => [
            "command"    => $signData["command"],
            "context"    => $signData["context"],
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
        $form = new CommandSignForm($this->allSigns, $this);
        $player->sendForm($form);
    }

    /**
     * Replace variables in a command string
     *
     * @param  string  $givenCommand
     * @param  \pocketmine\Player  $player
     *
     * @return string
     */
    private function replaceVariables(string $givenCommand, Player $player): string
    {
        $givenCommand = str_replace("{player}", $player->getName(), $givenCommand);
        $givenCommand = str_replace("{xc}", $player->getX(), $givenCommand);
        $givenCommand = str_replace("{yc}", $player->getY(), $givenCommand);
        $givenCommand = str_replace("{zc}", $player->getZ(), $givenCommand);

        if (($lvl = $player->getLevel()) !== null) {
            $givenCommand = str_replace("{world}", $lvl->getName(), $givenCommand);
        }

        return $givenCommand;
    }

}
