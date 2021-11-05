<?php


namespace Controllers;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function getimagesize;
use function imagecopyresampled;
use function imagecreatefromjpeg;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagejpeg;
use function min;

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

        if ($origHeight < $maxHeight and $origWidth < $maxWidth) return true;


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
     * Parse and validate json string
     * @param string $json json data string
     * @return bool|string true if successfully parsed
     */
    public static function validateJson (string $json)
    {
        // decode the JSON data
        json_decode($json);

        // switch and check possible JSON errors
        switch (json_last_error())
        {
            case JSON_ERROR_NONE:
                return true;

            case JSON_ERROR_DEPTH:
                return "the maximum stack depth has been exceeded";

            case JSON_ERROR_STATE_MISMATCH:
                return "invalid or malformed json";

            case JSON_ERROR_CTRL_CHAR:
                return "control character error, possibly incorrectly encoded";

            case JSON_ERROR_SYNTAX:
                return "syntax error because malformed json";

            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                return "malformed utf8 characters, possibly incorrectly encoded";

            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                return "one or more recursive references in the value to be encoded";

            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                return "one or more nan or inf values in the value to be encoded";

            case JSON_ERROR_UNSUPPORTED_TYPE:
                return "a value of a type that cannot be encoded was given";

            default:
                return "unknown json error occurred";
        }
    }
}