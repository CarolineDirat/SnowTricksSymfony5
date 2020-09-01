<?php

namespace App\FormHandler;

use App\Entity\User;
use App\Service\ImageProcessInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class UserProfileFormHandler extends AbstractFormHandler
{
    private ManagerRegistry $managerRegistry;

    private ImageProcessInterface $imageProcess;

    private Session $session;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ImageProcessInterface $imageProcess
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->imageProcess = $imageProcess;
        $this->session = new Session(new NativeSessionStorage(), new AttributeBag());
    }

    public function getEntityClass(): string
    {
        return User::class;
    }

    public function process(object $user): void
    {
        $file = $this->form->get('file')->getData();
        if (!($file instanceof UploadedFile)) {
            $this->session->getFlashBag()->add('notice', "L'upload du fichier a échoué");
        }
        if ($file instanceof UploadedFile) {
            // define filename without extension
            $filename = base_convert($user->getUuid()->getHex(), 16, 30);
            dump($filename);
            // Resize the picture file, move it in it's directory
            // and save it's name in profile user property
            try {
                $fullFilename = $this->imageProcess->executeForProfile($file, $filename);
                if (empty($user->getProfile())) {
                    $user->setProfile($fullFilename);
                }
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload or process
                $this->session->getFlashBag()->add('upload', $e->getMessage());
            }
            $this->managerRegistry->getManager()->flush();
            $this->session->getFlashBag()->add('success', "Votre photo de profil vient d'être modifiée");
        }
    }
}
