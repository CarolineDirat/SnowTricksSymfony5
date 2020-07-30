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
use Faker\Generator;
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
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Indy Trick 
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Indy';
        $description = "La main arrière vient graber la carre frontside entre les pieds. Sur un saut droit c’est un Indy Grab, sur un hip ou un quarter en front c’est un frontside indy ou frontside grab alors que sur un saut en back (3.6 back par exemple) ça sera un backside Indy.";
        $group = $groups[0];
        $indy = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(12, $indy, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($indy, 7, $faker);
        // add Videos
        $videosData = [
            '6QsLhWzXGu0' => 'YouTube',
            'iKkhKekZNQ8' => 'YouTube', // @phpstan-ignore-line
            '6yA3XqjTh_w' => 'YouTube',
            'iKkhKekZNQ8' => 'YouTube',
            'x2b3swg' => 'Dailymotion',
            '19967156' => 'Vimeo',
        ];
        $this->addVideoToTrick($indy, $videosData);
        $manager->persist($indy);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Canadian Bacon Trick 
        /////////////////////////////////////////////////////////////////////////////////////////////////////////



        $manager->flush();
    }
    
    /**
     * createTrickWithoutCommentsPicturesVideos :
     * create a trick without comments, pictures and videos neither
     *
     * @param  string $name
     * @param  string $description
     * @param  Group $group
     * @return Trick
     */
    public function createTrickWithoutCommentsPicturesVideos(
        string $name,
        string $description,
        Group $group,
        Generator $faker,
        AsciiSlugger $slugger
    ): Trick {
        $trick = new Trick();
        $trick->setUuid(Uuid::uuid4());
        $trick->setName($name);
        $trick->setDescription($description);
        $trick->setSlug(strtolower($slugger->slug($trick->getName())));
        $trick->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeThisMonth('now', 'Europe/Paris')));
        $trick->setUpdatedAt($trick->getCreatedAt()->add(new DateInterval('P2D')));
        $trick->setGroupTrick($group);

        return $trick;
    }
    
    /**
     * addCommentsToTrick : add $number comments to a trick entity, from 2 users in an array
     *
     * @return void
     */
    public function addCommentsToTrick(
        int $number,
        Trick $trick,
        array $users,
        ObjectManager $manager,
        Generator $faker
    ): void {
        $comments = [];
        for ($i = 0; $i < $number; ++$i) {
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
            $trick->addComment($comment);
        }
    }
    
    /**
     * addVideoToTrick
     *
     * @param  Trick $trick
     * @param  array $videosData array = ['code' => 'service']
     * @return void
     */
    public function addVideoToTrick(Trick $trick, array $videosData): void
    {
        foreach ($videosData as $code => $service) {
            $video = new Video();
            $video->setCode($code);
            $video->setService($service);
            $trick->addVideo($video);
        }
    }
    
    public function addPicturesToTrick(Trick $trick, int $number, Generator $faker): void
    {
        $pictures = [];
        for ($i = 1; $i <= $number; ++$i) {
            $picture = new Picture();
            $picture->setFilename('indy-'.$i.'.jpg');
            if ($i < 4) {
                $picture->setAlt($faker->realText(40));
            }
            $trick->addPicture($picture);
            $pictures[] = $picture;
        }
        $trick->setFirstPicture($pictures[1]);
    }
}
