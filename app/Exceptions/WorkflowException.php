<?php
declare(strict_types=1);
namespace App\Exceptions;

/** Thrown when an invalid workflow state transition is attempted. */
class WorkflowException extends AppException {}
