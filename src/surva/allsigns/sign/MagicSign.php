<?php

/**
 * AllSigns | general AllSigns sign interface
 */

namespace surva\allsigns\sign;

use Exception;
use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\player\Player;
use surva\allsigns\AllSigns;
use surva\allsigns\util\AllSignsGeneral;

abstract class MagicSign
{
    protected AllSigns $allSigns;

    protected ?int $signId;
    protected BaseSign $signBlock;
    /**
     * @var array<string, mixed>|null sign config data
     */
    protected ?array $data;

    /**
     * @param AllSigns $allSigns
     * @param BaseSign $signBlock
     * @param int|null $signId
     * @param array<string, mixed>|null $data
     */
    public function __construct(AllSigns $allSigns, BaseSign $signBlock, ?int $signId = null, ?array $data = null)
    {
        $this->allSigns = $allSigns;
        $this->signBlock = $signBlock;
        $this->signId = $signId;
        $this->data = $data;
    }

    /**
     * Handle player interaction with this sign
     *
     * @param Player $player
     * @param int $mode
     *
     * @return void
     */
    public function handleSignInteraction(Player $player, int $mode = AllSignsGeneral::INTERACT_MODE): void
    {
        if ($this->data === null) {
            return;
        }

        if ($mode === AllSignsGeneral::EDIT_MODE) {
            $this->sendCreateForm($player, $this->data);

            return;
        }

        $this->internallyHandleSignInteraction($player);
    }

    /**
     * Save to config and update sign block
     *
     * @param string $text
     *
     * @return bool
     */
    protected function createSignInternally(string $text): bool
    {
        if ($this->signId === null) {
            $this->signId = $this->allSigns->nextSignId();
        }

        $this->allSigns->getSignStorage()->setNested("signs." . $this->signId, $this->data);

        try {
            $this->allSigns->getSignStorage()->save();
        } catch (Exception) {
            $this->allSigns->getLogger()->error("Could not save sign storage config");
        }

        $pos = $this->signBlock->getPosition();

        $this->signBlock = $this->signBlock->setText(
            new SignText([
            AllSignsGeneral::SIGN_IDENTIFIER . AllSignsGeneral::ID_SEPARATOR . $this->signId,
            $text,
            ])
        );
        $pos->getWorld()->setBlock($pos, $this->signBlock);

        $this->allSigns->loadMagicSign($this->signId, $this->signBlock, true);

        return true;
    }

    /**
     * Remove a broken sign from config
     *
     * @return void
     */
    public function remove(): void
    {
        $this->allSigns->getSignStorage()->removeNested("signs." . $this->signId);

        try {
            $this->allSigns->getSignStorage()->save();
        } catch (Exception) {
            $this->allSigns->getLogger()->error("Could not save sign storage config");
        }
    }

    /**
     * Internal logic to execute when a player interacted
     * with this sign
     *
     * @param Player $player
     *
     * @return void
     */
    abstract protected function internallyHandleSignInteraction(Player $player): void;

    /**
     * Create a new sign
     *
     * @param array<string, string> $signData
     * @param string $text
     * @param string $permission
     *
     * @return bool
     */
    abstract public function createSign(array $signData, string $text, string $permission): bool;

    /**
     * Send the creation form to a player
     *
     * @param Player $player
     * @param string[]|null $existingData
     *
     * @return void
     */
    abstract public function sendCreateForm(Player $player, ?array $existingData = null): void;

    /**
     * @return int|null
     */
    public function getSignId(): ?int
    {
        return $this->signId;
    }

    /**
     * @return BaseSign
     */
    public function getSignBlock(): BaseSign
    {
        return $this->signBlock;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }
}
