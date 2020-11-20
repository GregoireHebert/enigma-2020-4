<?php

declare(strict_types=1);

namespace App\MatchMaking;

use App\Entity\MatchMaker;
use App\Entity\QueuingPlayer;
use App\Model\InLobbyPlayerInterface;
use App\Model\PlayerInterface;
use App\Repository\MatchMakerRepository;
use App\Repository\PlayerRepository;
use App\Repository\QueuingPlayerRepository;

class Lobby
{
    public array $players = [];
    public array $queuingPlayers = [];
    public array $matches = [];

    public MatchMakerRepository $matchMakerRepository;
    public PlayerRepository $playerRepository;
    public QueuingPlayerRepository $queuingPlayerRepository;

    public function __construct(PlayerRepository $playerRepository, MatchMakerRepository $matchMakerRepository, QueuingPlayerRepository $queuingPlayerRepository)
    {
        $this->matches = $matchMakerRepository->findAll();
        $this->matchMakerRepository = $matchMakerRepository;

        $this->players = $playerRepository->findAll();
        $this->playerRepository = $playerRepository;

        $this->queuingPlayers = $queuingPlayerRepository->findAll();
        usort($this->queuingPlayers, static function(PlayerInterface $p1, PlayerInterface $p2) {
            return $p1->getRatio() <=> $p2->getRatio();
        });
        $this->queuingPlayerRepository = $queuingPlayerRepository;
    }

    private function findOponents(InLobbyPlayerInterface $player)
    {
        $minLevel = round($player->getRatio()/100);
        $maxLevel = $minLevel+$player->getRange();

        $oponents = array_filter($this->queuingPlayers, static function(InLobbyPlayerInterface $potentialOponent) use ($minLevel, $maxLevel, $player) {
            $playerLevel = round($potentialOponent->getRatio()/100);

            return $player !== $potentialOponent && ($minLevel <= $playerLevel) && ($playerLevel <= $maxLevel);
        });

        return $oponents;
    }

    public function playerIsInLobby(PlayerInterface $player)
    {
        /** @var QueuingPlayer $queuingPlayer */
        foreach ($this->queuingPlayers as $queuingPlayer)
        {
            if ($queuingPlayer === $player || $queuingPlayer->getPlayer() === $player) {
                return $queuingPlayer;
            }
        }

        return false;
    }

    public function playerIsInMatch(PlayerInterface $player)
    {
        /** @var MatchMaker $match */
        foreach ($this->matches as $match)
        {
            if ($match->getStatus() !== MatchMaker::STATUS_OVER && ($match->getPlayerA() === $player || $match->getPlayerB() === $player)) {
                return true;
            }
        }

        return false;
    }

    public function removePlayer(PlayerInterface $player)
    {
        if(false === $queuingPlayer = $this->playerIsInLobby($player)) {
            throw new \Exception('You cannot remove a player that is not in the lobby.');
        }

        unset($this->queuingPlayers[array_search($queuingPlayer, $this->queuingPlayers)]);

        $this->queuingPlayerRepository->remove($queuingPlayer);
    }

    public function addPlayer(PlayerInterface $player): void
    {
        $this->queuingPlayers[] = $queuingPlayer  = new QueuingPlayer($player);
        $this->queuingPlayerRepository->save($queuingPlayer);
    }

    public function createMatchForPlayer(InLobbyPlayerInterface $player): void
    {
        if (false === $key = array_search($player, $this->queuingPlayers, true)) {
            return;
        }

        $opponents = $this->findOponents($player);

        if (empty($opponents)) {
            $player->upgradeRange();
            $this->queuingPlayerRepository->flush();
            return;
        }

        $opponent = array_shift($opponents);

        $this->matches[] = $match = new MatchMaker(
            $player->getPlayer(),
            $opponent->getPlayer(),
        );

        $this->matchMakerRepository->save($match);

        $this->removePlayer($opponent);
        $this->removePlayer($player);
    }

    public function createMatches()
    {
        foreach ($this->queuingPlayers as $key => $player) {
            $this->createMatchForPlayer($player);
        }
    }
}
