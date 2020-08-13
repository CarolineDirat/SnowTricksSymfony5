<?php

namespace App\EntityForm;

use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;

class TrickForm
{
    public function initialize(): Trick
    {
        $trick = new Trick();
        $picture = new Picture();
        $trick->addPicture($picture);
        $video = new Video();
        $trick->addVideo($video);

        return $trick;
    }
}
