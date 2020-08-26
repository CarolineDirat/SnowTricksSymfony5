<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use App\Service\ConstantsIni;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private array $constants;

    public function __construct(ConstantsIni $constantsIni)
    {
        $this->constants = $constantsIni->getConstantsIni();
    }

    /**
     * Display the home page.
     *
     * @Route("/", name="home")
     */
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->getPaginatedTricks(
            0,
            $this->constants['tricks']['number_first_displayed']
        );

        return $this->render('home/index.html.twig', ['tricks' => $tricks]);
    }

    /**
     * Display tricks.
     *
     * @Route("/tricks", name="tricks")
     */
    public function onlyTricks(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->getPaginatedTricks(
            0,
            $this->constants['tricks']['number_first_displayed']
        );

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
    public function loadMoreTricks(TrickRepository $trickRepository, int $offset = null): JsonResponse
    {
        if (empty($offset)) {
            $offset = $this->constants['tricks']['number_first_displayed'];
        }
        $tricks = $trickRepository->getArrayPaginatedTricks($offset, $this->constants['tricks']['limit_loaded']);

        return $this->json(
            $tricks,
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
