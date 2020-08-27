<?php

namespace App\Service\EntityHandler;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TrickHandler
{
    private ManagerRegistry $managerRegistry;

    private TrickRepository $trickRepository;

    public function __construct(ManagerRegistry $managerRegistry, TrickRepository $trickRepository)
    {
        $this->managerRegistry = $managerRegistry;
        $this->trickRepository = $trickRepository;
    }

    public function delete(Trick $trick): void
    {
        $entityManager = $this->managerRegistry->getManager();
        $this->trickRepository->deletePicturesFiles($trick);
        $entityManager->remove($trick);
        $entityManager->flush();
    }
}
