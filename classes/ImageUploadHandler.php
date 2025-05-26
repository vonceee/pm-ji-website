<?php
/**
 * Image Upload Handler for Payment Screenshots
 * store images in file system and save file paths in database
 */

class ImageUploadHandler
{
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB

    public function __construct()
    {
        // create upload directory structure
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/uploads/payment-screenshots/';
        $this->ensureDirectoryExists();
    }

    /**
     * handle payment screenshot upload
     */
    public function handlePaymentScreenshotUpload($files, $bookingId)
    {
        try {
            if (!isset($files['payment_screenshot']) || $files['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }

            $file = $files['payment_screenshot'];

            // validate file
            $this->validateFile($file);

            // generate unique filename
            $filename = $this->generateFilename($file['name'], $bookingId);
            $fullPath = $this->uploadDir . $filename;

            // move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                throw new Exception('Failed to move uploaded file');
            }

            // create thumbnail for faster loading
            $thumbnailPath = $this->createThumbnail($fullPath, $filename);

            return [
                'success' => true,
                'filename' => $filename,
                'full_path' => $fullPath,
                'thumbnail' => $thumbnailPath,
                'file_size' => filesize($fullPath)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * validate uploaded file
     */
    private function validateFile($file)
    {
        // check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum limit of 5MB');
        }

        // check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed');
        }

        // check if file is actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception('File is not a valid image');
        }
    }

    /**
     * generate unique filename
     */
    private function generateFilename($originalName, $bookingId)
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8));

        return "payment_{$bookingId}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * create thumbnail for faster loading
     */
    private function createThumbnail($originalPath, $filename)
    {
        $thumbnailDir = $this->uploadDir . 'thumbnails/';
        $this->ensureDirectoryExists($thumbnailDir);

        $thumbnailPath = $thumbnailDir . 'thumb_' . $filename;

        // get original image info
        $imageInfo = getimagesize($originalPath);
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $imageType = $imageInfo[2];

        // calculate thumbnail dimensions (max 300px width)
        $maxWidth = 300;
        $ratio = $maxWidth / $originalWidth;
        $newWidth = $maxWidth;
        $newHeight = (int) ($originalHeight * $ratio);

        // create image resource based on type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $originalImage = imagecreatefromjpeg($originalPath);
                break;
            case IMAGETYPE_PNG:
                $originalImage = imagecreatefrompng($originalPath);
                break;
            case IMAGETYPE_GIF:
                $originalImage = imagecreatefromgif($originalPath);
                break;
            case IMAGETYPE_WEBP:
                $originalImage = imagecreatefromwebp($originalPath);
                break;
            default:
                return null;
        }

        // create thumbnail
        $thumbnailImage = imagecreatetruecolor($newWidth, $newHeight);

        // preserve transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($thumbnailImage, false);
            imagesavealpha($thumbnailImage, true);
        }

        // resize image
        imagecopyresampled(
            $thumbnailImage,
            $originalImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        // save thumbnail
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnailImage, $thumbnailPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnailImage, $thumbnailPath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnailImage, $thumbnailPath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumbnailImage, $thumbnailPath, 85);
                break;
        }

        // clean up memory
        imagedestroy($originalImage);
        imagedestroy($thumbnailImage);

        return 'thumb_' . $filename;
    }

    /**
     * ensure directory exists
     */
    private function ensureDirectoryExists($dir = null)
    {
        $directory = $dir ?: $this->uploadDir;

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // create .htaccess for security
        $htaccessPath = $directory . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Prevent direct access to uploaded files\n";
            $htaccessContent .= "Options -Indexes\n";
            $htaccessContent .= "<Files *.php>\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</Files>\n";
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }

    /**
     * delete old image files
     */
    public function deleteImageFiles($filename)
    {
        if ($filename) {
            $fullPath = $this->uploadDir . $filename;
            $thumbnailPath = $this->uploadDir . 'thumbnails/thumb_' . $filename;

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }

    /**
     * get image URL for display
     */
    public function getImageUrl($filename)
    {
        if (!$filename)
            return null;

        return '/NEW-PM-JI-RESERVIFY/uploads/payment-screenshots/' . $filename;
    }

    /**
     * get thumbnail URL for display
     */
    public function getThumbnailUrl($filename)
    {
        if (!$filename)
            return null;

        return '/NEW-PM-JI-RESERVIFY/uploads/payment-screenshots/thumbnails/thumb_' . $filename;
    }
}