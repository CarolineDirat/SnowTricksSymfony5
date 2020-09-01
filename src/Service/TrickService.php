<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Trick;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class TrickService implements TrickServiceInterface
{
    private ManagerRegistry $managerRegistry;

    private ParameterBagInterface $container;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ParameterBagInterface $container
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->container = $container;
    }

    /**
     * deletePicturesFiles
     * Method called when a trick is delete, to delete it's pictures files.
     */
    public function deletePicturesFiles(Trick $trick): void
    {
        $pictures = $trick->getPictures();
        $filenames = [];
        $imagesDirectories = $this->container->get('app.pictures_folders_names');
        // the same picture is multiple, corresponding to different widths, in several folders
        foreach ($imagesDirectories as $value) {
            foreach ($pictures as $picture) {
                $filenames[] = $this->container->get('app.images_directory').$value.'/'.$picture->getFilename();
            }
        }
        $filesystem = new Filesystem();
        $filesystem->remove($filenames);
    }

    public function delete(Trick $trick): void
    {
        $entityManager = $this->managerRegistry->getManager();
        $this->deletePicturesFiles($trick);
        $entityManager->remove($trick);
        $entityManager->flush();
    }

    public function deleteFirstImage(Trick $trick): void
    {
        $trick->setFirstPicture(null);
        $this->managerRegistry->getManager()->flush();
    }

    public function updateFirstImage(Trick $trick, int $pictureId): Picture
    {
        foreach ($trick->getPictures() as $picture) {
            if ($pictureId === $picture->getId()) {
                $trick->setFirstPicture($picture);
            }
        }
        $this->managerRegistry->getManager()->flush();

        return $trick->getFirstPicture();
    }

    public function updateName(Trick $trick, string $name): string
    {
        $trick->setName($name);
        $this->managerRegistry->getManager()->flush();

        return $trick->getName();
    }
}
