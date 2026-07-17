<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\IncidentRepository;

final class MapController extends BaseController
{
    private IncidentRepository $incidentRepo;

    public function __construct(
        Request $request,
        Response $response
    ) {
        parent::__construct($request, $response);
        $this->incidentRepo = new IncidentRepository();
    }

    public function index(): void
    {
        $this->requireAuth();

        $incidents = $this->incidentRepo->findGeolocated();

        $this->view('map/index', [
            'pageTitle'   => 'Incident Map',
            'breadcrumbs' => [['label' => 'Incident Map']],
            'incidents'   => $incidents
        ]);
    }
}
