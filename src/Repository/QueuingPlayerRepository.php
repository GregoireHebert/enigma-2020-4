<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QueuingPlayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QueuingPlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QueuingPlayer::class);
    }

    public function save(QueuingPlayer $player)
    {
        $this->_em->persist($player);
        $this->_em->flush();
    }

    public function flush()
    {
        $this->_em->flush();
    }

    public function remove(QueuingPlayer $player)
    {
        $this->_em->remove($player);
        $this->_em->flush();
    }
}
