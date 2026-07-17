<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FileUploadException;

final class FileUploadService extends BaseService
{
    private string $storagePath;
    private array $allowedMimeTypes = [
        'image/jpeg', 'image/png', 'image/jpg',
        'application/pdf',
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    private int $maxSizeBytes = 10 * 1024 * 1024; // 10MB default

    public function __construct(?string $storagePath = null)
    {
        parent::__construct();
        // Use a secure directory outside public access by default
        $this->storagePath = $storagePath ?? dirname(__DIR__, 2) . '/storage/uploads';
        
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Handle single or multiple file uploads from $_FILES array.
     * 
     * @param array $fileArray An element from $_FILES, e.g., $_FILES['attachments']
     * @return array Array of successfully uploaded file metadata
     * @throws FileUploadException
     */
    public function handleUpload(array $fileArray): array
    {
        $uploadedFiles = [];

        // Convert single upload array format to multi-upload format for uniform processing
        $files = $this->normalizeFileArray($fileArray);

        foreach ($files as $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue; // Skip empty slots
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new FileUploadException("Upload error code: " . $file['error']);
            }

            if ($file['size'] > $this->maxSizeBytes) {
                throw new FileUploadException("File '{$file['name']}' exceeds the maximum allowed size of " . ($this->maxSizeBytes / 1024 / 1024) . "MB.");
            }

            // Secure MIME Type Checking using finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
                throw new FileUploadException("File type '{$mimeType}' is not allowed for '{$file['name']}'.");
            }

            // Generate secure filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $secureFilename = bin2hex(random_bytes(16)) . '.' . $extension;
            
            // Organize into year/month directories
            $datePath = date('Y/m');
            $targetDir = $this->storagePath . '/' . $datePath;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $targetPath = $targetDir . '/' . $secureFilename;
            $relativePath = $datePath . '/' . $secureFilename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new FileUploadException("Failed to move uploaded file.");
            }

            $uploadedFiles[] = [
                'original_name' => basename($file['name']),
                'stored_name'   => $secureFilename,
                'file_path'     => $relativePath,
                'mime_type'     => $mimeType,
                'file_size'     => $file['size'],
                'is_image'      => str_starts_with($mimeType, 'image/')
            ];
        }

        return $uploadedFiles;
    }

    /**
     * Gets the absolute path on disk for a stored relative path.
     */
    public function getAbsolutePath(string $relativePath): string
    {
        return $this->storagePath . '/' . $relativePath;
    }

    private function normalizeFileArray(array $fileArray): array
    {
        $normalized = [];
        if (is_array($fileArray['name'])) {
            foreach ($fileArray['name'] as $key => $name) {
                $normalized[] = [
                    'name'     => $fileArray['name'][$key],
                    'type'     => $fileArray['type'][$key],
                    'tmp_name' => $fileArray['tmp_name'][$key],
                    'error'    => $fileArray['error'][$key],
                    'size'     => $fileArray['size'][$key],
                ];
            }
        } else {
            $normalized[] = $fileArray;
        }
        return $normalized;
    }
}
