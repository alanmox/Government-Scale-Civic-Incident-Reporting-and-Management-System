<?php
declare(strict_types=1);
namespace App\Exceptions;

/** Thrown when a user cannot be authenticated (wrong credentials, locked account, etc.). */
class AuthenticationException extends AppException {}
