<?php

declare(strict_types=1);

namespace Internal\Http;

class Request
{
    protected array $query;
    protected array $body;
    protected array $server;
    protected array $files;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;

        // For JSON body requests, try to decode the raw input
        if ($this->getMethod() === 'POST' && $this->isJson()) {
            $input = file_get_contents('php://input');
            $this->body = json_decode($input, true) ?? [];
        }
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? "GET";
    }

    public function getPath(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? "/", PHP_URL_PATH);
    }

    /**
     * Get query parameters (GET parameters).
     * @template T
     * @param class-string<T> $modelType Optional class name to deserialize into.
     * @return T|array<string, mixed>
     */
    public function getQueryParams(string $modelType = null)
    {
        if ($modelType) {
            return $this->deserialize($this->query, $modelType);
        }
        return $this->query;
    }

    /**
     * Get the body of the request.
     * @template T
     * @param class-string<T> $modelType Optional class name to deserialize into.
     * @param bool $fromForm Whether to expect form data (default: true). If false, handles JSON.
     * @return T|array<string, mixed>
     */
    public function getBody(bool $fromForm = true, string $modelType = null)
    {
        if (!$fromForm && $this->isJson()) {
            $input = file_get_contents('php://input');
            $this->body = json_decode($input, true) ?? [];
        }

        if ($modelType) {
            return $this->deserialize($this->body, $modelType);
        }

        return $this->body;
    }

    /**
     * Get all uploaded files as FileModel instances.
     * @return array<FileModel>
     */
    public function getFiles(): array
    {
        $fileModels = [];
        foreach ($this->files as $key => $file) {
            $fileModels[$key] = new FileModel($file);
        }
        return $fileModels;
    }

    /**
     * Get a specific uploaded file by name as a FileModel instance.
     * @param string $key The file input field name.
     * @return FileModel|null
     */
    public function getFile(string $key): ?FileModel
    {
        if (isset($this->files[$key])) {
            return new FileModel($this->files[$key]);
        }
        return null;
    }

    /**
     * Check if the request is sending JSON.
     */
    protected function isJson(): bool
    {
        return isset($this->server['CONTENT_TYPE']) && stripos($this->server['CONTENT_TYPE'], 'application/json') !== false;
    }

    /**
     * Deserialize the data array into an object of the given type.
     * @template T
     * @param array<string, mixed> $data The array to deserialize.
     * @param class-string<T> $modelType The target class name.
     * @return T The deserialized object.
     */
    protected function deserialize(array $data, string $modelType)
    {
        if (!class_exists($modelType)) {
            throw new \InvalidArgumentException("Class {$modelType} does not exist.");
        }

        $object = new $modelType();

        foreach ($data as $key => $value) {
            if (property_exists($modelType, $key)) {
                $object->{$key} = $value;
            } else {
                throw new \InvalidArgumentException("Unexpected Value {$key} From Input Data.");
            }
        }

        if (method_exists($object, 'validate')) {
            $object->validate();
        }

        return $object;
    }
}
