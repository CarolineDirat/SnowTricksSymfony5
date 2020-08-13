<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageProcessInterface
{    
    /**
     * execute
     *
     * For each possible mime type of an uploaded image file,
     * we define the extension corresponding to the mime type file
     * then the $this->resizesAndMoves() method is called
     *       
     * @param  UploadedFile $file   uploaded file from trick form
     * @param  string $filename     the new name of resize file (without it's extension)
     * 
     * @return string               the new name of resizes files, with it's extension
     *                              (corresponding to the file mime type)
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
     * Finally it return filename with it's extension; corresponding to the file mime type
     * 
     */
    public function resizesAndMoves(UploadedFile $file, string $filename): string;

    /**
     * resizeAndMove
     * 
     * $file is resize to create an image 
     *      with width=$destinationWidth and heigth=$destinationHeigth
     * And the created image is saved in corresponding directory, 
     *      with imageType() method : 
     *      %app.images_directory%/$directoryWidth/$filename
     */    
    public function resizeAndMove(
        UploadedFile $file,
        string $filename,
        int $destinationWidth,
        int $destinationHeight,
        int $directoryWidth
    ): void;
}
