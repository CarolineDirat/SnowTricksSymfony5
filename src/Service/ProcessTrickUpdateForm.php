<?php

namespace App\Service;

use App\Entity\Trick;
use App\Repository\PictureRepository;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class ProcessTrickUpdateForm
{
    private Session $session;

    private ImageProcessInterface $imageProcess;

    private PictureRepository $pictureRepository;

    private ManagerRegistry $managerRegistry;

    private FormFactory $formFactory;

    public function __construct(
        ImageProcessInterface $imageProcess,
        PictureRepository $pictureRepository,
        ManagerRegistry $managerRegistry,
        FormFactory $formFactory
    ) {
        $this->session = new Session(new NativeSessionStorage(), new AttributeBag());
        $this->imageProcess = $imageProcess;
        $this->pictureRepository = $pictureRepository;
        $this->managerRegistry = $managerRegistry;
        $this->formFactory = $formFactory;
    }

    public function process(FormInterface $form): void
    {
        $trick = $form->getData();
        $trick->setUpdatedAt(new DateTimeImmutable());
        // processing of added pictures
        $this->processAddedPictures($form, $trick);
        $trick = $this->recoverPictures($trick);
        // processing of added videos
        $this->processAddedVideos($trick);
        // save changes
        $this->managerRegistry->getManager()->flush();
        $this->session->getFlashBag()->add('notice', 'Le trick <'.$trick->getName()."> vient d'être modifié");
    }

    public function processAddedPictures(FormInterface $form, Trick $trick): void
    {
        $addedPictures = $trick->getPictures();
        $addedPicturesForm = $form->get('pictures');
        foreach ($addedPictures as $key => $picture) {
            $file = $addedPicturesForm[$key]->get('file')->getData();
            if ($file instanceof UploadedFile) {
                $filename = uniqid($trick->getSlug().'-', true); // file name without extension
                // Resize the picture file to severals widths (cf service.yaml),
                // and move files in their corresponding directory named with each width
                try {
                    $fullFilename = $this->imageProcess->execute($file, $filename);
                    $picture->setFilename($fullFilename)->setTrick($trick);
                    $trick->addPicture($picture);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload or process
                    $this->session->getFlashBag()->add('upload', $e->getMessage());
                    $trick->removePicture($picture);
                }
            } else {
                $trick->removePicture($picture);
            }
        }
    }

    /**
     * recoverPictures.
     *
     * The pictures that the Trick already has are missing
     * because the form pictures (used by AJAX request to update pictures with file upload)
     * is out of trick form in trick/update.html.twig
     * (we cannot nest forms in HTML5)
     * So, we get pictures from database to add them
     */
    public function recoverPictures(Trick $trick): Trick
    {
        $pictures = $this->pictureRepository->findBy(['trick' => $trick]);
        foreach ($pictures as $picture) {
            $trick->addPicture($picture);
        }

        return $trick;
    }

    public function processAddedVideos(Trick $trick): void
    {
        $videos = $trick->getVideos();
        foreach ($videos as $video) {
            if (empty($video->getService()) || empty($video->getCode())) {
                $trick->removeVideo($video);
            } else {
                $video->setTrick($trick);
                $trick->addVideo($video);
            }
        }
    }

    public function errorsHandler(FormInterface $form): FormInterface
    {
        $trick = $form->getData();
        // if $form is submitted but not valid :
        // -> we recover the existing errors
        $errors = $form->getErrors(true);
        // -> added pictures not saved have null id
        // so we remove them because they cannot be display on update trick page
        foreach ($trick->getPictures() as $picture) {
            if (empty($picture->getId())) {
                $trick->removePicture($picture);
            }
        }
        $trick = $this->recoverPictures($trick);
        $form = $this->formFactory->createUpdateTrickForm($trick);
        foreach ($errors as $error) {
            $form->addError($error);
        }

        return $form;
    }
}
