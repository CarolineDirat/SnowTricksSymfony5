<?php

namespace App\FormHandler;

use App\Entity\Trick;
use App\Service\ImageProcessInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickFormHandler extends AbstractFormHandler
{
    private ManagerRegistry $managerRegistry;

    private ImageProcessInterface $imageProcess;

    private AsciiSlugger $slugger;

    private Session $session;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ImageProcessInterface $imageProcess
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->slugger = new AsciiSlugger();
        $this->imageProcess = $imageProcess;
        $this->session = new Session(new NativeSessionStorage(), new AttributeBag());
    }

    public function getEntityClass(): string
    {
        return Trick::class;
    }

    public function process(object $trick): void
    {
        // trick slug will be used in file name pictures
        $trick->setSlug($this->getSlugger()->slug(strtolower($trick->getName())));
        $this->processPictures($trick);
        $this->processVideos($trick);
        $entityManager = $this->getManagerRegistry()->getManager();
        $entityManager->persist($trick);
        $entityManager->flush();
        $this->session->getFlashBag()->add('notice', 'Le trick '.$trick->getName()." vient d'être ajouté");
    }

    public function processPictures(Trick $trick): void
    {
        $pictures = $trick->getPictures();
        $picturesForm = $this->getForm()->get('pictures');
        foreach ($pictures as $key => $picture) {
            $file = $picturesForm[$key]->get('file')->getData();
            if ($file instanceof UploadedFile) {
                $filename = uniqid($trick->getSlug().'-', true); // file name without extension
                // Resize the picture file to severals widths (cf service.yaml),
                // and move files in their corresponding directory named with each width
                try {
                    $fullFilename = $this->imageProcess->executeForPictures($file, $filename);
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

    public function processVideos(Trick $trick): void
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

    /**
     * Get the value of slugger.
     */
    public function getSlugger(): SluggerInterface
    {
        return $this->slugger;
    }

    /**
     * Get the value of managerRegistry.
     */
    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }
}
