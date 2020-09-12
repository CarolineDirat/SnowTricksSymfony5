<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Trick;
use App\Repository\PictureRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class PictureService implements PictureServiceInterface
{
    private PictureRepository $pictureRepository;

    private ManagerRegistry $managerRegistry;

    private ImageProcessInterface $imageProcess;

    private ParameterBagInterface $container;

    public function __construct(
        ManagerRegistry $managerRegistry,
        PictureRepository $pictureRepository,
        ImageProcessInterface $imageProcess,
        ParameterBagInterface $container
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->pictureRepository = $pictureRepository;
        $this->imageProcess = $imageProcess;
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
        $imagesDirectories = $this->container->get('app.pictures_folders_names');
        foreach ($imagesDirectories as $value) {
            $filenames[] = $this->container->get('app.images_directory').$value.'/'.$picture->getFilename();
        }
        $filesystem = new Filesystem();
        $filesystem->remove($filenames);
    }

    public function delete(Trick $trick, string $pictureId): void
    {
        $picture = $this->pictureRepository->find($pictureId);
        // delete files of the delete picture
        $this->deletePictureFiles($picture);
        // delete picture from database
        $trick->removePicture($picture);
        $this->managerRegistry->getManager()->flush();
    }

    public function getData(Request $request): array
    {
        $nameForm = $request->request->get('nameForm');
        $file = $request->files->get('trick')['pictures'][$nameForm]['file'];
        $alt = $request->request->get('trick')['pictures'][$nameForm]['alt'];

        return ['file' => $file, 'alt' => $alt];
    }

    public function isDataPictureValid(Request $request): ?string
    {
        $data = $this->getData($request);
        $file = $data['file'];
        $alt = $data['alt'];
        // validation
        list($width, $height) = getimagesize($file->getPathName());
        if (!in_array(
            $file->getClientMimeType(),
            ['image/png', 'image/jpeg', 'image/gif', 'image/webp']
        )) {
            return 'Le fichier n\'est pas accepté. 
                Ses types mimes acceptés sont image/png, image/jpeg, image/gif et image/webp. 
                (Et sa taille est limitée à 10M.)';
        }
        if ($width < 300) {
            return 'Le fichier n\'est pas accepté. 
                Il doit faire au minimum 300px de largeur. (Et sa taille est limitée à 10M.)';
        }
        if (0.67 > $width / $height) {
            return 'Le fichier n\'est pas accepté. 
                Le ratio largeur/hauteur doit faire au minimum de 0,67. 
                (Et sa taille est limitée à 10M.)';
        }
        if (strlen($alt) > 100) {
            return 'Attention ! La description ne doit pas dépasser 100 caractères';
        }

        return null;
    }

    public function update(Trick $trick, Picture $picture, Request $request): ?Picture
    {
        $data = $this->getData($request);
        $file = $data['file'];
        $alt = $data['alt'];
        // process file and filename
        if (!($file instanceof UploadedFile)) {
            return null;
        }
        $filename = uniqid($trick->getSlug().'-', true); // file name without extension
        // Resize the picture file to severals widths (cf service.yaml),
        // and move files in their corresponding directory named with each width
        $fullFilename = $this->imageProcess->executeForPictures($file, $filename);
        // delete files of the replaced picture
        $this->deletePictureFiles($picture);
        // define new file name of picture
        $picture->setFilename($fullFilename);
        // process alt
        $picture->setAlt($alt);
        $this->managerRegistry->getManager()->flush();

        return $picture;
    }
}
