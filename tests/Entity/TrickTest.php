<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\UuidInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TrickTest extends TestCase
{
    public function testUuid(): void
    {
        $trick = new Trick();
        $uuid = Uuid::uuid4();
        $this->assertInstanceOf(UuidInterface::class, $uuid);

        $trick->setUuid($uuid);
        $this->assertEquals($uuid, $trick->getUuid());
    }

    public function testName(): void
    {
        $trick = new Trick();
        $name = 'Indy';
        $trick->setName($name);
        $this->assertEquals('Indy', $trick->getName());
    }

    public function testDescription(): void
    {
        $trick = new Trick();
        $description = 'trick description';
        $trick->setDescription($description);
        $this->assertEquals('trick description', $trick->getDescription());
    }

    public function testSlug(): void
    {
        $trick = new Trick();
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug('trick title to slug');
        $trick->setSlug($slug);
        $this->assertEquals($slug, $trick->getSlug());
    }

    public function testCreatedAt(): void
    {
        $trick = new Trick();
        $this->assertInstanceOf(DateTimeImmutable::class, $trick->getCreatedAt());

        $date = new DateTimeImmutable('2020-11-11');
        $trick->setCreatedAt($date);
        $this->assertEquals($date, $trick->getCreatedAt());
    }

    public function testUpdatedAt(): void
    {
        $trick = new Trick();
        $this->assertInstanceOf(DateTimeImmutable::class, $trick->getUpdatedAt());

        $date = new DateTimeImmutable('2020-11-11');
        $trick->setUpdatedAt($date);
        $this->assertEquals($date, $trick->getUpdatedAt());
    }

    public function testPictures(): void
    {
        $trick = new Trick();
        $this->assertInstanceOf(ArrayCollection::class, $trick->getPictures());

        $picture = new Picture();
        $picture->setAlt('picture description');
        $trick->addPicture($picture);
        $this->assertTrue(in_array($picture, $trick->getPictures()->toArray(), true));

        $trick->removePicture($picture);
        $this->assertFalse(in_array($picture, $trick->getPictures()->toArray(), true));
    }

    public function testVideos(): void
    {
        $trick = new Trick();
        $this->assertInstanceOf(ArrayCollection::class, $trick->getVideos());

        $video = new Video();
        $video->setCode('LKJFQDJQ12421DQ6D');
        $trick->addVideo($video);
        $this->assertTrue(in_array($video, $trick->getVideos()->toArray(), true));

        $trick->removeVideo($video);
        $this->assertFalse(in_array($video, $trick->getVideos()->toArray(), true));
    }

    public function testComments(): void
    {
        $trick = new Trick();
        $this->assertInstanceOf(ArrayCollection::class, $trick->getComments());

        $comment = new Comment();
        $comment->setContent('I am a comment.');
        $trick->addComment($comment);
        $this->assertTrue(in_array($comment, $trick->getComments()->toArray(), true));

        $trick->removeComment($comment);
        $this->assertFalse(in_array($comment, $trick->getComments()->toArray(), true));
    }

    public function testGroupTrick(): void
    {
        $trick = new Trick();
        $group = new Group();
        $trick->setGroupTrick($group);
        $this->assertEquals($group, $trick->getGroupTrick());
    }

    public function testFirstPicture(): void
    {
        $trick = new Trick();
        $picture = new Picture();
        $picture->setFilename('indy-1.jpg');
        $trick->addPicture($picture);
        $trick->setFirstPicture($picture);
        $this->assertEquals($picture, $trick->getFirstPicture());
    }
}
