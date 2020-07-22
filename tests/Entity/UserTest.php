<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UserTest extends TestCase
{
    public function testUsername(): void
    {
        $user = new User();
        $username = "toto87";
        $user->setUsername($username);
        $this->assertEquals($username, $user->getUsername());
    }

    public function testRoles(): void
    {
        $user = new User();
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));

        $role = ['ROLE_APP'];
        $user->setRoles($role);
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));
        $this->assertTrue(in_array('ROLE_APP', $user->getRoles()));
        $this->assertEquals(['ROLE_APP', 'ROLE_USER'], $user->getRoles());
    }

    public function testPassword(): void
    {
        $user = new User();
        $password = password_hash("password", PASSWORD_BCRYPT);
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());
    }

    public function testEmail(): void
    {
        $user = new User();
        $email = "email.test@domain.com";
        $user->setEmail($email);
        $this->assertEquals($email, $user->getEmail());
    }

    public function testCreatedAt(): void
    {
        $user = new User();
        $createdAt = new DateTimeImmutable('200-11-03');
        $user->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $user->getCreatedAt());
    }

    public function testUuid(): void
    {
        $user = new User();
        $uuid = Uuid::uuid4();
        $this->assertInstanceOf(UuidInterface::class, $uuid);
        
        $user->setUuid($uuid);
        $this->assertEquals($uuid, $user->getUuid());
    }

    public function testComments(): void
    {
        $user = new User();
        $this->assertInstanceOf(ArrayCollection::class, $user->getComments());

        $comment = new Comment();
        $comment->setContent("Je suis un commentaire.");
        $user->addComment($comment);
        $this->assertTrue(in_array($comment, $user->getComments()->toArray(), true));

        $user->removeComment($comment);
        $this->assertFalse(in_array($comment, $user->getComments()->toArray(), true));
    }


}
