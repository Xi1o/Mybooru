<?php


namespace App\Service;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader {
    private $imageUploadDir;
    private $thumbnailUploadDir;
    private $imageUploadDirRelative;
    private $thumbnailUploadDirRelative;
    private $thumbnailDefaults;
    private $validExtensions;

    public function __construct($root, $imageUpload) {
        $this->imageUploadDir = $root . $imageUpload['image_upload_dir'];
        $this->thumbnailUploadDir = $root . $imageUpload['thumbnail_upload_dir'];
        $this->imageUploadDirRelative = $imageUpload['image_upload_dir'];
        $this->thumbnailUploadDirRelative = $imageUpload['thumbnail_upload_dir'];
        $this->thumbnailDefaults = $imageUpload['thumbnail_defaults'];
        $this->validExtensions = $imageUpload['valid_extensions'];
    }

    /**
     * @param UploadedFile $image
     * @param bool $withThumbnail
     * @throws FileException
     * @return array
     */
    public function upload(UploadedFile $image, bool $withThumbnail = false) {
        $extension = $image->guessExtension();
        if (!in_array($extension, $this->validExtensions)) {
            throw new InvalidArgumentException(
                $extension . ' wrong format must be: ' . implode(', ', $this->validExtensions)
            );
        }

        $fileName = md5(uniqid()) . '.' . $extension;
        $image->move($this->imageUploadDir, $fileName);

        $imagePath = $this->imageUploadDir . $fileName;
        $imagePathRelative = $this->imageUploadDirRelative . $fileName;
        $thumbnailPath = '';
        $thumbnailPathRelative = '';
        if ($withThumbnail) {
            $thumbnailPath = $this->thumbnailUploadDir . $fileName;
            $thumbnailPathRelative = $this->thumbnailUploadDirRelative . $fileName;
            if (!$this->createThumbnail($imagePath, $thumbnailPath)) {
                throw new FileException('Thumbnail could not be created');
            }
        }
        return [
            'image_path' => $imagePath
            , 'thumbnail_path' => $thumbnailPath
            , 'image_path_relative' => $imagePathRelative
            , 'thumbnail_path_relative' => $thumbnailPathRelative
        ];
    }

    /**
     * @param string $sourceImage
     * @param string $targetImage
     * @return bool
     */
    private function createThumbnail($sourceImage, $targetImage) {
        list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($sourceImage);

        switch ($sourceType) {
            case IMAGETYPE_GIF:
                $sourceGdImage = imagecreatefromgif($sourceImage);
                break;
            case IMAGETYPE_JPEG:
                $sourceGdImage = imagecreatefromjpeg($sourceImage);
                break;
            case IMAGETYPE_PNG:
                $sourceGdImage = imagecreatefrompng($sourceImage);
                break;
            default:
                return false;
        }

        if ($sourceGdImage === false) {
            return false;
        }

        $sourceRatio = $sourceWidth / $sourceHeight;
        $thumbnailRatio = $this->thumbnailDefaults['width'] / $this->thumbnailDefaults['height'];

        if ($sourceWidth <= $this->thumbnailDefaults['width'] && $sourceHeight <= $this->thumbnailDefaults['height']) {
            $thumbnailWidth = $sourceWidth;
            $thumbnailHeight = $sourceHeight;
        } elseif ($thumbnailRatio > $sourceRatio) {
            $thumbnailWidth = (int)($this->thumbnailDefaults['height'] * $sourceRatio);
            $thumbnailHeight = $this->thumbnailDefaults['height'];
        } else {
            $thumbnailWidth = $this->thumbnailDefaults['width'];
            $thumbnailHeight = (int)($this->thumbnailDefaults['width'] / $sourceRatio);
        }

        $thumbnailGdImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

        //Keep the transparency
        imagecolortransparent($thumbnailGdImage, imagecolorallocatealpha($thumbnailGdImage, 0, 0, 0, 127));
        imagealphablending($thumbnailGdImage, false);
        imagesavealpha($thumbnailGdImage, true);

        imagecopyresampled(
            $thumbnailGdImage,
            $sourceGdImage,
            0,
            0,
            0,
            0,
            $thumbnailWidth,
            $thumbnailHeight,
            $sourceWidth,
            $sourceHeight
        );

        switch ($sourceType) {
            case IMAGETYPE_GIF:
                imagegif($thumbnailGdImage, $targetImage);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnailGdImage, $targetImage, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnailGdImage, $targetImage, 9);
                break;
        }

        imagedestroy($sourceGdImage);
        imagedestroy($thumbnailGdImage);

        return true;
    }

}