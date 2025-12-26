<?php

/**
 * AllSigns | command sign class
 */

namespace surva\allsigns\sign;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use surva\allsigns\form\CommandSignForm;
use surva\allsigns\util\ExecutionContext;
use surva\allsigns\util\Messages;
use surva\allsigns\util\SignType;

class CommandSign extends MagicSign
{
    /**
     * @inheritDoc
     */
    protected function internallyHandleSignInteraction(Player $player): void
    {
        if ($this->data === null) {
            return;
        }

        $permission = $this->data["settings"]["permission"];
        $command = $this->data["settings"]["command"];
        $context = $this->data["settings"]["context"];

        if ($permission !== "" && !$player->hasPermission($permission)) {
            $this->allSigns->sendMessage($player, "form.nousepermission");

            return;
        }

        $sender = $player;

        if ($context === ExecutionContext::CONTEXT_SERVER) {
            $server = Server::getInstance();
            $sender = new ConsoleCommandSender($server, $server->getLanguage());
        }

        $command = $this->replaceVariables($command, $player);

        $this->allSigns->getServer()->dispatchCommand($sender, $command);
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
          "world" => $wld->getFolderName(),
          "coordinates" => [
            "xc" => $this->signBlock->getPosition()->getX(),
            "yc" => $this->signBlock->getPosition()->getY(),
            "zc" => $this->signBlock->getPosition()->getZ(),
          ],
          "type" => SignType::COMMAND_SIGN,
          "settings" => [
            "command" => $signData["command"],
            "context" => $signData["context"],
            "text" => $text,
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
        $form = new CommandSignForm($this, new Messages($this->allSigns, $player));
        $player->sendForm($form);
    }

    /**
     * Replace variables in a command string
     *
     * @param string $givenCommand
     * @param Player $player
     *
     * @return string
     */
    private function replaceVariables(string $givenCommand, Player $player): string
    {
        $givenCommand = str_replace("{player}", $player->getName(), $givenCommand);
        $givenCommand = str_replace("{xc}", (string) $player->getPosition()->getX(), $givenCommand);
        $givenCommand = str_replace("{yc}", (string) $player->getPosition()->getY(), $givenCommand);
        $givenCommand = str_replace("{zc}", (string) $player->getPosition()->getZ(), $givenCommand);
        return str_replace("{world}", $player->getWorld()->getFolderName(), $givenCommand);
    }
}
