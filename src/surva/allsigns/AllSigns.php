<?php
/**
 * AllSigns | plugin main class
 */

namespace surva\allsigns;

use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use surva\allsigns\tasks\SignUpdate;

class AllSigns extends PluginBase
{

    /* @var Config */
    private $messages;

    /**
     * Plugin has been enabled, initial setup
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->messages = new Config(
          $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getScheduler()->scheduleRepeatingTask(
          new SignUpdate($this),
          ($this->getConfig()->get("updateinterval", 3) * 20)
        );
    }

    /**
     * Updates the content of a world sign
     *
     * @param  Sign  $sign
     * @param  array  $signText
     * @param  string  $worldText
     */
    public function updateWorldSign(Sign $sign, array $signText, string $worldText): void
    {
        if ($level = $this->getServer()->getLevelByName($signText[1])) {
            $sign->setText(
              $worldText,
              $signText[1],
              $signText[2],
              $this->getMessage(
                "players",
                ["count" => count($level->getPlayers())]
              )
            );
        } else {
            $sign->setText(
              $worldText,
              $signText[1],
              $signText[2],
              $this->getMessage("players", ["count" => 0])
            );
        }
    }

    /**
     * Get a translated message
     *
     * @param  string  $key
     * @param  array  $replaces
     *
     * @return string
     */
    public function getMessage(string $key, array $replaces = []): string
    {
        if ($rawMessage = $this->messages->getNested($key)) {
            if (is_array($replaces)) {
                foreach ($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }

            return $rawMessage;
        }

        return $key;
    }

}
