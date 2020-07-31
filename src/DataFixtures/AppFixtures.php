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
        $description = 'La main arrière vient graber la carre frontside entre les pieds. Sur un saut droit c’est un Indy Grab, sur un hip ou un quarter en front c’est un frontside indy ou frontside grab alors que sur un saut en back (3.6 back par exemple) ça sera un backside Indy.';
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
        $name = 'Canadian Bacon';
        $description = 'La main arrière grab la carre front en passant la main derrière la jambe arrière.';
        $group = $groups[0];
        $canadianBacon = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $canadianBacon, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($canadianBacon, 1, $faker);
        // add Videos
        $videosData = [
            '6zALCB6WJBI' => 'YouTube',
        ];
        $this->addVideoToTrick($canadianBacon, $videosData);
        $manager->persist($canadianBacon);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Mute Trick
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Mute';
        $description = 'Saisie de la carre frontside de la planche entre les deux pieds avec la main avant.';
        $group = $groups[0];
        $mute = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $mute, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($mute, 3, $faker);
        // add Videos
        $videosData = [
            'jm19nEvmZgM' => 'YouTube',
            'aZNjaV1dzKg' => 'YouTube',
        ];
        $this->addVideoToTrick($mute, $videosData);
        $manager->persist($mute);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Nose Grab
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Nose Grab';
        $description = 'La main avant grab le nose de la board (la spatule avant).';
        $group = $groups[0];
        $noseGrab = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $noseGrab, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($noseGrab, 4, $faker);
        // add Videos
        $videosData = [
            'gZFWW4Vus-Q' => 'YouTube',
            'M-W7Pmo-YMY' => 'YouTube',
            '_Qq-YoXwNQY' => 'YouTube',
            'y2MHu0mbzQw' => 'YouTube',
        ];
        $this->addVideoToTrick($noseGrab, $videosData);
        $manager->persist($noseGrab);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Mac Twist
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Mac Twist';
        $description = 'Flip agrémentés d\'une vrille.
        Un grand classique des rotations tête en bas qui se fait en backside, sur un mur backside de pipe. 
        Le Mc Twist est généralement fait en japan, un grab très tweaké (action d\'accentuer un grab en se contorsionnant).
        https://www.futura-sciences.com/sante/questions-reponses/sport-lexique-snowboard-10-termes-mieux-comprendre-8422/
        Rien à voir avec un menu d\'une enseigne de restauration rapide. Le Mc Twist est une rotation verticale avec une vrille. 
        Les champions réalisent des « double Mc Twist » tout en y ajoutant des rotations horizontales.
        Double Mc Twist 1260 :
        https://www.mennenfrance.fr/article/les-figures-de-snowboard-les-plus-spectaculaires_a473/1
        Le Mc Twist est un flip (rotation verticale) agrémenté d\'une vrille. 
        Un saut très périlleux réservé aux professionnels. 
        Le champion précoce Shaun White s\'est illustré par un Double Mc Twist 1260 lors de sa session de Half-Pipe aux Jeux Olympiques de Vancouver en 2010. 
        Nul doute que c\'est cette figure qui lui a valu de remporter la médaille d\'or.
        ';
        $group = $groups[2];
        $mcTwist = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $mcTwist, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($mcTwist, 2, $faker);
        // add Videos
        $videosData = [
            'XATkSnCFsRU' => 'YouTube',
            'YQIvm_2ay-U' => 'YouTube',
            'DYX-1qzj-YE' => 'YouTube',
        ];
        $this->addVideoToTrick($mcTwist, $videosData);
        $manager->persist($mcTwist);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Backside rodeo
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Backside rodeo';
        $description = 'Une rotation tête en bas backside tournant dans le sens d\'un backflip qui peut se faire aussi bien sur un kicker, un pipe ou un hip.';
        $group = $groups[2];
        $backsideRodeo = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $backsideRodeo, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($backsideRodeo, 3, $faker);
        // add Videos
        $videosData = [
            'aHFlwDYdoIQ' => 'YouTube',
            'gBUCKSVi2Nc' => 'YouTube',
        ];
        $this->addVideoToTrick($backsideRodeo, $videosData);
        $manager->persist($backsideRodeo);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Cork
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Cork';
        $description = 'Le diminutif de corkscrew qui signifie littéralement tire-bouchon et désignait les premières simples rotations têtes en bas en frontside. Désormais, on utilise le mot cork à toute les sauces pour qualifier les figures où le rider passe la tête en bas, peu importe le sens de rotation. Et dorénavant en compétition, on parle souvent de double cork, triple cork et certains riders vont jusqu\'au quadruple cork !
        Backside Triple Cork 1440 : en langage snowboard, un cork est une rotation horizontale plus ou moins désaxée, selon un mouvement d\'épaules effectué juste au moment du saut. Le tout premier Triple Cork a été plaqué par Mark McMorris en 2011, lequel a récidivé lors des Winter X Games 2012... avant de se faire voler la vedette par Torstein Horgmo, lors de ce même championnat. Le Norvégien réalisa son propre Backside Triple Cork 1440 et obtint la note parfaite de 50/50.';
        $group = $groups[3];
        $cork = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $cork, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($cork, 3, $faker);
        // add Videos
        $videosData = [
            'Br6ZJM01I6s' => 'YouTube',
            'FMHiSF0rHF8' => 'YouTube',
        ];
        $this->addVideoToTrick($cork, $videosData);
        $manager->persist($cork);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // One Foot Grab
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'One Foot Grab';
        $description = 'Le pied arrière est détaché de la fix, et la main avant grab la carre arrière un peu avant le nose de la board.';
        $group = $groups[5];
        $oneFootGrab = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $oneFootGrab, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($oneFootGrab, 1, $faker);
        // add Videos
        $videosData = [
            'qFanNTiY6-0' => 'YouTube',
        ];
        $this->addVideoToTrick($oneFootGrab, $videosData);
        $manager->persist($oneFootGrab);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Backside air
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Backside air';
        $description = 'Le grab star du snowboard qui peut être fait d\'autant de façon différentes qu\'il y a de styles de riders. 
        Il consiste à attraper la carre arrière entre les pieds, ou légèrement devant, et à pousser avec sa jambe arrière pour ramener la planche devant. 
        C\'est une figure phare en pipe ou sur un hip en backside. C\'est généralement avec ce trick que les riders vont le plus haut.
        Les mauvaises langues prétendent qu’un backside air suffit à reconnaître ceux qui savent snowboarder. 
        Si c’est vrai, alors Nicolas Müller est le meilleur snowboardeur du monde.
         Personne ne sait s’étirer aussi joliment, ne demeure aussi zen, n’est aussi provocant dans la jouissance.';
        $group = $groups[6];
        $backsideAir = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $backsideAir, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($backsideAir, 3, $faker);
        // add Videos
        $videosData = [
            'h0UtyOX9p90' => 'YouTube',
        ];
        $this->addVideoToTrick($backsideAir, $videosData);
        $manager->persist($backsideAir);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Method air
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        $name = 'Method air';
        $description = 'Cette figure – qui consiste à attraper sa planche d\'une main et le tourner perpendiculairement au sol – est un classique \"old school\". 
        Il n\'empêche qu\'il est indémodable, avec de vrais ambassadeurs comme Jamie Lynn ou la star Terje Haakonsen. 
        En 2007, ce dernier a même battu le record du monde du "air" le plus haut en s\'élevant à 9,8 mètres au-dessus du kick (sommet d\'un mur d\'une rampe ou autre structure de saut).';
        $group = $groups[6];
        $methodAir = $this->createTrickWithoutCommentsPicturesVideos($name, $description, $group, $faker, $slugger);
        // add 12 comments to Indy trick
        $this->addCommentsToTrick(random_int(1, 15), $methodAir, $users, $manager, $faker);
        // add Images
        $this->addPicturesToTrick($methodAir, 3, $faker);
        // add Videos
        $videosData = [
            '2Ul5P-KucE8' => 'YouTube',
            'ZWZGE9yp5hA' => 'YouTube',
        ];
        $this->addVideoToTrick($methodAir, $videosData);
        $manager->persist($methodAir);

        $manager->flush();
    }

    /**
     * createTrickWithoutCommentsPicturesVideos :
     * create a trick without comments, pictures and videos neither.
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
     * addCommentsToTrick : add $number comments to a trick entity, from 2 users in an array.
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
     * addVideoToTrick.
     *
     * @param array $videosData array = ['code' => 'service']
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
            $picture->setFilename($trick->getSlug().'-'.$i.'.jpg');
            if ($i < 4) {
                $picture->setAlt($faker->realText(40));
            }
            $trick->addPicture($picture);
            $pictures[] = $picture;
        }
        $trick->setFirstPicture($pictures[0]);
    }
}
