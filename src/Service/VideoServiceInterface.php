<?php

namespace App\Service;

use App\Entity\Trick;
use App\Entity\Video;

interface VideoServiceInterface
{
    public function delete(Trick $trick, string $videoId): void;

    public function update(array $data): Video;
}
