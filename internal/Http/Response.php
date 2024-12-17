<?php

declare(strict_types=1);

namespace Internal\Http;

class Response
{
    protected int $statusCode = 200;
    protected string $contentType = 'text/html';

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }

    public function setContentType(string $type): self
    {
        $this->contentType = $type;
        header("Content-Type: {$type}");
        return $this;
    }

    public function send(string $body): void
    {
        echo $body;
        exit();
    }

    public function json($data): void
    {
        $this->setContentType("application/json");
        echo json_encode($data);
        exit();
    }

    /**
     * Renders Template to Frontend
     * takes template name and parameters
     * @return void
     */
    public function sendTemplate(string $templateName, array $parameters = [], bool $fullpath = false): void
    {
        $latte = new \Latte\Engine;
        $templateDirectory = __DIR__ . "/../../src/" . $_ENV['TEMPLATE_DIR'];
        $latte->setTempDirectory(__DIR__ . '/../../cache');
        if (!$fullpath) {
            $latte->setLoader(new \Latte\Loaders\FileLoader($templateDirectory));
            $latte->render($templateName, $parameters);
        } else {
            $latte->render($templateName, $parameters);
        }
    }

    /**
     * Redirects the response to a specific URL
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->setStatusCode($statusCode);
        header("Location: {$url}");
        exit();
    }
}
