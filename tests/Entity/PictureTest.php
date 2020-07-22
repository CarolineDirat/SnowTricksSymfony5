<?php

namespace App\Tests\Entity;

use App\Entity\Picture;
use App\Entity\Trick;
use PHPUnit\Framework\TestCase;

class PictureTest extends TestCase
{
    public function testFilename(): void
    {
        $picture = new Picture();
        $filename = "default.jpg";
        $picture->setFilename($filename);
        $this->assertEquals($filename, $picture->getFilename());
    }

    public function testAlt(): void
    {
        $picture = new Picture();
        $alt = "picture description";
        $picture->setAlt($alt);
        $this->assertEquals($alt, $picture->getAlt());
    }

    public function testTrick(): void
    {
        $picture = new Picture();
        $trick = new Trick();
        $picture->setTrick($trick);
        $this->assertEquals($trick, $picture->getTrick());
    }
}
