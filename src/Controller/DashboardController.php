<?php

namespace App\Controller;

use App\Service\FormService;
use App\Service\SessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController {
    private $formService;
    private $sessionService;

    public function __construct(FormService $formService, SessionService $sessionService) {
        $this->formService = $formService;
        $this->sessionService = $sessionService;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response {
        $totalForms = $this->formService->getTotalForms();
        $totalSessions = $this->sessionService->getTotalSessions();

        return $this->render('dashboard/index.html.twig', [
            'totalForms' => $totalForms,
            'totalSessions' => $totalSessions,
            'leads' => 'DashboardController',
        ]);
    }

    #[Route('/dashboard/form/results', name: 'app_dashboard_form_results')]
    public function formResults(): Response {
        $results = $this->sessionService->getTotalFormSessions();
        return new JsonResponse(["status" => 200, 'results' => $results], 200);
    }
}
