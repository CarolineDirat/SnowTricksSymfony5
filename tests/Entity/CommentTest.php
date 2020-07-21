<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testContent(): void
    {
        $comment = new Comment();
        $content = "Je suis un commentaire.";
        $comment->setContent($content);
        $this->assertEquals($content, $comment->getContent());
    }

    public function testCreatedAt(): void
    {
        $comment = new Comment();
        $createdAt = new DateTimeImmutable('2020-05-05');
        $comment->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $comment->getCreatedAt());
    }

    public function testUser(): void
    {
        $comment = new Comment();
        $user = new User();
        $comment->setUser($user);
        $this->assertInstanceOf(User::class, $comment->getUser(), "user property must be an instance of User");
    }

    public function testTrick(): void
    {
        $comment = new Comment();
        $trick = new Trick();
        $comment->setTrick($trick);
        $this->assertInstanceOf(Trick::class, $comment->getTrick(), "trick property must be an instance of Trick");
    }
}
