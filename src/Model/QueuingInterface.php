<?php

declare(strict_types=1);

namespace App\Model;

interface QueuingInterface {
    public function getPlayer(): PlayerInterface;
    public function getRange(): int;
    public function upgradeRange(): void;
}
