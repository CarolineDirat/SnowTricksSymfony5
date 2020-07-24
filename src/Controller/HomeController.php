<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Display the home page.
     *
     * @Route("/", name="home")
     */
    public function index(TrickRepository $trickRepo): Response
    {
        // temporary code
        $tricks = $trickRepo->findAll(); // There is actually only one trick on database
        
        return $this->render('home/index.html.twig', ['trick' => $tricks[0]]);
    }

    /**
     * Display the page of a trick.
     *
     * @Route("/trick/{uuid}/{slug}", name="trick")
     */
    public function readTrick(Trick $trick ): Response
    {
        return $this->render('home/trick.html.twig', [
            'trick' => $trick,
        ]);
    }
}
