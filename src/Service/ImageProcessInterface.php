<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageProcessInterface
{    
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
     * and then, the $this->resizesAndMoves() method is called
     *       
     * @param  UploadedFile $file   uploaded file from trick form
     * @param  string $filename     the new name of resize file (without it's extension)
     */
    public function execute(UploadedFile $file, string $filename): string;
    
   /**
     * resizesAndMoves
     *
     * for each needed width (defined in $this->widths of ImageProcess class)
     * the $this->resizeAndMove() method is called.
     * But if the image file is lower than the destination width,
     * then the file is only resized with it's originals dimensions.
     * 
     */
    public function resizesAndMoves(
        UploadedFile $file,
        string $filename,
        callable $imagecreatefromType,
        callable $imageType
    ): void;

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
        int $destinationHeigth,
        int $directoryWidth,
        callable $imagecreatefromType,
        callable $imageType
    ): void;
}
