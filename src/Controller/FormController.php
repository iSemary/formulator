<?php

namespace App\Controller;

use App\Entity\Form;
use App\Entity\FormElement;
use App\Entity\FormField;
use App\Entity\FormSetting;
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
        return $this->render('dashboard/forms/editor.html.twig', ['elements' => $this->formElements]);
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

        $formId = $form->getId();

        // Save Form Settings
        $requestSettings = $request->get('settings');
        $this->saveFormSettings($formId, $requestSettings);
        // Save Form Fields
        $fields = $request->get('fields');
        $this->syncFormFields($formId, $fields);
        return new JsonResponse(["status" => 200], 200);
    }

    #[Route('dashboard/forms/edit/{id}', name: 'app_forms_edit')]
    public function edit($id): Response {
        $form = $this->entityManager->getRepository(Form::class)->findOneById($id);
        $form->settings = $this->prepareFormSettings($id);

        return $this->render('dashboard/forms/editor.html.twig', ['elements' => $this->formElements, 'form' => $form]);
    }

    #[Route('/dashboard/forms/{id}', name: 'app_forms_show')]
    public function show($id): JsonResponse {
        $form = $this->prepareFormForModify($id);
        return new JsonResponse(['form' => $form], 200);
    }

    private function syncFormFields(int $id, array $fields): void {
        $orderNumber = 0;
        foreach ($fields as $key => $field) {
            $formField = new FormField();
            $formField->setFormId($id);
            $formField->setTitle($field['title']);
            $formField->setDescription($field['description'] ?? "");
            $formField->setType($field['type']);
            $formField->setName("field_" . $key);
            $formField->setRequired($field['required'] ? 1 : 0);
            $formField->setOrderNumber($orderNumber);
            $formField->setCreatedAt(now());
            $formField->setUpdatedAt(now());
            $this->entityManager->persist($formField);
            $this->entityManager->flush();
            $orderNumber++;
        }
    }


    private function saveFormSettings(int $id, array $settings): void {
        foreach ($settings as $key => $requestSetting) {
            if (!empty($requestSetting)) {
                $formSetting = new FormSetting();
                $formSetting->setFormId($id);
                $formSetting->setSettingKey($key);
                $formSetting->setSettingValue($requestSetting);
                $formSetting->setCreatedAt(now());
                $formSetting->setUpdatedAt(now());
                $this->entityManager->persist($formSetting);
                $this->entityManager->flush();
            }
        }
    }

    private function generateUniqueHashName(): string {
        $hashName = Uuid::uuid4();
        $hashExists = $this->entityManager->getRepository(Form::class)->findByHashName($hashName);
        if ($hashExists) {
            $this->generateUniqueHashName();
        }
        return $hashName;
    }

    private function prepareFormForModify(int $id): array {
        $form['details'] = $this->entityManager->getRepository(Form::class)->findOneById($id);;
        $form['settings'] = $this->prepareFormSettings($id);
        $form['fields'] = $this->prepareFormFields($id);
        return $form;
    }

    private function prepareFormFields(int $id): array {
        $formattedFields = [];
        $fields = $this->entityManager->getRepository(FormField::class)->findByFormId($id);
        foreach ($fields as $key => $field) {
            $formattedFields[$key]['id'] = $field->getId();
            $formattedFields[$key]['title'] = $field->getTitle();
            $formattedFields[$key]['description'] = $field->getDescription();
            $formattedFields[$key]['type'] = $field->getType();
            $formattedFields[$key]['required'] = $field->getRequired();
            $formattedFields[$key]['order_number'] = $field->getOrderNumber();
        }
        return $formattedFields;
    }

    private function prepareFormSettings(int $id): array {
        $formattedSettings = [];
        $settings = $this->entityManager->getRepository(FormSetting::class)->findByFormId($id);
        foreach ($settings as $key => $setting) {
            $formattedSettings[$setting->getSettingKey()] = $setting->getSettingValue();
        }
        return $formattedSettings;
    }
}
