<?php


namespace Controllers;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileController
{
    public const DirectoryNotExist = 0;
    public const RemovingException = 1;
    public const Successful = 2;

    /**
     * Method remove folder files recursively and then removes folder itself if other not specified
     * @param string $path path to folder that should be removed
     * @param bool $removeSelf if false, only files inside folder will be removed
     * @return int process result
     */
    public static function removeDirectory (string $path, bool $removeSelf = true)
    {
        if (!is_dir($path)) return FileController::DirectoryNotExist;
        try
        {
            $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file)
            {
                if ($file->isDir()) rmdir($file->getRealPath());
                else unlink($file->getRealPath());
            }
        } catch (Exception $e)
        {
            return FileController::RemovingException;
        }

        if ($removeSelf) return rmdir($path) ? FileController::Successful : FileController::RemovingException;
        else return FileController::Successful;
    }

    /**
     * Decode json string with unescaped content
     * @param string $json string with json object
     * @return mixed
     */
    public static function decodeJsonString (string $json)
    {
        return json_decode(
            $json, true, 512,

            JSON_UNESCAPED_UNICODE
            + JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Encode php object to pretty-printed json string
     * @param mixed $object php object
     * @return false|string
     */
    public static function encodeJsonString ($object)
    {
        return json_encode($object, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT);
    }

    public static function removeImageExifData ($path, $quality = 85)
    {
        $img = imagecreatefromjpeg($path);
        imagejpeg($img, $path, $quality);
        imagedestroy($img);
    }

    /**
     * Resize image - preserve ratio of width and height.
     * @param string $sourceImage path to source JPEG image
     * @param string $targetImage path to final JPEG image file
     * @param int $maxWidth maximum width of final image (value 0 - width is optional)
     * @param int $maxHeight maximum height of final image (value 0 - height is optional)
     * @param int $quality quality of final image (0-100)
     * @return bool
     */
    public static function resizeImage ($sourceImage, $targetImage, $maxWidth, $maxHeight, $quality = 85)
    {
        // Obtain image from given source file.
        if (!$image = @imagecreatefromjpeg($sourceImage)) return false;

        // Get dimensions of source image.
        list($origWidth, $origHeight) = getimagesize($sourceImage);

        if ($maxWidth == 0) $maxWidth = $origWidth;
        if ($maxHeight == 0) $maxHeight = $origHeight;


        // Calculate ratio of desired maximum sizes and original sizes.
        $widthRatio = $maxWidth / $origWidth;
        $heightRatio = $maxHeight / $origHeight;

        // Ratio used for calculating new image dimensions.
        $ratio = min($widthRatio, $heightRatio);

        // Calculate new image dimensions.
        $newWidth = (int)$origWidth * $ratio;
        $newHeight = (int)$origHeight * $ratio;

        // Create final image with new dimensions.
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagejpeg($newImage, $targetImage, $quality);

        // Free up the memory.
        imagedestroy($image);
        imagedestroy($newImage);

        return true;
    }

    /**
     * Example
     * resizeImage('image.jpg', 'resized.jpg', 200, 200);
     */
}