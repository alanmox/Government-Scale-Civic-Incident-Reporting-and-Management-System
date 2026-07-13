<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Attachment Model
 * Maps to the attachments table for polymorphic file associations.
 */
class Attachment extends BaseModel
{
    use Traits\TimestampTrait;
    use Traits\SoftDeleteTrait;
    use Traits\UUIDTrait;

    protected function getFillable(): array
    {
        return [
            'id', 'entity_type', 'entity_id', 'uploader_id',
            'original_name', 'stored_name', 'file_path',
            'mime_type', 'file_size', 'is_image',
            'created_at', 'deleted_at'
        ];
    }
}
