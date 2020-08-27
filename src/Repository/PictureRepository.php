<?php

namespace App\Repository;

use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    private ParameterBagInterface $container;

    public function __construct(ManagerRegistry $registry, ParameterBagInterface $container)
    {
        parent::__construct($registry, Picture::class);
        $this->container = $container;
    }

    /**
     * deletePictureFiles.
     *
     * Method called when a picture is deleted or updated, to delete it's pictures files.
     */
    public function deletePictureFiles(Picture $picture): void
    {
        // the same picture is multiple, corresponding to different widths, in several folders
        $filenames = [];
        $imagesDirectories = $this->container->get('app.images_folders_names');
        foreach ($imagesDirectories as $value) {
            $filenames[] = $this->container->get('app.images_directory').$value.'/'.$picture->getFilename();
        }
        $filesystem = new Filesystem();
        $filesystem->remove($filenames);
    }

    // /**
    //  * @return Picture[] Returns an array of Picture objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Picture
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
