<?php

namespace App\Controller;

use App\Entity\MatchMaker;
use App\Form\MatchMakerType;
use App\MatchMaking\Lobby;
use App\Repository\MatchMakerRepository;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as AccessControl;

/**
 * @Route("/match/maker")
 */
class MatchMakerController extends AbstractController
{
    /**
     * @Route("/", name="match_maker_index", methods={"GET"})
     * @AccessControl("is_granted('ROLE_USER')")
     */
    public function index(MatchMakerRepository $matchMakerRepository, Security $security, PlayerRepository $playerRepository): Response
    {
        $me = $security->getUser();

        // filtrer par l'utilisateur connecté
        $qb = $matchMakerRepository->createQueryBuilder('m');

        // le status en attente
        $qb->where('m.status = :status')
            ->setParameter('status', MatchMaker::STATUS_PENDING);

        if (!$security->isGranted('ROLE_ADMIN', $me)) {
            // si je suis l'un des deux joueurs
            $qb->andWhere('m.playerA = :me OR m.playerB = :me')
            ->setParameter('me', $me);
        }

        $matches = $qb->getQuery()->getResult();

        if (empty($matches) && !$security->isGranted('ROLE_ADMIN', $me)) {
            $players = $playerRepository->findAll();
            foreach ($players as $player) {
                if ($security->isGranted('ROLE_ADMIN', $player) || $me === $player) {
                    $player = null;
                    continue;
                }

                if ($me->getRatio() === $player->getRatio() || $me->getRatio() >= $player->getRatio() - 100 || $me->getRatio() <= $player->getRatio() + 100) {
                    break;
                }
            }

            $match = new MatchMaker($me, $player);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($match);
            $entityManager->flush();
        }

        return $this->render('match_maker/index.html.twig', [
            'match_makers' => $matches,
        ]);
    }

    /**
     * @Route("/new", name="match_maker_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $matchMaker = new MatchMaker();
        $form = $this->createForm(MatchMakerType::class, $matchMaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($matchMaker);
            $entityManager->flush();

            return $this->redirectToRoute('match_maker_index');
        }

        return $this->render('match_maker/new.html.twig', [
            'match_maker' => $matchMaker,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="match_maker_show", methods={"GET"})
     */
    public function show(MatchMaker $matchMaker): Response
    {
        return $this->render('match_maker/show.html.twig', [
            'match_maker' => $matchMaker,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="match_maker_edit", methods={"GET","POST"})
     * @AccessControl("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, MatchMaker $matchMaker): Response
    {
        $form = $this->createForm(MatchMakerType::class, $matchMaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $matchMaker->setStatus(MatchMaker::STATUS_OVER);
            $matchMaker->updateRatios();
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('match_maker_index');
        }

        return $this->render('match_maker/edit.html.twig', [
            'match_maker' => $matchMaker,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="match_maker_delete", methods={"DELETE"})
     */
    public function delete(Request $request, MatchMaker $matchMaker): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matchMaker->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($matchMaker);
            $entityManager->flush();
        }

        return $this->redirectToRoute('match_maker_index');
    }
}
