<?php
/**
 * AllSigns | plugin main class
 */

namespace surva\allsigns;

use pocketmine\block\BaseSign;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use surva\allsigns\sign\CommandSign;
use surva\allsigns\sign\MagicSign;
use surva\allsigns\sign\TeleportSign;
use surva\allsigns\util\AllSignsGeneral;
use surva\allsigns\util\SignType;

class AllSigns extends PluginBase
{

    private Config $signStorage;

    private array $signs;

    private Config $defaultMessages;

    private Config $messages;

    /**
     * Plugin has been enabled, initial setup
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->signStorage = new Config($this->getDataFolder() . "signs.yml");

        if (!$this->signStorage->exists("signs")) {
            $this->signStorage->set("signs", []);
        }

        $this->signs = [];

        $this->defaultMessages = new Config($this->getFile() . "resources/languages/en.yml");
        $this->messages        = new Config(
          $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    /**
     * Get a MagicSign object using the associated block
     *
     * @param  \pocketmine\block\BaseSign  $signBlock
     *
     * @return \surva\allsigns\sign\MagicSign|null
     */
    public function getMagicSignByBlock(BaseSign $signBlock): ?MagicSign
    {
        if (($idStr = $this->isMagicSign($signBlock)) === null) {
            return null;
        }

        $id = intval($idStr);

        if (($sign = $this->loadMagicSign($id, $signBlock)) === null) {
            return null;
        }

        if (($data = $sign->getData()) === null) {
            return null;
        }

        if (($world = $signBlock->getPosition()->getWorld()) === null) {
            return null;
        }

        if ($level->getName() !== $data["world"]) {
            return null;
        }

        if ($signBlock->getPosition()->getX() !== $data["coordinates"]["xc"]
            || $signBlock->getPosition()->getY() !== $data["coordinates"]["yc"]
            || $signBlock->getPosition()->getZ() !== $data["coordinates"]["zc"]
        ) {
            return null;
        }

        return $sign;
    }

    /**
     * Get a sign from the sign object array or load from config
     *
     * @param  int  $id
     * @param  \pocketmine\block\BaseSign  $signBlock
     *
     * @return \surva\allsigns\sign\MagicSign|null
     */
    private function loadMagicSign(int $id, BaseSign $signBlock): ?MagicSign
    {
        if (isset($this->signs[$id])) {
            return $this->signs[$id];
        }

        $data = $this->signStorage->getNested("signs." . $id);

        if ($data === null) {
            return null;
        }

        switch ($data["type"]) {
            case SignType::COMMAND_SIGN:
                $sign = new CommandSign($this, $signBlock, $id, $data);
                break;
            case SignType::TELEPORT_SIGN:
                $sign = new TeleportSign($this, $signBlock, $id, $data);
                break;
            default:
                return null;
        }

        $this->signs[$id] = $sign;
        return $sign;
    }

    /**
     * Check if a sign is a magic sign
     *
     * @param  \pocketmine\block\BaseSign  $signBlock
     *
     * @return string|null
     */
    public function isMagicSign(BaseSign $signBlock): ?string
    {
        $firstLine = $signBlock->getText()->getLine(0);

        if (preg_match(
              "/^" . AllSignsGeneral::SIGN_IDENTIFIER . AllSignsGeneral::ID_SEPARATOR . "[0-9]*/",
              $firstLine
            ) !== 1
        ) {
            return null;
        }

        $parts = explode("#", $firstLine);

        if (count($parts) !== 2) {
            return null;
        }

        return $parts[1];
    }

    /**
     * Get the next free sign ID
     *
     * @return int
     */
    public function nextSignId(): int
    {
        $signs = $this->signStorage->getNested("signs");

        if (count($signs) === 0) {
            return 0;
        }

        $highestId = max(array_keys($signs));

        return $highestId + 1;
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
        if (($rawMessage = $this->messages->getNested($key)) === null) {
            $rawMessage = $this->defaultMessages->getNested($key);
        }

        if ($rawMessage === null) {
            return $key;
        }

        foreach ($replaces as $replace => $value) {
            $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
        }

        return $rawMessage;
    }

    /**
     * @return \pocketmine\utils\Config
     */
    public function getSignStorage(): Config
    {
        return $this->signStorage;
    }

}
