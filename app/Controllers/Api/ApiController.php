<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;

abstract class ApiController
{
    protected Request $request;
    protected Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Send a standardized successful JSON response.
     */
    protected function success(mixed $data = null, string $message = 'Success', array $meta = null, int $status = 200): void
    {
        $this->response->apiSuccess($data, $message, $meta, $status);
    }

    /**
     * Send a standardized error JSON response.
     */
    protected function error(string $message, array $errors = null, int $status = 400): void
    {
        $this->response->apiError($message, $errors, $status);
    }
}
