<?php
/**
 * AllSigns | command sign class
 */

namespace surva\allsigns\sign;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
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
        if (($wld = $this->signBlock->getPosition()->getWorld()) === null) {
            return false;
        }

        $this->data = [
          "world"       => $wld->getFolderName(),
          "coordinates" => [
            "xc" => $this->signBlock->getPosition()->getX(),
            "yc" => $this->signBlock->getPosition()->getY(),
            "zc" => $this->signBlock->getPosition()->getZ(),
          ],
          "type"        => SignType::COMMAND_SIGN,
          "settings"    => [
            "command"    => $signData["command"],
            "context"    => $signData["context"],
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
        $form = new CommandSignForm($this->allSigns, $this);
        $player->sendForm($form);
    }

    /**
     * Replace variables in a command string
     *
     * @param  string  $givenCommand
     * @param  \pocketmine\player\Player  $player
     *
     * @return string
     */
    private function replaceVariables(string $givenCommand, Player $player): string
    {
        $givenCommand = str_replace("{player}", $player->getName(), $givenCommand);
        $givenCommand = str_replace("{xc}", $player->getPosition()->getX(), $givenCommand);
        $givenCommand = str_replace("{yc}", $player->getPosition()->getY(), $givenCommand);
        $givenCommand = str_replace("{zc}", $player->getPosition()->getZ(), $givenCommand);

        if (($wld = $player->getWorld()) !== null) {
            $givenCommand = str_replace("{world}", $wld->getFolderName(), $givenCommand);
        }

        return $givenCommand;
    }

}
