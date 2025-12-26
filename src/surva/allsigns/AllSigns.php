<?php

/**
 * AllSigns | plugin main class, management
 * of magic signs and translations
 */

namespace surva\allsigns;

use pocketmine\block\BaseSign;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use surva\allsigns\sign\CommandSign;
use surva\allsigns\sign\MagicSign;
use surva\allsigns\sign\TeleportSign;
use surva\allsigns\util\AllSignsGeneral;
use surva\allsigns\util\Messages;
use surva\allsigns\util\SignType;
use Symfony\Component\Filesystem\Path;

class AllSigns extends PluginBase
{
    private Config $signStorage;
    /**
     * @var MagicSign[] MagicSign objects
     */
    private array $signs;
    /**
     * @var Config default language config
     */
    private Config $defaultMessages;
    /**
     * @var Config[] available language configs
     */
    private array $translationMessages;

    /**
     * Initial setup, load sign storage config, language files and
     * register events
     *
     * @return void
     */
    public function onEnable(): void
    {
        $this->signStorage = new Config(Path::join($this->getDataFolder(), "signs.yml"));

        if (!$this->signStorage->exists("signs")) {
            $this->signStorage->set("signs", []);
        }

        $this->signs = [];

        $this->saveResource(Path::join("languages", "en.yml"), true);
        $this->defaultMessages = new Config(Path::join($this->getDataFolder(), "languages", "en.yml"));
        $this->loadLanguageFiles();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    /**
     * Get a MagicSign using the sign block
     *
     * @param BaseSign $signBlock
     *
     * @return MagicSign|null
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

        try {
            $world = $signBlock->getPosition()->getWorld();
        } catch (AssumptionFailedError $e) {
            return null;
        }

        if ($world->getFolderName() !== $data["world"]) {
            return null;
        }

        if (
            $signBlock->getPosition()->getX() !== $data["coordinates"]["xc"]
            || $signBlock->getPosition()->getY() !== $data["coordinates"]["yc"]
            || $signBlock->getPosition()->getZ() !== $data["coordinates"]["zc"]
        ) {
            return null;
        }

        return $sign;
    }

    /**
     * Get a sign from the sign object array or load from config
     * if not loaded yet
     *
     * @param int $id
     * @param BaseSign $signBlock
     *
     * @return MagicSign|null
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
     * Check if a sign block is a MagicSign
     *
     * @param BaseSign $signBlock
     *
     * @return string|null
     */
    public function isMagicSign(BaseSign $signBlock): ?string
    {
        $firstLine = $signBlock->getText()->getLine(0);

        if (
            preg_match(
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
     * Get the next available sign ID
     *
     * @return int
     */
    public function nextSignId(): int
    {
        $signs = $this->signStorage->getNested("signs");

        if (count($signs) === 0) {
            return 0;
        }

        // @phpstan-ignore argument.type
        $highestId = (int) max(array_keys($signs));

        return $highestId + 1;
    }

    /**
     * Shorthand to send a translated message to a command sender
     *
     * @param CommandSender $sender
     * @param string $key
     * @param array $replaces
     *
     * @return void
     */
    public function sendMessage(CommandSender $sender, string $key, array $replaces = []): void
    {
        $messages = new Messages($this, $sender);

        $sender->sendMessage($messages->getMessage($key, $replaces));
    }

    /**
     * Load all available language files
     *
     * @return void
     */
    private function loadLanguageFiles(): void
    {
        $resources = $this->getResources();
        $this->translationMessages = [];

        foreach ($resources as $resource) {
            $normalizedPath = Path::normalize($resource->getPathname());
            if (!preg_match("/languages\/[a-z]{2}.yml$/", $normalizedPath)) {
                continue;
            }

            preg_match("/^[a-z][a-z]/", $resource->getFilename(), $fileNameRes);

            if (!isset($fileNameRes[0])) {
                continue;
            }

            $langId = $fileNameRes[0];

            $this->saveResource(Path::join("languages", $langId . ".yml"), true);
            $this->translationMessages[$langId] = new Config(
                Path::join($this->getDataFolder(), "languages", $langId . ".yml")
            );
        }
    }

    /**
     * @return Config[]
     */
    public function getTranslationMessages(): array
    {
        return $this->translationMessages;
    }

    /**
     * @return Config
     */
    public function getDefaultMessages(): Config
    {
        return $this->defaultMessages;
    }

    /**
     * @return Config
     */
    public function getSignStorage(): Config
    {
        return $this->signStorage;
    }
}
