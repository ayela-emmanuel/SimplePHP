<?php

declare(strict_types=1);

namespace Internal\Http;

class FileModel
{
    public string $name;
    public string $type;
    public string $tmpName;
    public int $size;
    public int $error;

    /**
     * Constructor to initialize the file model.
     */
    public function __construct(array $fileData)
    {
        $this->name = $fileData['name'] ?? '';
        $this->type = $fileData['type'] ?? '';
        $this->tmpName = $fileData['tmp_name'] ?? '';
        $this->size = $fileData['size'] ?? 0;
        $this->error = $fileData['error'] ?? UPLOAD_ERR_NO_FILE;
    }

    /**
     * Validate the file size.
     * @param int $maxSize Maximum allowed size in bytes.
     * @throws \Exception if the file exceeds the maximum size.
     */
    public function validateSize(int $maxSize): void
    {
        if ($this->size > $maxSize) {
            throw new \Exception("File '{$this->name}' exceeds the maximum allowed size of {$maxSize} bytes.");
        }
    }

    /**
     * Validate the file type (MIME type).
     * @param array<string> $allowedTypes Array of allowed MIME types.
     * @throws \Exception if the file type is not allowed.
     */
    public function validateType(array $allowedTypes): void
    {
        if (!in_array($this->type, $allowedTypes, true)) {
            throw new \Exception("File '{$this->name}' has an invalid type '{$this->type}'. Allowed types are: " . implode(', ', $allowedTypes));
        }
    }

    /**
     * Check if the file was uploaded without any errors.
     * @throws \Exception if the file upload failed.
     */
    public function validateUpload(): void
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new \Exception("File '{$this->name}' failed to upload. Error code: {$this->error}");
        }
    }

    /**
     * Save the uploaded file to a specified directory.
     * @param string $directory The target directory where the file should be saved.
     * @param string|null $filename Optional new filename. If not provided, the original filename will be used.
     * @return string The full path of the saved file.
     * @throws \Exception if the file couldn't be moved or if the directory doesn't exist.
     */
    public function save(string $directory, ?string $filename = null): string
    {
        // Validate that the directory exists and is writable
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \Exception("The directory {$directory} does not exist or is not writable.");
        }

        // Use the provided filename or default to the original name
        $filename = $filename ?? $this->name;

        // Construct the full path to save the file
        $filePath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        // Move the file from temporary location to the target directory
        if (!move_uploaded_file($this->tmpName, $filePath)) {
            throw new \Exception("Failed to move the file to {$filePath}.");
        }

        return $filePath;
    }
}
