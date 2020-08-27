<?php

namespace App\Service\EntityHandler;

use App\Entity\Picture;
use App\Entity\Trick;
use App\Repository\TrickRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TrickHandler extends AbstractEntityHandler
{
    private TrickRepository $trickRepository;

    public function __construct(ManagerRegistry $managerRegistry, TrickRepository $trickRepository)
    {
        parent::__construct($managerRegistry);
        $this->trickRepository = $trickRepository;
    }

    public function delete(Trick $trick): void
    {
        $entityManager = $this->managerRegistry->getManager();
        $this->trickRepository->deletePicturesFiles($trick);
        $entityManager->remove($trick);
        $entityManager->flush();
    }

    public function deleteFirstImage(Trick $trick): void
    {
        $trick->setFirstPicture(null);
        $this->managerRegistry->getManager()->flush();
    }

    public function updateFirstImage(Trick $trick, int $pictureId): Picture
    {
        foreach ($trick->getPictures() as $picture) {
            if ($pictureId === $picture->getId()) {
                $trick->setFirstPicture($picture);
            }
        }
        $this->managerRegistry->getManager()->flush();

        return $trick->getFirstPicture();
    }

    public function updateName(Trick $trick, string $name): string
    {
        $trick->setName($name);
        $this->managerRegistry->getManager()->flush();

        return $trick->getName();
    }
}
