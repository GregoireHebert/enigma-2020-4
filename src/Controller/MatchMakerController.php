<?php

namespace App\Controller;

use App\Entity\MatchMaker;
use App\Entity\Player;
use App\Form\MatchMakerType;
use App\Repository\MatchMakerRepository;
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
    public function index(MatchMakerRepository $matchMakerRepository, Security $security): Response
    {
        $me = $security->getUser();

        // filtrer par l'utilisateur connecté
        $qb = $matchMakerRepository->createQueryBuilder('m');
        $qb->where('m.status = :status') // le status en attente
            ->andWhere('m.playerA = :me OR m.playerB = :me') // si je suis l'un des deux joueurs
            ->setParameter('status', MatchMaker::STATUS_PENDING)
            ->setParameter('me', $me);

        return $this->render('match_maker/index.html.twig', [
            'match_makers' => $qb->getQuery()->getResult(),
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
     */
    public function edit(Request $request, MatchMaker $matchMaker): Response
    {
        $form = $this->createForm(MatchMakerType::class, $matchMaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
