<?php

namespace App\Repository;

use App\Entity\Trick;
use App\Service\ConstantsIni;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
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

    private ParameterBagInterface $container;

    private CommentRepository $commentRepository;

    public function __construct(
        ManagerRegistry $registry,
        ParameterBagInterface $container,
        ConstantsIni $constantsIni,
        CommentRepository $commentRepository
    ) {
        parent::__construct($registry, Trick::class);
        $this->container = $container;
        $this->constants = $constantsIni->getConstantsIni();
        $this->commentRepository = $commentRepository;
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
     * findOneWithNLastFiveComments
     * get a tricks with it's last N comments.
     * Only N comments are requested (with extra_lazy on trick's comments)
     * (N is defined in constants.ini).
     *
     * @return Trick
     */
    public function findOneWithNLastComments(string $uuid): ?Trick
    {
        $number = $this->constants['comments']['number_last_displayed'];
        $trick = $this->findOneBy(['uuid' => $uuid]);

        /* first way :
        $comments = $trick->getComments();
        $nbComments = $comments->count();
        $NlastComments = $nbComments > $number ? $comments->slice($nbComments - $number) : $comments->slice(0);
        $NlastComments = array_reverse($NlastComments);
        $trick->getComments()->clear();
        foreach ($lastComments as $comment) {
            $trick->addComment($comment);
        }
        */

        // second way: (one less request)
        $NlastComments = $this
            ->commentRepository
            ->createQueryBuilder('c')
            ->where('c.trick = :trick')
            ->setParameter('trick', $trick)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($number)
            ->getQuery()
            ->getResult();
        $trick->getComments()->clear();
        foreach ($NlastComments as $comment) {
            $trick->addComment($comment);
        }

        return $trick;
    }

    /**
     * deletePicturesFiles
     * Method called when a trick is delete, to delete it's pictures files.
     */
    public function deletePicturesFiles(Trick $trick): void
    {
        $pictures = $trick->getPictures();
        $filenames = [];
        $imagesDirectories = $this->container->get('app.images_folders_names');
        // the same picture is multiple, corresponding to different widths, in several folders
        foreach ($imagesDirectories as $value) {
            foreach ($pictures as $picture) {
                $filenames[] = $this->container->get('app.images_directory').$value.'/'.$picture->getFilename();
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

    /*
    public function findOneWithCommentsOrderByDesc(string $uuid): Trick
    {
        $uuid = Uuid::fromString($uuid)->getBytes();

        return $this->createQueryBuilder('t')
            ->select('t, c')
            ->join('t.comments', 'c')
            ->andWhere('t.uuid = :uuid')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
