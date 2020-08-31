<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Trick;
use Symfony\Component\HttpFoundation\Request;

interface PictureServiceInterface
{
    public function deletePictureFiles(Picture $picture): void;

    public function delete(Trick $trick, string $pictureId): void;

    /**
     * getData.
     *
     * get data ($file and $alt) of new picture, from AJAX request formData object
     */
    public function getData(Request $request): array;

    public function isDataPictureValid(Request $request): ?string;

    public function update(Trick $trick, Picture $picture, Request $request): ?Picture;
}
