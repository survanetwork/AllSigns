<?php
/**
 * AllSigns | plugin main class
 */

namespace surva\allsigns;

use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use surva\allsigns\tasks\SignUpdate;
use pocketmine\plugin\PluginBase;

class AllSigns extends PluginBase {
    /* @var Config */
    private $messages;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->messages = new Config(
            $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->getScheduler()->scheduleRepeatingTask(
            new SignUpdate($this),
            ($this->getConfig()->get("updateinterval", 3) * 20)
        );
    }

    /**
     * Updates the content of a world sign
     *
     * @param Sign $sign
     * @param array $signText
     * @param string $worldText
     */
    public function updateWorldSign(Sign $sign, array $signText, string $worldText): void {
        if($level = $this->getServer()->getLevelByName($signText[1])) {
            $sign->setText(
                $worldText,
                $signText[1],
                $signText[2],
                $this->getMessage(
                    "players",
                    array("count" => count($level->getPlayers()))
                )
            );
        } else {
            $sign->setText(
                $worldText,
                $signText[1],
                $signText[2],
                $this->getMessage("players", array("count" => 0))
            );
        }
    }

    /**
     * Get a translated message
     *
     * @param string $key
     * @param array $replaces
     * @return string
     */
    public function getMessage(string $key, array $replaces = array()): string {
        if($rawMessage = $this->getMessages()->getNested($key)) {
            if(is_array($replaces)) {
                foreach($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }

            return $rawMessage;
        }

        return $key;
    }

    /**
     * @return Config
     */
    public function getMessages(): Config {
        return $this->messages;
    }
}
