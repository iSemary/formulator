<?php

namespace App\Controller;

use App\Entity\Form;
use App\Entity\FormElement;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function Symfony\Component\Clock\now;

class FormController extends AbstractController {
    private $entityManager;
    private $security;
    private $formElements;
    private $userId;

    public function __construct(EntityManagerInterface $entityManager, Security $security) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->formElements = $this->entityManager->getRepository(FormElement::class)->findAll();
        $this->userId = $this->security->getUser()->getUserIdentifier();
    }

    #[Route('dashboard/forms', name: 'app_forms')]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response {
        // return forms as dataTable
        $forms = $this->entityManager->getRepository(Form::class)->findByUserId($this->userId);
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, ['label' => "ID"])
            ->add('title', TextColumn::class, ['label' => "Title"])
            ->add('created_at', DateTimeColumn::class, ['label' => "Created At"])
            ->add('actions', TextColumn::class, ['label' => "Actions", 'render' => function ($value) {
                $buttons = sprintf('<a href="/dashboard/forms/edit/%u" class="btn btn-primary me-1">Edit</a>', $value);
                $buttons .= sprintf('<a href="/dashboard/forms/edit/%u" class="btn btn-danger">Delete</a>', $value);
                return $buttons;
            }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Form::class,
            ])
            ->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('dashboard/forms/index.html.twig', ['datatable' => $table]);
    }

    #[Route('dashboard/forms/create', name: 'app_forms_create')]
    public function create(): Response {
        return $this->render('dashboard/forms/create.html.twig', ['elements' => $this->formElements]);
    }

    #[Route('dashboard/forms/store', name: 'app_forms_store')]
    public function store(Request $request): JsonResponse {
        // Create Form
        $form = new Form();
        $form->setUserId($this->userId);
        $form->setTitle($request->get('details')['detail_title']);
        $form->setDescription($request->get('details')['detail_description']);
        $form->setHashName($this->generateUniqueHashName());
        $form->setCreatedAt(now());
        $form->setUpdatedAt(now());
        $form->setStatus(1);
        $this->entityManager->persist($form);
        $this->entityManager->flush();
        // TODO Save Form Fields
        // TODO Save Form Settings

        return new JsonResponse(["status" => 200], 200);
    }

    #[Route('dashboard/forms/edit/{id}', name: 'app_forms_edit')]
    public function edit($id): Response {
        return $this->render('dashboard/forms/edit.html.twig', ['elements' => $this->formElements]);
    }

    #[Route('forms/{id}', name: 'app_forms_show')]
    public function show($id): Response {
        return $this->render('forms/index.html.twig', []);
    }

    public function generateUniqueHashName(): string {
        $hashName = Uuid::uuid4();
        $hashExists = $this->entityManager->getRepository(Form::class)->findByHashName($hashName);
        if ($hashExists) {
            $this->generateUniqueHashName();
        }
        return $hashName;
    }
}
