<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\SlaRepository;
use App\Utilities\UUIDHelper;
use App\Exceptions\ValidationException;

final class SlaService extends BaseService
{
    private SlaRepository $repository;

    public function __construct(SlaRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function getAllSlas(): array
    {
        return $this->repository->getAllWithCategories();
    }

    public function saveSla(string $userId, array $data): void
    {
        $categoryId = $data['category_id'] ?? '';
        $priority = $data['priority'] ?? '';
        $resolveHours = (int)($data['resolve_hours'] ?? 0);
        $escalateHours = (int)($data['escalate_hours'] ?? 0);

        if (empty($categoryId) || empty($priority)) {
            throw new ValidationException("Category and priority are required.");
        }

        if ($resolveHours <= 0 || $escalateHours <= 0) {
            throw new ValidationException("Hours must be greater than zero.");
        }

        if ($escalateHours >= $resolveHours) {
            throw new ValidationException("Escalation hours must be less than resolution hours.");
        }

        $existing = $this->repository->findByCategoryAndPriority($categoryId, $priority);

        $payload = [
            'category_id' => UUIDHelper::toBinary($categoryId),
            'priority' => $priority,
            'resolve_hours' => $resolveHours,
            'escalate_hours' => $escalateHours,
            'updated_by' => UUIDHelper::toBinary($userId),
        ];

        if ($existing) {
            $this->repository->update(UUIDHelper::toBinary($existing['id']), $payload);
        } else {
            $payload['id'] = UUIDHelper::toBinary(UUIDHelper::generate());
            $payload['created_by'] = UUIDHelper::toBinary($userId);
            $this->repository->create($payload);
        }
    }
    
    public function deleteSla(string $userId, string $id): void
    {
        $this->repository->softDelete(UUIDHelper::toBinary($id), UUIDHelper::toBinary($userId));
    }
}
