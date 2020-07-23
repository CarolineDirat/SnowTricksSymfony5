<?php

namespace App\Tests\Entity;

use App\Entity\Trick;
use App\Entity\Video;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    public function testCode(): void
    {
        $video = new Video();
        $code = 'jdfsj132dfqs54f3df541q';
        $video->setCode($code);
        $this->assertEquals($code, $video->getCode());
    }

    public function testService(): void
    {
        $video = new Video();
        $service = 'Youtube';
        $video->setService($service);
        $this->assertEquals($service, $video->getService());
    }

    public function testTrick(): void
    {
        $video = new Video();
        $trick = new Trick();
        $video->setTrick($trick);
        $this->assertEquals($trick, $video->getTrick());
    }
}
