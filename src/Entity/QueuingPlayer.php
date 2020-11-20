<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\InLobbyPlayerInterface;
use App\Model\PlayerInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QueuingPlayerRepository;

/**
 * @ORM\Entity(repositoryClass=QueuingPlayerRepository::class)
 */
class QueuingPlayer implements InLobbyPlayerInterface
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity=Player::class)
     */
    private PlayerInterface $player;
    /**
     * @ORM\Column(type="integer")
     */
    private int $range = 1;

    public function __construct(PlayerInterface $player)
    {
        $this->player = $player;
    }

    public function getPlayer(): PlayerInterface
    {
        return $this->player;
    }

    public function updateRatioAgainst(PlayerInterface $player, $result): void
    {
        $this->player->updateRatioAgainst($player, $result);
    }

    public function getRatio(): float
    {
        return $this->player->getRatio();
    }

    public function getRange(): int
    {
        return $this->range;
    }

    public function upgradeRange(): void
    {
        $this->range = min($this->range+1, 25);
    }
}
