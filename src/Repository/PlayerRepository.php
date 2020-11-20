<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * @return Player[]| Collection
     */
    public function getTop10(): iterable
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.ratio', 'DESC')
            ->setMaxResults(10)
            ->getQuery()->getResult();
    }
}
