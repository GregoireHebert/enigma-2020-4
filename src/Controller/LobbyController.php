<?php

namespace App\Controller;

use App\Entity\MatchMaker;
use App\Entity\Player;
use App\MatchMaking\Lobby;
use App\Model\InLobbyPlayerInterface;
use App\Repository\MatchMakerRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as AccessControl;

class LobbyController extends AbstractController
{
    /**
     * @Route("/lobby", name="lobby")
     * @AccessControl("is_granted('ROLE_USER')")
     */
    public function enterLobby(Lobby $lobby, Security $security): Response
    {
        /** @var Player $me */
        $me = $security->getUser();

        if ($lobby->playerIsInMatch($me)) {
            return $this->redirectToRoute('match_maker_index');
        }

        if (!$lobby->playerIsInLobby($me)) {
            $lobby->addPlayer($me);
        }

        return $this->redirectToRoute('lobbyPending');
    }

    /**
     * @Route("/lobby/pending", name="lobbyPending")
     * @AccessControl("is_granted('ROLE_USER')")
     */
    public function pendingLobby(Lobby $lobby, Security $security, MatchMakerRepository $matchMakerRepository): Response
    {
        /** @var Player $me */
        $me = $security->getUser();

        if ($lobby->playerIsInMatch($me)) {
            return $this->redirectToRoute('match_maker_index');
        }

        if (!$lobby->playerIsInLobby($me) && !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('lobby/index.html.twig', [
                'queuingPlayers' => $lobby->queuingPlayers
            ]);
        }

        if (!empty($matches = $matchMakerRepository->getPlayerMatchesPending($me))) {
            $this->redirectToRoute('match_maker_index');
        }

        return $this->render('lobby/index.html.twig', ['queuingPlayers' => null]);
    }

    /**
     * @Route("/lobby/exit", name="lobbyExit")
     * @AccessControl("is_granted('ROLE_USER')")
     */
    public function exitLobby(Lobby $lobby, Security $security, LoggerInterface $logger): Response
    {
        /** @var Player|InLobbyPlayerInterface $me */
        $me = $security->getUser();

        try {
            $lobby->removePlayer($me);
        } catch (\Exception $e) {
            $logger->warning('Attempt to remove a non queued player.');
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/lobby/match", name="lobbyMatchCreate", methods={"POST"})
     * @AccessControl("is_granted('ROLE_USER')")
     */
    public function createMatches(Lobby $lobby, LoggerInterface $logger): Response
    {
        echo 'finding opponents to match.';
        try {
            $lobby->createMatches();
        } catch (\Exception $e) {
            $logger->warning('Attempt to remove a non queued player.');
        }
    }
}
