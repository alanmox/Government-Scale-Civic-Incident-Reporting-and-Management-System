<?php declare(strict_types=1);
namespace App\Interfaces;

/** Classes that support workflow state transitions. */
interface Workflowable
{
    public function getCurrentStage(): string;
    public function canTransitionTo(string $stage): bool;
    public function transitionTo(string $stage, string $actorId, string $notes = ''): bool;
}
