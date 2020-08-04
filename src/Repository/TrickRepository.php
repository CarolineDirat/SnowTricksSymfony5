<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
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
