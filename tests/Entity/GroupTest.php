<?php

namespace App\Tests\Entity;

use App\Entity\Group;
use App\Entity\Trick;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    public function testName(): void
    {
        $group = new Group();
        $name = 'Flip';
        $group->setName($name);
        $this->assertEquals($name, $group->getName());
    }

    public function testTricks(): void
    {
        $group = new Group();
        $this->assertInstanceOf(ArrayCollection::class, $group->getTricks());

        $trick = new Trick();
        $trick->setName('Cork');
        $group->addTrick($trick);
        $this->assertTrue(in_array($trick, $group->getTricks()->toArray(), true));

        $group->removeTrick($trick);
        $this->assertFalse(in_array($trick, $group->getTricks()->toArray(), true));
    }
}
