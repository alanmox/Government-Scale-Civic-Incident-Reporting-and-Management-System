<?php
declare(strict_types=1);
namespace App\Exceptions;

/** Thrown when an authenticated user lacks permission to perform an action. */
class AuthorizationException extends AppException {}
