<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageProcess implements ImageProcessInterface
{    
    /**
     * Different widths of images, corresponding to directories to move them
     *
     * @var int[]
     */
    private array $widths;

    private ParameterBagInterface $parameterBag;

    private string $type;

    public function __construct(ParameterBagInterface $parameterBag, array $widths)
    {
        $this->widths = $widths;
        $this->parameterBag = $parameterBag;
    }
    
    /**
     * execute
     *
     * For each possible mime type of an uploaded image file,
     * we define :
     *  - the file extension corresponding to the mime type
     *      $filename = $filename.'.extension';
     *  - the callable function $imagecreatefromType 
     *      witch will create the source image from the file
     *  - the callable function $imageType 
     *      witch will save the resized file in %app.images_directory%/$resizedWidth/$filename
     * and the $this->resizesAndMoves() method is called
     *       
     * @param  UploadedFile $file   uploaded file from trick form
     * @param  string $filename     the new name of resize file (without it's extension)
     */
    public function execute(UploadedFile $file, string $filename): string
    {
        $mime = $file->getMimeType();
        switch ($mime) {
            case 'image/jpeg':
                $filename = $filename.'.jpg';
                $this->type = 'jpeg';
                $this->resizesAndMoves($file, $filename);

                return $filename;
            case 'image/png':
                $filename = $filename.'.png';
                $this->type = 'png';
                $this->resizesAndMoves($file, $filename);

                return $filename;
            case 'image/gif':
                $filename = $filename.'.gif';
                $this->type = 'gif';
                $this->resizesAndMoves($file, $filename);
                
                return $filename;
            case 'image/webp':
                $filename = $filename.'.webp';
                $this->type = 'webp';
                $this->resizesAndMoves($file, $filename);

                return $filename;
            default:
                // should never happen because it's check during form validation
                throw new FileException("Unknown image type");
        }
    }
    
    /**
     * imagecreatefromType
     *
     * @param  UploadedFile $file
     * @return resource
     */
    public function imagecreatefromType(UploadedFile $file)
    {
        $imagecreatefromType = 'imagecreatefrom' . $this->type;
        
        return $imagecreatefromType($file->getPathname());
    }
    
    /**
     * imageType
     *
     * @param  resource $destination
     * @param  int $resizeWidth
     * @param  string $filename
     * @return bool
     */
    public function imageType($destination, int $resizeWidth, string $filename): bool
    {
        $imageType = 'image' . $this->type;
        
        return $imageType(
            $destination,
            $this->parameterBag->get('app.images_directory').(string)$resizeWidth.'/'.$filename
        );
    }
    
    /**
     * resizesAndMoves
     *
     * for each needed width (defined in $this->widths)
     * the $this->resizeAndMove() method is called.
     * But if the image file is lower than the destination width,
     * then the file is resized with it's originals dimensions.
     * 
     */
    public function resizesAndMoves(
        UploadedFile $file,
        string $filename
    ): void {
        list($originalWidth, $originalHeight) = getimagesize($file);
        foreach ($this->widths as $resizeWidth) {
            if ($originalWidth > $resizeWidth) {
                $resizeHeight = ceil(($originalHeight * $resizeWidth)/$originalWidth);
                $this->resizeAndMove(
                    $file,
                    $filename,
                    $resizeWidth,
                    $resizeHeight,
                    $resizeWidth
                );
            } else {
                $this->resizeAndMove(
                    $file,
                    $filename,
                    $originalWidth,
                    $originalHeight,
                    $resizeWidth
                );
            }
        }
    }
    
    /**
     * resizeAndMove
     * 
     * $file is resize to create an image 
     *      with width=$destinationWidth and heigth=$destinationHeigth
     * And the created image is saved in corresponding directory : 
     *      %app.images_directory%/$directoryWidth/$filename
     */    
    public function resizeAndMove(
        UploadedFile $file,
        string $filename,
        int $destinationWidth,
        int $destinationHeight,
        int $directoryWidth
    ): void {
        $source = $this->imagecreatefromType($file);
        $destination = imagecreatetruecolor($destinationWidth, $destinationHeight);
        imagecopyresampled(
            $destination,
            $source,
            0, 0, 0, 0,
            imagesx($destination),
            imagesy($destination),
            imagesx($source),
            imagesy($source)
        );
        if (!$this->imageType($destination, $directoryWidth, $filename)) {
            throw new FileException("Le fichier " . $file->getClientOriginalName() . " n'a pas pu être traité.");
        }
    }
}
