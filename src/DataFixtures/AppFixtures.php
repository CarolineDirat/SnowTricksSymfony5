<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void // @phpstan-ignore-line
    {
        $faker = Factory::create();
        $slugger = new AsciiSlugger();

        $groups = [];
        $groupsName = ['Grab', 'Rotation', 'Flip', 'Rotation désaxée', 'Slide', 'One foot', 'Old school', 'Autre'];
        foreach ($groupsName as $name) {
            $group = new Group();
            $group->setName($name);
            $manager->persist($group);
            $groups[] = $group;
        }
        // create 2 Users
        $users = [];
        for ($i = 0; $i < 2; ++$i) {
            $user = new User();
            $user->setUsername('user'.($i + 1));
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'password'));
            $user->setEmail('user'.($i + 1).'@domain.com');
            $user->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeThisMonth('now', 'Europe/Paris')));
            $user->setUuid(Uuid::uuid4());
            $users[] = $user;
            $manager->persist($user);
        }
        // put a profile picture on one user
        $users[1]->setProfile('squirrel.jpg');
        $manager->persist($users[1]);
        // Indy Trick without pictures and video1
        $indy = new Trick();
        $indy->setUuid(Uuid::uuid4());
        $indy->setName('Indy');
        $indy->setDescription($faker->paragraphs(10, true));
        $indy->setSlug(strtolower($slugger->slug($indy->getName())));
        $indy->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeThisMonth('now', 'Europe/Paris')));
        $indy->setUpdatedAt($indy->getCreatedAt()->add(new DateInterval('P2D')));
        $indy->setGroupTrick($groups[0]);
        // comments
        $comments = [];
        for ($i = 0; $i < 12; ++$i) {
            $comment = new Comment();
            $comment->setContent($faker->text(200));
            $comment->setCreatedAt((new DateTimeImmutable('2020-08-08 12:10:55'))->add(new DateInterval('PT'.$i.'H')));
            if (0 === ($i % 2)) {
                $users[0]->addComment($comment);
                $comment->setUser($users[0]);
                $manager->persist($users[0]);
            } else {
                $users[1]->addComment($comment);
                $comment->setUser($users[1]);
                $manager->persist($users[1]);
            }
            $comments[] = $comment;
        }
        foreach ($comments as $comment) {
            $indy->addComment($comment);
        }
        // Images
        $pictures = [];
        for ($i = 1; $i <= 7; ++$i) {
            $picture = new Picture();
            $picture->setFilename('indy-'.$i.'.jpg');
            if ($i < 4) {
                $picture->setAlt($faker->realText(40));
            }
            $indy->addPicture($picture);
            $pictures[] = $picture;
        }
        $indy->setFirstPicture($pictures[3]);
        // Videos
        $videosData = [
            '6QsLhWzXGu0' => 'YouTube',
            'iKkhKekZNQ8' => 'YouTube', // @phpstan-ignore-line
            '6yA3XqjTh_w' => 'YouTube',
            'iKkhKekZNQ8' => 'YouTube',
            'x2b3swg' => 'Dailymotion',
            '19967156' => 'Vimeo',
        ];
        foreach ($videosData as $code => $service) {
            $video = new Video();
            $video->setCode($code);
            $video->setService($service);
            $indy->addVideo($video);
        }
        $manager->persist($indy);
        $manager->flush();
    }
}
