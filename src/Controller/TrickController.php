<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Service\ImageProcessInterface;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        Request $request
    ): Response {
        // check slug
        if ($slug !== $trick->getSlug()) {
            return $this->redirectToRoute('display_trick', [
                'slug' => $trick->getSlug(),
                'uuid' => $trick->getUuid(),
            ]);
        }
        // create comment form
        $comment = new Comment();
        $comment->setTrick($trick);
        $comment->setCreatedAt(new DateTimeImmutable());
        $comment->setUser($this->getUser());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        // process comment form
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

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
     * Delete a trick.
     *
     * @Route(
     *      "/ajouter/trick",
     *      name="trick_new"
     * )
     * @isGranted("ROLE_USER")
     */
    public function new(
        Request $request,
        SluggerInterface $slugger,
        ImageProcessInterface $imageProcess
    ): Response {
        $trick = new Trick();
        $picture = new Picture();
        $trick->addPicture($picture);
        $video = new Video();
        $trick->addVideo($video);
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $trick->setSlug($slugger->slug(strtolower($trick->getName())));
            $pictures = $trick->getPictures();
            $picturesForm = $form->get('pictures');
            foreach ($pictures as $key => $picture) {
                $file = $picturesForm[$key]->get('file')->getData();
                if($file instanceof UploadedFile) { 
                    $filename = uniqid($trick->getSlug().'-', true); // file name without extension
                    // Resize the picture file to severals widths (cf service.yaml),
                    // and move files in their corresponding directory named with each width
                    try {
                        $filename = $imageProcess->execute($file, $filename);
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                        $this->addFlash('upload', "Le fichier ".$file->getFilename()." n'a pas pu être traité.". $e->getMessage());
                        return $this->redirectToRoute('trick_new', [
                            'form' => $form->createView(),
                        ]);
                    }
                    $picture->setFilename($filename);
                    $picture->setTrick($trick);
                    $trick->addPicture($picture);
                } else {
                    $trick->removePicture($picture);
                }
            }
            $videos = $trick->getVideos();
            foreach ($videos as $video) {
                if(empty($video->getService()) || empty($video->getCode())) {
                    $trick->removeVideo($video);
                } else {
                    $video->setTrick($trick);
                    $trick->addVideo($video);
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trick);
            $entityManager->flush();
            $this->addFlash(
                'notice',
                "Le trick " . $trick->getName() . " vient d'être ajouté"
            );

            return $this->redirectToRoute('tricks');
        }

        return $this->render('trick/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
