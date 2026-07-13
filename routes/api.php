<?php

declare(strict_types=1);

/** @var \App\Core\Router $router */

use App\Middleware\ApiAuthMiddleware;

$router->group(['prefix' => '/api/v1', 'middleware' => [ApiAuthMiddleware::class]], function ($router) {
    
    // Test endpoint
    $router->get('/ping', function() use ($router) {
        $response = new \App\Core\Response();
        $response->apiSuccess(['status' => 'ok', 'time' => date('Y-m-d H:i:s')]);
    });

    // Public/Verified Incidents Retrieval
    $router->get('/incidents', [\App\Controllers\Api\ApiIncidentController::class, 'index']);
    $router->get('/incidents/{id}', [\App\Controllers\Api\ApiIncidentController::class, 'show']);
    
});
