<?php

declare(strict_types=1);

namespace App\Model;

interface PlayerInterface
{
    public function updateRatioAgainst(PlayerInterface $player, $result): void;
    public function getRatio(): ?float;
}
