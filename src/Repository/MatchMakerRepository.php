<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MatchMaker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MatchMakerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchMaker::class);
    }

    public function save(MatchMaker $match)
    {
        $this->_em->persist($match);
        $this->_em->flush();
    }
}
