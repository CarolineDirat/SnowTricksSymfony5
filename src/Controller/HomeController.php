<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Display the home page.
     *
     * @Route("/", name="home")
     */
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->getPaginatedTricks(0,8);

        return $this->render('home/index.html.twig', ['tricks' => $tricks]);
    }

    /**
     * load more tricks.
     *
     * @Route(
     *      "/voir-plus/{offset<\d+>}",
     *      name="load-more-tricks",
     *      methods={"GET"}
     * )
     */
    public function loadMoreComments(TrickRepository $trickRepository, int $offset = 8): JsonResponse
    {
        $tricks = $trickRepository->getArrayPaginatedTricks($offset, 4);

        return $this->json(
            $tricks,
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
