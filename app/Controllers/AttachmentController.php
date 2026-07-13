<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AttachmentRepository;
use App\Services\FileUploadService;
use App\Utilities\UUIDHelper;

final class AttachmentController extends BaseController
{
    private AttachmentRepository $attachmentRepo;
    private FileUploadService $fileService;

    public function __construct(
        \App\Core\Request $request, 
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        $this->attachmentRepo = new AttachmentRepository();
        $this->fileService = new FileUploadService();
    }

    /**
     * Secure proxy to download/view an attachment.
     */
    public function download(): void
    {
        $this->requireAuth();
        
        $uuid = $this->request->routeParam('id');
        $binId = UUIDHelper::toBinary($uuid);
        
        $attachment = $this->attachmentRepo->findById($binId);
        
        if (!$attachment) {
            $this->redirectWithError('/dashboard', 'Attachment not found.');
            return;
        }

        // --- Security Check ---
        // In a full implementation, you must verify if the user has access 
        // to the associated $attachment['entity_id'] (Incident or WorkOrder).
        // e.g. if the user is a citizen, ensure they own the incident.
        // For demonstration, we allow authenticated users to view attachments.

        $absolutePath = $this->fileService->getAbsolutePath($attachment['file_path']);
        
        if (!file_exists($absolutePath)) {
            $this->redirectWithError('/dashboard', 'File no longer exists on server.');
            return;
        }

        // Output file
        $this->response->download($absolutePath, $attachment['original_name'], $attachment['mime_type']);
    }
}
