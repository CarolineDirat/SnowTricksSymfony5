<?php

namespace App\FormHandler;

use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Service\ImageProcess;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TrickFormHandler extends AbstractFormHandler
{
    private ManagerRegistry $managerRegistry;

    private ImageProcess $imageProcess;

    private AsciiSlugger $slugger;

    private Session $session;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ImageProcess $imageProcess
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

    public function initialize(): Trick
    {
        $trick = new Trick();
        $picture = new Picture();
        $trick->addPicture($picture);
        $video = new Video();
        $trick->addVideo($video);

        return $trick;
    }

    public function process(object $trick): void
    {
        $trick->setSlug($this->getSlugger()->slug(strtolower($trick->getName())));
        // process picture(s)
        $pictures = $trick->getPictures();
        $picturesForm = $this->getForm()->get('pictures');
        foreach ($pictures as $key => $picture) {
            $file = $picturesForm[$key]->get('file')->getData();
            if ($file instanceof UploadedFile) {
                $filename = uniqid($trick->getSlug() . '-', true); // file name without extension
                // Resize the picture file to severals widths (cf service.yaml),
                // and move files in their corresponding directory named with each width
                try {
                    $fullFilename = $this->getImageProcess()->execute($file, $filename);
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
        // process video(s)
        $videos = $trick->getVideos();
        foreach ($videos as $video) {
            if (empty($video->getService()) || empty($video->getCode())) {
                $trick->removeVideo($video);
            } else {
                $video->setTrick($trick);
                $trick->addVideo($video);
            }
        }
        $entityManager = $this->getManagerRegistry()->getManager();
        $entityManager->persist($trick);
        $entityManager->flush();
        $this->session->getFlashBag()->add('notice',"Le trick " . $trick->getName() . " vient d'être ajouté");
    }

    /**
     * Get the value of slugger
     */ 
    public function getSlugger(): SluggerInterface
    {
        return $this->slugger;
    }

    /**
     * Get the value of imageProcess
     */ 
    public function getImageProcess(): ImageProcess
    {
        return $this->imageProcess;
    }

    /**
     * Get the value of managerRegistry
     */ 
    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }
}
