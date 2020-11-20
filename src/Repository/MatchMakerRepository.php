<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MatchMaker;
use App\Model\PlayerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class MatchMakerRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, MatchMaker::class);
        $this->security = $security;
    }

    public function save(MatchMaker $match)
    {
        $this->_em->persist($match);
        $this->_em->flush();
    }

    public function getPlayerMatchesOver(PlayerInterface $player)
    {
        // filtrer par l'utilisateur connecté
        $qb = $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->setParameter('status', MatchMaker::STATUS_OVER);

        if (!$this->security->isGranted('ROLE_ADMIN', $player)) {
            // si je suis l'un des deux joueurs
            $qb->andWhere('m.playerA = :me OR m.playerB = :me')
                ->setParameter('me', $player);
        }

        return $qb->getQuery()->getResult();
    }

    public function getPlayerMatchesPlaying(PlayerInterface $player)
    {
        // filtrer par l'utilisateur connecté
        $qb = $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->setParameter('status', MatchMaker::STATUS_PLAYING);

        if (!$this->security->isGranted('ROLE_ADMIN', $player)) {
            // si je suis l'un des deux joueurs
            $qb->andWhere('m.playerA = :me OR m.playerB = :me')
                ->setParameter('me', $player);
        }

        return $qb->getQuery()->getResult();
    }

    public function getPlayerMatchesPending(PlayerInterface $player)
    {
        // filtrer par l'utilisateur connecté
        $qb = $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->setParameter('status', MatchMaker::STATUS_PENDING);

        if (!$this->security->isGranted('ROLE_ADMIN', $player)) {
            // si je suis l'un des deux joueurs
            $qb->andWhere('m.playerA = :me OR m.playerB = :me')
                ->setParameter('me', $player);
        }

        return $qb->getQuery()->getResult();
    }
}
