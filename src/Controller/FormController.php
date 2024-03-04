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
            ->add('actions', TextColumn::class, ['label' => "Actions", 'render' => function ($value, $row) {
                $buttons = sprintf('<a href="/dashboard/forms/edit/%u" class="btn btn-primary me-1">Edit</a>', $row->getId());
                $buttons .= sprintf('<a href="/dashboard/forms/edit/%u" class="btn btn-danger">Delete</a>', $row->getId());
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

    /**
     * The function stores a new form with its details, settings, and fields in a PHP application.
     * 
     * @param Request request The code snippet you provided is a PHP function that handles the storing of
     * form data in a database. It takes a Request object as a parameter, which likely contains the data
     * submitted from a form.
     * 
     * @return JsonResponse A JSON response with status code 200 is being returned.
     */
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

    #[Route('/dashboard/forms/{id}', name: 'app_forms_details')]
    public function returnFormDetails($id): JsonResponse {
        $form = $this->prepareFormForModify($id);
        return new JsonResponse(['form' => $form], 200);
    }

    #[Route('/forms/{hashName}', name: 'app_forms_show')]
    public function show($hashName) {
        $form = $this->prepareFormForSubmit($hashName);
        return $this->render('forms/index.html.twig', ['form' => $form]);
    }

    /**
     * The function syncFormFields takes an ID and an array of fields, creates FormField objects with the
     * provided data, and persists them to the database with an incremental order number.
     * 
     * @param int id The `id` parameter in the `syncFormFields` function represents the ID of the form to
     * which the form fields will be synced. This function takes an integer ID as the first parameter to
     * identify the form to which the form fields will be associated.
     * @param array fields The `syncFormFields` function takes an integer `` and an array `` as
     * parameters. The `` array contains information about form fields such as title, description,
     * type, required status, etc.
     */
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


    /**
     * The function `saveFormSettings` saves form settings to the database based on the provided settings
     * array.
     * 
     * @param int id The `id` parameter in the `saveFormSettings` function is an integer value that
     * represents the form ID to which the settings belong. This ID is used to associate the settings with
     * the specific form in the database.
     * @param array settings The `saveFormSettings` function takes an integer `` and an array
     * `` as parameters. The function iterates over the `` array, creates a new
     * `FormSetting` object for each non-empty setting, sets the form ID, setting key, setting value,
     * creation timestamp
     */
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

    /**
     * The function generates a unique hash name using UUID and recursively ensures its uniqueness in a
     * database table.
     * 
     * @return string a unique hash name generated using the `Uuid::uuid4()` method. If the generated hash
     * name already exists in the database, the function recursively calls itself to generate a new unique
     * hash name until a unique one is found.
     */
    private function generateUniqueHashName(): string {
        $hashName = Uuid::uuid4();
        $hashExists = $this->entityManager->getRepository(Form::class)->findByHashName($hashName);
        if ($hashExists) {
            $this->generateUniqueHashName();
        }
        return $hashName;
    }

    /**
     * The function prepareFormForModify retrieves form details, settings, and fields for a given form ID.
     * 
     * @param int id It looks like you were about to provide information about the `id` parameter in the
     * `prepareFormForModify` function, but the value is missing. Could you please provide the value of the
     * `id` parameter so I can assist you further?
     * 
     * @return array An array is being returned with three keys: 'details', 'settings', and 'fields'. The
     * 'details' key contains the form entity retrieved by its ID, the 'settings' key contains the form
     * settings prepared for the given ID, and the 'fields' key contains the form fields prepared for the
     * given ID.
     */
    private function prepareFormForModify(int $id): array {
        $form['fields'] = $this->prepareFormFields($id);
        return $form;
    }

    /**
     * The function prepares form details, settings, and fields for submission based on a given hash name.
     * 
     * @param string hashName It looks like you are working on a function `prepareFormForSubmit` that
     * prepares a form for submission. The function takes a parameter `` of type string.
     * 
     * @return array An array containing details, settings, and fields of a form is being returned.
     */
    private function prepareFormForSubmit(string $hashName): array {
        $form['details'] = $this->entityManager->getRepository(Form::class)->findOneByHashName($hashName);
        $form['settings'] = $this->prepareFormSettings($form['details']->getId());
        $form['fields'] = $this->prepareFormFields($form['details']->getId());
        return $form;
    }

    /**
     * The function `prepareFormFields` retrieves and formats form fields based on a given form ID.
     * 
     * @param int id The `prepareFormFields` function takes an integer parameter `` which represents the
     * form ID. This function retrieves form fields associated with the given form ID from the database and
     * formats them into an array with specific keys such as 'id', 'title', 'description', 'type',
     * 'required',
     * 
     * @return array An array of formatted form fields is being returned. Each element in the array
     * contains the following keys: 'id', 'title', 'description', 'type', 'required', and 'order_number',
     * with corresponding values extracted from the FormField entities fetched from the database based on
     * the provided form ID.
     */
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

    /**
     * The function `prepareFormSettings` retrieves and formats form settings based on the provided form
     * ID.
     * 
     * @param int id It looks like you are trying to prepare form settings by fetching them from the
     * database based on the provided form ID. The `prepareFormSettings` function takes an integer ID as a
     * parameter and returns an array of formatted settings.
     * 
     * @return array An array of formatted form settings is being returned from the `prepareFormSettings`
     * function. The settings are retrieved from the database based on the provided form ID, and then
     * formatted into an associative array where the setting key is the array key and the setting value is
     * the array value.
     */
    private function prepareFormSettings(int $id): array {
        $formattedSettings = [];
        $settings = $this->entityManager->getRepository(FormSetting::class)->findByFormId($id);
        foreach ($settings as $key => $setting) {
            $formattedSettings[$setting->getSettingKey()] = $setting->getSettingValue();
        }
        return $formattedSettings;
    }
}
