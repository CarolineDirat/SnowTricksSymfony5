<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\AppFormFactoryInterface;
use App\FormHandler\CommentFormHandler;
use App\FormHandler\TrickFormHandler;
use App\Repository\CommentRepository;
use App\Service\ConstantsIni;
use App\Service\PictureServiceInterface;
use App\Service\TrickServiceInterface;
use App\Service\TrickUpdateFormServiceInterface;
use App\Service\VideoServiceInterface;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    private AppFormFactoryInterface $appFormFactory;

    public function __construct(AppFormFactoryInterface $appFormFactory)
    {
        $this->appFormFactory = $appFormFactory;
    }

    /**
     * Display the page of one trick.
     *
     * @Route("/trick/{slug}/{uuid}", name="display_trick")
     * @Entity("trick", expr="repository.findOneWithNLastComments(uuid)")
     *
     * [Entity("trick", expr="repository.findOneWithCommentsOrderByDesc(uuid)")] alternative method
     */
    public function display(
        Trick $trick,
        string $slug,
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
        $comment = new Comment();
        $comment
            ->setTrick($trick)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUser($this->getUser());
        $commentForm = $this->appFormFactory->create('ad-comment', $comment);
        // process comment form
        if ($commentFormHandler->isHandled($request, $commentForm)) {
            return $this->redirect(
                $this->generateUrl(
                    'display_trick',
                    [
                        'slug' => $trick->getSlug(),
                        'uuid' => $trick->getUuid(),
                    ]
                ).'#comments'
            );
        }

        return $this->render('trick/index.html.twig', [
            'trick' => $trick,
            'form' => $commentForm->createView(),
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
    public function loadMoreComments(
        Trick $trick,
        CommentRepository $commentRepository,
        ConstantsIni $constantsIni,
        int $offset = null
    ): JsonResponse {
        $constantsIni = $constantsIni->getConstantsIni();
        if (empty($offset)) {
            $offset = $constantsIni['comments']['number_last_displayed'];
        }
        $comments = $commentRepository->getArrayPaginatedComments(
            $trick,
            $offset,
            $constantsIni['comments']['limit_loaded']
        );

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
     * @isGranted("ROLE_VERIFY")
     */
    public function deleteFromAJAXRequest(
        Trick $trick,
        Request $request,
        TrickServiceInterface $trickService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $trickName = $trick->getName();
        // 'delete-trick-token258941367' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-trick-token258941367', $data['_token'])) {
            $trickService->delete($trick);

            return $this->json(
                ['message' => 'Le trick "'.strtoupper($trickName).'" a bien été supprimé.'],
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
     * @isGranted("ROLE_VERIFY")
     */
    public function delete(
        Trick $trick,
        Request $request,
        TrickServiceInterface $trickService
    ): Response {
        $trickName = $trick->getName();
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-trick-'.$trick->getId(), $submittedToken)) {
            $trickService->delete($trick);
            $this->addFlash(
                'notice',
                'Le trick "'.strtoupper($trickName).'" a bien été supprimé.'
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
     * @isGranted("ROLE_VERIFY")
     */
    public function new(Request $request, TrickFormHandler $trickFormHandler): Response
    {
        $trick = new Trick();
        $picture = new Picture();
        $trick->addPicture($picture);
        $video = new Video();
        $trick->addVideo($video);

        $trickForm = $this->appFormFactory->create('ad-trick', $trick);

        if ($trickFormHandler->isHandled($request, $trickForm)) {
            return $this->redirectToRoute('tricks');
        }

        return $this->render('trick/new.html.twig', [
            'form' => $trickForm->createView(),
        ]);
    }

    /**
     * Update trick.
     *
     * @Route(
     *      "/modifier/trick/{slug}/{uuid}",
     *      name="trick_update"
     * )
     * @isGranted("ROLE_VERIFY")
     */
    public function update(
        Trick $trick,
        string $slug,
        Request $request,
        TrickUpdateFormServiceInterface $trickUpdateForm
    ): Response {
        // check slug
        if ($slug !== $trick->getSlug()) {
            return $this->redirectToRoute('trick_update', [
                'slug' => $trick->getSlug(),
                'uuid' => $trick->getUuid(),
            ]);
        }
        // trick form
        $form = $this->appFormFactory->create('up-trick', $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $trickUpdateForm->process($form);

            return $this->redirectToRoute('display_trick', [
                    'slug' => $trick->getSlug(),
                    'uuid' => $trick->getUuid(),
                ]);
        }
        if ($form->isSubmitted()) {
            $form = $trickUpdateForm->errorsHandler($form);
        }

        return $this->render('trick/update.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Display the page to update a trick from slug only.
     *
     * @Route("modifier/trick/{slug}", name="trick_update_by_slug")
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function redirectBySlugUpdate(Trick $trick): Response
    {
        return $this->redirectToRoute('trick_update', [
            'slug' => $trick->getSlug(),
            'uuid' => $trick->getUuid(),
        ]);
    }

    /**
     * Update trick first image.
     *
     * @Route("modifier/trick-image/{slug}/{uuid}", name="trick_update_first_image", methods={"POST"})
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function updateFirstImage(
        Trick $trick,
        Request $request,
        TrickServiceInterface $trickService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('update-first-image-token-'.$trick->getUuid(), $data['_token'])) {
            $firstPicture = $trickService->updateFirstImage($trick, $data['pictureId']);

            return $this->json(
                [
                    'message' => 'L\'image à la une a été modifiée.',
                    'filename' => $firstPicture->getFilename(),
                    'alt' => $firstPicture->getAlt(),
                    'trickName' => $trick->getName(),
                ],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Accès refusé.'],
            403,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Delete trick first image.
     *
     * @Route("supprimer/trick-image/{slug}/{uuid}", name="trick_delete_first_image", methods={"DELETE"})
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function deleteFirstImage(
        Trick $trick,
        Request $request,
        TrickServiceInterface $trickService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete-first-image-token-'.$trick->getUuid(), $data['_token'])) {
            $trickService->deleteFirstImage($trick);

            return $this->json(
                [
                    'message' => 'L\'image à la une a été supprimée.',
                    'filename' => 'default.jpg',
                    'alt' => '',
                    'trickName' => $trick->getName(),
                ],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Accès refusé.'],
            403,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Update trick video.
     *
     * @Route("modifier/trick-video/{slug}/{uuid}", name="trick_update_video", methods={"POST"})
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function updateVideo(
        Trick $trick,
        Request $request,
        VideoServiceInterface $videoService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if ('' === $data['code']) {
            return $this->json(
                ['message' => 'Attention ! Le code de la vidéo ne peut être vide.'],
                409,
                ['Content-Type' => 'application/json']
            );
        }
        if ($this->isCsrfTokenValid('update-video-token-'.$trick->getUuid(), $data['_token'])) {
            $video = $videoService->update($data);

            return $this->json(
                [
                    'message' => 'La vidéo a été modifiée.',
                    'service' => $video->getService(),
                    'code' => $video->getCode(),
                    'videoId' => $video->getId(),
                ],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Accès refusé.'],
            403,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Delete trick video.
     *
     * @Route("supprimer/trick-video/{slug}/{uuid}", name="trick_delete_video", methods={"DELETE"})
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function deleteVideo(
        Trick $trick,
        Request $request,
        VideoServiceInterface $videoService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete-video-token-'.$trick->getUuid(), $data['_token'])) {
            $videoId = $data['videoId'];
            $videoService->delete($trick, $videoId);

            return $this->json(
                [
                    'message' => 'La vidéo a été supprimée.',
                    'videoId' => $videoId,
                ],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Accès refusé.'],
            403,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Update trick name.
     *
     * @Route("modifier/trick-name/{slug}/{uuid}", name="trick_update_name", methods={"POST"})
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function updateName(
        Trick $trick,
        Request $request,
        TrickServiceInterface $trickService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('update-name-token-'.$trick->getUuid(), $data['_token'])) {
            $name = $trickService->updateName($trick, $data['newName']);

            return $this->json(
                [
                    'message' => 'Le nom du trick a été modifiée.',
                    'newName' => $name,
                ],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Accès refusé.'],
            403,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Update trick picture.
     *
     * @Route("modifier/trick-picture/{slug}/{uuid}/{picture_id}", name="trick_update_picture", methods={"POST"})
     * @Entity("picture", expr="repository.find(picture_id)")
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function updatePicture(
        Trick $trick,
        Picture $picture,
        Request $request,
        PictureServiceInterface $pictureService
    ): JsonResponse {
        $token = $request->request->get('token');
        if ($this->isCsrfTokenValid('update-picture-token-'.$picture->getId(), $token)) {
            $message = $pictureService->isDataPictureValid($request);
            if (!empty($message)) {
                return $this->json(
                    ['message' => $message],
                    409,
                    ['Content-Type' => 'application/json']
                );
            }
            $result = $pictureService->update($trick, $picture, $request);
            if (empty($result)) {
                return $this->json(
                    ['message' => 'Echec de l\'upload.'],
                    403,
                    ['Content-Type' => 'application/json']
                );
            }

            return $this->json(
                [
                    'message' => 'La photo a été modifiée.',
                    'filename' => $result->getFilename(),
                    'alt' => $result->getAlt(),
                    'pictureId' => $result->getId(),
                    'trick' => $trick->getName(),
                ],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Accès refusé.'],
            403,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Delete trick picture.
     *
     * @Route("supprimer/trick-picture/{slug}/{uuid}", name="trick_delete_picture", methods={"DELETE"})
     *
     * @isGranted("ROLE_VERIFY")
     */
    public function deletePicture(
        Trick $trick,
        Request $request,
        PictureServiceInterface $pictureService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $pictureId = $data['pictureId'];
        if ($this->isCsrfTokenValid('delete-picture-token-'.$pictureId, $data['_token'])) {
            $pictureService->delete($trick, $pictureId);

            return $this->json(
                [
                    'message' => 'L\'image a été supprimée.',
                    'pictureId' => $pictureId,
                ],
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $this->json(
            ['message' => 'Accès refusé.'],
            403,
            ['Content-Type' => 'application/json']
        );
    }
}
