<?php

namespace App\Service\EntityHandler;

use App\Entity\Trick;
use App\Repository\VideoRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VideoHandler extends AbstractEntityHandler
{
    private VideoRepository $videoRepository;

    public function __construct(ManagerRegistry $managerRegistry, VideoRepository $videoRepository)
    {
        parent::__construct($managerRegistry);
        $this->videoRepository = $videoRepository;
    }

    public function delete(Trick $trick, string $videoId): void
    {
        $video = $this->videoRepository->find($videoId);
        $trick->removeVideo($video);
        $this->managerRegistry->getManager()->flush();
    }
}
