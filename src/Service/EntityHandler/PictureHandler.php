<?php

namespace App\Service\EntityHandler;

use App\Entity\Trick;
use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PictureHandler extends AbstractEntityHandler
{
    private PictureRepository $pictureRepository;

    public function __construct(ManagerRegistry $managerRegistry, PictureRepository $pictureRepository)
    {
        parent::__construct($managerRegistry);
        $this->pictureRepository = $pictureRepository;
    }

    public function delete(Trick $trick, string $pictureId): void
    {
        $picture = $this->pictureRepository->find($pictureId);
        // delete files of the delete picture
        $this->pictureRepository->deletePictureFiles($picture);
        // delete picture from database
        $trick->removePicture($picture);
        $this->managerRegistry->getManager()->flush();
    }
}
