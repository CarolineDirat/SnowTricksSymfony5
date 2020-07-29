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
        // temporary code
        $tricks = $trickRepo->findAll(); // There is actually only one trick on database
        
        return $this->render('home/index.html.twig', ['trick' => $tricks[0]]);
    }

    /**
     * Display the page of a trick.
     *
     * @Route("/trick/{slug}/{uuid}", name="trick")
     */
    public function readTrick(Trick $trick): Response
    {
        // reverse comments and define file name of profile picture of its user 
        $comments = array_slice(array_reverse($trick->getComments()->toArray()), 0, 5);
        
        return $this->render('home/trick.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
        ]);
    }

    /**
     * load more comments
     *
     * @Route("/trick/{slug}/{uuid}/voir-plus/{offset<\d+>}", name="load-more-comments", methods={"GET"})
     */
    public function loadMoreComments(Trick $trick, int $offset = 5, CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->getPaginatedComments($trick, $offset, 5);
        $comments = $this->deleteSensitiveData($comments);
        
        return $this->json(
            $comments,
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function deleteSensitiveData(array $comments): array
    {
        for ($i=0; $i < 5 ; $i++) { 
            unset($comments[$i]['user']['id']);
            unset($comments[$i]['user']['roles']);
            unset($comments[$i]['user']['password']);
            unset($comments[$i]['user']['email']);
            unset($comments[$i]['user']['uuid']);
            unset($comments[$i]['user']['createdAt']);
        }

        return $comments;
    }
}
