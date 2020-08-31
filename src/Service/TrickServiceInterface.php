<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Trick;

interface TrickServiceInterface
{
    public function deletePicturesFiles(Trick $trick): void;

    public function delete(Trick $trick): void;

    public function deleteFirstImage(Trick $trick): void;

    public function updateFirstImage(Trick $trick, int $pictureId): Picture;

    public function updateName(Trick $trick, string $name): string;
}
