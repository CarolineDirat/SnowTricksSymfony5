<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\FormHandler\CommentFormHandler;
use App\FormHandler\TrickFormHandler;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * Display the page of one trick.
     *
     * @Route("/trick/{slug}/{uuid}", name="display_trick")
     */
    public function display(
        Trick $trick,
        string $slug,
        CommentRepository $commentRepository,
        Request $request,
        CommentFormHandler $commentFormHandler
    ): Response {
        // check slug
        if ($slug !== $trick->getSlug()) {
            return $this->redirectToRoute('display_trick', [
                'slug' => $trick->getSlug(),
                'uuid' => $trick->getUuid(),
            ]);
        }
        // create comment form
        $comment = $commentFormHandler->initialize($trick, $this->getUser());
        $form = $this->createForm(CommentType::class, $comment);
        // process comment form
        if ($commentFormHandler->isHandled($request, $form, $comment)) {
            return $this->redirectToRoute('display_trick', [
                'slug' => $trick->getSlug(),
                'uuid' => $trick->getUuid(),
            ]);
        }

        return $this->render('trick/index.html.twig', [
            'trick' => $trick,
            'comments' => $commentRepository->getLastComments($trick, 5),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Display the page of a trick from slug only.
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
     * load more comments.
     *
     * @Route(
     *      "/trick/{slug}/{uuid}/voir-plus/{offset<\d+>}",
     *      name="load-more-comments",
     *      methods={"GET"}
     * )
     */
    public function loadMoreComments(Trick $trick, CommentRepository $commentRepository, int $offset = 5): JsonResponse
    {
        $comments = $commentRepository->getArrayPaginatedComments($trick, $offset, 5);

        return $this->json(
            $comments,
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Delete a trick from AJAX request.
     *
     * @Route(
     *      "/trick-suppression/{uuid}",
     *      name="trick_delete_ajax",
     *      methods={"DELETE"}
     * )
     * @isGranted("ROLE_USER")
     */
    public function deleteFromAJAXRequest(
        Trick $trick,
        Request $request,
        TrickRepository $trickRepository,
        ParameterBagInterface $container
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $trickName = $trick->getName();
        // 'delete-trick-token258941367' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-trick-token258941367', $data['_token'])) {
            $entityManager = $this->getDoctrine()->getManager();
            $trickRepository->deletePicturesFiles($trick, $container);
            $entityManager->remove($trick);
            $entityManager->flush();

            return $this->json(
                ['message' => 'Le trick '.$trickName.' a bien été supprimé.'],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Oups ! La suppression n\'est pas possible...'],
            403,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Delete a trick.
     *
     * @Route(
     *      "/trick--suppression/{uuid}",
     *      name="trick_delete",
     *      methods={"POST"}
     * )
     * @isGranted("ROLE_USER")
     */
    public function delete(
        Trick $trick,
        Request $request,
        TrickRepository $trickRepository,
        ParameterBagInterface $container
    ): Response {
        $trickName = $trick->getName();
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-trick-'.$trick->getId(), $submittedToken)) {
            $entityManager = $this->getDoctrine()->getManager();
            $trickRepository->deletePicturesFiles($trick, $container);
            $entityManager->remove($trick);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                'Le trick "'.$trickName.'" a bien été supprimé.'
            );

            return $this->redirectToRoute('tricks');
        }

        return $this->redirectToRoute('display_trick', [
            'slug' => $trick->getSlug(),
            'uuid' => $trick->getUuid(),
        ]);
    }

    /**
     * New trick.
     *
     * @Route(
     *      "/ajouter/trick",
     *      name="trick_new"
     * )
     * @isGranted("ROLE_USER")
     */
    public function new(Request $request, TrickFormHandler $trickFormHandler): Response 
    {
        $trick = $trickFormHandler->initialize();
        $form = $this->createForm(TrickType::class, $trick);
        if ($trickFormHandler->isHandled($request, $form, $trick)) {
            return $this->redirectToRoute('tricks');
        }

        return $this->render('trick/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Update trick.
     *
     * @Route(
     *      "/modifier/trick/{slug}/{uuid}",
     *      name="trick_update"
     * )
     * @isGranted("ROLE_USER")
     */
    public function update(Trick $trick, string $slug, Request $request): Response 
    {        
        // check slug
        if ($slug !== $trick->getSlug()) {
            return $this->redirectToRoute('trick_update', [
                'slug' => $trick->getSlug(),
                'uuid' => $trick->getUuid(),
            ]);
        }
        
        $form = $this->createForm(TrickType::class, $trick);

        return $this->render('trick/update.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Display the page to update a trick from slug only.
     *
     * @Route("modifier/trick/{slug}", name="trick_update_slug")
     */
    public function redirectBySlugUpdate(Trick $trick): Response
    {
        return $this->redirectToRoute('trick_update', [
            'slug' => $trick->getSlug(),
            'uuid' => $trick->getUuid(),
        ]);
    }
}
