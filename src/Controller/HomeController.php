<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        // There is actually only one trick on database
        $tricks = $trickRepo->findAll(); // temporary code
        
        return $this->render('home/index.html.twig', ['trick' => $tricks[0]]); // temporary code
    }

    /**
     * Display the page of a trick.
     *
     * @Route("/trick/{slug}/{uuid}", name="display_trick")
     */
    public function displayTrick(Trick $trick, string $slug, CommentRepository $commentRepository): Response
    {        
        if ($slug !== $trick->getSlug()) {
            return $this->redirectToRoute('display_trick', [
                'slug' => $trick->getSlug(), 
                'uuid' => $trick->getUuid(),
            ]);
        }
        
        return $this->render('home/trick.html.twig', [
            'trick' => $trick,
            'comments' => $commentRepository->getLastComments($trick, 5),
        ]);
    }

    /**
     * Display the page of a trick from slug only
     *
     * @Route("/trick/{slug}", name="trick_slug")
     */
    public function redirectBySlug(Trick $trick): Response
    {                     
        return $this->redirectToRoute('display_trick', [
            'slug' => $trick->getSlug(),
            'uuid' => $trick->getUuid(),
        ]);
    }

    /**
     * load more comments
     *
     * @Route(
     *      "/trick/{slug}/{uuid}/voir-plus/{offset<\d+>}", 
     *      name="load-more-comments", 
     *      methods={"GET"}
     * )
     */
    public function loadMoreComments(Trick $trick, int $offset = 5, CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->getPaginatedComments($trick, $offset, 5);
        
        return $this->json(
            $comments,
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
