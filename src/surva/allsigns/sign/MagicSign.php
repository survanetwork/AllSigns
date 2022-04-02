<?php

/**
 * AllSigns | general AllSigns sign interface
 */

namespace surva\allsigns\sign;

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

    protected ?array $data;

    /**
     * @param  \surva\allsigns\AllSigns  $allSigns
     * @param  \pocketmine\block\BaseSign  $signBlock
     * @param  int|null  $signId
     * @param  array|null  $data
     */
    public function __construct(AllSigns $allSigns, BaseSign $signBlock, ?int $signId = null, ?array $data = null)
    {
        $this->allSigns  = $allSigns;
        $this->signBlock = $signBlock;
        $this->signId    = $signId;
        $this->data      = $data;
    }

    /**
     * Handle if a player interacts with a sign
     *
     * @param  \pocketmine\player\Player  $player
     * @param  int  $mode
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
     * @param  string  $text
     *
     * @return bool
     */
    protected function createSignInternally(string $text): bool
    {
        if ($this->signId === null) {
            $this->signId = $this->allSigns->nextSignId();
        }

        $this->allSigns->getSignStorage()->setNested("signs." . $this->signId, $this->data);

        $this->allSigns->getSignStorage()->save();

        if (!($this->signBlock instanceof BaseSign)) {
            return false;
        }

        $pos = $this->signBlock->getPosition();

        $this->signBlock = $this->signBlock->setText(
            new SignText([
            AllSignsGeneral::SIGN_IDENTIFIER . AllSignsGeneral::ID_SEPARATOR . $this->signId,
            $text,
            ])
        );
        $pos->getWorld()->setBlock($pos, $this->signBlock);

        return true;
    }

    /**
     * Remove a broken sign from config
     */
    public function remove(): void
    {
        $this->allSigns->getSignStorage()->removeNested("signs." . $this->signId);

        $this->allSigns->getSignStorage()->save();
    }

    /**
     * Handle if a player interacts with a sign
     *
     * @param  \pocketmine\player\Player  $player
     */
    abstract protected function internallyHandleSignInteraction(Player $player): void;

    /**
     * Create a new sign
     *
     * @param  array  $signData
     * @param  string  $text
     * @param  string  $permission
     *
     * @return bool
     */
    abstract public function createSign(array $signData, string $text, string $permission): bool;

    /**
     * Send the creation form to the player
     *
     * @param  \pocketmine\player\Player  $player
     * @param  array|null  $existingData
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
     * @return \pocketmine\block\BaseSign
     */
    public function getSignBlock(): BaseSign
    {
        return $this->signBlock;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }
}
