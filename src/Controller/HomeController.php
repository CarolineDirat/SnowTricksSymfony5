<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    const NUMBER_FIRST_TRICKS = 8;
    const OFFSET_LOADED_TRICKS = self::NUMBER_FIRST_TRICKS;
    const LIMIT_LOADED_TRICKS = 4;

    /**
     * Display the home page.
     *
     * @Route("/", name="home")
     */
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->getPaginatedTricks(0, self::NUMBER_FIRST_TRICKS);

        return $this->render('home/index.html.twig', ['tricks' => $tricks]);
    }

    /**
     * Display tricks.
     *
     * @Route("/tricks", name="tricks")
     */
    public function onlyTricks(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->getPaginatedTricks(0, self::NUMBER_FIRST_TRICKS);

        return $this->render('home/tricks.html.twig', ['tricks' => $tricks]);
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
    public function loadMoreTricks(TrickRepository $trickRepository, int $offset = self::OFFSET_LOADED_TRICKS): JsonResponse
    {
        $tricks = $trickRepository->getArrayPaginatedTricks($offset, self::LIMIT_LOADED_TRICKS);

        return $this->json(
            $tricks,
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
