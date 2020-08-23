<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    private array $constants;

    private CommentRepository $commentRepository;

    public function __construct(ManagerRegistry $registry, ParameterBagInterface $container, CommentRepository $commentRepository)
    {
        parent::__construct($registry, Trick::class);
        $this->commentRepository = $commentRepository;
        $this->constants = parse_ini_file(
            $container->get('kernel.project_dir').'/constants.ini',
            true,
            INI_SCANNER_TYPED
        );
    }

    /**
     * findPaginatedTricks
     * Find $limits tricks from $offset.
     *
     * @param int $offset position of first Trick
     * @param int $limit  number of tricks
     *
     * @return Trick[]
     */
    public function getPaginatedTricks(int $offset, int $limit): array
    {
        return $this
            ->getEntityManager()
            ->createQuery(
                'SELECT t
                FROM App\Entity\Trick t
                ORDER BY t.name ASC'
            )
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getResult()
        ;
    }

    /**
     * getArrayPaginatedTricks
     * Find $limits tricks from $offset.
     *
     * @param int $offset position of first Trick
     * @param int $limit  number of tricks
     */
    public function getArrayPaginatedTricks(int $offset, int $limit): array
    {
        $tricksWithPictures = $this->getArrayPaginatedTricksWithPictures($offset, $limit);
        $tricksWithFirstPicture = $this->getArrayPaginatedTricksWithFirstPicture($offset, $limit);

        return $this->addPicturesToTricksWithFirstPictures($tricksWithPictures, $tricksWithFirstPicture);
    }

    public function getArrayPaginatedTricksWithFirstPicture(int $offset, int $limit): array
    {
        return $this
            ->createQueryBuilder('t')
            ->addSelect('picture')
            ->leftJoin('t.firstPicture', 'picture')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getArrayResult()
        ;
    }

    public function getArrayPaginatedTricksWithPictures(int $offset, int $limit): array
    {
        $allTricksWithPictures = $this
            ->createQueryBuilder('t')
            ->addSelect('picture')
            ->leftJoin('t.pictures', 'picture')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ; // I don't know why but setFirstResult($offset) and setMaxResults($limit) doesn't work ...

        return array_slice($allTricksWithPictures, $offset, $limit); // ... so I use array_slice
    }

    public function addPicturesToTricksWithFirstPictures(
        array $tricksWithPictures,
        array $tricksWithFirstPicture
    ): array {
        foreach ($tricksWithFirstPicture as $key => $value) {
            $tricksWithFirstPicture[$key]['pictures'] = $tricksWithPictures[$key]['pictures'];
        }

        return $tricksWithFirstPicture; // witch is now with pictures
    }
    
    /**
     * findWithLastFiveComments
     * get a tricks with it's last x comments
     * it returns an trick entity (lazy) and and a trick as array with only it's last x comments
    */
    public function findWithLastComments(string $uuid): array
    {
        $number = $this->constants['comments']['number_last_displayed']; // number of last comments = x

        $trick = $this->findOneBy(['uuid' => $uuid]);     

        $trickArray = $this
            ->createQueryBuilder('t')
            ->where('t.uuid = :uuid')
            ->setParameter('uuid', $trick->getUuid()->getBytes())
            ->getQuery()
            ->getArrayResult();

        $trickArray[0]['pictures'] = $trick->getPictures()->toArray();
        $trickArray[0]['videos'] = $trick->getVideos()->toArray();
        $trickArray[0]['groupTrick'] = $trick->getGroupTrick();
        $trickArray[0]['firstPicture'] = $trick->getFirstPicture();
        
        $comments = $this->commentRepository
            ->createQueryBuilder('c')
            ->andWhere('c.trick = :trick')
            ->setParameter('trick', $trick)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($number)
            ->getQuery()
            ->getResult();

        $trickArray[0]['comments'] = $comments;

        return ['trickArray' => $trickArray[0], 'trickEntity' => $trick];
    }

    /**
     * deletePicturesFiles
     * Method called when a trick is delete, to delete it's pictures files.
     */
    public function deletePicturesFiles(Trick $trick, ParameterBagInterface $container): void
    {
        $pictures = $trick->getPictures();
        $filenames = [];
        $imagesDirectories = $container->get('app.images_folders_names');
        // the same picture is multiple, corresponding to different widths, in several folders
        foreach ($imagesDirectories as $value) {
            foreach ($pictures as $picture) {
                $filenames[] = $container->get('app.images_directory').$value.'/'.$picture->getFilename();
            }
        }
        $filesystem = new Filesystem();
        $filesystem->remove($filenames);
    }

    // /**
    //  * @return Trick[] Returns an array of Trick objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
