<?php

namespace App\Controller;

use App\Entity\FormResults;
use App\Entity\Session;
use App\Service\FormSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ResultManager;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResultController extends AbstractController {
    private $entityManager;
    private $security;
    private $resultManager;
    private $userId;
    private $formSubmission;

    public function __construct(EntityManagerInterface $entityManager, Security $security, ResultManager $resultManager,  FormSubmission $formSubmission) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->resultManager = $resultManager;
        $this->formSubmission = $formSubmission;
        $this->userId = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null;
    }

    #[Route('dashboard/results', name: 'app_results')]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response {
        $formId = $request->query->get("form_id");
        // return results as dataTable
        $table = $this->resultManager->getAllResults($request, $dataTableFactory, $formId);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('dashboard/results/index.html.twig', ['datatable' => $table]);
    }

    #[Route('dashboard/results/{sessionId}', name: 'app_form_results')]
    public function get(int $sessionId): Response {
        $session = $this->entityManager->getRepository(Session::class)->findOneByIdAndUserId($sessionId, $this->userId);
        if (!$session) {
            throw new NotFoundHttpException('Session not authorized or does not exist.');
        }
        // Get session and results details  
        $data['details'] = $session;
        $data['results'] = $this->resultManager->getSessionResults($sessionId);
        return new JsonResponse(["status" => 200, "session" => $data], 200);
    }

    #[Route('dashboard/uploads/forms/{fileName}', name: 'app_download_file')]
    public function downloadFile(string $fileName): Response {
        $file = $this->entityManager->getRepository(FormResults::class)->findFileByNameAndUserId($fileName, $this->userId);
        if (!$file) {
            throw new NotFoundHttpException('File not authorized or does not exist.');
        }

        $fileLocation =  $this->formSubmission->filePath . '/' . $fileName;
        if (!file_exists($fileLocation)) {
            throw new NotFoundHttpException('File not found.');
        }

        $response = new BinaryFileResponse($fileLocation);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );

        return $response;
        return new JsonResponse(["status" => $fileLocation], 200);
    }
}
