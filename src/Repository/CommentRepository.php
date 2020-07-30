<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getLastComments(Trick $trick, int $number): array
    {
        return array_slice(array_reverse($trick->getComments()->toArray()), 0, $number);
    }

    /**
     * get $limit comments from $offset
     *
     */
    public function getPaginatedComments(Trick $trick, int $offset, int $limit): array
    {
        $comments = $this
                ->createQueryBuilder('c')
                ->addSelect('user')
                ->leftJoin('c.user', 'user' )
                ->where('c.trick = :trick')
                ->setParameter('trick', $trick)
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->orderBy('c.createdAt', 'DESC')
                ->getQuery()
                ->getArrayResult()
        ;

        return $this->deleteSensitiveData($comments);
    }

    public function deleteSensitiveData(array $comments): array
    {
        $sensitiveDataToDelete = ['id', 'roles', 'password', 'email', 'uuid', 'createdAt'];
        
        for ($i=0; $i < count($comments) ; $i++) {
            foreach ($sensitiveDataToDelete as $value) {
                unset($comments[$i]['user'][$value]);
            } 
        }

        return $comments;
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
